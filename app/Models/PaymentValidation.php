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
   * @param array $option Validate options.
   */
  public static function validate($option = [])
  {
    $createdAt = date('Y-m-d H:i:s');
    $startDate = date('Y-m-d H:i:s', strtotime('-7 day')); // We retrieve data from 7 days ago.

    // Manual Validation.
    if (isset($option['manual']) && $option['manual']) {
      if (empty($option['amount'])) {
        setLastError('Amount is empty.');
        return false;
      }

      if (empty($option['bank_id'])) {
        setLastError('Bank is empty.');
        return false;
      }

      if (empty($option['biller_id'])) {
        setLastError('Biller is empty.');
        return false;
      }

      $date = ($option['date'] ?? date('Y-m-d H:i:s'));
      $bank = Bank::getRow(['id' => $option['bank_id']]);

      if (!$bank) {
        setLastError('Bank is not found.');
        return false;
      }

      $pv = self::select('*')
        ->whereIn('status', ['expired', 'pending'])
        ->groupStart()
        ->where('mutation_id', $option['mutation_id'])
        ->orWhere('sale_id', $option['sale_id'])
        ->groupEnd()
        ->orderBy('id', 'DESC')
        ->getRow();

      if (!$pv) {
        setLastError('Payment validation is not found.');
        return false;
      }

      if (floatval($pv->amount) != floatval($option['amount'])) {
        setLastError('The amount is not the same as Payment validation.');
        return false;
      }

      if ($pv->sale_id) {
        $sale = Sale::getRow(['id' => $pv->sale_id]);

        if (!$sale) {
          setLastError('Sale is not found.');
          return false;
        }

        if ($sale->payment_status == 'paid') {
          setLastError('Sale is already paid.');
          return false;
        }
      } else if ($pv->mutation_id) {
        $mutation = BankMutation::getRow(['id' => $pv->mutation_id]);

        if (!$mutation) {
          setLastError('Bank mutation is not found.');
          return false;
        }

        if ($mutation->status == 'paid') {
          setLastError('Bank mutation is already paid.');
          return false;
        }
      }

      $bmObject = [
        'account_number'  => $bank->number,
        'data_mutasi'     => [
          [
            'created'     => $date,
            'type'        => 'CR',
            'amount'      => floatval($pv->amount) + floatval($pv->unique),
            'description' => ($option['note'] ?? '')
          ]
        ]
      ];

      $insertId = MutasiBank::add([
        'data' => json_encode($bmObject)
      ]);

      if (!$insertId) {
        return false;
      }
    }

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

      if (!isset($mb->data_mutasi)) {
        $res = MutasiBank::delete(['id' => $mutasiBank->id]);

        if (!$res) {
          setLastError('Failed to delete Mutasibank. data_mutasi object is not present.');
          return false;
        }

        setLastError('data_mutasi object is not present. data_mutasi is deleted.');
        return false;
      }

      if (!is_array($mb->data_mutasi)) {
        $res = MutasiBank::delete(['id' => $mutasiBank->id]);

        if (!$res) {
          setLastError('Failed to delete Mutasibank. data_mutasi object is not an array.');
          return false;
        }

        setLastError('data_mutasi object is not an array. data_mutasi is deleted.');
        return false;
      }

      foreach ($mb->data_mutasi as $dm) {
        if ($dm->type != 'CR') continue; // Only incoming amount is accepted. CR = Credit, DB = Debit

        foreach ($paymentValidations as $pv) {
          if (intval($dm->amount) == intval($pv->amount + $pv->unique)) {
            $bank = Bank::getRow(['number' => $mb->account_number, 'biller_id' => $pv->biller_id]);

            $pvData = [
              'bank_id'           => $bank->id,
              'transaction_at'    => $dm->created,
              'transaction_date'  => $dm->created,
              'description'       => $dm->description,
              'note'              => $dm->description,
              'status'            => 'verified'
            ];

            if (isset($option['manual']) && $option['manual']) {
              $pvData = setCreatedBy($pvData);
              $pvData['verified_at'] = null; // Manual not verified automatically.
              $pvData['description'] = '(MANUAL) ' . $pvData['description'];
              $pvData['note'] = $pvData['description'];
            } else {
              $pvData['verified_at'] = date('Y-m-d H:i:s');
            }

            if (isset($option['attachment'])) {
              $pvData['attachment'] = $option['attachment'];
            }

            if (!self::update((int)$pv->id, $pvData)) {
              return false;
            }

            if ($pv->sale_id) {
              $sale = Sale::getRow(['id' => $pv->sale_id]);

              if (!$sale) {
                setLastError('Sale is not found.');
                return false;
              }

              if ($sale->payment_status == 'paid') {
                setLastError('Sale is already paid.');
                return false;
              }

              $payment = [
                'amount'      => $pv->amount,
                'method'      => 'Transfer',
                'bank_id'     => $bank->id,
                'created_at'  => $createdAt,
                'created_by'  => $pv->created_by,
                'type'        => 'received'
              ];

              if (isset($option['attachment'])) {
                $payment['attachment'] = $option['attachment'];
              }

              if (!Sale::addPayment((int)$sale->id, $payment)) { // Add real payment to sales.
                return false;
              }

              $validated++;
            }

            if ($pv->mutation_id) {
              $mutation = BankMutation::getRow(['id' => $pv->mutation_id]);

              if (!$mutation) {
                setLastError('Bank mutation is not found.');
                return false;
              }

              if ($mutation->status == 'paid') {
                setLastError('Bank mutation is already paid.');
                return false;
              }

              $paymentFrom = [
                'date'        => $mutation->date,
                'mutation'    => $mutation->reference,
                'bank'        => $mutation->bankfrom,
                'biller'      => $mutation->biller,
                'method'      => 'Transfer',
                'amount'      => $mutation->amount + $pv->unique,
                'type'        => 'sent',
                'note'        => $mutation->note
              ];

              if (isset($option['attachment'])) {
                $paymentFrom['attachment'] = $option['attachment'];
              }

              $insertId = Payment::add($paymentFrom);

              if (!$insertId) {
                return false;
              }

              $paymentTo = [
                'date'        => $mutation->date,
                'mutation'    => $mutation->reference,
                'bank'        => $mutation->bankto,
                'biller'      => $mutation->biller,
                'method'      => 'Transfer',
                'amount'      => $mutation->amount + $pv->unique,
                'type'        => 'received',
                'note'        => $mutation->note
              ];

              if (isset($option['attachment'])) {
                $paymentTo['attachment'] = $option['attachment'];
              }

              $insertId = Payment::add($paymentTo);

              if (!$insertId) {
                return false;
              }

              $res = BankMutation::update((int)$mutation->id, [
                'status' => 'paid'
              ]);

              if (!$res) {
                return false;
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
      } else {
        setLastError('Not validated.');
      }
    }

    return $validated;
  }
}
