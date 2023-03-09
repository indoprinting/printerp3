<?php

declare(strict_types=1);

namespace App\Models;

class ProductPrice
{
  /**
   * Add new ProductPrice.
   */
  public static function add(array $data)
  {
    DB::table('product_prices')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete ProductPrice.
   */
  public static function delete(array $where)
  {
    DB::table('product_prices')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get ProductPrice collections.
   */
  public static function get($where = [])
  {
    return DB::table('product_prices')->get($where);
  }

  /**
   * Get ProductPrice row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select ProductPrice.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('product_prices')->select($columns, $escape);
  }

  /**
   * Update ProductPrice.
   */
  public static function update(int $id, array $data)
  {
    DB::table('product_prices')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
