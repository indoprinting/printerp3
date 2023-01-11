<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;

class Sale extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getSales()
  {
    checkPermission('Sale.View');

    $receivable = (getPost('receivable') == 1 ? 1 : 0);

    $dt = new DataTables('sale');
    $dt
      ->select("sale.id AS id, sale.created_at, sale.reference, creator.fullname,
        biller.name, warehouse.name, customer.name, customergroup.name, sale.status,
        sale.grand_total, sale.paid, sale.balance,
        sale.payment_status, sale.attachment")
      ->join('biller', 'biller.code = sale.biller', 'left')
      ->join('customer', 'customer.phone = sale.customer', 'left')
      ->join('customergroup', 'customergroup.name = customer.group', 'left')
      ->join('users creator', 'creator.id = sale.created_by', 'left')
      ->join('warehouse', 'warehouse.code = sale.warehouse', 'left')
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
              <a class="dropdown-item" href="' . base_url('sale/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
              <a class="dropdown-item" href="' . base_url('sale/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.view') . '
              </a>
            </div>
          </div>';
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

    if ($receivable) {
      $dt->where('balance >', 0)->where('customergroup.allow_production', 1);
    }

    $dt->generate();
  }

  public function invoice()
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
      'content' => 'Sale/Invoice/index',
      'title' => lang('App.invoice')
    ];

    return $this->buildPage($this->data);
  }
}
