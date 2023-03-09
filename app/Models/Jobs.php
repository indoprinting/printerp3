<?php

declare(strict_types=1);

namespace App\Models;

class Jobs
{
  /**
   * Add new Jobs.
   */
  public static function add(array $data)
  {
    DB::table('jobs')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Jobs.
   */
  public static function delete(array $where)
  {
    DB::table('jobs')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Jobs collections.
   */
  public static function get($where = [])
  {
    return DB::table('jobs')->get($where);
  }

  /**
   * Get Jobs row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Jobs.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('jobs')->select($columns, $escape);
  }

  /**
   * Update Jobs.
   */
  public static function update(int $id, array $data)
  {
    DB::table('jobs')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
