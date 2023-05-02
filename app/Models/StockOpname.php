<?php

declare(strict_types=1);

namespace App\Models;

class StockOpname
{
  /**
   * Add new StockOpname.
   */
  public static function add(array $data, array $items)
  {
    $data = setCreatedBy($data);

    $data['reference'] = OrderRef::getReference('opname');

    DB::table('stock_opnames')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      if ($items) {
        $insertIds = StockOpnameItem::add((int)$insertId, $items);

        if (!$insertIds) {
          return false;
        }
      }

      if (!OrderRef::updateReference('opname')) {
        return false;
      }

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete StockOpname.
   */
  public static function delete(array $where)
  {
    DB::table('stock_opnames')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get StockOpname collections.
   */
  public static function get($where = [])
  {
    return DB::table('stock_opnames')->get($where);
  }

  /**
   * Get StockOpname row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select StockOpname.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('stock_opnames')->select($columns, $escape);
  }

  /**
   * Update StockOpname.
   */
  public static function update(int $id, array $data)
  {
    DB::table('stock_opnames')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
