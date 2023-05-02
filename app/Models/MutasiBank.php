<?php

declare(strict_types=1);

namespace App\Models;

class MutasiBank
{
  /**
   * Add new MutasiBank.
   */
  public static function add(array $data)
  {
    DB::table('mutasibank')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete MutasiBank.
   */
  public static function delete(array $where)
  {
    DB::table('mutasibank')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get MutasiBank collections.
   */
  public static function get($where = [])
  {
    return DB::table('mutasibank')->get($where);
  }

  /**
   * Get MutasiBank row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select MutasiBank.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('mutasibank')->select($columns, $escape);
  }

  /**
   * Update MutasiBank.
   */
  public static function update(int $id, array $data)
  {
    DB::table('mutasibank')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
