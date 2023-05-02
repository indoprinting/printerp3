<?php

declare(strict_types=1);

namespace App\Models;

class StockAdjustment
{
  /**
   * Add new StockAdjustment.
   * @param array $data [ *warehouse_id, *mode ]
   * @param array $items [ *id, *quantity ]
   */
  public static function add(array $data, array $items)
  {
    if (empty($data['warehouse_id'])) {
      setLastError('Warehouse ID is not set.');
      return false;
    }

    $warehouse = Warehouse::getRow(['id' => $data['warehouse_id']]);

    if (!$warehouse) {
      setLastError("Warehouse {$data['warehouse']} is not found.");
      return false;
    }

    $data = setCreatedBy($data);
    $data['reference']  = OrderRef::getReference('adjustment');

    DB::table('adjustments')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      if (!OrderRef::updateReference('adjustment')) {
        return false;
      }

      foreach ($items as $item) {
        $product = Product::getRow(['id' => $item['id']]);

        if (!$product) {
          setLastError("Product {$item['id']} is not found.");
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
          'adjustment_id'   => $insertId,
          'product_id'      => $product->id,
          'warehouse_id'    => $warehouse->id,
          'quantity'        => $adjusted['quantity'],
          'adjustment_qty'  => $item['quantity'],
          'status'          => $adjusted['type']
        ]);

        if (!$res) {
          return false;
        }

        Product::sync((int)$product->id);
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
  public static function update(int $id, array $data, array $items = [])
  {
    $adjustment = self::getRow(['id' => $id]);

    if (!$adjustment) {
      setLastError("Stock Adjustment id {$id} is not found.");
      return false;
    }

    if (!empty($data['warehouse_id'])) {
      $warehouse = Warehouse::getRow(['id' => $data['warehouse_id']]);

      if (!$warehouse) {
        setLastError("Warehouse {$data['warehouse_id']} is not found.");
        return false;
      }

      $data['warehouse_id'] = $warehouse->id;
    }

    $data = setUpdatedBy($data);

    DB::table('adjustments')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      if ($items) {
        $adjustment = self::getRow(['id' => $id]);

        Stock::delete(['adjustment_id' => $id]);

        $warehouse = Warehouse::getRow(['id' => $adjustment->warehouse_id]);

        if (!$warehouse) {
          setLastError('Warehouse is not found.');
          return false;
        }

        foreach ($items as $item) {
          $product = Product::getRow(['id' => $item['id']]);

          Product::sync((int)$product->id);

          if (!$product) {
            setLastError("Product {$item['id']} is not found.");
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
            'adjustment_id'   => $id,
            'product_id'      => $product->id,
            'warehouse_id'    => $warehouse->id,
            'quantity'        => $adjusted['quantity'],
            'adjustment_qty'  => $item['quantity'],
            'status'          => $adjusted['type']
          ]);

          if (!$res) {
            return false;
          }

          Product::sync((int)$product->id);
        }
      }

      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
