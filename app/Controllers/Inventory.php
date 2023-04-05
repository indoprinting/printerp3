<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{
  Attachment,
  DB,
  InternalUse,
  Product,
  ProductCategory,
  Stock,
  StockAdjustment,
  Warehouse,
  WarehouseProduct
};

class Inventory extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getCategories()
  {
    checkPermission('ProductCategory.View');

    $dt = new DataTables('categories');
    $dt
      ->select("id, code, name, parent_code, description")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/category/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/category/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/category/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->generate();
  }

  public function getInternalUses()
  {
    checkPermission('InternalUse.View');

    $dt = new DataTables('internal_uses');
    $dt
      ->select("internal_uses.id AS id, internal_uses.date, internal_uses.reference,
        pic.fullname, whfrom.name AS warehouse_from_name, whto.name AS warehouse_to_name,
        internal_uses.items, internal_uses.grand_total, internal_uses.counter,
        internal_uses.note, internal_uses.status, internal_uses.created_at,
        internal_uses.attachment")
      ->join('warehouse whfrom', 'whfrom.id = internal_uses.from_warehouse_id', 'left')
      ->join('warehouse whto', 'whto.id = internal_uses.to_warehouse_id', 'left')
      ->join('users pic', 'pic.id = internal_uses.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/internaluse/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/internaluse/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/internaluse/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('grand_total', function ($data) {
        return '<div class="float-right">' . formatNumber($data['grand_total']) . '</div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->generate();
  }

  public function getProducts()
  {
    checkPermission('Product.View');

    $dt = new DataTables('products');
    $dt
      ->select("products.id AS id, products.id AS cid, products.code, products.name, products.type,
        categories.name AS category_name, products.cost, products.markon_price, products.quantity")
      ->join('categories', 'categories.id = products.category_id', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
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
      ->editColumn('cid', function ($data) {
        return "<input class=\"checkbox\" type=\"checkbox\" value=\"{$data['cid']}\">";
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
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
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

  public function category()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('ProductCategory.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.category'), 'slug' => 'category', 'url' => '#']
      ],
      'content' => 'Inventory/Category/index',
      'title' => lang('App.productcategory')
    ];

    return $this->buildPage($this->data);
  }

  protected function category_add()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $code   = getPost('code');
      $name   = getPost('name');
      $parent = getPost('parent');
      $desc   = getPost('desc');

      if (empty($code)) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      $parentCategory = ProductCategory::getRow(['id' => $parent]);

      if ($parentCategory) {
        $parentCode = $parentCategory->code;
      } else {
        $parentCode = null;
      }

      $data = [
        'code'        => $code,
        'name'        => $name,
        'parent_code' => $parentCode,
        'description' => $desc
      ];

      DB::transStart();

      $insertId = ProductCategory::add($data);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product category has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addproductcategory');

    $this->response(200, ['content' => view('Inventory/Category/add', $this->data)]);
  }

  protected function category_edit($id = null)
  {
    $category = ProductCategory::getRow(['id' => $id]);

    if (!$category) {
      $this->response(404, ['message' => 'Product Category is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $code   = getPost('code');
      $name   = getPost('name');
      $parent = getPost('parent');
      $desc   = getPost('desc');

      if (empty($code)) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      $parentCategory = ProductCategory::getRow(['id' => $parent]);

      if ($parentCategory) {
        $parentCode = $parentCategory->code;
      } else {
        $parentCode = null;
      }

      $data = [
        'code'        => $code,
        'name'        => $name,
        'parent_code' => $parentCode,
        'description' => $desc
      ];

      DB::transStart();

      $insertId = ProductCategory::update((int)$id, $data);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product category has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['category'] = $category;
    $this->data['title']    = lang('App.editproductcategory');

    $this->response(200, ['content' => view('Inventory/Category/edit', $this->data)]);
  }

  protected function category_view($id = null)
  {
    $category = ProductCategory::getRow(['id' => $id]);

    if (!$category) {
      $this->response(404, ['message' => 'Product Category is not found.']);
    }

    $parent = ProductCategory::getRow(['code' => $category->parent_code]);

    $this->data['category'] = $category;
    $this->data['parent']   = $parent;
    $this->data['title']    = lang('App.viewproductcategory');

    $this->response(200, ['content' => view('Inventory/Category/view', $this->data)]);
  }

  /**
   * Internal Use
   * 
   * Decrease quantity warehouseFrom without increase quantity warehouseTo.
   * 
   * category:
   *  - consumable: completed
   *  - sparepart: need_approval, approved
   *    - packing:
   *      - cancelled:
   *        - returned
   *      - installed:
   *        - completed
   * */
  public function internaluse()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('InternalUse.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.internaluse'), 'slug' => 'internaluse', 'url' => '#']
      ],
      'content' => 'Inventory/InternalUse/index',
      'title' => lang('App.internaluse')
    ];

    return $this->buildPage($this->data);
  }

  protected function internaluse_add()
  {
    checkPermission('InternalUse.Add');

    if (requestMethod() == 'POST') {
      $category         = getPost('category');
      $warehouseIdFrom  = getPost('warehousefrom');
      $warehouseIdTo    = getPost('warehouseto');

      $data = [
        'date'              => dateTimePHP(getPost('date')),
        'from_warehouse_id' => $warehouseIdFrom,
        'to_warehouse_id'   => $warehouseIdTo,
        'category'          => $category,
        'note'              => stripTags(getPost('note')),
        'status'            => 'need_approval',
        'supplier_id'       => getPost('supplier'),
        'ts_id'             => getPost('teamsupport'),
      ];

      // Auto complete for consumable category.
      if ($data['category'] == 'consumable') {
        $data['status'] = 'completed';
      }

      $itemId       = getPost('item[id]');
      $itemCode     = getPost('item[code]');
      $itemCounter  = getPost('item[counter]');
      $itemMachine  = getPost('item[machine]');
      $itemQty      = getPost('item[quantity]');
      $itemUcr      = getPost('item[ucr]'); // Unique Code Replacement.


      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        // Prevent input lower counter than current counter.
        if (!empty($itemCounter[$a])) {
          $whp = WarehouseProduct::getRow(['product_code' => 'KLIKPOD', 'warehouse_id' => $warehouseIdTo]);

          if ($whp) {
            $lastKLIKQty = intval($whp->quantity);

            if ($lastKLIKQty > intval($itemCounter[$a])) {
              $this->response(400, ['message' => "Klik {$itemCounter[$a]} tidak sesuai klik terakhir {$lastKLIKQty}."]);
            }
          }
        }

        $items[] = [
          'id'          => $itemId[$a],
          'counter'     => $itemCounter[$a],
          'machine_id'  => $itemMachine[$a],
          'quantity'    => $itemQty[$a],
          'ucr'         => $itemUcr[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = InternalUse::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Internal Use has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addinternaluse');

    $this->response(200, ['content' => view('Inventory/InternalUse/add', $this->data)]);
  }

  protected function internaluse_delete($id = null)
  {
    $iUse = InternalUse::getRow(['id' => $id]);
    $iUseItems = Stock::get(['internal_use_id' => $id]);

    if (!$iUse) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    DB::transStart();

    Attachment::delete(['hashname' => $iUse->attachment]);
    Stock::delete(['internal_use_id' => $id]);

    foreach ($iUseItems as $iUseItem) {
      Product::sync((int)$iUseItem->product_id, (int)$iUse->from_warehouse_id);
    }

    $res = InternalUse::delete(['id' => $id]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Internal Use has been deleted.']);
    }

    $this->response(400, ['message' => getLastError()]);
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

  protected function product_delete($id = null)
  {
    $product = Product::getRow(['id' => $id]);

    if (!$product) {
      $this->response(404, ['message' => 'Product is not found.']);
    }

    DB::transStart();

    $res = Product::delete(['id' => $id]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Product has been deleted.']);
    }

    $this->response(400, ['message' => getLastError()]);
  }

  protected function product_sync()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $ids = getPost('id');

      if (empty($ids)) {
        $ids = [];

        foreach (Product::get(['active' => 1]) as $product) {
          $ids[] = $product->id;
        }
      }

      $synced = 0;

      foreach (Warehouse::get(['active' => 1]) as $warehouse) {
        foreach ($ids as $productId) {
          $res = Product::sync((int)$productId, (int)$warehouse->id);

          if ($res) {
            $synced++;
          }
        }
      }

      $this->response(200, ['message' => "{$synced} products have been synced."]);
    }

    $this->response(400, ['message' => 'Bad request.']);
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
        'date'          => dateTimePHP(getPost('date')),
        'warehouse_id'  => getPost('warehouse'),
        'mode'          => getPost('mode'),
        'note'          => stripTags(getPost('note'))
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
        'date'          => dateTimePHP(getPost('date')),
        'warehouse_id'  => getPost('warehouse'),
        'mode'          => getPost('mode'),
        'note'          => stripTags(getPost('note'))
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
