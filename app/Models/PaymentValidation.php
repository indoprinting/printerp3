<?php

declare(strict_types=1);

namespace App\Models;

class PaymentValidation
{
  /**
   * Add new PaymentValidation.
   */
  public static function add(array $data)
  {
    if (isset($data['expense'])) {
      $expense = Expense::getRow(['reference' => $data['expense']]);
      $data['reference']  = $expense->reference;
      $data['expense_id'] = $expense->id;
    }

    if (isset($data['mutation'])) {
      $mutation = BankMutation::getRow(['reference' => $data['mutation']]);
      $data['reference']    = $mutation->reference;
      $data['mutation_id']  = $mutation->id;
    }

    if (isset($data['sale'])) {
      $sale = Sale::getRow(['reference' => $data['sale']]);
      $data['reference']  = $sale->reference;
      $data['sale_id']    = $sale->id;
    }

    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id']  = $biller->id;
    }

    if (empty($data['status'])) {
      $data['status'] = 'pending';
    }

    $data['unique'] = self::getUniqueCode();
    $data['unique_code'] = $data['unique']; // Compatibility.

    $data = setCreatedBy($data);
    $data = setExpired($data);

    DB::table('payment_validations')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete PaymentValidation.
   */
  public static function delete(array $where)
  {
    DB::table('payment_validations')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get PaymentValidation collections.
   */
  public static function get($where = [])
  {
    return DB::table('payment_validations')->get($where);
  }

  /**
   * Get PaymentValidation row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Get random unique code.
   */
  public static function getUniqueCode()
  {
    $pvs = self::get(['status' => 'pending']);

    if ($pvs) {
      $uqs = [];

      foreach ($pvs as $pv) {
        $uqs[] = $pv->unique_code;
      }

      while (1) {
        $uq = mt_rand(1, 200);

        if (array_search($uq, $uqs) === false) {
          break;
        }
      }
    } else {
      $uq = mt_rand(1, 200);
    }

    return $uq;
  }

  /**
   * Select PaymentValidation.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('payment_validations')->select($columns, $escape);
  }

  public static function sync()
  {
    $synced = false;

    $validations = self::get(['status' => 'pending']);

    if ($validations) {
      foreach ($validations as $pv) {
        if (time() > strtotime($pv->expired_at ?? $pv->expired_date)) { // Expired
          self::update((int)$pv->id, ['status' => 'expired']);

          if ($pv->sale_id) {
            Sale::update((int)$pv->sale_id, ['payment_status' => 'expired']);
            Sale::sync(['id' => $pv->sale_id]);
          }
          if ($pv->mutation_id) {
            BankMutation::update((int)$pv->mutation_id, ['status' => 'expired']);
          }

          $synced = true;
        }
      }
    }

    /* Set payment_status to pending or partial if sale payment_status == waiting_transfer but no payment validation. */
    $waiting_transfers = Sale::get(['payment_status' => 'waiting_transfer']);

    if ($waiting_transfers) {
      foreach ($waiting_transfers as $wt) {
        $pv = PaymentValidation::getRow(['sale_id' => $wt->id]);

        if (!$pv && ($wt->paid == 0)) {
          Sale::update((int)$wt->id, ['payment_status' => 'pending']);
        } else if (!$pv && ($wt->paid > 0 && $wt->paid < $wt->grand_total)) {
          Sale::update((int)$wt->id, ['payment_status' => 'partial']);
        }

        Sale::sync(['id' => $wt->id]);
      }
    }

    return $synced;
  }

  /**
   * Update PaymentValidation.
   */
  public static function update(int $id, array $data)
  {
    DB::table('payment_validations')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Validate payment validation by mutasibank.
   * @param bool $manual Determine if validation from manual.
   */
  public static function validate($manual = false)
  {
    $createdAt = date('Y-m-d H:i:s');
    $startDate = date('Y-m-d H:i:s', strtotime('-7 day')); // We retrieve data from 7 days ago.

    $status = ['pending'];

    $mutasiBanks = DB::table('mutasibank')
      ->whereIn('status', $status)
      ->where("created_at >= '{$startDate} 00:00:00'")
      ->get();

    if (!$mutasiBanks) {
      setLastError('No mutasibank.');
      return false;
    }

    $status = array_merge($status, ['expired']);

    $paymentValidations = self::select('*')
      ->whereIn('status', $status)
      ->where("date >= '{$startDate} 00:00:00'")
      ->get();

    if (!$paymentValidations) {
      setLastError('No Payment Validation.');
      return false;
    }

    $validated = 0;

    foreach ($mutasiBanks as $mutasiBank) {
      $mb = getJSON($mutasiBank->data);

      foreach ($mb->data_mutasi as $dm) {
        foreach ($paymentValidations as $pv) {
          if (intval($dm->amount) == intval($pv->amount + $pv->unique)) {
            $bank = Bank::getRow(['number' => $mb->account_number, 'biller_id' => $pv->biller_id]);

            $pvData = [
              'bank'              => $bank->code,
              'bank_id'           => $bank->id,
              'transaction_at'    => $dm->transaction_date,
              'transaction_date'  => $dm->transaction_date,
              'description'       => $dm->description,
              'note'              => $dm->description,
              'status'            => 'verified'
            ];

            if ($manual) {
              $pvData = setCreatedBy($pvData);
              $pvData['verified_at'] = null; // Manual not verified automatically.
              $pvData['description'] = '(MANUAL) ' . $pvData['description'];
              $pvData['note'] = $pvData['description'];
            } else {
              $pvData['verified_at'] = date('Y-m-d H:i:s');
            }

            self::update((int)$pv->id, $pvData);

            if ($pv->sale_id) {
              $sale = Sale::getRow(['id' => $pv->sale_id]);

              if ($sale->payment_status == 'paid') {
                setLastError('Sale is already paid.');
                return false;
              }

              $payment = [
                'amount'          => $pv->amount,
                'method'          => 'Transfer',
                'bank'            => $bank->code,
                'created_at'      => $createdAt,
                'created_by'      => $pv->created_by,
                'type'            => 'received'
              ];

              if ($sale->attachment) {
                $options['attachment'] = $sale->attachment;
              }

              Sale::addPayment((int)$sale->id, $payment); // Add real payment to sales.

              $validated++;
            }

            if ($pv->mutation_id) {
              $mutation = BankMutation::getRow(['id' => $pv->mutation_id]);

              if ($mutation->status == 'paid') {
                setLastError('Bank mutation is already paid.');
                return false;
              }

              $payment_from = [
                'created_at'      => date('Y-m-d H:i:s'),
                'date'            => $mutation->date,
                'mutation_id'     => $mutation->id,
                'bank_id'         => $mutation->from_bank_id,
                'method'          => 'Transfer',
                'amount'          => $mutation->amount + $pv->unique,
                'created_by'      => $mutation->created_by,
                'type'            => 'sent',
                'note'            => $mutation->note
              ];

              if ($mutation->attachment) {
                $options['attachment'] = $mutation->attachment;
              }

              if (Payment::add($payment_from)) {
                $payment_to = [
                  'created_at'  => date('Y-m-d H:i:s'),
                  'date'        => $mutation->date,
                  'mutation_id' => $mutation->id,
                  'bank_id'     => $mutation->to_bank_id,
                  'method'      => 'Transfer',
                  'amount'      => $mutation->amount + $pv->unique,
                  'created_by'  => $mutation->created_by,
                  'type'        => 'received',
                  'note'        => $mutation->note
                ];

                if (isset($options['attachment'])) $payment_to['attachment'] = $options['attachment'];

                if (Payment::add($payment_to)) {
                  BankMutation::update((int)$mutation->id, [
                    'status' => 'paid'
                  ]);
                }
              }

              $validated++;
            }
          }
        }
      }

      if ($validated) {
        DB::table('mutasibank')->update([
          'status' => 'validated',
          'validated' => $validated
        ], ['id' => $mutasiBank->id]);
      }
    }

    return $validated;
  }
}
