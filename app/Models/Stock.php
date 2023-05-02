<?php

declare(strict_types=1);

namespace App\Models;

class Stock
{
  /**
   * Add new stock.
   * UCR = Unique Code Replacement.
   * @param array $data [ date, *(adjustment_id|internal_use_id|pm_id|purchase_id|sale_id|transfer_id),
   *  saleitem_id, *product_id, *warehouse_id, *quantity, *status(received|sent), cost, price,
   *  adjustment_qty, purchased_qty, spec, machine_id, ucr, unique_code, json_data ]
   */
  public static function add(array $data)
  {
    if (empty($data['quantity']) && $data['quantity'] != 0) {
      setLastError("Stock:add(): Quantity is empty. Must be 0 or greater.");
      return false;
    }

    if (empty($data['status'])) {
      setLastError("Stock::add(): Status is empty.");
      return false;
    }

    if (empty($data['product_id'])) {
      setLastError('Stock::add() Product is empty.');
      return false;
    }

    if (empty($data['warehouse_id'])) {
      setLastError('Stock::add() Warehouse is empty.');
      return false;
    }

    if (isset($data['adjustment_id'])) {
      $inv = StockAdjustment::getRow(['id' => $data['adjustment_id']]);

      $data['adjustment'] = $inv->reference;
    } else if (isset($data['internal_use_id'])) {
      $inv = InternalUse::getRow(['id' => $data['internal_use_id']]);

      $data['internal_use'] = $inv->reference;
    } else if (isset($data['pm_id'])) {
      $inv = ProductMutation::getRow(['id' => $data['pm_id']]);

      $data['product_mutation'] = $inv->reference;
    } else if (isset($data['purchase_id'])) {
      $inv = ProductPurchase::getRow(['id' => $data['purchase_id']]);

      $data['purchase'] = $inv->reference;
    } else if (isset($data['sale_id'])) {
      $inv = Sale::getRow(['id' => $data['sale_id']]);

      $data['sale'] = $inv->reference;
    } else if (isset($data['transfer_id'])) {
      $inv = ProductTransfer::getRow(['id' => $data['transfer_id']]);

      $data['transfer'] = $inv->reference;
    }

    $product  = Product::getRow(['id' => $data['product_id']]);

    if ($product) {
      $data['product']      = $product->code;
      $data['product_code'] = $product->code;
      $data['product_name'] = $product->name;
      $data['product_type'] = $product->type;
    } else {
      setLastError("Stock::add(): Product id '{$data['product_id']}' is not found.");
      return false;
    }

    $category = ProductCategory::getRow(['id' => $product->category_id]);

    if ($category) {
      $data['category']       = $category->code;
      $data['category_id']    = $category->id;
      $data['category_code']  = $category->code;
      $data['category_name']  = $category->name;
    }

    // Not all use unit like service.
    $unit = Unit::getRow(['id' => $product->unit]);

    if ($unit) {
      $data['unit']       = $unit->code;
      $data['unit_id']    = $unit->id;
      $data['unit_code']  = $unit->code;
      $data['unit_name']  = $unit->name;
    }

    $warehouse  = Warehouse::getRow(['id' => $data['warehouse_id']]);

    if ($warehouse) {
      $data['warehouse']      = $warehouse->code;
      $data['warehouse_code'] = $warehouse->code;
      $data['warehouse_name'] = $warehouse->name;
    } else {
      setLastError("Stock::add(): Warehouse id '{$data['warehouse_id']}' is not found.");
      return false;
    }

    // Cost = Vendor price (Purchase). Price = Mark On Price (Transfer).
    if (!isset($data['cost']))  $data['cost']   = $product->cost;
    if (!isset($data['price'])) $data['price']  = $product->price;

    $data['subtotal'] = filterDecimal($data['price']) * filterDecimal($data['quantity']);

    $data = setCreatedBy($data);

    DB::table('stocks')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      if ($data['status'] == 'received') {
        WarehouseProduct::increaseQuantity((int)$product->id, (int)$warehouse->id, (float)$data['quantity']);
      } else if ($data['status'] == 'sent') {
        WarehouseProduct::decreaseQuantity((int)$product->id, (int)$warehouse->id, (float)$data['quantity']);
      }

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
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

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
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
    return null;
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
  public static function select(string $columns, $escape = true)
  {
    return DB::table('stocks')->select($columns, $escape);
  }

  /**
   * Sync stocks.
   */
  public static function sync(int $productId, int $warehouseId)
  {
    $whp = WarehouseProduct::getRow(['product_id' => $productId, 'warehouse_id' => $warehouseId]);

    if (!$whp) return false;

    return WarehouseProduct::update(
      (int)$whp->id,
      ['quantity' => self::totalQuantity($productId, $warehouseId)]
    );
  }

  /**
   * Get total quantity based by product and warehouse.
   * @param int $productId Product ID.
   * @param int $warehouseId Warehouse ID.
   * @return float Return total quantity.
   */
  public static function totalQuantity(int $productId, int $warehouseId)
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
    if (isset($data['product_id'])) {
      $product  = Product::getRow(['id' => $data['product_id']]);

      if ($product) {
        $data['product_code']   = $product->code;
        $data['product_name']   = $product->name;
        $data['product_type']   = $product->type;
      } else {
        setLastError("Stock::update(): Product id '{$data['product_id']}' is not found.");
        return false;
      }

      $category = ProductCategory::getRow(['id' => $product->category_id]);

      if ($category) {
        $data['category_id']    = $category->id;
        $data['category_code']  = $category->code;
        $data['category_name']  = $category->name;
      }

      // Not all use unit like service.
      $unit = Unit::getRow(['id' => $product->unit]);

      if ($unit) {
        $data['unit_id']        = $unit->id;
        $data['unit_code']      = $unit->code;
        $data['unit_name']      = $unit->name;
      }

      $data['cost']     = $product->cost;
      $data['price']    = $product->price;
      $data['subtotal'] = filterDecimal($data['price']) * filterDecimal($data['quantity']);
    }

    if (isset($data['warehouse_id'])) {
      $warehouse  = Warehouse::getRow(['id' => $data['warehouse_id']]);

      if ($warehouse) {
        $data['warehouse_code'] = $warehouse->code;
        $data['warehouse_name'] = $warehouse->name;
      } else {
        setLastError("Stock::add(): Warehouse id '{$data['warehouse_id']}' is not found.");
        return false;
      }
    }

    $data = setUpdatedBy($data);

    DB::table('stocks')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
