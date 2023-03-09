<?php

declare(strict_types=1);

namespace App\Models;

class _Template
{
  /**
   * Add new _Template.
   */
  public static function add(array $data)
  {
    DB::table('tableName')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete _Template.
   */
  public static function delete(array $where)
  {
    DB::table('tableName')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get _Template collections.
   */
  public static function get($where = [])
  {
    return DB::table('tableName')->get($where);
  }

  /**
   * Get _Template row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select _Template.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('tableName')->select($columns, $escape);
  }

  /**
   * Update _Template.
   */
  public static function update(int $id, array $data)
  {
    DB::table('tableName')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
