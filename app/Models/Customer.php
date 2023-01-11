<?php

declare(strict_types=1);

namespace App\Models;

class Customer
{
  /**
   * Add new Customer.
   */
  public static function add(array $data)
  {
    DB::table('customers')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete Customer.
   */
  public static function delete(array $where)
  {
    DB::table('customers')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get Customer collections.
   */
  public static function get($where = [])
  {
    return DB::table('customers')->get($where);
  }

  /**
   * Get Customer row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Customer.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('customers')->select($columns, $escape);
  }

  /**
   * Update Customer.
   */
  public static function update(int $id, array $data)
  {
    DB::table('customers')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
