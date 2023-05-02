<?php

declare(strict_types=1);

namespace App\Models;

class ProductMutationItem
{
  /**
   * Add new ProductMutationItem.
   */
  public static function add(int $mutationId, array $items)
  {
    $insertIds = [];

    $mutation = ProductMutation::getRow(['id' => $mutationId]);

    foreach ($items as $item) {
      $data = [
        'pm_id'       => $mutationId,
        'product_id'  => $item['id'],
        'quantity'    => $item['quantity']
      ];

      DB::table('product_mutation_item')->insert($data);

      if (DB::error()['code'] == 0) {
        $insertIds[] = DB::insertID();

        if ($mutation->status == 'completed') {
          $res = Stock::decrease([
            'date'          => $mutation->date,
            'pm_id'         => $mutationId,
            'product_id'    => $item['id'],
            'warehouse_id'  => $mutation->from_warehouse_id,
            'quantity'      => $item['quantity']
          ]);

          if (!$res) {
            return false;
          }

          $res = Stock::increase([
            'date'          => $mutation->date,
            'pm_id'         => $mutationId,
            'product_id'    => $item['id'],
            'warehouse_id'  => $mutation->to_warehouse_id,
            'quantity'      => $item['quantity']
          ]);

          if (!$res) {
            return false;
          }
        }
      } else {
        setLastError(DB::error()['message']);

        return false;
      }
    }


    return $insertIds;
  }

  /**
   * Delete ProductMutationItem.
   */
  public static function delete(array $where)
  {
    DB::table('product_mutation_item')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get ProductMutationItem collections.
   */
  public static function get($where = [])
  {
    return DB::table('product_mutation_item')->get($where);
  }

  /**
   * Get ProductMutationItem row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select ProductMutationItem.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('product_mutation_item')->select($columns, $escape);
  }

  /**
   * Update ProductMutationItem.
   */
  public static function update(int $id, array $data)
  {
    DB::table('product_mutation_item')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
