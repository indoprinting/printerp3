<?php

declare(strict_types=1);

namespace App\Models;

class Sale
{
  /**
   * Add new sales.
   * @param array $data
   * @param array $items [ ]
   */
  public static function add(array $data, array $items)
  {
    $biller = Biller::getRow(['code' => $data['biller']]);

    if (!$biller) {
      setLastError('Biller is not found.');
      return false;
    }

    $customer = Customer::getRow(['phone' => $data['customer']]);

    if (!$customer) {
      setLastError('Customer is not found.');
      return false;
    }

    $warehouse = Warehouse::getRow(['code' => $data['warehouse']]);

    if (!$warehouse) {
      setLastError('Warehouse is not found.');
      return false;
    }

    $cashier = User::getRow(['phone' => $data['cashier']]);

    if (!$cashier) {
      setLastError('Cashier is not found.');
      return false;
    }

    // Is special customer (Privilege, TOP)
    $isSpecialCustomer = isSpecialCustomer($customer->id);

    $data['status'] = ($data['status'] ?? ($isSpecialCustomer ? 'waiting_production' : 'need_payment'));

    $grandTotal  = 0;
    $totalPrice  = 0;
    $totalItems = 0.0;
    $date = ($data['date'] ?? date('Y-m-d H:i:s'));
    $reference = OrderRef::getReference('sale');

    foreach ($items as $item) {
      $price    = filterDecimal($item['price']);
      $quantity = filterDecimal($item['quantity']);
      $width    = filterDecimal($item['width'] ?? 1);
      $length   = filterDecimal($item['length'] ?? 1);
      $area     = ($width * $length);

      $totalQty         = ($area * $quantity);
      $totalPrice  += round($price * $totalQty);
      $totalItems += $totalQty;
    }

    // Discount.
    $grandTotal = ($totalPrice - ($data['discount'] ?? 0));

    // Get balance.
    $balance = ($isSpecialCustomer ? $grandTotal : 0);

    // Determine use TB by biller and warehouse, if both different, then use tb (1).
    $useTB = isTBSale($data['biller'], $data['warehouse']);

    // Get payment term.
    $payment_term = filterDecimal($data['payment_term'] ?? 1);
    $payment_term = ($payment_term > 0 ? $payment_term : 1);

    $saleJS = json_encode([
      'approved'                => ($data['approved'] ?? 0),
      'cashier_by'              => $cashier->id,
      'est_complete_date'       => ($data['due_date'] ?? ''),
      'payment_due_date'        => ($data['payment_due_date'] ?? getWorkingDateTime(date('Y-m-d H:i:s', strtotime('+1 days')))),
      'source'                  => ($data['source'] ?? ''),
      'vouchers'                => ($data['vouchers'] ?? []),
      'waiting_production_date' => ($data['waiting_production_date'] ?? '')
    ]);

    $saleData = [
      'date'            => $date,
      'reference'       => $reference,
      'customer_id'     => $customer->id,
      'customer'        => $customer->phone,
      'biller_id'       => $biller->id,
      'biller'          => $biller->code,
      'warehouse_id'    => $warehouse->id,
      'warehouse'       => $warehouse->code,
      'no_po'           => ($data['no_po'] ?? null),
      'note'            => ($data['note'] ?? null),
      'discount'        => filterDecimal($data['discount'] ?? 0),
      'total'           => roundDecimal($totalPrice),
      'shipping'        => filterDecimal($data['shipping'] ?? 0),
      'grand_total'     => roundDecimal($grandTotal), // IMPORTANT roundDecimal !!
      'balance'         => $balance,
      'status'          => $data['status'],
      'payment_status'  => ($data['payment_status'] ?? 'pending'),
      'payment_term'    => $payment_term,
      'due_date'        => ($data['due_date'] ?? null),
      'total_items'     => $totalItems,
      'paid'            => filterDecimal($data['paid'] ?? 0),
      'attachment'      => ($data['attachment'] ?? null),
      'payment_method'  => ($data['payment_method'] ?? null),
      'use_tb'          => $useTB,
      'active'          => 1,
      'json'            => $saleJS,
      'json_data'       => $saleJS
    ];

    $saleData = setCreatedBy($saleData);

    DB::table('sales')->insert($saleData);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      foreach ($items as $item) {
        $product = Product::getRow(['code' => $item['code']]);
        $productJS = getJSON($product->json_data);
        $operator = User::getRow(['phone' => $item['operator']]);

        if (!empty($item['width']) && !empty($item['length'])) {
          $area = filterDecimal($item['width']) * filterDecimal($item['length']);
          $quantity = ($area * filterDecimal($item['quantity']));
        } else {
          $area           = 0;
          $quantity       = $item['quantity'];
          $item['width']  = 0;
          $item['length'] = 0;
        }

        $saleItemId = SaleItem::add([
          'sale'          => $reference,
          'product'       => $product->code,
          'price'         => $item['price'],
          'quantity'      => $quantity,
          'subtotal'      => (floatval($item['price']) * $quantity),
          'json'          => json_encode([
            'w'             => $item['width'],
            'l'             => $item['length'],
            'area'          => $area,
            'sqty'          => $item['quantity'],
            'spec'          => ($item['spec'] ?? ''),
            'status'        => $saleData['status'],
            'operator_id'   => $operator->id,
            'due_date'      => ($saleData['due_date'] ?? ''),
            'completed_at'  => ($item['completed_at'] ?? '')
          ]),
          'json_data'     => json_encode([
            'w'             => $item['width'],
            'l'             => $item['length'],
            'area'          => $area,
            'sqty'          => $item['quantity'],
            'spec'          => ($item['spec'] ?? ''),
            'status'        => $saleData['status'],
            'operator_id'   => $operator->id,
            'due_date'      => ($saleData['due_date'] ?? ''),
            'completed_at'  => ($item['completed_at'] ?? '')
          ])
        ]);

        if (!$saleItemId) {
          return false;
        }

        /**
         * Autocomplete Engine
         */
        if ($saleData['status'] == 'waiting_production') {
          if (!isWeb2Print($insertId) && isset($productJS->autocomplete) && $productJS->autocomplete == 1) {
            $saleItem = SaleItem::getRow(['id' => $saleItemId]);
            $saleItemJS = getJSON($saleItem->json);

            if (isCompleted($saleItemJS->status)) {
              continue;
            }

            $res = SaleItem::complete((int)$saleItemId, [
              'quantity'    => $saleItem->quantity,
              'created_by'  => $saleItemJS->operator_id
            ]);

            if (!$res) {
              return false;
            }
          }
        }
      }

      OrderRef::updateReference('sale');

      return $insertId;
    }

    return false;
  }

  /**
   * Add sale payment.
   * @param int $saleId Sale ID.
   * @param array $data [ *amount, *bank, attachment ]
   */
  public static function addPayment(int $saleId, array $data)
  {
    $sale = self::getRow(['id' => $saleId]);

    $insertId = Payment::add([
      'sale_id'     => $sale->id,
      'bank_id'     => $data['bank_id'],
      'biller_id'   => $sale->biller_id,
      'amount'      => $data['amount'],
      'type'        => 'received',
      'method'      => ($data['method'] ?? 'Transfer'),
      'attachment'  => ($data['attachment'] ?? null)
    ]);

    if (!$insertId) {
      return false;
    }

    self::sync(['id' => $sale->id]);

    return $insertId;
  }

  /**
   * Delete Sale.
   */
  public static function delete(array $where)
  {
    DB::table('sales')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Sale collections.
   */
  public static function get($where = [])
  {
    return DB::table('sales')->get($where);
  }

  /**
   * Get Sale row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Sale.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('sales')->select($columns, $escape);
  }

  /**
   * Sync sales.
   */
  public static function sync($clause = [])
  {
    $sales = [];
    $updateCounter = 0;

    // $this->syncPaymentValidations(); // Cause memory crash (looping).

    if (!empty($clause)) {
      if (isset($clause['id']) && is_array($clause['id'])) {
        foreach ($clause['id'] as $id) {
          $sales[] = self::getRow(['id' => $id]);
        }
      } else {
        $sales = self::get($clause);
      }
    } else { // Default if id is null.
      $sales = self::get();
    }

    if (empty($sales)) {
      setLastError('Sale::sync() Why sales is empty? Is deleted?');
      return false;
    }

    foreach ($sales as $sale) {
      if (empty($sale->json)) {
        setLastError("Sale::sync() Sale ID {$sale->id} has invalid json column");
        return false;
      }

      $saleJS = getJSON($sale->json);
      $saleData = [];

      if (!$saleJS) {
        setLastError("Sale::sync() Invalid sales->json in sale id {$sale->id}, {$sale->reference}");
        return false;
      }

      $isDuePayment      = isDueDate($saleJS->payment_due_date ?? $sale->due_date);
      $isW2PUser         = isW2PUser($sale->created_by); // Is sale created_by user is W2P?
      $isSpecialCustomer = isSpecialCustomer($sale->customer_id); // Special customer (Privilege, TOP)
      $payments          = Payment::get(['sale_id' => $sale->id]);
      $paymentValidation = PaymentValidation::select('*')
        ->orderBy('id', 'DESC')
        ->where('sale_id', $sale->id)
        ->getRow();

      $saleItems  = SaleItem::get(['sale_id' => $sale->id]);

      if (empty($saleItems)) {
        setLastError("Sale::sync() Sale items empty. Sale id {$sale->id}, {$sale->reference}");
        continue;
      }

      $completedItems = 0;
      $deliveredItems = 0;
      $finishedItems  = 0;
      $total          = 0;
      $hasPartial     = false;
      $totalSaleItems = 0;
      $saleStatus     = $sale->status;

      foreach ($saleItems as $saleItem) {
        $saleItemJS = getJSON($saleItem->json);
        $saleItemStatus = $saleItemJS->status;
        $totalSaleItems++;
        $total += round($saleItem->price * $saleItem->quantity);
        $isItemFinished = ($saleItem->quantity == $saleItem->finished_qty ? true : false);
        $isItemFinishedPartial = ($saleItem->finished_qty > 0 && $saleItem->quantity > $saleItem->finished_qty ? true : false);

        if ($saleItemStatus == 'delivered') {
          $completedItems++;
          $deliveredItems++;
        } else if ($saleItemStatus == 'finished') {
          $completedItems++;
          $finishedItems++;
        } else if ($isItemFinished) {
          $completedItems++;
          $saleItemStatus = 'completed';
        } else if ($isItemFinishedPartial) {
          $hasPartial = true;
          $saleItemStatus = 'completed_partial';
        } else if ($isSpecialCustomer || $payments) {
          if ($isW2PUser) {
            $saleItemStatus = 'preparing';
          } else {
            $saleItemStatus = 'waiting_production';
          }
        } else {
          $saleItemStatus = 'need_payment';
        }

        if ($sale->status == 'inactive') {
          $saleItemJS->status = 'inactive';
        }

        if ($saleItemJS->status == 'draft') {
          continue;
        }

        $saleItemJS->status = $saleItemStatus;

        SaleItem::update((int)$saleItem->id, [
          'json'      => json_encode($saleItemJS),
          'json_data' => json_encode($saleItemJS)
        ]);
      }

      if ($sale->discount > $total) {
        $sale->discount = $total;
      }

      // Tax calculation.
      $tax        = ($sale->tax * 0.01 * $total);
      $grandTotal = round($total + $tax - $sale->discount);

      $saleData['total']        = $total;
      $saleData['grand_total']  = $grandTotal; // Inclue tax.

      $isSaleCompleted        = ($completedItems == $totalSaleItems ? true : false);
      $isSaleCompletedPartial = (($completedItems > 0 && $completedItems < $totalSaleItems) || $hasPartial ? true : false);
      $isSaleDelivered        = ($deliveredItems == $totalSaleItems ? true : false);
      $isSaleFinished         = ($finishedItems == $totalSaleItems ? true : false);

      if ($isSaleCompleted) {
        if ($isSaleDelivered) {
          $saleStatus = 'delivered';
        } else if ($isSaleFinished) {
          $saleStatus = 'finished';
        } else {
          $saleStatus = 'completed';
        }
      } else if ($isSaleCompletedPartial) {
        if ($isW2PUser) { // Important !!!
          $saleStatus = 'preparing';
        } else {
          $saleStatus = 'completed_partial';
        }
      } else if ($isSpecialCustomer || $payments) {
        if ($isW2PUser) {
          $saleStatus = 'preparing';
        } else {
          $saleStatus = 'waiting_production';
        }
      } else if (!$payments) {
        $saleStatus = 'need_payment';
      }

      $isPaid        = false;
      $isPaidPartial = false;
      $totalPaid     = 0;
      $balance       = 0;
      $paymentStatus = $sale->payment_status;

      if ($payments) {
        foreach ($payments as $payment) {
          $totalPaid += $payment->amount;
        }

        $balance = ($grandTotal - $totalPaid);

        $isPaid        = ($balance == 0 ? true : false);
        $isPaidPartial = ($balance > 0  ? true : false);

        if ($isPaid) {
          $paymentStatus = 'paid';
        } else if ($isPaidPartial) {
          $paymentStatus = ($isDuePayment ? 'due_partial' : 'partial');
        }
      } else {
        if ($isSpecialCustomer) {
          $balance = $grandTotal;
        }

        $paymentStatus = ($isDuePayment ? 'due' : 'pending');
      }

      if ($paymentValidation) { // If any transfer.
        $isPVPending  = ($paymentValidation->status == 'pending'  ? true : false);
        $isPVExpired  = ($paymentValidation->status == 'expired'  ? true : false);

        if ($isPaid) {
          $paymentStatus = 'paid';
        } else if ($isPVPending) {
          $paymentStatus = 'waiting_transfer';
        } else if ($isPVExpired) {
          $paymentStatus = 'expired';
        }
      }

      if ($saleStatus == 'waiting_production' && empty($saleJS->waiting_production_date)) {
        $saleJS->waiting_production_date = date('Y-m-d H:i:s');
      }

      if ($sale->status != 'draft') {
        $saleData['status']         = $saleStatus;
        $saleData['payment_status'] = $paymentStatus;
      }

      if ($sale->status == 'inactive') {
        $saleData['status'] = 'inactive';
      }

      $saleData['paid']       = $totalPaid;
      $saleData['balance']    = $balance;
      $saleData['json']       = json_encode($saleJS);
      $saleData['json_data']  = json_encode($saleJS);

      if (self::update((int)$sale->id, $saleData)) {
        $updateCounter++;
      }

      // If any change of sale status or payment status for W2P sale then dispatch W2P sale info.
      if (isset($saleJS->source) && $saleJS->source == 'W2P') {
        if ($sale->status != $saleStatus || $sale->payment_status != $paymentStatus) {
          dispatchW2PSale($sale->id);
        }
      }
    }

    return $updateCounter;
  }

  /**
   * Update Sale.
   */
  public static function update(int $id, array $data, array $items = [])
  {
    $sale = self::getRow(['id' => $id]);

    if (!$sale) {
      setLastError('Sale is not found.');
      return false;
    }

    $saleJS = getJSON($sale->json);

    if (isset($data['approved'])) {
      $saleJS->approved = $data['approved'];

      unset($data['approved']);
    }

    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);

      $data['biller_id']  = $biller->id;
    }

    if (isset($data['customer'])) {
      $customer = Customer::getRow(['phone' => $data['customer']]);

      $data['customer_id']    = $customer->id;
      $data['customer_name']  = $customer->name;
    }

    if (isset($data['cashier'])) {
      $cashier = User::getRow(['phone' => $data['cashier']]);
      $saleJS->cashier_by = $cashier->id;

      unset($data['cashier']);
    }

    if (isset($data['est_complete_date'])) {
      $saleJS->est_complete_date = $data['est_complete_date'];
      unset($data['est_complete_date']);
    }

    if (isset($data['payment_due_date'])) {
      $saleJS->payment_due_date = $data['payment_due_date'];
      unset($data['payment_due_date']);
    }

    if (isset($data['source'])) {
      $saleJS->source = $data['source'];
      unset($data['source']);
    }

    if (isset($data['vouchers'])) {
      $saleJS->vouchers = $data['vouchers'];
      unset($data['vouchers']);
    }

    if (isset($data['waiting_production_date'])) {
      $saleJS->waiting_production_date = $data['waiting_production_date'];
      unset($data['waiting_production_date']);
    }

    if (isset($data['warehouse'])) {
      $warehouse = Warehouse::getRow(['code' => $data['warehouse']]);

      $data['warehouse_id'] = $warehouse->id;
    }

    if ($items) {
      SaleItem::delete(['sale_id' => $sale->id]);
      Stock::delete(['sale_id' => $sale->id]);

      foreach ($items as $item) {
        $product  = Product::getRow(['code' => $item['code']]);
        $operator = User::getRow(['phone' => $item['operator']]);

        if (!empty($item['width']) && !empty($item['length'])) {
          $area = floatval($item['width']) * floatval($item['length']);
          $quantity = ($area * floatval($item['quantity']));
        } else {
          $area           = 1;
          $quantity       = floatval($item['quantity']);
          $item['width']  = 1;
          $item['length'] = 1;
        }

        $saleItemJS = json_encode([
          'w'             => floatval($item['width']),
          'l'             => floatval($item['length']),
          'area'          => $area,
          'sqty'          => floatval($item['quantity']),
          'spec'          => ($item['spec'] ?? ''),
          'status'        => $item['status'],
          'operator_id'   => $operator->id,
          'due_date'      => ($data['due_date'] ?? ''),
          'completed_at'  => ($item['completed_at'] ?? '')
        ]);

        $saleItemId = SaleItem::add([
          'sale'          => $sale->reference,
          'product'       => $product->code,
          'price'         => $item['price'],
          'quantity'      => $quantity,
          'finished_qty'  => ($item['finished_qty'] ?? 0),
          'subtotal'      => (floatval($item['price']) * $quantity),
          'json'          => $saleItemJS,
          'json_data'     => $saleItemJS
        ]);

        if (!$saleItemId) {
          return false;
        }
      }
    }

    $data['json']       = json_encode($saleJS);
    $data['json_data']  = json_encode($saleJS);

    DB::table('sales')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
