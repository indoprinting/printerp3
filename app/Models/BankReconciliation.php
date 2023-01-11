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
    return DB::insertID();
  }

  /**
   * Delete BankReconciliation.
   */
  public static function delete(array $where)
  {
    DB::table('bank_reconciliations')->delete($where);
    return DB::affectedRows();
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
    return NULL;
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

    $res = json_decode($data);

    echo '<pre>';
    print_r($res);
    echo '</pre>';
    die;

    if (!$res) {
      setLastError('Failed get data from api mutasibank accounts.');
      return FALSE;
    }

    $banks = Bank::select('banks.number, banks.name, banks.holder, banks.type')
      ->where('active', 1)
      ->where("number <> '2222004005'")
      ->notLike('type', 'Cash')
      ->groupBy('number');

    foreach ($banks->get() as $row) { // Grouped by bank number.
      $banks = Bank::get(['active' => 1]);
      $mutasi_bank = NULL;
      $totalBalance = 0;

      foreach ($banks as $bank) { // Collect balance.
        if (strcmp($row->number, $bank->number) === 0) {
          $totalBalance += $bank->amount;
        }
      }

      foreach ($res as $mb) {
        if (strcmp($mb->account_no, $row->number) === 0) {
          $mutasi_bank = $mb;
          break;
        }
      }

      $recon = self::getRow(['account_no' => $row->number]);

      if ($recon) { // If exist, then update.
        $recon_data = [
          'erp_acc_name' => $row->holder,
          'account_no'   => $row->number,
          'amount_erp'   => $totalBalance
        ];

        if ($mutasi_bank) {
          $recon_data['mb_acc_name']    = $mutasi_bank->account_name;
          $recon_data['mb_bank_name']   = $mutasi_bank->bank;
          $recon_data['amount_mb']      = $mutasi_bank->balance;
          $recon_data['last_sync_date'] = $mutasi_bank->last_bot_activity;
        }

        self::update((int)$recon->id, $recon_data);
      } else { // If not exist, insert new.
        $recon_data = [
          'erp_acc_name' => $row->holder,
          'account_no'   => $row->number,
          'amount_erp'   => $totalBalance
        ];

        if ($mutasi_bank) {
          $recon_data['mb_acc_name']    = $mutasi_bank->account_name;
          $recon_data['mb_bank_name']   = $mutasi_bank->bank;
          $recon_data['amount_mb']      = $mutasi_bank->balance;
          $recon_data['last_sync_date'] = $mutasi_bank->last_bot_activity;
        }

        self::add($recon_data);
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
    return DB::affectedRows();
  }
}
