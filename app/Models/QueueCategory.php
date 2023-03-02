<?php

declare(strict_types=1);

namespace App\Models;

class QueueCategory
{
  /**
   * Add new QueueCategory.
   */
  public static function add(array $data)
  {
    DB::table('queue_categories')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete QueueCategory.
   */
  public static function delete(array $where)
  {
    DB::table('queue_categories')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get QueueCategory collections.
   */
  public static function get($where = [])
  {
    return DB::table('queue_categories')->get($where);
  }

  /**
   * Get QueueCategory row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select QueueCategory.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('queue_categories')->select($columns, $escape);
  }

  /**
   * Update QueueCategory.
   */
  public static function update(int $id, array $data)
  {
    DB::table('queue_categories')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
