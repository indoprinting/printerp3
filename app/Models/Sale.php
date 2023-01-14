<?php

declare(strict_types=1);

namespace App\Models;

class Sale
{
  /**
   * Add new Sale.
   */
  public static function add(array $data)
  {
    DB::table('sales')->insert($data);
    return DB::insertID();
  }

  /**
   * Add sale payment.
   * @param int $saleId Sale ID.
<<<<<<< HEAD
   * @param array $data [ *amount, *bank_id ]
=======
   * @param array $data [ *amount, *bank_id, attachment ]
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
   */
  public static function addPayment(int $saleId, array $data)
  {
    $sale = self::getRow(['id' => $saleId]);
<<<<<<< HEAD

=======
    
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
    Payment::add([
      'reference'       => $sale->reference,
      'reference_date'  => $sale->created_at,
      'bank_id'         => $data['bank_id'],
      'biller_id'       => $sale->biller_id,
      'sale_id'         => $sale->id,
      'amount'          => $data['amount'],
      'type'            => 'received',
<<<<<<< HEAD
=======
      'attachment'      => ($data['attachment'] ?? NULL)
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
    ]);

    self::sync(['id' => $sale->id]);

    return TRUE;
  }

  /**
   * Delete Sale.
   */
  public static function delete(array $where)
  {
    DB::table('sales')->delete($where);
    return DB::affectedRows();
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
    return NULL;
  }

  /**
   * Select Sale.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('sales')->select($columns, $escape);
  }

  public static function sync($clause = [])
  {
    $sales = [];

    // $this->syncPaymentValidations(); // Cause memory crash (looping).

    if (!empty($clause['sale_id'])) {
      $saleType = gettype($clause['sale_id']);

      if ($saleType == 'array') {
        $sales = $clause['sale_id'];
      } else if ($saleType == 'integer' || $saleType == 'string') {
        if ($sale = self::getRow(['id' => $clause['sale_id']])) {
          $sales[] = $sale;
        }
      } else {
        setLastError("Sale::sync() Unknown data type '" . gettype($clause['sale_id']) . "'");
        return FALSE;
      }
    } else { // Default if sale_id is NULL.
      $sales = self::get();
    }

    if (empty($sales)) {
      setLastError('Sale::sync() Why sales is empty?');
      return FALSE;
    }

    foreach ($sales as $sale) {
      if (empty($sale->json_data)) {
        setLastError("Models\Sale::sync() Sale ID {$sale->id} has invalid json_data");
        continue;
      }

      $saleJS = getJSON($sale->json_data ?? '{}');
      $saleData = [];

      if (!$saleJS) {
        setLastError("Sale::sync() Invalid sales->json_data in sale id {$sale->id}, {$sale->reference}");
        log_message('error', $sale->json_data);
        continue;
      }

      $isDuePayment      = isDueDate($saleJS->payment_due_date ?? $sale->due_date);
      $isW2PUser         = isW2PUser($sale->created_by); // Is sale created_by user is w2p?
      $isSpecialCustomer = isSpecialCustomer($sale->customer_id); // Special customer (Privilege, TOP)
      $payments          = Payment::get(['sale_id' => $sale->id]);
      $paymentValidation = PaymentValidation::getRow(['sale_id' => $sale->id]);
      $saleItems         = SaleItem::get(['sale_id' => $sale->id]);

      if (empty($saleItems)) {
        setLastError("Sale::sync() Sale items empty. Sale id {$sale->id}, {$sale->reference}");
        continue;
      }

      $completedItems = 0;
      $deliveredItems = 0;
      $finishedItems  = 0;
      $grandTotal     = 0;
      $hasPartial     = FALSE;
      $totalSaleItems = 0;
      $saleStatus     = $sale->status;

      foreach ($saleItems as $saleItem) {
        $saleItemJS = getJSON($saleItem->json_data);
        $saleItemStatus = $saleItemJS->status;
        $totalSaleItems++;
        $grandTotal += round($saleItem->price * $saleItem->quantity);
        $isItemFinished = ($saleItem->quantity == $saleItem->finished_qty ? TRUE : FALSE);
        $isItemFinishedPartial = ($saleItem->finished_qty > 0 && $saleItem->quantity > $saleItem->finished_qty ? TRUE : FALSE);

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
          $hasPartial = TRUE;
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

        $saleItemJS->status = $saleItemStatus;

        SaleItem::update((int)$saleItem->id, ['json_data' => json_encode($saleItemJS)]);
      }

      $grandTotal = round($grandTotal - $sale->discount);

      $saleData['grand_total'] = $grandTotal;

      $isSaleCompleted        = ($completedItems == $totalSaleItems ? TRUE : FALSE);
      $isSaleCompletedPartial = (($completedItems > 0 && $completedItems < $totalSaleItems) || $hasPartial ? TRUE : FALSE);
      $isSaleDelivered        = ($deliveredItems == $totalSaleItems ? TRUE : FALSE);
      $isSaleFinished         = ($finishedItems == $totalSaleItems ? TRUE : FALSE);

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

      $isPaid        = FALSE;
      $isPaidPartial = FALSE;
      $totalPaid     = 0;
      $balance       = 0;
      $paymentStatus = $sale->payment_status;

      if ($payments) {
        foreach ($payments as $payment) {
          $totalPaid += $payment->amount;
        }

        $balance = ($grandTotal - $totalPaid);

        $isPaid        = ($balance == 0 ? TRUE : FALSE);
        $isPaidPartial = ($balance > 0  ? TRUE : FALSE);

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
        $isPVPending  = ($paymentValidation->status == 'pending'  ? TRUE : FALSE);
        $isPVExpired  = ($paymentValidation->status == 'expired'  ? TRUE : FALSE);

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

      $saleData['paid']           = $totalPaid;
      $saleData['balance']        = $balance;
      $saleData['status']         = $saleStatus;
      $saleData['payment_status'] = $paymentStatus;
      $saleData['json_data']      = json_encode($saleJS);

      self::update((int)$sale->id, $saleData);

      // If any change of sale status or payment status for W2P sale then dispatch W2P sale info.
      if (isset($saleJS->source) && $saleJS->source == 'W2P') {
        if ($sale->status != $saleStatus || $sale->payment_status != $paymentStatus) {
          dispatchW2PSale($sale->id);
        }
      }
    }
  }

  /**
   * Update Sale.
   */
  public static function update(int $id, array $data)
  {
    DB::table('sales')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
