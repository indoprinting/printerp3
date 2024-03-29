<?php

declare(strict_types=1);

namespace App\Models;

class PriceGroup
{
  /**
   * Add new PriceGroup.
   */
  public static function add(array $data)
  {
    DB::table('pricegroup')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete PriceGroup.
   */
  public static function delete(array $where)
  {
    DB::table('pricegroup')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get PriceGroup collections.
   */
  public static function get($where = [])
  {
    return DB::table('pricegroup')->get($where);
  }

  /**
   * Get PriceGroup row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select PriceGroup.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('pricegroup')->select($columns, $escape);
  }

  /**
   * Update PriceGroup.
   */
  public static function update(int $id, array $data)
  {
    DB::table('pricegroup')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
