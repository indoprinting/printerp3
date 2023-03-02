<?php

declare(strict_types=1);

namespace App\Models;

class InternalUse
{
  /**
   * Add new InternalUse.
   */
  public static function add(array $data)
  {
    DB::table('internal_uses')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete InternalUse.
   */
  public static function delete(array $where)
  {
    DB::table('internal_uses')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get InternalUse collections.
   */
  public static function get($where = [])
  {
    return DB::table('internal_uses')->get($where);
  }

  /**
   * Get InternalUse row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select InternalUse.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('internal_uses')->select($columns, $escape);
  }

  /**
   * Update InternalUse.
   */
  public static function update(int $id, array $data)
  {
    DB::table('internal_uses')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
