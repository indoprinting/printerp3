<?php

declare(strict_types=1);

namespace App\Models;

class BankReconciliation
{
  /**
   * Add new BankReconciliation.
   */
  public static function add(array $data)
  {
    DB::table('bank_reconciliations')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete BankReconciliation.
   */
  public static function delete(array $where)
  {
    DB::table('bank_reconciliations')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get BankReconciliation collections.
   */
  public static function get($where = [])
  {
    return DB::table('bank_reconciliations')->get($where);
  }

  /**
   * Get BankReconciliation row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select BankReconciliation.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('bank_reconciliations')->select($columns, $escape);
  }

  /**
   * Sync BankReconciliation.
   */
  public static function sync()
  {
    $curl = curl_init(base_url('api/v1/mutasibank/accounts'));

    curl_setopt_array($curl, [
      CURLOPT_HEADER => FALSE,
      CURLOPT_RETURNTRANSFER => TRUE
    ]);

    $data = curl_exec($curl);

    if (!$data) {
      return FALSE;
    }

    $res = getJSON($data);

    if (!$res) {
      setLastError('Failed get data from api mutasibank accounts.');
      return FALSE;
    }

    $bankGroups = DB::table('banks')->select('number, name, holder, type')
      ->where('active', 1)
      ->where("number <> '2222004005'")
      ->whereIn('type', ['EDC', 'Transfer'])
      ->groupBy('number');

    $bankGroup = $bankGroups->get();

    $banks = Bank::get(['active' => 1]);

    foreach ($bankGroup as $row) { // Grouped by bank number.
      $mutasi_bank = null;
      $totalBalance = 0;

      foreach ($banks as $bank) { // Collect balance.
        if (strcmp(strval($row->number), strval($bank->number)) === 0) {
          $totalBalance += $bank->amount;
        }
      }

      foreach ($res->data as $mb) {
        if (strcmp(strval($mb->account_no), strval($row->number)) === 0) {
          $mutasi_bank = $mb;
          break;
        }
      }

      $recon = self::getRow(['account_no' => $row->number]);

      if ($recon) { // If exist, then update.
        $reconData = [
          'erp_acc_name'  => $row->holder,
          'account_no'    => $row->number,
          'amount_erp'    => $totalBalance
        ];

        if ($mutasi_bank) {
          $reconData['mb_acc_name']     = $mutasi_bank->account_name;
          $reconData['mb_bank_name']    = $mutasi_bank->bank;
          $reconData['amount_mb']       = $mutasi_bank->balance;
          $reconData['last_sync_date']  = $mutasi_bank->last_bot_activity;
        }

        self::update((int)$recon->id, $reconData);
      } else { // If not exist, insert new.
        $reconData = [
          'erp_acc_name' => $row->holder,
          'account_no'   => $row->number,
          'amount_erp'   => $totalBalance
        ];

        if ($mutasi_bank) {
          $reconData['mb_acc_name']    = $mutasi_bank->account_name;
          $reconData['mb_bank_name']   = $mutasi_bank->bank;
          $reconData['amount_mb']      = $mutasi_bank->balance;
          $reconData['last_sync_date'] = $mutasi_bank->last_bot_activity;
        }

        self::add($reconData);
      }
    }

    return TRUE;
  }

  /**
   * Update BankReconciliation.
   */
  public static function update(int $id, array $data)
  {
    DB::table('bank_reconciliations')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
