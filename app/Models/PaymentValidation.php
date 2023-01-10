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
    if (!empty($data['sale'])) {
      $sale = Sale::getRow(['reference' => $data['sale']]);
      $data['reference']  = $sale->reference;
      $data['sale_id']    = $sale->id; // Obsolete
    }

    if (!empty($data['mutation'])) {
      $mutation = BankMutation::getRow(['reference' => $data['mutation']]);
      $data['reference']    = $mutation->reference;
      $data['mutation_id']  = $mutation->id; // Obsolete
    }

    if (!empty($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id']  = $biller->id; // Obsolete
    }

    if (empty($data['status'])) {
      $data['status'] = 'pending';
    }

    $data['unique_code'] = self::getUniqueCode();

    $data = setCreatedBy($data);
    $data = setExpired($data);

    DB::table('payment_validations')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete PaymentValidation.
   */
  public static function delete(array $where)
  {
    DB::table('payment_validations')->delete($where);
    return DB::affectedRows();
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
    return NULL;
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

        if (array_search($uq, $uqs) === FALSE) {
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
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('payment_validations')->select($columns, $escape);
  }

  public static function sync()
  {
    $synced = FALSE;

    $validations = self::get(['status' => 'pending']);

    if ($validations) {
      foreach ($validations as $pv) {
        if (time() > strtotime($pv->expired_at ?? $pv->expired_date)) { // Expired
          self::update((int)$pv->id, ['status' => 'expired']);

          if ($pv->sale_id) {
            Sale::update((int)$pv->sale_id, ['payment_status' => 'expired']);
            Sale::sync();
          }
          if ($pv->mutation_id) {
            BankMutation::update((int)$pv->mutation_id, ['status' => 'expired']);
          }
          $synced = TRUE;
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

        Sale::sync(['sale_id' => $wt->id]);
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
    return DB::affectedRows();
  }

  /**
   * Validate payment validation.
   */
  public static function validate(string $response, array $options = [])
  {
    if (empty($response)) {
      return FALSE;
    }

    $paymentValidated = FALSE;
    $mb_res = getJSON($response);

    self::sync(); // Change pending payment to expired if any.
    return true;
    $sale_id     = ($options['sale_id'] ?? NULL);
    $mutation_id = ($options['mutation_id'] ?? NULL);
    $status = ($sale_id || $mutation_id ? ['expired', 'pending'] : 'pending');
    // $status = ($sale_id || $mutation_id ? ['pending'] : 'pending'); // New
    $paymentValidation = self::get(['status' => $status]);
    $validatedCount = 0;

    if ($paymentValidation) {
      foreach ($paymentValidation as $pv) {
        $accountNo  = $mb_res->account_number;
        $dataMutasi = $mb_res->data_mutasi;

        foreach ($dataMutasi as $dm) { // DM = Data Mutasi.
          $amount_match = ((floatval($pv->amount) + floatval($pv->unique_code)) == floatval($dm->amount) ? TRUE : FALSE);
          // If amount same as unique_code + amount OR sale_id same OR mutation_id same
          // Executed by CRON or Manually.
          // CR(mutasibank) = Masuk ke rekening.
          // DB(mutasibank) = Keluar dari rekening.
          if (
            ($amount_match && $dm->type == 'CR') ||
            ($sale_id && $sale_id == $pv->sale_id) || ($mutation_id && $mutation_id == $pv->mutation_id)
          ) {

            $bank = Bank::getRow(['number' => $accountNo, 'biller_id' => $pv->biller_id]);

            if (!$bank) {
              die('Bank not defined');
            }

            $pv_data = [
              'bank_id'           => $bank->id,
              'transaction_date'  => $dm->transaction_date,
              'description'       => $dm->description,
              'status'            => 'verified'
            ];

            if (!empty($options['manual'])) {
              $pv_data = setCreatedBy($pv_data);
              $pv_data['description'] = '(MANUAL) ' . $pv_data['description'];
            }

            if (self::update((int)$pv->id, $pv_data)) {
              if ($pv->sale_id) { // If sale_id exists.
                $sale = Sale::getRow(['id' => $pv->sale_id]);

                $payment = [
                  'reference_date'  => $sale->created_at,
                  'sale_id'         => $pv->sale_id,
                  'amount'          => $pv->amount,
                  'method'          => 'Transfer',
                  'bank_id'         => $bank->id,
                  'created_by'      => $pv->created_by,
                  'type'            => 'received'
                ];

                if (isset($options['attachment_id'])) $payment['attachment_id'] = $options['attachment_id'];

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
                  'mutation_id'     => $mutation->id,
                  'bank_id'         => $mutation->from_bank_id,
                  'method'          => 'Transfer',
                  'amount'          => $mutation->amount + $pv->unique_code,
                  'type'            => 'sent',
                  'note'            => $mutation->note
                ];

                if (isset($options['attachment_id'])) $paymentFrom['attachment_id'] = $options['attachment_id'];

                if (Payment::add($paymentFrom)) {
                  $paymentTo = [
                    'mutation_id' => $mutation->id,
                    'bank_id'     => $mutation->to_bank_id,
                    'method'      => 'Transfer',
                    'amount'      => $mutation->amount + $pv->unique_code,
                    'created_by'  => $mutation->created_by,
                    'type'        => 'received',
                    'note'        => $mutation->note
                  ];

                  if (isset($options['attachment_id'])) $paymentTo['attachment_id'] = $options['attachment_id'];

                  if (Payment::add($paymentTo)) {
                    BankMutation::update((int)$mutation->id, [
                      'status' => 'paid'
                    ]);
                  }
                }

                $validatedCount++;
              }

              $paymentValidated = TRUE;
            }
          }
        }
      }

      if ($paymentValidated) return $validatedCount;
    }
    return FALSE;
  }
}
