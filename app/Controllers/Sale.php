<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;

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
    $this->data['title'] = lang('App.addsale');

    if (requestMethod() == 'POST' && isAJAX()) {
      $this->response(400, ['message' => 'Not implemented']);
    }

    $this->response(200, ['content' => view('Sale/add', $this->data)]);
  }
}
