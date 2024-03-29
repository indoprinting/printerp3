<?php

declare(strict_types=1);

namespace App\Models;

class Voucher
{
  /**
   * Add new Voucher.
   */
  public static function add(array $data)
  {
    $data = setCreatedBy($data);

    DB::table('voucher')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Voucher.
   */
  public static function delete(array $where)
  {
    DB::table('voucher')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Voucher collections.
   */
  public static function get($where = [])
  {
    return DB::table('voucher')->get($where);
  }

  /**
   * Get Voucher row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Voucher.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('voucher')->select($columns, $escape);
  }

  /**
   * Update Voucher.
   */
  public static function update(int $id, array $data)
  {
    DB::table('voucher')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
