<?php

declare(strict_types=1);

namespace App\Models;

class Notification
{
  /**
   * Add new Notification.
   */
  public static function add(array $data)
  {
    DB::table('notification')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete Notification.
   */
  public static function delete(array $where)
  {
    DB::table('notification')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get Notification collections.
   */
  public static function get($where = [])
  {
    return DB::table('notification')->get($where);
  }

  /**
   * Get Notification row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Notification.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('notification')->select($columns, $escape);
  }

  /**
   * Update Notification.
   */
  public static function update(int $id, array $data)
  {
    DB::table('notification')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
