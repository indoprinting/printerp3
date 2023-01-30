<?php

declare(strict_types=1);

namespace App\Models;

class StockAdjustmentItem
{
  /**
   * Add new StockAdjustmentItem.
   */
  public static function add(array $data)
  {
    DB::table('adjustment_item')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete StockAdjustmentItem.
   */
  public static function delete(array $where)
  {
    DB::table('adjustment_item')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get StockAdjustmentItem collections.
   */
  public static function get($where = [])
  {
    return DB::table('adjustment_item')->get($where);
  }

  /**
   * Get StockAdjustmentItem row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select StockAdjustmentItem.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('adjustment_item')->select($columns, $escape);
  }

  /**
   * Update StockAdjustmentItem.
   */
  public static function update(int $id, array $data)
  {
    DB::table('adjustment_item')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
