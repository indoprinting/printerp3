<?php

declare(strict_types=1);

namespace App\Models;

class Test1
{
  /**
   * Add new Test1.
   */
  public static function add(array $data)
  {
    DB::table('test1')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Test1.
   */
  public static function delete(array $where)
  {
    DB::table('test1')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Test1 collections.
   */
  public static function get($where = [])
  {
    return DB::table('test1')->get($where);
  }

  /**
   * Get Test1 row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Test1.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('test1')->select($columns, $escape);
  }

  /**
   * Update Test1.
   */
  public static function update(int $id, array $data)
  {
    DB::table('test1')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
