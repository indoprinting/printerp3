<?php

declare(strict_types=1);

namespace App\Models;

class Sale
{
  /**
   * Add new sales.
   * @param array $data
   * @param array $items [ id, spec, width, length, price, quantity, operator ]
   */
  public static function add(array $data, array $items)
  {
    $biller = Biller::getRow(['id' => $data['biller_id']]);

    if (!$biller) {
      setLastError('Biller is not found.');
      return false;
    }

    $customer = Customer::getRow(['id' => $data['customer_id']]);

    if (!$customer) {
      setLastError('Customer is not found.');
      return false;
    }

    $warehouse = Warehouse::getRow(['id' => $data['warehouse_id']]);

    if (!$warehouse) {
      setLastError('Warehouse is not found.');
      return false;
    }

    // Is special customer (Privilege, TOP)
    $isSpecialCustomer = isSpecialCustomer($customer->id);

    $data['status'] = ($data['status'] ?? ($isSpecialCustomer ? 'waiting_production' : 'need_payment'));

    $discount   = floatval($data['discount'] ?? 0); // Currency
    $grandTotal = 0.0;
    $tax        = floatval($data['tax'] ?? 0); // Percent.
    $totalPrice = 0.0;
    $totalItems = 0.0;

    $date       = ($data['date'] ?? date('Y-m-d H:i:s'));
    $reference  = OrderRef::getReference('sale');

    // Calculate totalQty, totalPrice and totalItems.
    foreach ($items as $item) {
      $price    = filterDecimal($item['price']);
      $quantity = filterDecimal($item['quantity']);
      $width    = filterDecimal($item['width'] ?? 1);
      $length   = filterDecimal($item['length'] ?? 1);
      $area     = ($width * $length);

      $totalQty   = ($area * $quantity);
      $totalPrice += round($price * $totalQty);
      $totalItems += $totalQty;
    }

    // Using Vouchers.
    if (!empty($data['vouchers']) && is_array($data['vouchers'])) {
      $discount = useVouchers($data['vouchers'], $totalPrice, $discount);
    }

    // Discount protection prevent minus.
    if ($discount > $totalPrice) {
      $discount = $totalPrice;
    }

    // Tax calculation.
    $taxPrice  = ($tax * 0.01 * $totalPrice);

    // Grand Total.
    $grandTotal = round($totalPrice + $taxPrice - $discount);

    // Get balance.
    $balance = ($isSpecialCustomer ? $grandTotal : 0);

    // Determine use TB by biller and warehouse, if both different, then use tb (1).
    $useTB = isTBSale($biller->code, $warehouse->code);

    // Get payment term.
    $payment_term = filterDecimal($data['payment_term'] ?? 1);
    $payment_term = ($payment_term > 0 ? $payment_term : 1);

    $saleJS = json_encode([
      'approved'                => ($data['approved'] ?? 0),
      'cashier_by'              => ($data['cashier_id'] ?? 0),
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
      'discount'        => $discount,
      'tax'             => $tax,
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

      $res = SaleItem::add((int)$insertId, $items);

      if (!$res) {
        return false;
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

    if ($sale->payment_status == 'paid') {
      setLastError('Invoice is already paid.');
      return false;
    }

    $insertId = Payment::add([
      'sale_id'     => $sale->id,
      'bank_id'     => $data['bank_id'],
      'biller_id'   => $sale->biller_id,
      'amount'      => $data['amount'],
      'type'        => 'received',
      'method'      => ($data['method'] ?? 'Cash'),
      'note'        => ($data['note'] ?? null),
      'attachment'  => ($data['attachment'] ?? null),
      'created_by'  => ($data['created_by'] ?? null)
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

      $completedItems = 0.0;
      $deliveredItems = 0.0;
      $finishedItems  = 0.0;
      $discount       = floatval($sale->discount);
      $tax            = floatval($sale->tax);
      $total          = 0.0;
      $hasPartial     = false;
      $totalSaleItems = 0.0;
      $saleStatus     = $sale->status;

      foreach ($saleItems as $saleItem) {
        $saleItemStatus = $saleItem->status;
        $isItemFinished = ($saleItem->quantity == $saleItem->finished_qty ? true : false);
        $isItemFinishedPartial = ($saleItem->finished_qty > 0 && $saleItem->quantity > $saleItem->finished_qty ? true : false);
        $total += round($saleItem->price * $saleItem->quantity);
        $totalSaleItems++;

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
          $saleItemStatus = 'inactive';
        }

        if ($saleItemStatus == 'draft') {
          continue;
        }

        SaleItem::update((int)$saleItem->id, [
          'status' => $saleItemStatus
        ]);
      }

      // Use Vouchers.
      if (!empty($saleJS->vouchers) && is_array($saleJS->vouchers)) {
        $discount = useVouchers($saleJS->vouchers, $total, $discount);
      }

      // Discount protection prevent minus.
      if ($discount > $total) {
        $discount = $total;
      }

      // Tax calculation.
      $taxPrice   = ($tax * 0.01 * $total);
      $grandTotal = round($total + $taxPrice - $discount);

      $saleData['discount']     = $discount;
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

    if (isset($data['customer_id'])) {
      $customer = Customer::getRow(['id' => $data['customer_id']]);

      if ($customer) {
        $data['customer_id']    = $customer->id;
        $data['customer_name']  = $customer->name;
        $data['customer']       = $customer->phone;
      }
    }

    if (isset($data['cashier_id'])) {
      $cashier = User::getRow(['id' => $data['cashier_id']]);

      if ($cashier) {
        $saleJS->cashier_by = $cashier->id;
      }

      unset($data['cashier_id']);
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

    if ($items) {
      SaleItem::delete(['sale_id' => $sale->id]);
      Stock::delete(['sale_id' => $sale->id]);

      $insertIds = SaleItem::add($id, $items);

      if (!$insertIds) {
        return false;
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
