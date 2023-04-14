<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{
  Attachment,
  Biller,
  Customer,
  DB,
  Payment,
  PaymentValidation,
  PriceGroup,
  Product,
  ProductCategory,
  ProductPrice,
  Sale as Invoice,
  SaleItem,
  Stock,
  User,
  Voucher
};

class Sale extends BaseController
{
  public function index()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Sale.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.sale'), 'slug' => 'sale', 'url' => '#'],
        ['name' => lang('App.invoice'), 'slug' => 'invoice', 'url' => '#']
      ],
      'content' => 'Sale/index',
      'title' => lang('App.invoice')
    ];

    return $this->buildPage($this->data);
  }

  public function getSales()
  {
    checkPermission('Sale.View');

    $billers        = getPost('biller');
    $customers      = getPost('customer');
    $warehouses     = getPost('warehouse');
    $status         = getPost('status');
    $paymentStatus  = getPost('payment_status');
    $createdBy      = getPost('created_by');
    $receivable     = (getPost('receivable') == 1);
    $startDate      = (getPost('start_date') ?? date('Y-m-d', strtotime('-1 month')));
    $endDate        = (getPost('end_date') ?? date('Y-m-d'));

    $dt = new DataTables('sales');
    $dt
      ->select("sales.id AS id, sales.date, sales.reference, pic.fullname,
        biller.name AS biller_name, warehouse.name AS warehouse_name,
        CONCAT(customers.name, ' (', customers.phone, ')') AS customer_name,
        customergroup.name AS customergroup_name,
        sales.status, sales.payment_status, sales.grand_total, sales.paid, sales.balance,
        sales.created_at, sales.attachment")
      ->join('biller', 'biller.code = sales.biller', 'left')
      ->join('customers', 'customers.phone = sales.customer', 'left')
      ->join('customergroup', 'customergroup.id = customers.customer_group_id', 'left')
      ->join('users pic', 'pic.id = sales.created_by', 'left')
      ->join('warehouse', 'warehouse.code = sales.warehouse', 'left')
      ->where("sales.date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('sale/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('sale/print/' . $data['id']) . '"
                target="_blank">
                <i class="fad fa-fw fa-print"></i> ' . lang('App.print') . '
              </a>
              <a class="dropdown-item" href="' . base_url('sale/print/' . $data['id']) . '?deliverynote=1"
                target="_blank">
                <i class="fad fa-fw fa-print"></i> ' . lang('App.deliverynote') . '
              </a>
              <a class="dropdown-item" href="' . base_url('sale/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('payment/add/sale/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.addpayment') . '
              </a>
              <a class="dropdown-item" href="' . base_url('payment/view/sale/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.viewpayment') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('finance/validation/manual/sale/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.manualvalidation') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('sale/reset/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-undo"></i> ' . lang('App.resetcomplete') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('sale/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->editColumn('grand_total', function ($data) {
        return '<div class="float-right">' . formatNumber($data['grand_total']) . '</div>';
      })
      ->editColumn('paid', function ($data) {
        return '<div class="float-right">' . formatNumber($data['paid']) . '</div>';
      })
      ->editColumn('balance', function ($data) {
        return '<div class="float-right">' . formatNumber($data['balance']) . '</div>';
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

    if ($customers) {
      $dt->whereIn('sales.customer_id', $customers);
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

    if ($createdBy) {
      $dt->whereIn('pic.phone', $createdBy);
    }

    if ($receivable) {
      $dt->where('balance >', 0)->where('customergroup.allow_production', 1);
    }

    $dt->generate();
  }

  public function getVouchers()
  {
    checkPermission('Voucher.View');

    $createdBy  = getPost('created_by');
    $startDate  = getPost('start_date');
    $endDate    = getPost('end_date');

    $dt = new DataTables('voucher');
    $dt
      ->select("voucher.id AS id, voucher.created_at, voucher.code, voucher.name, voucher.amount,
      voucher.method, voucher.percent, voucher.quota, voucher.valid_from, voucher.valid_to,
      creator.fullname")
      ->join('users creator', 'creator.id = voucher.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('sale/voucher/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('sale/voucher/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalDefault"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('sale/voucher/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('amount', function ($data) {
        return formatCurrency($data['amount']);
      })
      ->editColumn('method', function ($data) {
        return renderStatus($data['method']);
      })
      ->editColumn('percent', function ($data) {
        return $data['percent'] . ' %';
      });

    if ($startDate) {
      $dt->where("voucher.created_at >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("voucher.created_at <= '{$endDate} 23:59:59'");
    }

    if ($createdBy) {
      $dt->whereIn('voucher.created_by', $createdBy);
    }

    $dt->generate();
  }

  public function add()
  {
    checkPermission('Sale.Add');

    if (requestMethod() == 'POST' && isAJAX()) {
      $date       = dateTimePHP(getPost('date'));
      $biller     = getPost('biller');
      $warehouse  = getPost('warehouse');
      $cashier    = getPost('cashier');
      $customer   = getPost('customer');
      $discount   = filterDecimal(getPost('discount') ?? 0);
      $dueDate    = dateTimePHP(getPost('duedate'));
      $note       = getPost('note');
      $approved   = (getPost('approved') == 1 ? 1 : 0);
      $transfer   = (getPost('transfer') == 1);
      $draft      = (getPost('draft') == 1);
      $rawItems   = getPost('item');
      $vouchers   = getPost('voucher');

      if (empty($biller)) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($warehouse)) {
        $this->response(400, ['message' => 'Warehouse is required.']);
      }

      if (empty($customer)) {
        $this->response(400, ['message' => 'Customer is required.']);
      }

      if (empty($rawItems) || !is_array($rawItems)) {
        $this->response(400, ['message' => 'Item is empty or not valid.']);
      }

      // Convert rawItems to items
      $items = [];

      for ($a = 0; $a < count($rawItems['id']); $a++) {
        if (empty($rawItems['operator'][$a])) {
          $this->response(400, ['message' => "Operator is empty for {$rawItems['code'][$a]}. Please set operator!"]);
        }

        $items[] = [
          'id'          => $rawItems['id'][$a],
          'spec'        => $rawItems['spec'][$a],
          'width'       => $rawItems['width'][$a],
          'length'      => $rawItems['length'][$a],
          'price'       => filterDecimal($rawItems['price'][$a]),
          'quantity'    => $rawItems['quantity'][$a],
          'operator_id' => $rawItems['operator'][$a],
        ];
      }

      unset($rawItems);

      $data = [
        'date'          => $date,
        'biller_id'     => $biller,
        'warehouse_id'  => $warehouse,
        'cashier_id'    => $cashier,
        'customer_id'   => $customer,
        'due_date'      => $dueDate,
        'note'          => $note,
        'source'        => 'PrintERP3',
        'approved'      => $approved,
        'vouchers'      => $vouchers
      ];

      if ($discount) {
        $data['discount'] = $discount;
      }

      if ($draft) {
        $data['status'] = 'draft';
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertId = Invoice::add($data, $items);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      if ($transfer) {
        $sale = Invoice::getRow(['id' => $insertId]);

        $res = PaymentValidation::add([
          'sale_id'   => $sale->id,
          'biller_id' => $sale->biller_id,
          'amount'    => $sale->grand_total,
        ]);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }

        Invoice::sync(['id' => $insertId]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Invoice has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addsale');

    $this->response(200, ['content' => view('Sale/add', $this->data)]);
  }

  public function delete($id = null)
  {
    checkPermission('Sale.Delete');

    $sale = Invoice::getRow(['id' => $id]);

    if (!$sale) {
      $this->response(404, ['message' => 'Invoice is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      $res = Invoice::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      $res = SaleItem::delete(['sale_id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      Attachment::delete(['hashname' => $sale->attachment]);
      Payment::delete(['sale_id' => $id]);
      PaymentValidation::delete(['sale_id' => $id]);
      Stock::delete(['sale_id' => $id]);
      Invoice::sync(['id' => $id]);

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Invoice has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to delete invoice.']);
  }

  public function edit($id = null)
  {
    $sale = Invoice::getRow(['id' => $id]);

    if (!$sale) {
      $this->response(404, ['message' => 'Invoice is not found.']);
    }

    if ($sale->status == 'draft' && session('login')->user_id == $sale->created_by) {
      checkPermission('Sale.Draft');
    } else {
      checkPermission('Sale.Edit');
    }

    $customer = Customer::getRow(['id' => $sale->customer_id]);

    if (!$sale) {
      $this->response(404, ['message' => 'Customer is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $date       = dateTimePHP(getPost('date'));
      $biller     = getPost('biller');
      $warehouse  = getPost('warehouse');
      $cashier    = getPost('cashier');
      $customer   = getPost('customer');
      $discount   = filterDecimal(getPost('discount') ?? 0);
      $dueDate    = dateTimePHP(getPost('duedate'));
      $note       = getPost('note');
      $approved   = (getPost('approved') == 1 ? 1 : 0);
      $transfer   = (getPost('transfer') == 1);
      $draft      = (getPost('draft') == 1);
      $rawItems   = getPost('item');
      $vouchers   = getPost('voucher');

      if (empty($biller)) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($warehouse)) {
        $this->response(400, ['message' => 'Warehouse is required.']);
      }

      if (empty($customer)) {
        $this->response(400, ['message' => 'Customer is required.']);
      }

      if (empty($rawItems) || !is_array($rawItems)) {
        $this->response(400, ['message' => 'Item is empty or not valid.']);
      }

      // Convert rawItems to items
      $items = [];

      for ($a = 0; $a < count($rawItems['code']); $a++) {
        // if (empty($rawItems['operator'][$a])) {
        //   $this->response(400, ['message' => "Operator is empty for {$rawItems['code'][$a]}. Please set operator!"]);
        // }

        $items[] = [
          'id'            => $rawItems['id'][$a],
          'width'         => $rawItems['width'][$a],
          'length'        => $rawItems['length'][$a],
          'spec'          => $rawItems['spec'][$a],
          'price'         => filterDecimal($rawItems['price'][$a]),
          'quantity'      => $rawItems['quantity'][$a],
          'operator_id'   => $rawItems['operator'][$a],
          'status'        => $rawItems['status'][$a],
          'finished_qty'  => $rawItems['finished_qty'][$a],
          'complete'      => getJSON($rawItems['complete'][$a]),
          'completed_at'  => $rawItems['completed_at'][$a]
        ];
      }

      unset($rawItems);

      $data = [
        'date'          => $date,
        'biller_id'     => $biller,
        'warehouse_id'  => $warehouse,
        'cashier_id'    => $cashier,
        'customer_id'   => $customer,
        'due_date'      => $dueDate,
        'note'          => $note,
        'source'        => 'PrintERP3',
        'approved'      => $approved
      ];

      if ($discount) {
        $data['discount'] = floatval($discount);
      }

      if ($vouchers) {
        $data['vouchers'] = $vouchers;
      }

      if ($draft) {
        $data['status'] = 'draft';
      } else {
        $data['status'] = 'need_payment';
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $data = setUpdatedBy($data);

      $res = Invoice::update((int)$id, $data, $items);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      if ($transfer) {
        $sale = Invoice::getRow(['id' => $id]);

        $res = PaymentValidation::add([
          'sale'    => $sale->reference,
          'biller'  => $sale->biller,
          'amount'  => $sale->grand_total,
        ]);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }
      }

      Invoice::sync(['id' => $id]);

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Invoice has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $items = [];
    $saleItems = SaleItem::get(['sale_id' => $sale->id]);

    foreach ($saleItems as $saleItem) {
      $product      = Product::getRow(['code' => $saleItem->product]);
      $saleItemJS   = getJSON($saleItem->json);
      $operator     = User::getRow(['id' => $saleItemJS->operator_id]);

      $items[] = [
        'id'            => $saleItem->product_id,
        'code'          => $saleItem->product_code,
        'name'          => $saleItem->product_name,
        'category'      => ProductCategory::getRow(['id' => $product->category_id])->code,
        'width'         => floatval($saleItemJS->w),
        'length'        => floatval($saleItemJS->l),
        'quantity'      => floatval($saleItemJS->sqty),
        'finished_qty'  => floatval($saleItem->finished_qty),
        'spec'          => $saleItemJS->spec,
        'complete'      => ($saleItemJS->complete ?? []),
        'completed_at'  => $saleItemJS->completed_at,
        'operator'      => ($operator ? $operator->phone : ''),
        'status'        => $saleItem->status,
        'type'          => $saleItem->product_type,
        'ranges'        => getJSON($product->price_ranges_value),
        'prices'        => [
          floatval($saleItem->price), floatval($saleItem->price), floatval($saleItem->price),
          floatval($saleItem->price), floatval($saleItem->price), floatval($saleItem->price)
        ]
      ];
    }

    $this->data['sale']   = $sale;
    $this->data['saleJS'] = getJSON($sale->json);
    $this->data['items']  = $items;
    $this->data['title']  = lang('App.editsale') . ' ' . $sale->reference;

    $this->response(200, ['content' => view('Sale/edit', $this->data)]);
  }

  public function preview()
  {
    $this->data['title'] = lang('App.preview');

    $this->response(200, ['content' => view('Sale/preview', $this->data)]);
  }

  public function print($id = null)
  {
    checkPermission('Sale.View');

    $sale = Invoice::getRow(['id' => $id]);

    if (!$sale) {
      $this->response(404, ['message' => 'Invoice is not found.']);
    }

    $saleItems = SaleItem::get(['sale_id' => $sale->id]);

    $this->data['sale']       = $sale;
    $this->data['saleJS']     = getJSON($sale->json);
    $this->data['saleItems']  = $saleItems;
    $this->data['title']      = "Invoice {$sale->reference}";

    return view('Sale/print', $this->data);
  }

  public function reset($id = null)
  {
    checkPermission('Sale.ResetComplete');

    $sale = Invoice::getRow(['id' => $id]);

    if (!$sale) {
      $this->response(404, ['message' => 'Invoice is not found.']);
    }

    DB::transStart();

    Stock::delete(['sale_id' => $id]);

    $res = Invoice::update((int)$id, ['status' => 'waiting_production']);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    foreach (SaleItem::get(['sale_id' => $id]) as $saleItem) {
      $saleItemJS = getJSON($saleItem->json);

      $saleItemJS->complete = [];
      // Deprecated: 2 lines below.
      $saleItemJS->completed_at = '';
      $saleItemJS->operator_id = 0;

      $json = json_encode($saleItemJS);

      SaleItem::update((int)$saleItem->id, [
        'finished_qty'  => 0,
        'status'        => 'waiting_production',
        'json'          => $json,
        'json_data'     => $json
      ]);
    }

    Invoice::sync(['id' => $id]);

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Invoice complete has been reset.']);
    }

    $this->response(400, ['message' => getLastError()]);
  }

  public function view($id = null)
  {
    checkPermission('Sale.View');

    $sale = Invoice::getRow(['id' => $id]);

    if (!$sale) {
      $this->response(404, ['message' => 'Invoice is not found.']);
    }

    Invoice::sync(['id' => $id]);

    $saleItems = SaleItem::get(['sale_id' => $sale->id]);

    $this->data['sale']       = $sale;
    $this->data['saleJS']     = getJSON($sale->json);
    $this->data['saleItems']  = $saleItems;
    $this->data['biller']     = Biller::getRow(['code' => $sale->biller]);
    $this->data['customer']   = Customer::getRow(['id' => $sale->customer_id]);
    $this->data['title']      = "Invoice {$sale->reference}";

    $this->response(200, ['content' => view('Sale/view', $this->data)]);
  }

  public function voucher()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Voucher.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.sale'), 'slug' => 'sale', 'url' => '#'],
        ['name' => lang('App.voucher'), 'slug' => 'voucher', 'url' => '#']
      ],
      'content' => 'Sale/Voucher/index',
      'title' => lang('App.voucher')
    ];

    return $this->buildPage($this->data);
  }

  protected function voucher_add()
  {
    checkPermission('Voucher.Add');

    if (requestMethod() == 'POST' && isAJAX()) {
      $code       = getPost('code');
      $name       = getPost('name');
      $amount     = filterDecimal(getPost('amount'));
      $method     = getPost('method');
      $percent    = getPost('percent');
      $quota      = getPost('quota');
      $validFrom  = dateTimePHP(getPost('validfrom'));
      $validTo    = dateTimePHP(getPost('validto'));

      if (empty($code)) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($amount) && empty($percent)) {
        $this->response(400, ['message' => 'Currency amount or Percent are required.']);
      }

      if (empty($quota)) {
        $this->response(400, ['message' => 'Quota is required.']);
      }

      if (empty($validFrom)) {
        $this->response(400, ['message' => 'Valid From is required.']);
      }

      if (empty($validTo)) {
        $this->response(400, ['message' => 'Valid To is required.']);
      }

      try {
        $validFrom  = new \DateTime($validFrom);
        $validTo    = new \DateTime($validTo);
      } catch (\Exception $e) {
        $this->response(400, ['message' => $e->getMessage()]);
      }

      if ($validFrom->getTimeStamp() >= $validTo->getTimeStamp()) {
        $this->response(400, ['message' => 'Valid From cannot be exceed or equal than Valid To.']);
      }

      $data = [
        'code'        => $code,
        'name'        => $name,
        'amount'      => $amount,
        'method'      => $method,
        'percent'     => $percent,
        'quota'       => $quota,
        'valid_from'  => $validFrom->format('Y-m-d H:i:s'),
        'valid_to'    => $validTo->format('Y-m-d H:i:s')
      ];

      DB::transStart();

      $insertId = Voucher::add($data);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Voucher has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addvoucher');

    $this->response(200, ['content' => view('Sale/Voucher/add', $this->data)]);
  }

  protected function voucher_delete($id = null)
  {
    checkPermission('Voucher.Delete');

    $voucher = Voucher::getRow(['id' => $id]);

    if (!$voucher) {
      $this->response(404, ['message' => 'Voucher is not found.']);
    }

    DB::transStart();

    $res = Voucher::delete(['id' => $id]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Voucher has been deleted.']);
    }

    $this->response(400, ['message' => getLastError()]);
  }

  protected function voucher_edit($id = null)
  {
    checkPermission('Voucher.Edit');

    $voucher = Voucher::getRow(['id' => $id]);

    if (!$voucher) {
      $this->response(404, ['message' => 'Voucher is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $code       = getPost('code');
      $name       = getPost('name');
      $amount     = filterDecimal(getPost('amount'));
      $method     = getPost('method');
      $percent    = getPost('percent');
      $quota      = getPost('quota');
      $validFrom  = dateTimePHP(getPost('validfrom'));
      $validTo    = dateTimePHP(getPost('validto'));

      if (empty($code)) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($amount) && empty($percent)) {
        $this->response(400, ['message' => 'Currency amount or Percent are required.']);
      }

      if (empty($quota)) {
        $this->response(400, ['message' => 'Quota is required.']);
      }

      if (empty($validFrom)) {
        $this->response(400, ['message' => 'Valid From is required.']);
      }

      if (empty($validTo)) {
        $this->response(400, ['message' => 'Valid To is required.']);
      }

      try {
        $validFrom  = new \DateTime($validFrom);
        $validTo    = new \DateTime($validTo);
      } catch (\Exception $e) {
        $this->response(400, ['message' => $e->getMessage()]);
      }

      if ($validFrom->getTimeStamp() >= $validTo->getTimeStamp()) {
        $this->response(400, ['message' => 'Valid From cannot be exceed or equal than Valid To.']);
      }

      $data = [
        'code'        => $code,
        'name'        => $name,
        'amount'      => $amount,
        'method'      => $method,
        'percent'     => $percent,
        'quota'       => $quota,
        'valid_from'  => $validFrom->format('Y-m-d H:i:s'),
        'valid_to'    => $validTo->format('Y-m-d H:i:s')
      ];

      DB::transStart();

      $res = Voucher::update((int)$id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Voucher has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['voucher']  = $voucher;
    $this->data['title']    = lang('App.addvoucher');

    $this->response(200, ['content' => view('Sale/Voucher/edit', $this->data)]);
  }

  protected function voucher_view($id = null)
  {
    checkPermission('Voucher.View');

    $voucher = Voucher::getRow(['id' => $id]);

    if (!$voucher) {
      $this->response(404, ['message' => 'Voucher is not found.']);
    }

    $this->data['voucher']  = $voucher;
    $this->data['title']    = lang('App.viewvoucher');

    $this->response(200, ['content' => view('Sale/Voucher/view', $this->data)]);
  }
}
