<?php

declare(strict_types=1);

namespace App\Models;

class Supplier
{
  /**
   * Add new Supplier.
   */
  public static function add(array $data)
  {
    DB::table('suppliers')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete Supplier.
   */
  public static function delete(array $where)
  {
    DB::table('suppliers')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get Supplier collections.
   */
  public static function get($where = [])
  {
    return DB::table('suppliers')->get($where);
  }

  /**
   * Get Supplier row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Supplier.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('suppliers')->select($columns, $escape);
  }

  /**
   * Update Supplier.
   */
  public static function update(int $id, array $data)
  {
    DB::table('suppliers')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}