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
    $data['reference'] = OrderRef::getReference('mutation');

    $data = setCreatedBy($data);

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
    return null;
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
