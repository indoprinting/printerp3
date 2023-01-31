<?php

declare(strict_types=1);

namespace App\Models;

class StockAdjustment
{
  /**
   * Add new StockAdjustment.
   * @param array $data []
   * @param array $items [ *code, *quantity ]
   */
  public static function add(array $data, array $items)
  {
    if (empty($data['warehouse'])) {
      setLastError('Warehouse ID is not set.');
      return false;
    }

    $warehouse = Warehouse::getRow(['code' => $data['warehouse']]);
    $data['warehouse_id'] = $warehouse->id;

    DB::table('adjustments')->insert($data);
    $insertID = DB::insertID();

    if ($insertID) {
      foreach ($items as $item) {
        $product = Product::getRow(['code' => $item['code']]);
        $whProduct = WarehouseProduct::getRow(['product_id' => $product->id, 'warehouse_id' => $warehouse->id]);

        $adjusted = getAdjustedQty((float)$whProduct->quantity, (float)$item['quantity']);

        Stock::add([
          'date'            => $data['date'],
          'adjustment_id'   => $insertID,
          'product_id'      => $product->id,
          'warehouse_id'    => $warehouse->id,
          'quantity'        => $adjusted['quantity'],
          'adjustment_qty'  => $item['quantity'],
          'type'            => $adjusted['type']
        ]);
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
