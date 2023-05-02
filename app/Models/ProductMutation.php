<?php

declare(strict_types=1);

namespace App\Models;

class ProductMutation
{
  /**
   * Add new ProductMutation.
   */
  public static function add(array $data, array $items)
  {
    $data['items']  = '';

    foreach ($items as $item) {
      $product = Product::getRow(['id' => $item['id']]);

      if (!$product) {
        setLastError("Product id {$product->id} is not found.");
        return false;
      }

      $data['items']  .= '- ' . getExcerpt($product->name) . '<br>';
    }

    $data['status'] = ($data['status'] ?? 'packing');

    $data['reference'] = OrderRef::getReference('pm');
    $data = setCreatedBy($data);

    DB::table('product_mutation')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      $insertIds = ProductMutationItem::add((int)$insertId, $items);

      if (!$insertIds) {
        return false;
      }

      OrderRef::updateReference('pm');

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete ProductMutation.
   */
  public static function delete(array $where)
  {
    DB::table('product_mutation')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get ProductMutation collections.
   */
  public static function get($where = [])
  {
    return DB::table('product_mutation')->get($where);
  }

  /**
   * Get ProductMutation row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select ProductMutation.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('product_mutation')->select($columns, $escape);
  }

  /**
   * Update ProductMutation.
   */
  public static function update(int $id, array $data, array $items = null)
  {
    if ($items) {
      $data['items']  = '';

      foreach ($items as $item) {
        $product = Product::getRow(['id' => $item['id']]);

        if (!$product) {
          setLastError("Product id {$product->id} is not found.");
          return false;
        }

        $data['items']  .= '- ' . getExcerpt($product->name) . '<br>';
      }
    }

    $data = setUpdatedBy($data);

    DB::table('product_mutation')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      if ($items) {
        ProductMutationItem::delete(['pm_id' => $id]);
        Stock::delete(['pm_id' => $id]);

        $insertIds = ProductMutationItem::add((int)$id, $items);

        if (!$insertIds) {
          return false;
        }
      }

      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
