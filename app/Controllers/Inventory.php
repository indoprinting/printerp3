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
  ProductMutation,
  ProductMutationItem,
  Stock,
  StockAdjustment,
  StockOpname,
  StockOpnameItem,
  User,
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

  public function getProductMutations()
  {
    checkPermission('ProductMutation.View');

    $dt = new DataTables('product_mutation');
    $dt
      ->select("product_mutation.id AS id, product_mutation.id AS cid, product_mutation.date,
      product_mutation.reference,
      warehousefrom.name AS warehouse_from_name, warehouseto.name AS warehouse_to_name,
      product_mutation.items, product_mutation.note, product_mutation.status,
      product_mutation.attachment, product_mutation.created_at, creator.fullname")
      ->join('warehouse warehousefrom', 'warehousefrom.id = product_mutation.from_warehouse_id', 'left')
      ->join('warehouse warehouseto', 'warehouseto.id = product_mutation.to_warehouse_id', 'left')
      ->join('users creator', 'creator.id = product_mutation.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/mutation/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/mutation/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/mutation/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('cid', function ($data) {
        return "<input class=\"checkbox\" type=\"checkbox\" value=\"{$data['cid']}\">";
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

  public function getStockOpnames()
  {
    checkPermission('StockOpname.View');

    $dt = new DataTables('stock_opnames');
    $dt
      ->select("stock_opnames.id AS id, stock_opnames.date, stock_opnames.reference,
        adjustment_plus.reference AS plus_ref, adjustment_min.reference AS min_ref,
        creator.fullname, warehouse.name AS warehouse_name,
        stock_opnames.total_lost, stock_opnames.total_plus, stock_opnames.total_edited,
        stock_opnames.status, stock_opnames.note,
        stock_opnames.created_at, stock_opnames.attachment")
      ->join('adjustments adjustment_plus', 'adjustment_plus.id = stock_opnames.adjustment_plus_id', 'left')
      ->join('adjustments adjustment_min', 'adjustment_min.id = stock_opnames.adjustment_min_id', 'left')
      ->join('warehouse', 'warehouse.id = stock_opnames.warehouse_id', 'left')
      ->join('users creator', 'creator.id = stock_opnames.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('inventory/stockopname/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('inventory/stockopname/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('inventory/stockopname/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
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

  protected function category_delete($id = null)
  {
    $category = ProductCategory::getRow(['id' => $id]);

    if (!$category) {
      $this->response(404, ['message' => 'Product category is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      $res = ProductCategory::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product category has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Bad request.']);
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
   * category and status:
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
        'supplier_id'       => getPost('supplier'),
        'ts_id'             => getPost('teamsupport'),
      ];

      $itemId       = getPost('item[id]');
      $itemCode     = getPost('item[code]');
      $itemCounter  = getPost('item[counter]');
      $itemMachine  = getPost('item[machine]');
      $itemQty      = getPost('item[quantity]');
      $itemUnique   = getPost('item[unique]'); // Auto-generated on add.
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
          'unique_code' => $itemUnique[$a],
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

      $this->response(400, ['message' => getLastError()]);
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

    if (requestMethod() == 'POST' && isAJAX()) {

      DB::transStart();

      Attachment::delete(['hashname' => $iUse->attachment]);
      Stock::delete(['internal_use_id' => $id]);

      foreach ($iUseItems as $iUseItem) {
        Product::sync((int)$iUseItem->product_id);
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

    $this->response(400, ['message' => 'Bad Request.']);
  }

  protected function internaluse_edit($id = null)
  {
    checkPermission('InternalUse.Edit');

    $internalUse = InternalUse::getRow(['id' => $id]);

    if (!$internalUse) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

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
        'status'            => getPost('status'), // Status is changeable from edit.
        'supplier_id'       => getPost('supplier'),
        'ts_id'             => getPost('teamsupport'),
      ];

      $itemId       = getPost('item[id]');
      $itemCode     = getPost('item[code]');
      $itemCounter  = getPost('item[counter]');
      $itemMachine  = getPost('item[machine]');
      $itemQty      = getPost('item[quantity]');
      $itemUnique   = getPost('item[unique]');
      $itemUcr      = getPost('item[ucr]'); // Unique Code Replacement.

      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        $items[] = [
          'id'          => $itemId[$a],
          'counter'     => $itemCounter[$a],
          'machine_id'  => $itemMachine[$a],
          'quantity'    => $itemQty[$a],
          'unique_code' => $itemUnique[$a],
          'ucr'         => $itemUcr[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = InternalUse::update((int)$id, $data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Internal Use has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $items = [];

    foreach (Stock::get(['internal_use_id' => $internalUse->id]) as $stock) {
      $whp = WarehouseProduct::getRow([
        'product_id' => $stock->product_id, 'warehouse_id' => $internalUse->from_warehouse_id
      ]);

      $items[] = [
        'id'          => intval($stock->product_id),
        'code'        => $stock->product_code,
        'name'        => $stock->product_name,
        'unit'        => $stock->unit,
        'quantity'    => floatval($stock->quantity),
        'counter'     => $stock->spec,
        'unique'      => $stock->unique_code,
        'ucr'         => $stock->ucr,
        'current_qty' => floatval($whp->quantity),
        'machine'     => intval($stock->machine_id),
      ];
    }

    $this->data['internalUse']  = $internalUse;
    $this->data['items']        = $items;
    $this->data['title']        = lang('App.editinternaluse');

    $this->response(200, ['content' => view('Inventory/InternalUse/edit', $this->data)]);
  }

  protected function internaluse_status($id = null)
  {
    $internalUse = InternalUse::getRow(['id' => $id]);

    if (!$internalUse) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    $iuseItems = Stock::get(['internal_use_id' => $internalUse->id]);

    if (!$iuseItems) {
      $this->response(404, ['message' => 'Internal Use items are not found.']);
    }

    $items        = [];
    $status       = getPost('status');
    $itemId       = getPost('item[id]');
    $itemCode     = getPost('item[code]');
    $itemCounter  = getPost('item[counter]');

    foreach ($iuseItems as $iuseItem) {
      $counter = $iuseItem->spec;

      if ($status == 'completed') {
        for ($a = 0; $a < count($itemId); $a++) {
          if (empty($itemCounter[$a])) {
            $this->response(400, ['message' => "Counter {$itemCode[$a]} harus diisi."]);
          }

          if ($iuseItem->product_id == $itemId[$a]) {
            $counter = $itemCounter[$a];
            break;
          }
        }
      }

      $items[] = [
        'id'          => $iuseItem->product_id,
        'counter'     => $counter,
        'machine_id'  => $iuseItem->machine_id,
        'quantity'    => $iuseItem->quantity,
        'unique_code' => $iuseItem->unique_code,
        'ucr'         => $iuseItem->ucr
      ];
    }

    DB::transStart();

    $res = InternalUse::update((int)$id, ['status' => $status], $items);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Internal Use status has been updated.']);
    }

    $this->response(400, ['message' => 'Failed to update status.']);
  }

  protected function internaluse_view($id = null)
  {
    checkPermission('InternalUse.View');

    $internalUse = InternalUse::getRow(['id' => $id]);

    if (!$internalUse) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    $this->data['internalUse']  = $internalUse;
    $this->data['title']        = lang('App.viewinternaluse');

    $this->response(200, ['content' => view('Inventory/InternalUse/view', $this->data)]);
  }

  /**
   * Product Mutation.
   * Status: packing -> received
   */
  public function mutation()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('ProductMutation.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.productmutation'), 'slug' => 'mutation', 'url' => '#']
      ],
      'content' => 'Inventory/Mutation/index',
      'title' => lang('App.productmutation')
    ];

    return $this->buildPage($this->data);
  }

  protected function mutation_add()
  {
    checkPermission('ProductMutation.Add');

    if (requestMethod() == 'POST') {
      $warehouseIdFrom  = getPost('warehousefrom');
      $warehouseIdTo    = getPost('warehouseto');

      $data = [
        'date'              => dateTimePHP(getPost('date')),
        'from_warehouse_id' => $warehouseIdFrom,
        'to_warehouse_id'   => $warehouseIdTo,
        'note'              => stripTags(getPost('note')),
      ];

      $itemId   = getPost('item[id]');
      $itemCode = getPost('item[code]');
      $itemQty  = getPost('item[quantity]');

      if (!is_array($itemId) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemId); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Quantity untuk {$itemCode[$a]} harus lebih besar dari nol."]);
        }

        $items[] = [
          'id'        => $itemId[$a],
          'quantity'  => $itemQty[$a],
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = ProductMutation::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Product Mutation has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addproductmutation');

    $this->response(200, ['content' => view('Inventory/Mutation/add', $this->data)]);
  }

  protected function mutation_delete($id = null)
  {
    $mutation       = ProductMutation::getRow(['id' => $id]);
    $mutationItems  = Stock::get(['pm_id' => $id]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Product Mutation is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {

      DB::transStart();

      Attachment::delete(['hashname' => $mutation->attachment]);
      Stock::delete(['pm_id' => $id]);

      foreach ($mutationItems as $mutationItem) {
        Product::sync((int)$mutationItem->product_id);
      }

      $res = ProductMutation::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Product Mutation has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Bad request.']);
  }

  protected function mutation_status($id = null)
  {
    $mutation = ProductMutation::getRow(['id' => $id]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Product Mutation is not found.']);
    }

    $mutationItems = ProductMutationItem::get(['pm_id' => $mutation->id]);

    if (!$mutationItems) {
      $this->response(404, ['message' => 'Product Mutation items are not found.']);
    }

    $items  = [];
    $status = getPost('status');

    foreach ($mutationItems as $mutationItem) {
      $items[] = [
        'id'        => $mutationItem->product_id,
        'quantity'  => $mutationItem->quantity,
      ];
    }

    DB::transStart();

    $res = ProductMutation::update((int)$id, ['status' => $status], $items);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Product Mutation status has been updated.']);
    }

    $this->response(400, ['message' => 'Failed to update status.']);
  }

  protected function mutation_view($id = null)
  {
    checkPermission('ProductMutation.View');

    $mutation = ProductMutation::getRow(['id' => $id]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Internal Use is not found.']);
    }

    $this->data['mutation'] = $mutation;
    $this->data['title']    = lang('App.viewproductmutation');

    $this->response(200, ['content' => view('Inventory/Mutation/view', $this->data)]);
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

    if (requestMethod() == 'POST' && isAJAX()) {

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

    $this->response(400, ['message' => 'Bad request.']);
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

      foreach ($ids as $productId) {
        if (Product::sync((int)$productId)) {
          $synced++;
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

      $itemIds    = getPost('item[id]');
      $itemCodes  = getPost('item[code]');
      $itemQty    = getPost('item[quantity]');

      if (!is_array($itemIds) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemIds); $a++) {
        if (empty($itemQty[$a])) {
          $this->response(400, ['message' => "Item {$itemCodes[$a]} has invalid quantity."]);
        }

        $items[] = [
          'id'        => $itemIds[$a],
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
        $this->response(201, ['message' => 'Stock Adjustment has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
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
        Product::sync((int)$stock->product_id);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Stock Adjustment has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
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

      $itemIds    = getPost('item[id]');
      $itemCodes  = getPost('item[code]');
      $itemQty    = getPost('item[quantity]');

      for ($a = 0; $a < count($itemIds); $a++) {
        if (empty($itemQty[$a]) && $itemQty[$a] != 0) {
          $this->response(400, ['message' => "Item {$itemCodes[$a]} has invalid quantity."]);
        }

        $items[] = [
          'id'        => $itemIds[$a],
          'quantity'  => $itemQty[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = StockAdjustment::update((int)$id, $data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Stock Adjustment has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $items = [];

    foreach ($stocks as $stock) {
      $whProduct = WarehouseProduct::getRow(['product_id' => $stock->product_id, 'warehouse_id' => $stock->warehouse_id]);

      $items[] = [
        'id'          => $stock->product_id,
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

  /**
   * Stock Opname.
   * Stock Qty == Input Qty => 'Excellent'.
   * Stock Qty < Input Qty  => 'Good' + Adjustment Plus.
   * Stock Qty > Input Qty  => 'Need Confirm' (Checked).
   * 'Need Confirm' => First SO Qty == Update SO Qty.
   */
  public function stockopname()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('StockOpname.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.inventory'), 'slug' => 'inventory', 'url' => '#'],
        ['name' => lang('App.stockopname'), 'slug' => 'stockopname', 'url' => '#']
      ],
      'content' => 'Inventory/StockOpname/index',
      'title' => lang('App.stockopname')
    ];

    return $this->buildPage($this->data);
  }

  protected function stockopname_add()
  {
    checkPermission('StockOpname.Add');

    if (requestMethod() == 'POST') {
      $itemIds    = getPost('item[id]');
      $itemQty    = getPost('item[quantity]');
      $itemReject = getPost('item[reject]');

      $data = [
        'date'          => dateTimePHP(getPost('date')),
        'warehouse_id'  => getPost('warehouse'),
        'cycle'         => (getPost('cycle') ?? 1),
        'note'          => stripTags(getPost('note')),
        'created_by'    => getPost('pic')
      ];

      if (!is_array($itemIds) && !is_array($itemQty)) {
        $this->response(400, ['message' => 'Item tidak ada atau tidak valid.']);
      }

      for ($a = 0; $a < count($itemIds); $a++) {
        if (empty($itemQty[$a]) && $itemQty[$a] != 0) {
          $this->response(400, ['message' => "Item {$itemIds[$a]} has invalid quantity."]);
        }

        $items[] = [
          'id'        => $itemIds[$a],
          'quantity'  => $itemQty[$a],
          'reject'    => $itemReject[$a]
        ];
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = StockOpname::add($data, $items);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Stock Opname has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addstockopname');

    $this->response(200, ['content' => view('Inventory/StockOpname/add', $this->data)]);
  }

  protected function stockopname_delete($id = null)
  {
    $opname = StockOpname::getRow(['id' => $id]);
    $soItems = StockOpnameItem::get(['opname_id' => $id]);

    if (!$opname) {
      $this->response(404, ['message' => 'Stock Opname is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      Attachment::delete(['hashname' => $opname->attachment]);

      if ($opname->adjustment_plus_id) {
        StockAdjustment::delete(['id' => $opname->adjustment_plus_id]);
        Stock::delete(['adjustment_id' => $opname->adjustment_plus_id]);
      }

      if ($opname->adjustment_min_id) {
        StockAdjustment::delete(['id' => $opname->adjustment_min_id]);
        Stock::delete(['adjustment_id' => $opname->adjustment_min_id]);
      }

      foreach ($soItems as $soItem) {
        Product::sync((int)$soItem->product_id);
      }

      $res = StockOpname::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Stock Opname has been deleted.']);
      }
    }

    $this->response(400, ['message' => getLastError()]);
  }

  protected function stockopname_edit($id = null)
  {
  }

  protected function stockopname_status($id = null)
  {
    $opname = StockOpname::getRow(['id' => $id]);

    if (!$opname) {
      $this->response(404, ['message' => 'Stock opname is not found.']);
    }

    
  }

  protected function stockopname_suggestion()
  {
    $userId = getGet('pic');
    $warehouseId = getGet('warehouse');
    $items = [];

    $user = User::getRow(['id' => $userId]);

    if (!$user) {
      $this->response(404, ['message' => 'User is not found.']);
    }

    $userJS = getJSON($user->json);
    $soCycle = intval($userJS->so_cycle ?? 1);

    $warehouse = Warehouse::getRow(['id' => $warehouseId]);

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    if (empty($userJS->so_cycle)) {
      $userJS->so_cycle = $soCycle;

      $data = [
        'json'      => json_encode($userJS),
        'json_data' => json_encode($userJS),
      ];

      if (!User::update((int)$user->id, $data)) {
        $this->response(400, ['message' => 'Failed to update user data.']);
      }
    }

    $items = getStockOpnameSuggestion((int)$user->id, (int)$warehouse->id, $soCycle);

    if (!$items) {
      $soCycle = 1;
      $userJS->so_cycle = $soCycle;

      $items = getStockOpnameSuggestion((int)$user->id, (int)$warehouse->id, $soCycle);
    }

    if (!$items) {
      $this->response(400, ['message' => 'No items to be check.']);
    }

    foreach ($items as $item) {
      Product::sync((int)$item->id);
    }

    // Updated items quantity.
    $items = getStockOpnameSuggestion((int)$user->id, (int)$warehouse->id, $soCycle);

    $this->response(200, ['data' => [
      'cycle' => $soCycle,
      'items' => $items,
    ]]);
  }

  protected function stockopname_view($id = null)
  {
    checkPermission('StockOpname.View');

    $opname = StockOpname::getRow(['id' => $id]);

    if (!$opname) {
      $this->response(404, ['message' => 'Stock Opname is not found.']);
    }

    $this->data['opname'] = $opname;
    $this->data['title']  = lang('App.viewstockopname');

    $this->response(200, ['content' => view('Inventory/StockOpname/view', $this->data)]);
  }
}
