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
    if (isArrayEmpty($data, ['expense_id', 'mutation_id', 'sale_id'])) {
      setLastError('One of expense, mutation or sale must be selected.');
      return false;
    }

    if (empty($data['amount'])) {
      setLastError('Amount is required.');
      return false;
    }

    if (empty($data['biller_id'])) {
      setLastError('Biller is required.');
      return false;
    }

    if (!empty($data['expense_id'])) {
      $expense = Expense::getRow(['id' => $data['expense_id']]);
      $data['reference']  = $expense->reference;
    }

    if (!empty($data['mutation_id'])) {
      $mutation = BankMutation::getRow(['id' => $data['mutation_id']]);
      $data['reference']    = $mutation->reference;
    }

    if (!empty($data['sale_id'])) {
      $sale = Sale::getRow(['id' => $data['sale_id']]);
      $data['reference']  = $sale->reference;
    }

    if (empty($data['status'])) {
      $data['status'] = 'pending';
    }

    $biller = Biller::getRow(['id' => $data['biller_id']]);
    $data['biller']  = $biller->code;

    $data['unique'] = self::getUniqueCode((int)$data['amount']);
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
   * (NEW) Get random unique code.
   */
  public static function getUniqueCode(int $amount)
  {
    $pvs = self::get(['status' => 'pending']);

    if ($pvs) {
      $amounts = [];

      foreach ($pvs as $pv) {
        $amounts[] = $pv->amount + $pv->unique;
      }

      while (1) {
        $uq = mt_rand(1, 100);

        if (!in_array($uq + $amount, $amounts)) {
          break;
        }
      }
    } else {
      $uq = mt_rand(1, 100);
    }

    return $uq;
  }

  /**
   * Get random unique code.
   * @deprecated Not valid unique code.
   */
  public static function getUniqueCode_()
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
      return true;
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
    $createdAt = ($option['date'] ?? date('Y-m-d H:i:s'));
    $startDate = date('Y-m-d H:i:s', strtotime('-1 day')); // We retrieve data from 7 days ago.
    $useManual = false;

    // Manual Validation.
    if (isset($option['manual']) && $option['manual']) {
      $useManual = true;

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

      $bank = Bank::getRow(['id' => $option['bank_id']]);

      if (!$bank) {
        setLastError('Bank is not found.');
        return false;
      }

      if (!isset($option['mutation_id']) && !isset($option['sale_id'])) {
        setLastError('Either mutation or sale must be selected.');
        return false;
      }

      $q = self::select('*')
        ->whereIn('status', ['expired', 'pending'])
        ->orderBy('id', 'DESC');

      if (isset($option['mutation_id'])) {
        $q->where('mutation_id', $option['mutation_id']);
      }

      if (isset($option['sale_id'])) {
        $q->where('sale_id', $option['sale_id']);
      }

      $pv = $q->getRow();

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

      // Since we need a manual validation.
      // We need a custom mutasibank data manually.
      $data = [
        'account' => $bank->number,
        'data'    => json_encode([
          'amount'      => $pv->amount + $pv->unique,
          'created'     => $createdAt,
          'description' => ($option['note'] ?? ''),
          'type'        => 'CR',
        ]),
        'module'  => 'manual'
      ];

      $insertId = MutasiBank::add($data);

      if (!$insertId) {
        return false;
      }
    } // End Manual Validation.

    $status = ['pending'];

    if (!$useManual) {
      // Delete old MutasiBank data.
      DB::table('mutasibank')
        ->whereIn('status', $status)
        ->where("created_at < '{$startDate} 00:00:00'")
        ->delete();
    }

    $mutasiBanks = DB::table('mutasibank')
      ->whereIn('status', $status)
      ->where("created_at >= '{$startDate} 00:00:00'")
      ->get();

    if (!$mutasiBanks) {
      setLastError('No mutasibank.');
      return false;
    }

    $paymentValidations = self::select('*')
      ->whereIn('status', $status)
      ->where("date >= '{$startDate} 00:00:00'")
      ->get();

    if (!$paymentValidations) {
      setLastError('No Payment Validation.');
      return false;
    }

    $validateTotal = 0;

    foreach ($mutasiBanks as $mb) {
      $dm = getJSON($mb->data);
      $validated = false;

      if ($dm->type != 'CR') continue; // Only incoming amount is accepted. CR = Credit, DB = Debit

      foreach ($paymentValidations as $pv) {
        if (intval($dm->amount) == intval($pv->amount + $pv->unique)) {
          $bank = Bank::getRow(['number' => $mb->account, 'biller_id' => $pv->biller_id]);

          $pvData = [
            'bank_id'           => $bank->id,
            'transaction_at'    => $dm->created,
            'transaction_date'  => $dm->created,
            'description'       => $dm->description,
            'status'            => 'verified'
          ];

          if ($useManual) {
            $pvData = setCreatedBy($pvData);
            $pvData['verified_at'] = null; // Manual not verified automatically.
            $pvData['description'] = '(MANUAL) ' . $pvData['description'];
          } else {
            $pvData['verified_at'] = date('Y-m-d H:i:s');
          }

          if (isset($option['attachment'])) {
            $pvData['attachment'] = $option['attachment'];
          }

          if (!self::update((int)$pv->id, $pvData)) {
            continue;
          }

          // Problem double payment. See if there is a double validate.
          $log = [
            'account' => $mb->account,
            'module'  => $dm->bank_module,
            'bank'    => [
              'id'      => $bank->id,
              'name'    => $bank->name,
              'number'  => $bank->number
            ],
            'dm'  => [
              'id'          => $dm->id,
              'amount'      => $dm->amount,
              'bank_module' => $dm->bank_module,
              'created'     => $dm->created,
              'description' => $dm->description,
              'type'        => $dm->type,
            ],
            'pv'  => [
              'id'          => $pv->id,
              'date'        => $pv->date,
              'biller'      => $pv->biller,
              'biller_id'   => $pv->biller_id,
              'mutation'    => $pv->mutation,
              'mutation_id' => $pv->mutation_id,
              'sale'        => $pv->sale,
              'sale_id'     => $pv->sale_id,
              'reference'   => $pv->reference,
              'amount'      => $pv->amount,
              'unique'      => $pv->unique,
              'status'      => $pv->status,
              'created_at'  => $pv->created_at,
              'created_by'  => $pv->created_by
            ],
            'pvData'      => $pvData,
            'created_at'  => $createdAt,
            'created_by'  => $pv->created_by
          ];

          log_message('notice', json_encode($log, JSON_PRETTY_PRINT));

          if ($pv->sale_id) {
            $sale = Sale::getRow(['id' => $pv->sale_id]);

            if (!$sale) {
              continue;
            }

            if ($sale->payment_status == 'paid') {
              continue;
            }

            $payment = [
              'amount'      => $pv->amount,
              'method'      => 'Transfer',
              'bank_id'     => $bank->id,
              'created_at'  => $createdAt,
              'created_by'  => $pv->created_by,
              'type'        => 'received',
              'note'        => '(MB) ' . $pvData['description']
            ];

            if (isset($option['attachment'])) {
              $payment['attachment'] = $option['attachment'];
            }

            if (!Sale::addPayment((int)$sale->id, $payment)) { // Add real payment and sync sales.
              continue;
            }

            $validated = true;
            $validateTotal++;
          }

          if ($pv->mutation_id) {
            $mutation = BankMutation::getRow(['id' => $pv->mutation_id]);

            if (!$mutation) {
              continue;
            }

            if ($mutation->status == 'paid') {
              continue;
            }

            $paymentFrom = [
              'date'        => $mutation->date,
              'mutation_id' => $mutation->id,
              'bank_id'     => $mutation->bankfrom_id,
              'biller_id'   => $mutation->biller_id,
              'method'      => 'Transfer',
              'amount'      => $mutation->amount + $pv->unique,
              'type'        => 'sent',
              'note'        => '(MB) ' . $pvData['description']
            ];

            if (isset($option['attachment'])) {
              $paymentFrom['attachment'] = $option['attachment'];
            }

            $insertId = Payment::add($paymentFrom);

            if (!$insertId) {
              continue;
            }

            $paymentTo = [
              'date'        => $mutation->date,
              'mutation_id' => $mutation->id,
              'bank_id'     => $mutation->bankto_id,
              'biller_id'   => $mutation->biller_id,
              'method'      => 'Transfer',
              'amount'      => $mutation->amount + $pv->unique,
              'type'        => 'received',
              'note'        => '(MB) ' . $pvData['description']
            ];

            if (isset($option['attachment'])) {
              $paymentTo['attachment'] = $option['attachment'];
            }

            $insertId = Payment::add($paymentTo);

            if (!$insertId) {
              continue;
            }

            $res = BankMutation::update((int)$mutation->id, [
              'status' => 'paid'
            ]);

            if (!$res) {
              continue;
            }

            $validated = true;
            $validateTotal++;
          }
        }
      }

      if ($validated) {
        DB::table('mutasibank')->update([
          'status'  => 'validated'
        ], ['id' => $mb->id]);
      } else {
        setLastError('Not validated.');
      }
    }

    return $validateTotal;
  }
}
