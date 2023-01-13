<?php

declare(strict_types=1);

namespace App\Models;

class Income
{
  /**
   * Add new Income.
   */
  public static function add(array $data)
  {
    $data = setCreatedBy($data);

    DB::table('incomes')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete Income.
   */
  public static function delete(array $where)
  {
    DB::table('incomes')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get Income collections.
   */
  public static function get($where = [])
  {
    return DB::table('incomes')->get($where);
  }

  /**
   * Get Income row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Income.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('incomes')->select($columns, $escape);
  }

  /**
   * Update Income.
   */
  public static function update(int $id, array $data)
  {
    DB::table('incomes')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
