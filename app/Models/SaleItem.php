<?php

declare(strict_types=1);

namespace App\Models;

class SaleItem
{
  /**
   * Add new SaleItem.
   */
  public static function add(array $data)
  {
    if (isset($data['sale'])) {
      $sale = Sale::getRow(['reference' => $data['sale']]);

      if (!$sale) {
        setLastError("Sale {$data['sale']} is not found.");
        return false;
      }

      $data['sale_id'] = $sale->id;
    } else {
      setLastError("Sale is not set.");
      return false;
    }

    if (isset($data['product'])) {
      $product = Product::getRow(['code' => $data['product']]);

      if (!$product) {
        setLastError("Product {$data['product']} is not found.");
        return false;
      }

      $data['product_id']   = $product->id;
      $data['product_code'] = $product->code;
      $data['product_name'] = $product->name;
      $data['product_type'] = $product->type;
    } else {
      setLastError("Product is not set.");
      return false;
    }

    DB::table('sale_items')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      $saleItem   = self::getRow(['id' => $insertId]);
      $saleItemJS = getJSON($saleItem->json);

      if (isCompleted($saleItemJS->status)) {
        if ($product->type == 'combo') {
          $comboItems = ComboItem::get(['product_id' => $product->id]);

          foreach ($comboItems as $comboItem) {
            $rawItem = Product::getRow(['code' => $comboItem->item_code]);

            if ($rawItem->type == 'standard') {
              $res = Stock::decrease([
                'date'        => $saleItemJS->completed_at ?? date('Y-m-d H:i:s'),
                'sale'        => $sale->reference,
                'saleitem_id' => $saleItem->id,
                'product'     => $rawItem->code,
                'quantity'    => ($saleItem->finished_qty * $comboItem->quantity),
                'warehouse'   => $sale->warehouse,
                'created_by'  => $saleItemJS->operator_id
              ]);

              if (!$res) {
                return false;
              }
            } else if ($rawItem->type == 'service') {
              $res = Stock::increase([
                'date'        => $saleItemJS->completed_at ?? date('Y-m-d H:i:s'),
                'sale'        => $sale->reference,
                'saleitem_id' => $saleItem->id,
                'product'     => $rawItem->code,
                'quantity'    => ($saleItem->finished_qty * $comboItem->quantity),
                'warehouse'   => $sale->warehouse,
                'created_by'  => $saleItemJS->operator_id
              ]);

              if (!$res) {
                return false;
              }
            }
          }
        } else if ($product->type == 'service') {
          $res = Stock::increase([
            'date'        => $saleItemJS->completed_at ?? date('Y-m-d H:i:s'),
            'sale'        => $sale->reference,
            'saleitem_id' => $saleItem->id,
            'product'     => $product->code,
            'quantity'    => $saleItem->finished_qty,
            'warehouse'   => $sale->warehouse,
            'created_by'  => $saleItemJS->operator_id
          ]);

          if (!$res) {
            return false;
          }
        } else if ($product->type == 'standard') {
          $res = Stock::decrease([
            'date'        => $saleItemJS->completed_at ?? date('Y-m-d H:i:s'),
            'sale'        => $sale->reference,
            'saleitem_id' => $saleItem->id,
            'product'     => $product->code,
            'quantity'    => $saleItem->finished_qty,
            'warehouse'   => $sale->warehouse,
            'created_by'  => $saleItemJS->operator_id
          ]);

          if (!$res) {
            return false;
          }
        }
      }

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }


  /**
   * Complete sale item.
   * @param int $id Sale item ID.
   * @param array $data [ *quantity, spec, created_at, created_by ]
   */
  public static function complete(int $id, array $data)
  {
    $data = setCreatedBy($data);
    $saleItem = self::getRow(['id' => $id]);

    if ($saleItem) {
      $completedQty = $data['quantity']; // Quantity to complete.
      $sale         = Sale::getRow(['id' => $saleItem->sale_id]);
      $saleItemJS   = getJSON($saleItem->json_data);
      $status       = ($saleItemJS ? $saleItemJS->status : 'waiting_production'); // Default status.

      if (empty($data['quantity'])) {
        setLastError("SaleItem::complete(): Quantity is missing?");
        return false;
      }

      // Get operator data.
      $operator = User::getRow(['id' => $data['created_by']]);

      if (($completedQty + $saleItem->finished_qty) < $saleItem->quantity) { // If completed partial.
        $status = 'completed_partial';
      } else if (($completedQty + $saleItem->finished_qty) == $saleItem->quantity) { // If fully completed.
        $status = 'completed';
      } else {
        setLastError("SaleItem::complete(): Complete is more than requested. Complete: {$completedQty}, " .
          "Finished: {$saleItem->finished_qty}, Quantity: {$saleItem->quantity}");
        return false;
      }

      // Set Completed date and Operator who completed it.

      $saleItemJS->completed_at = ($data['created_at'] ?? date('Y-m-d H:i:s')); // Completed date.
      $saleItemJS->operator_id  = $operator->id; // Change PIC who completed it.
      $saleItemJS->status       = $status; // Restore status as completed or completed_partial.

      if (isset($data['spec'])) {
        $saleItemJS->spec = $data['spec'];
        unset($data['spec']);
      }

      $klikpod = Product::getRow(['code' => 'KLIKPOD']);

      $saleItemJSON = json_encode($saleItemJS);

      $saleItemData = [
        'finished_qty'  => ($saleItem->finished_qty + $completedQty),
        'json'          => $saleItemJSON,
        'json_data'     => $saleItemJSON
      ];
      print_r($saleItemData);
      die;
      if (self::update((int)$saleItem->id, $saleItemData)) {
        // Increase and Decrease item.

        if ($saleItem->product_type == 'combo') { // SALEITEM. (Decrement|Increment). POFF28
          $comboItems = ComboItem::get(['product_id' => $saleItem->product_id]);

          if ($comboItems) {
            foreach ($comboItems as $comboItem) {
              $rawItem  = Product::getRow(['code' => $comboItem->item_code]);

              if (!$rawItem) {
                setLastError("SaleItem::complete(): RAW item is not found.");
                return false;
              }

              $finalCompletedQty = filterDecimal($comboItem->quantity) * filterDecimal($completedQty);

              if ($rawItem->type == 'standard') { // COMBOITEM. Decrement. POSTMN, POCT15, FFC280
                if ($rawItem->id == $klikpod->id) {
                  setLastError("CRITICAL: KLIKPOD KNOWN AS COMBO STANDARD TYPE MUST NOT BE DECREASED!");
                  return false;
                }

                Stock::decrease([
                  'sale'        => $sale->reference,
                  'saleitem_id' => $saleItem->id,
                  'product'     => $rawItem->code,
                  'price'       => $saleItem->price,
                  'quantity'    => $finalCompletedQty,
                  'warehouse'   => $sale->warehouse,
                  'created_at'  => $data['created_at'],
                  'created_by'  => $operator->id
                ]);
              } else if ($rawItem->type == 'service') { // COMBOITEM. Increment. KLIKPOD
                // Since no decimal point for KLIKPOD/KLIKPODBW, we must round it up without precision.
                switch ($rawItem->code) {
                  case 'KLIKPOD':
                  case 'KLIKPODBW':
                    $finalCompletedQty = ceil($finalCompletedQty);
                    break;
                }

                Stock::increase([
                  'sale'        => $sale->reference,
                  'saleitem_id' => $saleItem->id,
                  'product'     => $rawItem->code,
                  'price'       => $saleItem->price,
                  'quantity'    => $finalCompletedQty,
                  'warehouse'   => $sale->warehouse,
                  'created_at'  => $data['created_at'],
                  'created_by'  => $operator->id
                ]);
              }
            }
          }
        } else if ($saleItem->product_type == 'service') { // SALEITEM. Increment. JASA POTONG
          // Since no decimal point for KLIKPOD/KLIKPODBW, we must round it up without precision.
          switch ($saleItem->product_code) {
            case 'KLIKPOD':
            case 'KLIKPODBW':
              $completedQty = ceil($completedQty);
              break;
          }

          Stock::increase([
            'sale'          => $sale->reference,
            'saleitem_id'   => $saleItem->id,
            'product'       => $saleItem->product,
            'price'         => $saleItem->price,
            'quantity'      => $completedQty,
            'warehouse'     => $sale->warehouse,
            'created_at'    => $data['created_at'],
            'created_by'    => $operator->id
          ]);
        } else if ($saleItem->product_type == 'standard') { // SALEITEM. Decrement. FFC280, POCT15
          if ($saleItem->product_code == 'KLIKPOD') {
            setLastError('CRITICAL: KLIKPOD KNOWN AS STANDARD TYPE MUST NOT BE DECREASED!');
            return false;
          }

          Stock::decrease([
            'sale_id'       => $sale->id,
            'saleitem_id'   => $saleItem->id,
            'product'       => $saleItem->product,
            'price'         => $saleItem->price,
            'quantity'      => $completedQty,
            'warehouse'     => $sale->warehouse,
            'created_at'    => $data['created_at'],
            'created_by'    => $operator->id
          ]);
        }

        // Sync sale after operator complete the item.
        Sale::sync(['sale_id' => $sale->id]);

        return true;
      }
    }
    return false;
  }


  /**
   * Delete SaleItem.
   */
  public static function delete(array $where)
  {
    DB::table('sale_items')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get SaleItem collections.
   */
  public static function get($where = [])
  {
    return DB::table('sale_items')->get($where);
  }

  /**
   * Get SaleItem row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select SaleItem.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('sale_items')->select($columns, $escape);
  }

  /**
   * Update SaleItem.
   */
  public static function update(int $id, array $data)
  {
    DB::table('sale_items')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
