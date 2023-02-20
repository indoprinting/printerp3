<?php

declare(strict_types=1);

namespace App\Models;

class BankMutation
{
  /**
   * Add new BankMutation.
   */
  public static function add(array $data)
  {
    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id;
    }

    if (isset($data['bankfrom'])) {
      $bankFrom = Bank::getRow(['code' => $data['bankfrom']]);
      $data['from_bank_id'] = $bankFrom->id;
      $data['from_bank_name'] = $bankFrom->name;
    }

    if (isset($data['bankto'])) {
      $bankTo = Bank::getRow(['code' => $data['bankto']]);
      $data['to_bank_id'] = $bankTo->id;
      $data['to_bank_name'] = $bankTo->name;
    }

    $data = setCreatedBy($data);
    $data['reference'] = OrderRef::getReference('mutation');

    DB::table('bank_mutations')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();
      OrderRef::updateReference('mutation');

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete BankMutation.
   */
  public static function delete(array $where)
  {
    DB::table('bank_mutations')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get BankMutation collections.
   */
  public static function get($where = [])
  {
    return DB::table('bank_mutations')->get($where);
  }

  /**
   * Get BankMutation row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select BankMutation.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('bank_mutations')->select($columns, $escape);
  }

  /**
   * Update BankMutation.
   */
  public static function update(int $id, array $data)
  {
    DB::table('bank_mutations')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
