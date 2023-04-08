<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Product;

class InternalUse
{
  /**
   * Add new InternalUse.
   */
  public static function add(array $data, array $items)
  {

    $data['counter']      = '';
    $data['grand_total']  = 0;
    $data['items']        = '';

    foreach ($items as $item) {
      $product = Product::getRow(['id' => $item['id']]);

      if (!$product) {
        setLastError("Product id {$product->id} is not found.");
        return false;
      }

      $data['counter']      .= $item['counter'] . '<br>';
      $data['grand_total']  += floatval(getMarkonPrice($product->cost, $product->markon) * $item['quantity']);
      $data['items']        .= '- ' . getExcerpt($product->name) . '<br>';
    }

    // Auto complete for consumable category.
    if ($data['category'] == 'consumable') {
      $data['status'] = 'completed';
    } else if ($data['category'] == 'sparepart') {
      $data['status'] = 'need_approval';
    }

    $data['reference'] = OrderRef::getReference('iuse');
    $data = setCreatedBy($data);

    DB::table('internal_uses')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      foreach ($items as $item) {
        $product = Product::getRow(['id' => $item['id']]);

        if (!$product) {
          setLastError("Product id {$product->id} is not found.");
          return false;
        }

        $item = nulling($item, ['counter', 'machine_id', 'ucr']);

        if (inStatus($data['status'], ['cancelled', 'completed', 'installed', 'packing'])) {
          $res = Stock::decrease([
            'date'            => ($data['date'] ?? date('Y-m-d H:i:s')),
            'internal_use_id' => $insertId,
            'machine_id'      => $item['machine_id'],
            'product_id'      => $product->id,
            'quantity'        => $item['quantity'],
            'spec'            => $item['counter'], // Spec is counter in InternalUse.
            'ucr'             => $item['ucr'],
            'warehouse_id'    => $data['from_warehouse_id'],
          ]);

          if (!$res) {
            return false;
          }
        } else {
          $res = Stock::add([
            'date'            => ($data['date'] ?? date('Y-m-d H:i:s')),
            'internal_use_id' => $insertId,
            'machine_id'      => $item['machine_id'],
            'product_id'      => $product->id,
            'quantity'        => $item['quantity'],
            'spec'            => $item['counter'], // Spec is counter in InternalUse.
            'status'          => $data['status'],
            'ucr'             => $item['ucr'],
            'warehouse_id'    => $data['from_warehouse_id'],
          ]);
        }
      }

      OrderRef::updateReference('iuse');

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete InternalUse.
   */
  public static function delete(array $where)
  {
    DB::table('internal_uses')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get InternalUse collections.
   */
  public static function get($where = [])
  {
    return DB::table('internal_uses')->get($where);
  }

  /**
   * Get InternalUse row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select InternalUse.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('internal_uses')->select($columns, $escape);
  }

  /**
   * Update InternalUse.
   */
  public static function update(int $id, array $data)
  {
    DB::table('internal_uses')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
