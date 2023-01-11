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
    return DB::insertID();
  }

  /**
   * Delete warehouses.
   * @param array $clause [ id, name, code ]
   */
  public static function delete(array $clause)
  {
    DB::table('warehouse')->delete($clause);
    return DB::affectedRows();
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
    return NULL;
  }

  /**
   * Update warehouses.
   * @param int $id warehouses ID.
   * @param array $data [ name, code ]
   */
  public static function update(int $id, array $data)
  {
    DB::table('warehouse')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
