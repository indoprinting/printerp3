<?php

declare(strict_types=1);

namespace App\Models;

class Biller
{
  /**
   * Add new billers.
   * @param array $data [ name, code ]
   */
  public static function add(array $data)
  {
    DB::table('biller')->insert($data);

    if ($insertID = DB::insertID()) {
      return $insertID;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete billers.
   * @param array $clause [ id, name, code ]
   */
  public static function delete(array $clause)
  {
    DB::table('biller')->delete($clause);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get billers collections.
   * @param array $clause [ id, name, code ]
   */
  public static function get($clause = [])
  {
    return DB::table('biller')->get($clause);
  }

  /**
   * Get billers row.
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
   * Select _Template.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('tableName')->select($columns, $escape);
  }

  /**
   * Update billers.
   * @param int $id billers ID.
   * @param array $data [ name, code ]
   */
  public static function update(int $id, array $data)
  {
    DB::table('biller')->update($data, ['id' => $id]);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
