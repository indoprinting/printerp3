<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\{Product, ProductCategory};

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
        $product  = Product::getRow(['id' => $item['id']]);

        if (!$product) {
          setLastError("Product id {$product->id} is not found.");
          return false;
        }

        $productJS = getJSON($product->json);

        $category = ProductCategory::getRow(['id' => $product->category_id]);

        if (!$category) {
          setLastError("Product category id {$product->category_id} is not found.");
          return false;
        }

        if ($data['category'] == 'consumable') { // Consumable = Disposal.
          if ($category->code == 'AST' || $category->code == 'EQUIP') {
            $productJS->disposal_date   = $data['created_at'];
            $productJS->disposal_price  = $product->price;

            $json = json_encode($productJS);

            Product::update((int)$product->id, [
              'json'      => $json,
              'json_data' => $json
            ]);
          }
        } else if ($data['category'] == 'sparepart') { // Maintenance for machine.
          $machine = Product::getRow(['id' => $item['machine_id']]);

          if ($machine) {
            $machineJS = getJSON($machine->json);
            $maintain = (!empty($machineJS->maintenance_qty) ? $machineJS->maintenance_qty : 0);
            $mainCost = (!empty($machineJS->maintenance_cost) ? $machineJS->maintenance_cost : 0);

            $maintain++;
            $mainCost += $product->price;

            $productJS->maintenance_qty = $maintain;
            $productJS->maintenance_cost = $mainCost;

            $json = json_encode($productJS);

            Product::update((int)$product->id, [
              'json'      => $json,
              'json_data' => $json
            ]);
          }
        }

        $item = nulling($item, ['counter', 'machine_id', 'unique_code', 'ucr']);

        if (inStatus($data['status'], ['cancelled', 'completed', 'installed', 'packing'])) {
          $data['status'] = 'sent';
        }

        $res = Stock::add([
          'date'            => ($data['date'] ?? date('Y-m-d H:i:s')),
          'internal_use_id' => $insertId,
          'machine_id'      => $item['machine_id'],
          'product_id'      => $product->id,
          'quantity'        => $item['quantity'],
          'spec'            => $item['counter'], // Spec is counter in InternalUse.
          'status'          => $data['status'],
          'unique_code'     => generateInternalUseUniqueCode($data['category']),
          'ucr'             => $item['ucr'],
          'warehouse_id'    => $data['from_warehouse_id'],
          'created_at'      => $data['created_at'],
          'created_by'      => $data['created_by']
        ]);

        if (!$res) {
          return false;
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
  public static function update(int $id, array $data, array $items = null)
  {
    if ($items) {
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
    }

    $data = setUpdatedBy($data);

    DB::table('internal_uses')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      if ($items) {
        Stock::delete(['internal_use_id' => $id]);
        $newInternalUse = self::getRow(['id' => $id]);

        foreach ($items as $item) {
          $product = Product::getRow(['id' => $item['id']]);

          if (!$product) {
            setLastError("Product id {$product->id} is not found.");
            return false;
          }

          $item = nulling($item, ['counter', 'machine_id', 'unique_code', 'ucr']);

          if (empty($item['unique_code'])) {
            $item['unique_code'] = generateInternalUseUniqueCode($newInternalUse->category);
          }

          if (inStatus($newInternalUse->status, ['cancelled', 'completed', 'installed', 'packing'])) {
            $data['status'] = 'sent';
          }

          $res = Stock::add([
            'date'            => $newInternalUse->date,
            'internal_use_id' => $id,
            'machine_id'      => $item['machine_id'],
            'product_id'      => $product->id,
            'quantity'        => $item['quantity'],
            'spec'            => $item['counter'], // Spec is counter in InternalUse.
            'status'          => $data['status'],
            'unique_code'     => $item['unique_code'],
            'ucr'             => $item['ucr'],
            'warehouse_id'    => $newInternalUse->from_warehouse_id,
            'created_at'      => $newInternalUse->created_at,
            'created_by'      => $newInternalUse->created_by
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
