<?php

declare(strict_types=1);

namespace App\Models;

class StockAdjustment
{
  /**
   * Add new StockAdjustment.
   */
  public static function add(array $data, array $items)
  {
    if ($data['warehouse']) {
      $warehouse = Warehouse::getRow(['code' => $data['warehouse']]);

      $data['warehouse_id'] = $warehouse->id;
    }

    DB::table('adjustments')->insert($data);
    $insertID = DB::insertID();

    if ($insertID) {
      foreach ($items as $item) {
        StockAdjustmentItem::add($item);
      }

      return $insertID;
    }

    return false;
  }

  /**
   * Delete StockAdjustment.
   */
  public static function delete(array $where)
  {
    DB::table('adjustments')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get StockAdjustment collections.
   */
  public static function get($where = [])
  {
    return DB::table('adjustments')->get($where);
  }

  /**
   * Get StockAdjustment row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select StockAdjustment.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('adjustments')->select($columns, $escape);
  }

  /**
   * Update StockAdjustment.
   */
  public static function update(int $id, array $data)
  {
    DB::table('adjustments')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
