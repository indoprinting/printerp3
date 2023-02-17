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

    if ($insertID = DB::insertID()) {
      return $insertID;
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

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
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

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Validate payment validation.
   * @param string $response Response from Mutasibank.
   * @param array $options [ sale_id, mutation_id, attachment ]
   */
  public static function validate(string $response, array $options = [])
  {
    if (empty($response)) {
      return false;
    }

    $paymentValidated = false;
    $mbResponse = getJSON($response);

    self::sync(); // Change pending payment to expired if any.
    return true;
    $saleId     = ($options['sale_id'] ?? null);
    $mutationId = ($options['mutation_id'] ?? null);
    // Expired required for manual validation.
    $status = ($saleId || $mutationId ? ['expired', 'pending'] : 'pending');
    $paymentValidation = self::get(['status' => $status]);
    $validatedCount = 0;

    if ($paymentValidation) {
      foreach ($paymentValidation as $pv) {
        $accountNo  = $mbResponse->account_number;
        $dataMutasi = $mbResponse->data_mutasi;

        foreach ($dataMutasi as $dm) { // DM = Data Mutasi.
          $amount_match = ((floatval($pv->amount) + floatval($pv->unique)) == floatval($dm->amount) ? true : false);
          // If amount same as unique_code + amount OR sale_id same OR mutation_id same
          // Executed by CRON or Manually.
          // CR(mutasibank) = Masuk ke rekening.
          // DB(mutasibank) = Keluar dari rekening.
          if (
            ($amount_match && $dm->type == 'CR') ||
            ($saleId && $saleId == $pv->sale_id) || ($mutationId && $mutationId == $pv->mutation_id)
          ) {

            $bank = Bank::getRow(['number' => $accountNo, 'biller_id' => $pv->biller_id]);

            if (!$bank) {
              die('Bank not defined');
            }

            $data = [
              'bank_id'           => $bank->id,
              'transaction_date'  => $dm->transaction_date,
              'description'       => $dm->description,
              'status'            => 'verified'
            ];

            if (!empty($options['manual'])) {
              $data = setCreatedBy($data);
              $data['description'] = '(MANUAL) ' . $data['description'];
            } else {
              $data['verified_at'] = date('Y-m-d H:i:s');
            }

            if (self::update((int)$pv->id, $data)) {
              if ($pv->sale_id) { // If sale_id exists.
                $sale = Sale::getRow(['id' => $pv->sale_id]);

                $payment = [
                  'sale'        => $sale->reference,
                  'amount'      => $pv->amount,
                  'method'      => 'Transfer',
                  'bank'        => $bank->code,
                  'created_by'  => $pv->created_by,
                ];

                if (isset($options['attachment'])) $payment['attachment'] = $options['attachment'];

                Sale::addPayment((int)$payment['sale_id'], $payment);
                $customer = Customer::getRow(['id' => $sale->customer_id]);

                if ($customer && $amount_match) { // Restore unique code as deposit for customer if amount match.
                  Customer::update((int)$sale->customer_id, [
                    'deposit_amount' => $customer->deposit_amount + $pv->unique_code
                  ]);
                }

                $validatedCount++;
              }

              if ($pv->mutation_id) { // If mutation_id exists.
                $mutation = BankMutation::getRow(['id' => $pv->mutation_id]);

                $paymentFrom = [
                  'reference_date'  => $mutation->date,
                  'mutation'        => $mutation->reference,
                  'bank'            => $mutation->bankfrom,
                  'method'          => 'Transfer',
                  'amount'          => $mutation->amount + $pv->unique_code,
                  'created_by'      => $mutation->created_by,
                  'type'            => 'sent',
                  'note'            => $mutation->note
                ];

                if (isset($options['attachment'])) $paymentFrom['attachment'] = $options['attachment'];

                if (Payment::add($paymentFrom)) {
                  $paymentTo = [
                    'reference_date'  => $mutation->date,
                    'mutation'        => $mutation->reference,
                    'bank'            => $mutation->bankto,
                    'method'          => 'Transfer',
                    'amount'          => $mutation->amount + $pv->unique_code,
                    'created_by'      => $mutation->created_by,
                    'type'            => 'received',
                    'note'            => $mutation->note
                  ];

                  if (isset($options['attachment'])) $paymentTo['attachment'] = $options['attachment'];

                  if (Payment::add($paymentTo)) {
                    BankMutation::update((int)$mutation->id, [
                      'status' => 'paid'
                    ]);
                  }
                }

                $validatedCount++;
              }

              $paymentValidated = true;
            }
          }
        }
      }

      if ($paymentValidated) return $validatedCount;
    }
    return false;
  }
}
