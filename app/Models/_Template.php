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

    if ($insertID = DB::insertID()) {
      return $insertID;
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

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
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
    return NULL;
  }

  /**
   * Select _Template.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('tableName')->select($columns, $escape);
  }

  /**
   * Update _Template.
   */
  public static function update(int $id, array $data)
  {
    DB::table('tableName')->update($data, ['id' => $id]);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
