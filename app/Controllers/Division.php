<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables};
use App\Models\{Biller, DB, Warehouse};

class Division extends BaseController
{
  public function index()
  {
    checkPermission();
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
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('division/biller/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('division/biller/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
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
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('division/warehouse/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <div class="dropdown-divider"></div>
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

    if (requestMethod() == 'POST' && isAJAX()) {
      $data = [
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

      DB::transStart();

      $insertID = Biller::add($data);

      DB::transComplete();

      if (DB::transStatus()) {
        $biller = Biller::getRow(['id' => $insertID]);

        addActivity("Biller ({$biller->code}) {$biller->code} has been added.", [
          'add' => $biller
        ]);

        $this->response(201, ['message' => 'Biller has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addbiller');

    $this->response(200, ['content' => view('Division/Biller/add', $this->data)]);
  }

  protected function biller_delete($id = NULL)
  {
    checkPermission('Biller.Delete');

    $biller = Biller::getRow(['id' => $id]);

    if (!$biller) {
      $this->response(404, ['message' => 'Biller is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      if (Biller::delete(['id' => $id])) {
        addActivity("Biller ({$biller->code}) {$biller->name} has been deleted.", [
          'delete' => $biller
        ]);

        $this->response(200, ['message' => 'Biller has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete biller.']);
  }

  protected function biller_edit($id = NULL)
  {
    checkPermission('Biller.Edit');

    $biller = Biller::getRow(['id' => $id]);

    if (!$biller) {
      $this->response(404, ['message' => 'Biller is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $data = [
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

      DB::transStart();

      Biller::update((int)$id, $data);

      DB::transComplete();

      if (DB::transStatus()) {
        $billerNew = Biller::getRow(['id' => $id]);

        addActivity("Biller ({$biller->code}) {$biller->name} has been updated.", [
          'edit' => [
            'old' => $biller,
            'new' => $billerNew
          ]
        ]);

        $this->response(200, ['message' => sprintf(lang('Msg.billerEditOK'), $biller->name)]);
      }

      $this->response(400, ['message' => sprintf(lang('Msg.billerEditNO'), $biller->name)]);
    }

    $this->data['biller']   = $biller;
    $this->data['billerJS'] = getJSON($biller->json);
    $this->data['title']    = lang('App.editbiller');

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

    if (requestMethod() == 'POST' && isAJAX()) {
      $maintenances = [];

      foreach (getPost('maintenance') as $main) {
        $maintenances[] = [
          'pic'         => intval($main['pic'] ?? 0),
          'category'    => $main['category'],
          'auto_assign' => intval($main['auto_assign'] ?? 0)
        ];
      }

      $data = [
        'code'    => getPost('code'),
        'name'    => getPost('name'),
        'address' => getPost('address'),
        'phone'   => getPost('phone'),
        'email'   => getPost('email'),
        'active'  => (getPost('active') == 1 ? 1 : 0),
        'json'    => json_encode([
          'cycle_transfer'  => intval(getPost('transfer_cycle')),
          'delivery_time'   => intval(getPost('delivery_time')),
          'lat'             => getPost('latitude'),
          'lon'             => getPost('longitude'),
          'maintenances'    => $maintenances,
          'visit_days'      => implode(',', (getPost('visit_days') ?? [])),
          'visit_weeks'     => implode(',', (getPost('visit_weeks') ?? [])),
        ])
      ];

      DB::transStart();

      $insertID = Warehouse::add($data);

      DB::transComplete();

      if (DB::transStatus()) {
        $warehouse = Warehouse::getRow(['id' => $insertID]);

        addActivity("Warehouse ({$warehouse->code}) {$warehouse->name} has been added.", [
          'add' => $warehouse
        ]);

        $this->response(201, ['message' => 'Warehouse has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addwarehouse');

    $this->response(200, ['content' => view('Division/Warehouse/add', $this->data)]);
  }

  protected function warehouse_delete($id = NULL)
  {
    checkPermission('Warehouse.Delete');

    $warehouse = Warehouse::getRow(['id' => $id]);

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      if (Warehouse::delete(['id' => $id])) {
        addActivity("Warehouse ({$warehouse->code}) {$warehouse->name} has been deleted.", [
          'delete' => $warehouse
        ]);

        $this->response(200, ['message' => 'Warehouse has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete warehouse.']);
  }

  protected function warehouse_edit($id = NULL)
  {
    checkPermission('Warehouse.Edit');

    $warehouse = Warehouse::getRow(['id' => $id]);

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not exists.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $maintenances = [];

      foreach (getPost('maintenance') as $main) {
        $maintenances[] = [
          'pic'         => intval($main['pic'] ?? 0),
          'category'    => $main['category'],
          'auto_assign' => intval($main['auto_assign'] ?? 0)
        ];
      }

      $data = [
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

      DB::transStart();

      Warehouse::update((int)$id, $data);

      DB::transComplete();

      if (DB::transStatus()) {
        $newWarehouse = Warehouse::getRow(['id' => $id]);

        addActivity("Warehouse ({$warehouse->code}) {$warehouse->name} has been updated.", [
          'edit' => [
            'old' => $warehouse,
            'new' => $newWarehouse
          ]
        ]);

        $this->response(200, ['message' => sprintf(lang('Msg.warehouseEditOK'), $warehouse->name)]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title']        = lang('App.editwarehouse');
    $this->data['warehouse']    = $warehouse;
    $this->data['warehouseJS']  = getJSON($warehouse->json);

    $this->response(200, ['content' => view('Division/Warehouse/edit', $this->data)]);
  }
}
