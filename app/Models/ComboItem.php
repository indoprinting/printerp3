<?php

declare(strict_types=1);

namespace App\Models;

class ComboItem
{
  /**
   * Add new ComboItem.
   */
  public static function add(array $data)
  {
    DB::table('combo_items')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete ComboItem.
   */
  public static function delete(array $where)
  {
    DB::table('combo_items')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get ComboItem collections.
   */
  public static function get($where = [])
  {
    return DB::table('combo_items')->get($where);
  }

  /**
   * Get ComboItem row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select ComboItem.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('combo_items')->select($columns, $escape);
  }

  /**
   * Update ComboItem.
   */
  public static function update(int $id, array $data)
  {
    DB::table('combo_items')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
