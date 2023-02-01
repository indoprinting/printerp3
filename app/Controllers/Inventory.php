<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables, FileUpload};
use App\Models\{DB, StockAdjustment};

class Inventory extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getStockAdjustments()
  {
    checkPermission('StockAdjustment.View');

    $dt = new DataTables('adjustments');
    $dt
      ->select("adjustments.id AS id, adjustments.date, adjustments.reference,
        warehouse.name AS warehouse_name, adjustments.mode, adjustments.note,
        adjustments.created_at, creator.fullname, adjustments.attachment")
      ->join('warehouse', 'warehouse.id = adjustments.warehouse_id', 'left')
      ->join('users creator', 'creator.id = adjustments.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/stockadjustment/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/stockadjustment/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/stockadjustment/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->generate();
  }

  public function stockadjustment()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('StockAdjustment.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.stockadjustment'), 'slug' => 'stockadjustment', 'url' => '#']
      ],
      'content' => 'Inventory/StockAdjustment/index',
      'title' => lang('App.stockadjustment')
    ];

    return $this->buildPage($this->data);
  }

  protected function stockadjustment_add()
  {
    checkPermission('StockAdjustment.Add');

    if (requestMethod() == 'POST') {
      $data = [
        'date'      => dateTimeJS(getPost('date')),
        'warehouse' => getPost('warehouse'),
        'mode'      => getPost('mode'),
        'note'      => stripTags(getPost('note'))
      ];

      $itemCodes  = getPost('item[code]');
      $itemQty    = getPost('item[quantity]');

      for ($a = 0; $a < count($itemCodes); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Item {$itemCodes[$a]} has invalid quantity."]);
        }

        $items[] = [
          'code'      => $itemCodes[$a],
          'quantity'  => $itemQty[$a]
        ];
      }

      DB::transStart();

      $insertID = StockAdjustment::add($data, $items);

      DB::transComplete();

      if (DB::transStatus()) {
        $adjustment = StockAdjustment::getRow(['id' => $insertID]);

        addActivity("Stock Adjustment {$adjustment->reference} has been added.", [
          'add' => $adjustment
        ]);

        $this->response(201, ['message' => 'Stock Adjustment has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addstockadjustment');

    $this->response(200, ['content' => view('Inventory/StockAdjustment/add', $this->data)]);
  }
}
