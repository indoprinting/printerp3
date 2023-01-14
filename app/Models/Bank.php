<?php

declare(strict_types=1);

namespace App\Models;

class Bank
{
  /**
   * Add new Bank.
   */
  public static function add(array $data)
  {
<<<<<<< HEAD
=======
    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id;
    }

>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
    DB::table('banks')->insert($data);
    return DB::insertID();
  }

  /**
   * Get bank balance.
   */
<<<<<<< HEAD
  public static function balance($clause = [])
  {
    $res = Bank::select('(COALESCE(recv.total, 0) - COALESCE(sent.total, 0)) AS balance')
      ->join("(SELECT bank_id, SUM(amount) AS total FROM payment WHERE type LIKE 'received' GROUP BY bank_id) recv", 'recv.bank_id = bank.id', 'left')
      ->join("(SELECT bank_id, SUM(amount) AS total FROM payment WHERE type LIKE 'sent' GROUP BY bank_id) sent", 'sent.bank_id = bank.id', 'left')
      ->where($clause)
=======
  public static function balance(int $bankId)
  {
    $res = Bank::select('(COALESCE(recv.total, 0) - COALESCE(sent.total, 0)) AS balance')
      ->join("(SELECT bank_id, SUM(amount) AS total FROM payments WHERE type LIKE 'received' GROUP BY bank_id) recv", 'recv.bank_id = banks.id', 'left')
      ->join("(SELECT bank_id, SUM(amount) AS total FROM payments WHERE type LIKE 'sent' GROUP BY bank_id) sent", 'sent.bank_id = banks.id', 'left')
      ->where('banks.id', $bankId)
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
      ->getRow();

    if ($res) {
      return $res->balance;
    }

    return FALSE;
  }

  /**
   * Delete Bank.
   */
  public static function delete(array $where)
  {
    DB::table('banks')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get Bank collections.
   */
  public static function get($where = [])
  {
    return DB::table('banks')->get($where);
  }

  /**
   * Get Bank row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Bank.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('banks')->select($columns, $escape);
  }

  /**
   * Sync bank balance.
   */
  public static function sync(int $bankId = NULL)
  {
    $banks = [];

    if ($bankId) {
      $banks[] = self::getRow(['id' => $bankId]);
    } else {
      $banks = self::get();
    }

    if ($banks) {
      foreach ($banks as $bank) {
        $bank->balance = self::balance((int)$bank->id);
        self::update((int)$bank->id, ['amount' => $bank->balance]);
      }

      return TRUE;
    }
    return FALSE;
  }

  /**
   * Update Bank.
   */
  public static function update(int $id, array $data)
  {
<<<<<<< HEAD
=======
    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id;
    }

>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
    DB::table('banks')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
