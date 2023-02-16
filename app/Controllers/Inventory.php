<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{Attachment, DB, Stock, StockAdjustment, WarehouseProduct};

class Inventory extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getProducts()
  {
    checkPermission('Product.View');

    $dt = new DataTables('products');
    $dt
      ->select("products.id AS id, products.code, products.name, products.type,
        categories.name AS category_name, products.cost, products.markon_price, products.quantity")
      ->join('categories', 'categories.id = products.category_id', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/product/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/product/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/product/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('cost', function ($data) {
        return '<span class="float-right">' . formatNumber($data['cost']) . '</span>';
      })
      ->editColumn('markon_price', function ($data) {
        return '<span class="float-right">' . formatNumber($data['markon_price']) . '</span>';
      })
      ->editColumn('quantity', function ($data) {
        return '<span class="float-right">' . formatNumber($data['quantity']) . '</span>';
      })
      ->generate();
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
      ->editColumn('mode', function ($data) {
        return renderStatus($data['mode']);
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->generate();
  }

  public function product()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Product.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.product'), 'slug' => 'product', 'url' => '#']
      ],
      'content' => 'Inventory/Product/index',
      'title' => lang('App.product')
    ];

    return $this->buildPage($this->data);
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

      if (!is_array($itemCodes) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Items are not present or invalid.']);
      }

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

      $data = $this->useAttachment($data);

      $insertID = StockAdjustment::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $adjustment = StockAdjustment::getRow(['id' => $insertID]);
        $attachment = Attachment::getRow(['hashname' => ($data['attachment'] ?? null)]);

        addActivity("Stock Adjustment {$adjustment->reference} has been added.", [
          'add'         => $adjustment,
          'attachment'  => $attachment
        ]);

        $this->response(201, ['message' => 'Stock Adjustment has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addstockadjustment');

    $this->response(200, ['content' => view('Inventory/StockAdjustment/add', $this->data)]);
  }

  protected function stockadjustment_delete($id = NULL)
  {
    checkPermission('StockAdjustment.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $adjustment = StockAdjustment::getRow(['id' => $id]);
      $stocks     = Stock::get(['adjustment_id' => $id]);
      $attachment = Attachment::getRow(['hashname' => $adjustment->attachment]);

      if (!$adjustment) {
        $this->response(404, ['message' => 'Stock Adjustment is not found.']);
      }

      DB::transStart();

      $res = StockAdjustment::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      Stock::delete(['adjustment_id' => $id]);
      Attachment::delete(['hashname' => $adjustment->attachment]);

      foreach ($stocks as $stock) {
        Stock::sync((int)$stock->product_id, (int)$stock->warehouse_id);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        if ($attachment) {
          $attachment->data = base64_encode($attachment->data);
        }

        addActivity("Stock Adjustment {$adjustment->reference} has been deleted.", [
          'delete'      => $adjustment,
          'attachment'  => $attachment,
          'stocks'      => $stocks
        ]);

        $this->response(200, ['message' => 'Stock Adjustment has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete Stock Adjustment.']);
  }

  protected function stockadjustment_edit($id = null)
  {
    checkPermission('StockAdjustment.Edit');

    $adjustment = StockAdjustment::getRow(['id' => $id]);

    if (!$adjustment) {
      $this->response(404, ['message' => 'Stock Adjustment is not found.']);
    }

    $stocks = Stock::get(['adjustment_id' => $adjustment->id]);

    if (!$stocks) {
      $this->response(404, ['message' => 'Stock Adjustment item is not found.']);
    }

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

      $data = $this->useAttachment($data);

      $insertID = StockAdjustment::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $adjustment = StockAdjustment::getRow(['id' => $insertID]);
        $attachment = Attachment::getRow(['hashname' => ($data['attachment'] ?? null)]);

        addActivity("Stock Adjustment {$adjustment->reference} has been added.", [
          'add'         => $adjustment,
          'attachment'  => $attachment
        ]);

        $this->response(201, ['message' => 'Stock Adjustment has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $items = [];

    foreach ($stocks as $stock) {
      $whProduct = WarehouseProduct::getRow(['product_id' => $stock->product_id, 'warehouse_id' => $stock->warehouse_id]);

      $items[] = [
        'code'        => $stock->product_code,
        'name'        => $stock->product_name,
        'quantity'    => $stock->adjustment_qty,
        'current_qty' => $whProduct->quantity
      ];
    }

    $this->data['adjustment'] = $adjustment;
    $this->data['items']      = $items;
    $this->data['title'] = lang('App.editstockadjustment');

    $this->response(200, ['content' => view('Inventory/StockAdjustment/edit', $this->data)]);
  }

  protected function stockadjustment_view($id = null)
  {
    checkPermission('StockAdjustment.View');

    $adjustment = StockAdjustment::getRow(['id' => $id]);

    if (!$adjustment) {
      $this->response(404, ['message' => 'Stock Adjustment is not found.']);
    }

    $this->data['adjustment'] = $adjustment;
    $this->data['title']      = lang('App.viewstockadjustment');

    $this->response(200, ['content' => view('Inventory/StockAdjustment/view', $this->data)]);
  }
}
