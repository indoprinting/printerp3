<?php

declare(strict_types=1);

namespace App\Models;

class SaleItem
{
  /**
   * Add new SaleItem.
   * @param int $saleId Sale ID.
   * @param array $items Sale items collection to add.
   * 
   * [ *id, *quantity, completed_at, finished_qty, length,
   * operator_id, price, spec, status, waiting_production_date, width ]
   */
  public static function add(int $saleId, array $items)
  {
    $sale = Sale::getRow(['id' => $saleId]);

    if (!$sale) {
      setLastError("Sale id {$saleId} is not found.");
      return false;
    }

    if (empty($items)) {
      setLastError('Items are empty.');
      return false;
    }

    $insertIds = [];

    foreach ($items as $item) {
      $product    = Product::getRow(['id' => $item['id']]);
      $productJS  = getJSON($product->json);

      if (!$product) {
        setLastError("Product id {$item['id']} is not found.");
        return false;
      }

      if (empty($item['quantity'])) {
        setLastError('Item quantity is not set.');
        return false;
      }

      $completedAt  = ($item['completed_at'] ?? '');
      $price        = floatval($item['price'] ?? $product->price);
      $width        = floatval($item['width'] ?? 1);
      $length       = floatval($item['length'] ?? 1);
      $area         = ($width * $length);
      $quantity     = ($area * floatval($item['quantity']));
      $finishedQty  = floatval($item['finished_qty'] ?? 0);
      $operatorId   = 0;
      $status       = ($item['status'] ?? $sale->status); // Status sync by Sale::sync
      $spec         = ($item['spec'] ?? '');
      $subQty       = floatval($item['quantity']);
      $subTotal     = ($price * $quantity);

      if (!empty($item['operator_id'])) {
        $operator = User::getRow(['id' => $item['operator_id']]);

        if (!$operator) {
          setLastError("Operator id {$item['operator_id']} is not found.");
          return false;
        }

        $operatorId = $operator->id;
      }

      $dataJS = json_encode([
        'area'          => $area,
        'completed_at'  => $completedAt,
        'l'             => $length,
        'operator_id'   => $operatorId,
        'spec'          => $spec,
        'sqty'          => $subQty,
        'status'        => $status, // Backward PrintERP 2 compatibility.
        'w'             => $width,
        'completed'     => getJSON('{}'), // [{"date": "", "quantity":"", "created_by":""}]
      ]);

      $data = [
        'date'          => $sale->date,
        'sale'          => $sale->reference,
        'sale_id'       => $sale->id,
        'product'       => $product->code,
        'product_id'    => $product->id,
        'product_code'  => $product->code,
        'product_name'  => $product->name,
        'product_type'  => $product->type,
        'price'         => $price,
        'quantity'      => $quantity,
        'finished_qty'  => $finishedQty,
        'status'        => $status, // New PrintERP 3. Replacement from $json->status
        'subtotal'      => $subTotal,
        'json'          => $dataJS,
        'json_data'     => $dataJS
      ];

      DB::table('sale_items')->insert($data);

      if (DB::error()['code'] == 0) {
        $insertId = DB::insertID();

        $insertIds[] = $insertId;

        $saleItem = self::getRow(['id' => $insertId]);

        if (isCompleted($saleItem->status) || (isset($productJS->autocomplete) && $productJS->autocomplete == 1)) {
          $sysUser = User::getRow(['username' => 'system']);

          if (!$sysUser) {
            setLastError('Username system is not found. Autocomplete aborted.');
            return false;
          }

          $res = self::complete((int)$saleItem->id, [
            'quantity'  => $saleItem->quantity,
            'spec'      => 'AUTOCOMPLETE',
            'completed_by'  => $sysUser->id
          ]);

          if (!$res) {
            return false;
          }
        }
      } else {
        setLastError(DB::error()['message']);
        return false;
      }
    } // foreach

    return $insertIds;
  }

  /**
   * Add new SaleItem.
   */
  public static function add_(array $data)
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

      if (isCompleted($saleItem->status)) {
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
   * @param array $data [ *quantity, spec, completed_at, completed_by ]
   */
  public static function complete(int $id, array $data)
  {
    $data     = setCreatedBy($data);
    $saleItem = self::getRow(['id' => $id]);

    if ($saleItem) {
      $completedQty = $data['quantity']; // Quantity to complete.
      $sale         = Sale::getRow(['id' => $saleItem->sale_id]);
      $saleItemJS   = getJSON($saleItem->json_data);
      $status       = ($saleItemJS ? $saleItem->status : 'waiting_production'); // Default status.

      if (empty($data['quantity'])) {
        setLastError("Quantity is missing?");
        return false;
      }

      // Get operator data.
      $operator = User::getRow(['id' => $data['completed_by']]);

      if (!$operator) {
        setLastError("Operator is not found.");
        return false;
      }

      // Get completed date. Default current date.
      $completedDate = new \DateTime($data['completed_at'] ?? date('Y-m-d H:i:s'));

      if (($completedQty + $saleItem->finished_qty) < $saleItem->quantity) { // If completed partial.
        $status = 'completed_partial';
      } else if (($completedQty + $saleItem->finished_qty) == $saleItem->quantity) { // If fully completed.
        $status = 'completed';
      } else {
        setLastError("Complete is more than requested. Complete: {$completedQty}, " .
          "Finished: {$saleItem->finished_qty}, Quantity: {$saleItem->quantity}");
        return false;
      }

      // Set Completed date and Operator who completed it.

      $saleItemJS->completed_at = $completedDate->format('Y-m-d H:i:s'); // Completed date.
      $saleItemJS->operator_id  = $operator->id; // Change PIC who completed it.
      $saleItemJS->status       = $status; // Restore status as completed or completed_partial.

      if (isset($data['spec'])) {
        $saleItemJS->spec = $data['spec'];
        unset($data['spec']);
      }

      $klikpod = Product::getRow(['code' => 'KLIKPOD']);

      $saleItemJSON = json_encode($saleItemJS);

      $saleItemData = [
        'status'        => $status,
        'finished_qty'  => ($saleItem->finished_qty + $completedQty),
        'json'          => $saleItemJSON,
        'json_data'     => $saleItemJSON
      ];

      if (self::update((int)$saleItem->id, $saleItemData)) {
        // Increase and Decrease item.

        if ($saleItem->product_type == 'combo') { // SALEITEM. (Decrement|Increment). POFF28
          $comboItems = ComboItem::get(['product_id' => $saleItem->product_id]);

          if ($comboItems) {
            foreach ($comboItems as $comboItem) {
              $rawItem  = Product::getRow(['code' => $comboItem->item_code]);

              if (!$rawItem) {
                setLastError("RAW item is not found.");
                return false;
              }

              $finalCompletedQty = filterDecimal($comboItem->quantity) * filterDecimal($completedQty);

              if ($rawItem->type == 'standard') { // COMBOITEM. Decrement. POSTMN, POCT15, FFC280
                if ($rawItem->id == $klikpod->id) {
                  setLastError("CRITICAL: KLIKPOD KNOWN AS COMBO STANDARD TYPE MUST NOT BE DECREASED!");
                  return false;
                }

                $res = Stock::decrease([
                  'date'          => $completedDate->format('Y-m-d H:i:s'),
                  'sale_id'       => $sale->id,
                  'saleitem_id'   => $saleItem->id,
                  'product_id'    => $rawItem->id,
                  'price'         => $saleItem->price,
                  'quantity'      => $finalCompletedQty,
                  'warehouse_id'  => $sale->warehouse_id,
                  'created_by'    => $operator->id
                ]);

                if (!$res) {
                  return false;
                }
              } else if ($rawItem->type == 'service') { // COMBOITEM. Increment. KLIKPOD
                // Since no decimal point for KLIKPOD/KLIKPODBW, we must round it up without precision.
                switch ($rawItem->code) {
                  case 'KLIKPOD':
                  case 'KLIKPODBW':
                    $finalCompletedQty = ceil($finalCompletedQty);
                    break;
                }

                $res = Stock::increase([
                  'date'          => $completedDate->format('Y-m-d H:i:s'),
                  'sale_id'       => $sale->reference,
                  'saleitem_id'   => $saleItem->id,
                  'product_id'    => $rawItem->id,
                  'price'         => $saleItem->price,
                  'quantity'      => $finalCompletedQty,
                  'warehouse_id'  => $sale->warehouse_id,
                  'created_by'    => $operator->id
                ]);

                if (!$res) {
                  return false;
                }
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

          $res = Stock::increase([
            'date'          => $completedDate->format('Y-m-d H:i:s'),
            'sale_id'       => $sale->reference,
            'saleitem_id'   => $saleItem->id,
            'product_id'    => $saleItem->product_id,
            'price'         => $saleItem->price,
            'quantity'      => $completedQty,
            'warehouse_id'  => $sale->warehouse_id,
            'created_by'    => $operator->id
          ]);

          if (!$res) {
            return false;
          }
        } else if ($saleItem->product_type == 'standard') { // SALEITEM. Decrement. FFC280, POCT15
          if ($saleItem->product_code == 'KLIKPOD') {
            setLastError('CRITICAL: KLIKPOD KNOWN AS STANDARD TYPE MUST NOT BE DECREASED!');
            return false;
          }

          $res = Stock::decrease([
            'date'          => $completedDate->format('Y-m-d H:i:s'),
            'sale_id'       => $sale->id,
            'saleitem_id'   => $saleItem->id,
            'product_id'    => $saleItem->product_id,
            'price'         => $saleItem->price,
            'quantity'      => $completedQty,
            'warehouse_id'  => $sale->warehouse_id,
            'created_by'    => $operator->id
          ]);

          if (!$res) {
            return false;
          }
        }

        // Sync sale after complete operation.
        Sale::sync(['id' => $sale->id]);

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
   * Update SaleItem. Better not used. (Rarely used)
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
