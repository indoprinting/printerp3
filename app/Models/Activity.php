<?php

declare(strict_types=1);

namespace App\Models;

class Activity
{
  /**
   * Add new Activity.
   */
  public static function add(array $data)
  {
    $data = setCreatedBy($data);

    DB::table('activity')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete Activity.
   */
  public static function delete(array $where)
  {
    DB::table('activity')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get Activity collections.
   */
  public static function get($where = [])
  {
    return DB::table('activity')->get($where);
  }

  /**
   * Get Activity row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Activity.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('activity')->select($columns, $escape);
  }

  /**
   * Update Activity.
   */
  public static function update(int $id, array $data)
  {
    DB::table('activity')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
