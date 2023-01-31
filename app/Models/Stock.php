<?php

declare(strict_types=1);

namespace App\Models;

class Stock
{
  /**
   * Add new stock.
   * UCR = Unique Code Replacement.
   * @param array $data [ *(adjustment_id|internal_use_id|pm_id|purchase_id|sale_id|transfer_id),
   *  saleitem_id, *product_id, *warehouse_id, *quantity, *status(received|sent), cost, price,
   *  adjustment_qty, purchased_qty, spec, machine_id, ucr, unique_code, json_data ]
   */
  public static function add(array $data)
  {
    $data = setCreatedBy($data);

    if (empty($data['quantity'])) {
      setLastError("Stock:add(): Quantity is empty.");
      return FALSE;
    }

    if (empty($data['status'])) {
      setLastError("Stock::add(): Status is empty.");
      return FALSE;
    }

    $product  = Product::getRow(['id' => $data['product_id']]);
    if ($product) {
      $data['product_code']   = $product->code;
      $data['product_name']   = $product->name;
      $data['product_type']   = $product->type;
    } else {
      setLastError("Stock::add(): Product '{$data['product_id']}' is not found.");
      return FALSE;
    }

    // Not all use unit like service.
    $unit = Unit::getRow(['id' => $product->unit]);
    if ($unit) {
      $data['unit_id']        = $unit->id;
      $data['unit_code']      = $unit->code;
      $data['unit_name']      = $unit->name;
    }

    $warehouse  = Warehouse::getRow(['id' => $data['warehouse_id']]);
    if ($warehouse) {
      $data['warehouse_code'] = $warehouse->code;
      $data['warehouse_name'] = $warehouse->name;
    } else {
      setLastError("Stock::add(): Warehouse '{$data['warehouse_id']}' is not found.");
      return FALSE;
    }

    if (isset($data['price'])) {
      $data['subtotal'] = filterDecimal($data['price']) * filterDecimal($data['quantity']);
    }

    // Cost = Vendor price (Purchase). Price = Mark On Price (Transfer).
    if (!isset($data['cost']))  $data['cost']   = $product->cost;
    if (!isset($data['price'])) $data['price']  = $product->price;

    DB::table('stocks')->insert($data);

    if ($insertId = DB::insertID()) {
      if ($data['status'] == 'received')
        WarehouseProduct::increaseQuantity((int)$product->id, (int)$warehouse->id, (float)$data['quantity']);
      if ($data['status'] == 'sent')
        WarehouseProduct::decreaseQuantity((int)$product->id, (int)$warehouse->id, (float)$data['quantity']);

      return $insertId;
    }

    return FALSE;
  }

  /**
   * Decrease stock quantity.
   */
  public static function decrease(array $data)
  {
    $data['status'] = 'sent';
    return self::add($data);
  }

  /**
   * Delete Stock.
   */
  public static function delete(array $where)
  {
    DB::table('stocks')->delete($where);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Stock collections.
   */
  public static function get($where = [])
  {
    $stock = DB::table('stocks');

    if (!empty($where['not_null'])) {
      $stock->isNotNull($where['not_null']);
      unset($where['not_null']);
    }
    if (!empty($where['start_date'])) {
      $stock->where("created_at >= '{$where['start_date']} 00:00:00'");
      unset($where['start_date']);
    }
    if (!empty($where['end_date'])) {
      $stock->where("created_at <= '{$where['end_date']} 23:59:59'");
      unset($where['end_date']);
    }
    if (!empty($where['order'])) {
      // $where['order'][0] = 'created_at | $where['order'][1] = 'ASC'
      $stock->orderBy($where['order'][0], $where['order'][1]);
      unset($where['order']);
    }

    return $stock->get($where);
  }

  /**
   * Get Stock row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Increase stock quantity.
   */
  public static function increase(array $data)
  {
    $data['status'] = 'received';
    return self::add($data);
  }

  /**
   * Select Stock.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('stocks')->select($columns, $escape);
  }

  /**
   * Get total quantity based by product and warehouse.
   * @param int $productId Product ID.
   * @param int $warehouseId Warehouse ID.
   * @param array $opt [ start_date, end_date, order_by(column,ASC|DESC) ]
   * @return float Return total quantity.
   */
  public static function totalQuantity(int $productId, int $warehouseId, $opt = [])
  {
    $result = DB::table('stocks')
      ->select('(COALESCE(stock_recv.total, 0) - COALESCE(stock_sent.total, 0)) AS total')
      ->join(
        "(
        SELECT product_id, SUM(quantity) total FROM stocks
        WHERE product_id = {$productId} AND warehouse_id = {$warehouseId}
        AND status LIKE 'received') stock_recv",
        'stock_recv.product_id = stocks.product_id',
        'left'
      )
      ->join(
        "(
        SELECT product_id, SUM(quantity) total FROM stocks
        WHERE product_id = {$productId} AND warehouse_id = {$warehouseId}
        AND status LIKE 'sent') stock_sent",
        'stock_sent.product_id = stocks.product_id',
        'left'
      )
      ->groupBy('stocks.product_id')
      ->getRow(['stocks.product_id' => $productId, 'stocks.warehouse_id' => $warehouseId]);

    return $result?->total;
  }

  /**
   * Update Stock.
   */
  public static function update(int $id, array $data)
  {
    DB::table('stocks')->update($data, ['id' => $id]);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
