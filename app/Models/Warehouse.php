<?php

declare(strict_types=1);

namespace App\Models;

class Warehouse
{
  /**
   * Add new warehouses.
   * @param array $data [ name, code ]
   */
  public static function add(array $data)
  {
    DB::table('warehouse')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete warehouses.
   * @param array $clause [ id, name, code ]
   */
  public static function delete(array $clause)
  {
    DB::table('warehouse')->delete($clause);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get warehouses collections.
   * @param array $clause [ id, name, code ]
   */
  public static function get($clause = [])
  {
    return DB::table('warehouse')->get($clause);
  }

  /**
   * Get warehouses row.
   * @param array $clause [ id, name, code ]
   */
  public static function getRow($clause = [])
  {
    if ($rows = self::get($clause)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Warehouse.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('warehouse')->select($columns, $escape);
  }

  /**
   * Update warehouses.
   * @param int $id warehouses ID.
   * @param array $data [ name, code ]
   */
  public static function update(int $id, array $data)
  {
    DB::table('warehouse')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
