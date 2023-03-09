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

    if (!$warehouse) {
      setLastError("Warehouse {$data['warehouse']} is not found.");
      return false;
    }

    $data = setCreatedBy($data);
    $data['reference']    = OrderRef::getReference('adjustment');
    $data['warehouse_id'] = $warehouse->id;

    DB::table('adjustments')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      OrderRef::updateReference('adjustment');

      foreach ($items as $item) {
        $product = Product::getRow(['code' => $item['code']]);

        if (!$product) {
          setLastError("Product {$item['code']} is not found.");
          return false;
        }

        $whProduct = WarehouseProduct::getRow(['product_id' => $product->id, 'warehouse_id' => $warehouse->id]);

        if (!$whProduct) {
          setLastError("WarehouseProduct {$product->code}, {$warehouse->code} is not found.");
          return false;
        }

        if ($data['mode'] == 'overwrite') {
          $adjusted = getAdjustedQty((float)$whProduct->quantity, (float)$item['quantity']);
        } else if ($data['mode'] == 'formula') {
          $adjusted = [
            'quantity'  => $item['quantity'],
            'type'      => 'received'
          ];
        } else {
          setLastError('Mode must be overwrite or formula.');
          return false;
        }

        $res = Stock::add([
          'date'            => $data['date'],
          'adjustment'      => $data['reference'],
          'product'         => $product->code,
          'warehouse'       => $warehouse->code,
          'quantity'        => $adjusted['quantity'],
          'adjustment_qty'  => $item['quantity'],
          'status'          => $adjusted['type']
        ]);

        if (!$res) {
          return false;
        }
      }

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete StockAdjustment.
   */
  public static function delete(array $where)
  {
    DB::table('adjustments')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
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

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
