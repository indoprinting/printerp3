<?php

declare(strict_types=1);

namespace App\Models;

class Product
{
  /**
   * Add new Product.
   */
  public static function add(array $data)
  {
    DB::table('products')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Product.
   */
  public static function delete(array $where)
  {
    DB::table('products')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Product collections.
   */
  public static function get($where = [])
  {
    return DB::table('products')->get($where);
  }

  /**
   * Get Product row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Product.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('products')->select($columns, $escape);
  }

  /**
   * Sync product quantity.
   */
  public static function sync(int $productId)
  {
    $whIds = [];

    foreach (Warehouse::get(['active' => 1]) as $warehouse) {
      $whIds[] = $warehouse->id;
    }

    $totalQty = 0;

    foreach ($whIds as $whId) {
      if (Stock::sync((int)$productId, (int)$whId)) {
        $totalQty += Stock::totalQuantity((int)$productId, (int)$whId);
      } else {
        setLastError("Failed sync Product: {$productId}, Warehouse: {$whId}");
      }
    }

    if (Product::update((int)$productId, ['quantity' => $totalQty])) {
      return true;
    }

    return false;
  }

  /**
   * Update Product.
   */
  public static function update(int $id, array $data)
  {
    DB::table('products')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
