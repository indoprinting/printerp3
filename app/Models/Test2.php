<?php

declare(strict_types=1);

namespace App\Models;

class Test2
{
  /**
   * Add new Test2.
   */
  public static function add(array $data)
  {
    DB::table('test2')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Test2.
   */
  public static function delete(array $where)
  {
    DB::table('test2')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Test2 collections.
   */
  public static function get($where = [])
  {
    return DB::table('test2')->get($where);
  }

  /**
   * Get Test2 row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Test2.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('test2')->select($columns, $escape);
  }

  /**
   * Update Test2.
   */
  public static function update(int $id, array $data)
  {
    DB::table('test2')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
