<?php

declare(strict_types=1);

namespace App\Models;

class ProductCategory
{
  /**
   * Add new ProductCategory.
   */
  public static function add(array $data)
  {
    DB::table('categories')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete ProductCategory.
   */
  public static function delete(array $where)
  {
    DB::table('categories')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get ProductCategory collections.
   */
  public static function get($where = [])
  {
    return DB::table('categories')->get($where);
  }

  /**
   * Get ProductCategory row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select ProductCategory.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('categories')->select($columns, $escape);
  }

  /**
   * Update ProductCategory.
   */
  public static function update(int $id, array $data)
  {
    DB::table('categories')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
