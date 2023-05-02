<?php

declare(strict_types=1);

namespace App\Models;

class ProductPurchase
{
  /**
   * Add new ProductPurchase.
   */
  public static function add(array $data)
  {
    DB::table('purchases')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete ProductPurchase.
   */
  public static function delete(array $where)
  {
    DB::table('purchases')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get ProductPurchase collections.
   */
  public static function get($where = [])
  {
    return DB::table('purchases')->get($where);
  }

  /**
   * Get ProductPurchase row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select ProductPurchase.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('purchases')->select($columns, $escape);
  }

  /**
   * Update ProductPurchase.
   */
  public static function update(int $id, array $data)
  {
    DB::table('purchases')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
