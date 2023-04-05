<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{DB, Sale, SaleItem};

class Production extends BaseController
{
  public function getSaleItems()
  {
    checkPermission('Sale.Complete');

    $billers        = getPost('biller');
    $warehouses     = getPost('warehouse');
    $status         = getPost('status');
    $paymentStatus  = getPost('payment_status');
    $operatorBy     = getPost('operator_by');
    $startDate      = (getPost('start_date') ?? date('Y-m-d', strtotime('-1 month')));
    $endDate        = (getPost('end_date') ?? date('Y-m-d'));

    $dt = new DataTables('sale_items');
    $dt
      ->select("sale_items.id AS id, sale_items.date,
        sales.reference, operator.fullname AS operator_name,
        biller.name AS biller_name, warehouse.name AS warehouse_name,
        CONCAT(customers.name, ' (', customers.phone, ')') AS customer_name,
        sale_items.product_name, sales.status, sales.payment_status")
      ->join('sales', 'sales.id = sale_items.sale_id', 'left')
      ->join('biller', 'biller.code = sales.biller', 'left')
      ->join('customers', 'customers.phone = sales.customer', 'left')
      ->join('users operator', "operator.id = sale_items.json->>'$.operator_id'", 'left')
      ->join('warehouse', 'warehouse.code = sales.warehouse', 'left')
      ->whereIn('sale_items.status', ['completed_partial', 'waiting_production'])
      ->where("sales.date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
      ->editColumn('id', function ($data) {
        return '<input class="checkbox" type="checkbox" value="' . $data['id'] . '">';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('payment_status', function ($data) {
        return renderStatus($data['payment_status']);
      });

    $userJS = getJSON(session('login')?->json);

    if (isset($userJS->billers) && !empty($userJS->billers)) {
      if ($billers) {
        $billers = array_merge($billers, $userJS->billers);
      } else {
        $billers = $userJS->billers;
      }
    }

    if (session('login')->biller) {
      if ($billers) {
        $billers[] = session('login')->biller;
      } else {
        $billers = [session('login')->biller];
      }
    }

    if ($billers) {
      $dt->whereIn('sales.biller', $billers);
    }

    if ($warehouses) {
      $dt->whereIn('sales.warehouse', $warehouses);
    }

    if ($status) {
      $dt->whereIn('sales.status', $status);
    }

    if ($paymentStatus) {
      $dt->whereIn('sales.payment_status', $paymentStatus);
    }

    if ($operatorBy) {
      $dt->whereIn("sale_items.json->'$.operator_id'", $operatorBy);
    }

    $dt->generate();
  }

  public function index()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Sale.Complete');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.production'), 'slug' => 'production', 'url' => '#'],
        ['name' => lang('App.saleitem'), 'slug' => 'saleitem', 'url' => '#']
      ],
      'content' => 'Production/index',
      'title' => lang('App.saleitem')
    ];

    return $this->buildPage($this->data);
  }

  public function complete()
  {
    checkPermission('Sale.Complete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $_dbg         = (getPost('_dbg') == 1);
      $items        = getPost('item');
      $operatorId   = getPost('operator');
      $completeDate = dateTimePHP(getPost('completedate'));

      if (!$items) {
        $this->response(400, ['message' => 'No sale items are selected.']);
      }

      $isCompleteOverTime = false;

      DB::transStart();

      for ($a = 0; $a < count($items['id']); $a++) {
        $itemId       = intval($items['id'][$a]);
        $itemCode     = $items['code'][$a];
        $finishedQty  = floatval($items['finished_qty'][$a]);
        $quantity     = floatval($items['quantity'][$a]);
        $saleId       = intval($items['sale_id'][$a]);
        $totalQty     = floatval($items['total_qty'][$a]);

        $sale = Sale::getRow(['id' => $saleId]);

        if (!$sale) {
          $this->response(404, ['message' => 'Invoice is missing.']);
        }

        $saleJS = getJSON($sale->json);

        if (isset($saleJS->approved) && $saleJS->approved != 1) {
          $this->response(400, ['message' => "Sale item {$itemCode} is not approved yet."]);
        }

        if (($finishedQty + $quantity) > $totalQty) {
          $this->response(400, ['message' => "Sale item {$itemCode} cannot over-complete."]);
        }

        if ($quantity <= 0) {
          $this->response(400, ['message' => "Sale item {$itemCode} quantity cannot be zero or less."]);
        }

        if (time() > strtotime($sale->due_date)) {
          $isCompleteOverTime = true;
        }

        if ($isCompleteOverTime && $_dbg) {
          $minutes      = rand(10, (60 * 5)); // 10 minute to 5 hours
          $completeDate = date('Y-m-d H:i:s', strtotime("-{$minutes} minute", strtotime($sale->due_date)));
        }

        $res = SaleItem::complete($itemId, [
          'completed_at'  => $completeDate,
          'completed_by'  => $operatorId,
          'quantity'      => $quantity,
        ]);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }

        Sale::sync(['id' => $sale->id]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Sale items have been completed.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.completeitem');

    $this->response(200, ['content' => view('Production/complete', $this->data)]);
  }
}
