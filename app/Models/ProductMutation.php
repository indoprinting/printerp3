<?php

declare(strict_types=1);

namespace App\Models;

class ProductMutation
{
  /**
   * Add new ProductMutation.
   */
  public static function add(array $data)
  {
    DB::table('product_mutation')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete ProductMutation.
   */
  public static function delete(array $where)
  {
    DB::table('product_mutation')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get ProductMutation collections.
   */
  public static function get($where = [])
  {
    return DB::table('product_mutation')->get($where);
  }

  /**
   * Get ProductMutation row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select ProductMutation.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('product_mutation')->select($columns, $escape);
  }

  /**
   * Update ProductMutation.
   */
  public static function update(int $id, array $data)
  {
    DB::table('product_mutation')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
