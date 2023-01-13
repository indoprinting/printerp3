<?php

declare(strict_types=1);

namespace App\Models;

class CustomerGroup
{
  /**
   * Add new CustomerGroup.
   */
  public static function add(array $data)
  {
    DB::table('customergroup')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete CustomerGroup.
   */
  public static function delete(array $where)
  {
    DB::table('customergroup')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get CustomerGroup collections.
   */
  public static function get($where = [])
  {
    return DB::table('customergroup')->get($where);
  }

  /**
   * Get CustomerGroup row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select CustomerGroup.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('customergroup')->select($columns, $escape);
  }

  /**
   * Update CustomerGroup.
   */
  public static function update(int $id, array $data)
  {
    DB::table('customergroup')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
