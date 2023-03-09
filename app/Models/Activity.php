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

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Activity.
   */
  public static function delete(array $where)
  {
    DB::table('activity')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
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
    return null;
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

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
