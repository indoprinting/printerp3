<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables, FileUpload};
use App\Models\{Biller, Warehouse};

class Division extends BaseController
{
  public function index()
  {
  }

  public function getBillers()
  {
    checkPermission('Biller.View');

    $dt = new DataTables('biller');
    $dt
      ->select("id, code, name, address, city, phone, email, json->>'$.target' AS target, active")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('division/biller/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
<<<<<<< HEAD
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
=======
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <a class="dropdown-item" href="' . base_url('division/biller/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> Delete
              </a>
            </div>
          </div>';
      })
      ->editColumn('active', function ($data) {
        $type = ($data['active'] == 1 ? 'success' : 'danger');
        $status = ($data['active'] == 1 ? lang('App.active') : lang('App.inactive'));

        return "<div class=\"badge bg-gradient-{$type}\">{$status}</div>";
      })
      ->editColumn('target', function ($data) {
        return formatCurrency(floatval($data['target']));
      })
      ->generate();
  }

  public function getWarehouses()
  {
    checkPermission('Warehouse.View');

    $dt = new DataTables('warehouse');
    $dt
      ->select("id, code, name, address, phone, email, active")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('division/warehouse/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <a class="dropdown-item" href="' . base_url('division/warehouse/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> Delete
              </a>
            </div>
          </div>';
      })
      ->editColumn('active', function ($data) {
        $type = ($data['active'] == 1 ? 'success' : 'danger');
        $status = ($data['active'] == 1 ? lang('App.active') : lang('App.inactive'));

        return "<div class=\"badge bg-gradient-{$type}\">{$status}</div>";
      })
      ->generate();
  }

  public function biller()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Biller.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.division'), 'slug' => 'division', 'url' => '#'],
        ['name' => lang('App.biller'), 'slug' => 'biller', 'url' => '#']
      ],
      'content' => 'Division/Biller/index',
      'title' => lang('App.biller')
    ];

    return $this->buildPage($this->data);
  }

  protected function biller_add()
  {
    checkPermission('Biller.Add');

    if (requestMethod() == 'POST') {
      $billerData = [
        'code'    => getPost('code'),
        'name'    => getPost('name'),
        'address' => getPost('address'),
        'city'    => getPost('city'),
        'phone'   => getPost('phone'),
        'email'   => getPost('email'),
        'active'  => getPost('active'),
        'json'    => json_encode([
          'target' => filterDecimal(getPost('target'))
        ])
      ];

      if (Biller::add($billerData)) {
        $this->response(201, ['message' => 'Biller has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addbiller');

    $this->response(200, ['content' => view('Division/Biller/add', $this->data)]);
  }

  protected function biller_delete($userGroupId = NULL)
  {
    checkPermission('Biller.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      if (Biller::delete(['id' => $userGroupId])) {
        $this->response(200, ['message' => 'Biller has been deleted.']);
      }
      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }
    $this->response(400, ['message' => 'Failed to delete biller.']);
  }

  protected function biller_edit($billerId = NULL)
  {
    checkPermission('Biller.Edit');

    $biller = Biller::getRow(['id' => $billerId]);

    if (requestMethod() == 'POST') {
      $billerData = [
        'code'    => getPost('code'),
        'name'    => getPost('name'),
        'address' => getPost('address'),
        'city'    => getPost('city'),
        'phone'   => getPost('phone'),
        'email'   => getPost('email'),
        'active'  => getPost('active'),
        'json'    => json_encode([
          'target' => filterDecimal(getPost('target'))
        ])
      ];

      if (Biller::update((int)$biller->id, $billerData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.billerEditOK'), $biller->name)]);
      }
      $this->response(400, ['message' => sprintf(lang('Msg.billerEditNO'), $biller->name)]);
    }

    $this->data['biller'] = $biller;
    $this->data['billerJS'] = getJSON($biller->json);
    $this->data['title']  = lang('App.editbiller');

    $this->response(200, ['content' => view('Division/Biller/edit', $this->data)]);
  }

  public function warehouse()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Warehouse.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.division'), 'slug' => 'division', 'url' => '#'],
        ['name' => lang('App.warehouse'), 'slug' => 'warehouse', 'url' => '#']
      ],
      'content' => 'Division/Warehouse/index',
      'title' => lang('App.warehouse')
    ];

    return $this->buildPage($this->data);
  }

  protected function warehouse_add()
  {
    checkPermission('Warehouse.Add');

    if (requestMethod() == 'POST') {
      $maintenances = [];

      foreach (getPost('maintenance') as $main) {
        $maintenances[] = [
          'pic'         => intval($main['pic'] ?? 0),
          'category'    => $main['category'],
          'auto_assign' => intval($main['auto_assign'] ?? 0)
        ];
      }

      $warehouseData = [
        'code'        => getPost('code'),
        'name'        => getPost('name'),
        'address'     => getPost('address'),
        'phone'       => getPost('phone'),
        'email'       => getPost('email'),
        'active'      => (getPost('active') == 1 ? 1 : 0),
        'json'        => json_encode([
          'cycle_transfer'  => intval(getPost('transfer_cycle')),
          'delivery_time'   => intval(getPost('delivery_time')),
          'lat'             => getPost('latitude'),
          'lon'             => getPost('longitude'),
          'maintenances'    => $maintenances,
          'visit_days'      => implode(',', (getPost('visit_days') ?? [])),
          'visit_weeks'     => implode(',', (getPost('visit_weeks') ?? [])),
        ])
      ];

      if (Warehouse::add($warehouseData)) {
        $this->response(201, ['message' => sprintf(lang('Msg.warehouseAddOK'), $warehouseData['username'])]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addwarehouse');

    $this->response(200, ['content' => view('Division/Warehouse/add', $this->data)]);
  }

  protected function warehouse_delete($warehouseId = NULL)
  {
    checkPermission('Warehouse.Delete');

    if (requestMethod() != 'POST') {
      $this->response(405, ['message' => 'Method is not allowed.']);
    }

    if (Warehouse::delete(['id' => $warehouseId])) {
      $this->response(200, ['message' => lang('Msg.warehouseDeleteOK')]);
    }
    $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
  }

  protected function warehouse_edit($warehouseId = NULL)
  {
    checkPermission('Warehouse.Edit');

    $warehouse = Warehouse::getRow(['id' => $warehouseId]);

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not exists.']);
    }

    if (requestMethod() == 'POST') {
      $maintenances = [];

      foreach (getPost('maintenance') as $main) {
        $maintenances[] = [
          'pic'         => intval($main['pic'] ?? 0),
          'category'    => $main['category'],
          'auto_assign' => intval($main['auto_assign'] ?? 0)
        ];
      }

      $warehouseData = [
        'code'        => getPost('code'),
        'name'        => getPost('name'),
        'address'     => getPost('address'),
        'phone'       => getPost('phone'),
        'email'       => getPost('email'),
        'active'      => (getPost('active') == 1 ? 1 : 0),
        'json'        => json_encode([
          'cycle_transfer'  => intval(getPost('transfer_cycle')),
          'delivery_time'   => intval(getPost('delivery_time')),
          'lat'             => getPost('latitude'),
          'lon'             => getPost('longitude'),
          'maintenances'    => $maintenances,
          'visit_days'      => implode(',', (getPost('visit_days') ?? [])),
          'visit_weeks'     => implode(',', (getPost('visit_weeks') ?? [])),
        ])
      ];

      if (Warehouse::update((int)$warehouseId, $warehouseData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.warehouseEditOK'), $warehouse->name)]);
      }
      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.editwarehouse');
    $this->data['warehouse'] = $warehouse;
    $this->data['warehouseJS'] = getJSON($warehouse->json);

    $this->response(200, ['content' => view('Division/Warehouse/edit', $this->data)]);
  }
}
