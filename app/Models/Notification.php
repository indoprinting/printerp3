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
    if (empty($data['title'])) {
      setLastError('Title is required.');
      return false;
    }

    if (empty($data['note'])) {
      setLastError('Note is required.');
      return false;
    }

    $data = setCreatedBy($data);

    DB::table('notification')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Notification.
   */
  public static function delete(array $where)
  {
    DB::table('notification')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
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
    return null;
  }

  /**
   * Select Notification.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('notification')->select($columns, $escape);
  }

  /**
   * Update Notification.
   */
  public static function update(int $id, array $data)
  {
    DB::table('notification')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
