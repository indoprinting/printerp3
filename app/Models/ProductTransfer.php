<?php

declare(strict_types=1);

namespace App\Models;

class ProductTransfer
{
  /**
   * Add new ProductTransfer.
   */
  public static function add(array $data)
  {
    DB::table('product_transfer')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete ProductTransfer.
   */
  public static function delete(array $where)
  {
    DB::table('product_transfer')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get ProductTransfer collections.
   */
  public static function get($where = [])
  {
    return DB::table('product_transfer')->get($where);
  }

  /**
   * Get ProductTransfer row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select ProductTransfer.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('product_transfer')->select($columns, $escape);
  }

  /**
   * Update ProductTransfer.
   */
  public static function update(int $id, array $data)
  {
    DB::table('product_transfer')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}