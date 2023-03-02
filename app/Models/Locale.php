<?php

declare(strict_types=1);

namespace App\Models;

class Locale
{
  /**
   * Add new Locale.
   */
  public static function add(array $data)
  {
    DB::table('locale')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Locale.
   */
  public static function delete(array $where)
  {
    DB::table('locale')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Locale collections.
   */
  public static function get($where = [])
  {
    return DB::table('locale')->get($where);
  }

  /**
   * Get Locale row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Locale.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('locale')->select($columns, $escape);
  }

  /**
   * Update Locale.
   */
  public static function update(int $id, array $data)
  {
    DB::table('locale')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
