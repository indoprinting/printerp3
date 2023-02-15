<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\Attachment;
use App\Models\Biller;
use App\Models\Customer;
use App\Models\DB;
use App\Models\Payment;
use App\Models\Sale as Invoice;
use App\Models\SaleItem;

class Sale extends BaseController
{
  public function index()
  {
    checkPermission('Sale.View');

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

    $billers    = getPost('biller');
    $warehouses = getPost('warehouse');
    $createdBy  = getPost('created_by');
    $receivable = (getPost('receivable') == 1 ? 1 : 0);
    $startDate  = (getPost('start_date') ?? date('Y-m-d', strtotime('-1 month')));
    $endDate    = (getPost('end_date') ?? date('Y-m-d'));

    $dt = new DataTables('sales');
    $dt
      ->select("sales.id AS id, sales.date, sales.reference, pic.fullname,
        biller.name AS biller_name, warehouse.name AS warehouse_name,
        customers.name AS customer_name, customergroup.name AS customergroup_name,
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
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('sale/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('sale/print/' . $data['id']) . '"
                target="_blank">
                <i class="fad fa-fw fa-print"></i> ' . lang('App.print') . '
              </a>
              <a class="dropdown-item" href="' . base_url('sale/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.view') . '
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

    if ($biller = session('login')->biller) {
      $billers = [];
      $billers[] = $biller;
    }

    if ($warehouse = session('login')->warehouse) {
      $warehouses = [];
      $warehouses[] = $warehouse;
    }

    if ($billers) {
      $dt->whereIn('sales.biller', $billers);
    }

    if ($warehouses) {
      $dt->whereIn('sales.warehouse', $warehouses);
    }

    if ($createdBy) {
      $dt->whereIn('sales.created_by', $createdBy);
    }

    if ($receivable) {
      $dt->where('balance >', 0)->where('customergroup.allow_production', 1);
    }

    $dt->generate();
  }

  public function add()
  {
    checkPermission('Sale.Add');

    if (requestMethod() == 'POST' && isAJAX()) {
      $date       = dateTimeJS(getPost('date'));
      $biller     = getPost('biller');
      $warehouse  = getPost('warehouse');
      $cashier    = getPost('cashier');
      $customer   = getPost('customer');
      $dueDate    = dateTimeJS(getPost('duedate'));
      $note       = getPost('note');
      $approve    = (getPost('approve') == 1 ? 1 : 0);
      $rawItems   = getPost('item');

      if (empty($biller)) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($warehouse)) {
        $this->response(400, ['message' => 'Warehouse is required.']);
      }

      if (empty($customer)) {
        $this->response(400, ['message' => 'Customer is required.']);
      }

      if (empty($cashier)) {
        $this->response(400, ['message' => 'Cashier is required.']);
      }

      if (empty($rawItems) || !is_array($rawItems)) {
        $this->response(400, ['message' => 'Item is empty or not valid.']);
      }

      // Convert rawItems to items
      $items = [];

      for ($a = 0; $a < count($rawItems['code']); $a++) {
        if (empty($rawItems['operator'][$a])) {
          $this->response(400, ['message' => "Operator is empty for {$rawItems['code'][$a]}. Please set operator!"]);
        }

        $items[] = [
          'code'      => $rawItems['code'][$a],
          'width'     => $rawItems['width'][$a],
          'length'    => $rawItems['length'][$a],
          'price'     => filterDecimal($rawItems['price'][$a]),
          'quantity'  => $rawItems['quantity'][$a],
          'operator'  => $rawItems['operator'][$a],
        ];
      }

      unset($rawItems);

      $data = [
        'date'      => $date,
        'biller'    => $biller,
        'warehouse' => $warehouse,
        'cashier'   => $cashier,
        'customer'  => $customer,
        'due_date'  => $dueDate,
        'note'      => $note,
        'source'    => 'PrintERP',
        'approved'  => $approve,
      ];

      $data = $this->useAttachment($data);

      DB::transStart();

      $insertId = Invoice::add($data, $items);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
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

      Invoice::delete(['id' => $id]);
      SaleItem::delete(['sale_id' => $id]);
      Attachment::delete(['hashname' => $sale->attachment]);
      Payment::delete(['sale_id' => $id]);

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Invoice has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to delete invoice.']);
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
    $this->data['biller']     = Biller::getRow(['code' => $sale->biller]);
    $this->data['customer']   = Customer::getRow(['id' => $sale->customer_id]);
    $this->data['title']      = "Invoice {$sale->reference}";

    return view('Sale/print', $this->data);
  }
}
