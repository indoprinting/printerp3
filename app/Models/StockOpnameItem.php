<?php

declare(strict_types=1);

namespace App\Models;

class StockOpnameItem
{
  /**
   * Add new StockOpnameItem.
   */
  public static function add(int $opnameId, array $items)
  {
    $adjustPlus = [];
    $adjustLost = [];
    $insertIds = [];
    $totalPlus = 0;
    $totalLost = 0;

    $opname = StockOpname::getRow(['id' => $opnameId]);

    if (!$opname) {
      setLastError('Stock Opname is not found.');
      return false;
    }

    $status = null;

    foreach ($items as $item) {
      $product = Product::getROw(['id' => $item['id']]);
      $warehouse = Warehouse::getRow(['id' => $opname->warehouse_id]);
      $whp = WarehouseProduct::getRow(['product_id' => $product->id, 'warehouse_id' => $warehouse->id]);

      if (!$whp) {
        setLastError("Warehouse product is not found for {$product->code} at {$warehouse->code}");
        return false;
      }

      $firstQty   = floatval($item['quantity']);
      $rejectQty  = floatval($item['reject']);
      $realQty    = floatval($whp->quantity);
      $price      = ($warehouse->code == 'LUC' ? $product->cost : $product->markon_price);

      // If minus, become debt.
      $restQty = ($firstQty - $realQty + $rejectQty);

      $subTotal = ($price * $restQty);

      if ($restQty > 0) {
        $adjustPlus[] = [
          'id'        => $product->id,
          'quantity'  => $firstQty
        ];

        $totalPlus += $subTotal;
      } else if ($firstQty < $realQty) { // If minus.
        if (($firstQty + $rejectQty) == $realQty) { // Adjustment min and status Good.
          $adjustLost[] = [
            'id'        => $product->id,
            'quantity'  => $firstQty
          ];

          $totalLost += $subTotal;
        } else {
          $status = 'checked';
        }
      } else if (($firstQty + $rejectQty) == $realQty) { // Excellent.
        // Nothing todo.
      }

      $data = [
        'opname_id'       => $opnameId,
        'product_id'      => $product->id,
        'product_code'    => $product->code,
        'warehouse_id'    => $warehouse->id,
        'warehouse_code'  => $warehouse->code,
        'quantity'        => $whp->quantity,
        'first_qty'       => $item['quantity'],
        'reject_qty'      => $item['reject'],
        'last_qty'        => ($item['last'] ?? 0),
        'price'           => $price,
        'subtotal'        => $subTotal
      ];

      DB::table('stock_opname_items')->insert($data);

      if (DB::error()['code'] == 0) {
        $insertId = DB::insertID();

        $insertIds[] = $insertId;
      } else {
        setLastError(DB::error()['message']);
        return false;
      }
    }

    if ($status == 'checked') {
    } else if (!$status && ($adjustPlus || $adjustLost)) {
      $status = 'good';
    } else if (!$status && !$adjustPlus) {
      $status = 'excellent';
    }

    $data = [
      'total_lost'  => $totalLost,
      'total_plus'  => $totalPlus,
      'status'      => $status
    ];

    if (!StockOpname::update((int)$opname->id, $data)) {
      return false;
    }

    if ($adjustPlus) {
      $data = [
        'date'          => $opname->date,
        'warehouse_id'  => $opname->warehouse_id,
        'mode'          => 'overwrite',
        'note'          => $opname->reference,
      ];

      $insertId = StockAdjustment::add($data, $adjustPlus);

      if (!$insertId) {
        return false;
      }

      if (!StockOpname::update((int)$opname->id, ['adjustment_plus_id' => $insertId])) {
        return false;
      }
    }

    if ($adjustLost) {
      $data = [
        'date'          => $opname->date,
        'warehouse_id'  => $opname->warehouse_id,
        'mode'          => 'overwrite',
        'note'          => $opname->reference,
      ];

      $insertId = StockAdjustment::add($data, $adjustLost);

      if (!$insertId) {
        return false;
      }

      if (!StockOpname::update((int)$opname->id, ['adjustment_min_id' => $insertId])) {
        return false;
      }
    }

    foreach ($items as $item) {
      Product::sync((int)$item['id']);
    }

    return $insertIds;
  }

  /**
   * Delete StockOpnameItem.
   */
  public static function delete(array $where)
  {
    DB::table('stock_opname_items')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get StockOpnameItem collections.
   */
  public static function get($where = [])
  {
    return DB::table('stock_opname_items')->get($where);
  }

  /**
   * Get StockOpnameItem row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select StockOpnameItem.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('stock_opname_items')->select($columns, $escape);
  }

  /**
   * Update StockOpnameItem.
   */
  public static function update(int $id, array $data)
  {
    DB::table('stock_opname_items')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
