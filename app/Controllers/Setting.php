<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{DB, Permission, User};

class Setting extends BaseController
{
  public function index()
  {
    checkPermission('Setting.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.settings'), 'slug' => 'settings', 'url' => '#'],
        ['name' => lang('App.general'), 'slug' => 'general', 'url' => '#']
      ],
      'content' => 'Setting/index',
      'title' => lang('App.general')
    ];

    return $this->buildPage($this->data);
  }

  public function getPermissions()
  {
    checkPermission('UserGroup.View');

    $dt = new DataTables('permission');
    $dt
      ->select("permission.id, permission.name, permission.actions")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('setting/permission/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('setting/permission/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('actions', function ($data) {
        $actions = getJSON($data['actions']);
        $res = '';

        foreach ($actions as $action) {
          $badge = 'bg-gradient-navy';

          if (strcasecmp($action, 'Add') === 0) {
            $badge = 'bg-gradient-success';
          } else if (strcasecmp($action, 'All') === 0) {
            $badge = 'bg-gradient-indigo';
          } else if (strcasecmp($action, 'Delete') === 0) {
            $badge = 'bg-gradient-danger';
          } else if (strcasecmp($action, 'Edit') === 0) {
            $badge = 'bg-gradient-warning';
          } else if (strcasecmp($action, 'View') === 0) {
            $badge = 'bg-gradient-primary';
          }

          $action = lang('App.' . strtolower($action));

          $res .= "<div class=\"badge {$badge} m-1 p-2\">{$action}</div>";
        }

        return $res;
      })
      ->generate();
  }

  public function permission()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('All');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.setting'), 'slug' => 'setting', 'url' => '#'],
        ['name' => lang('App.permission'), 'slug' => 'permission', 'url' => '#']
      ],
      'content' => 'Setting/Permission/index',
      'title' => lang('App.permission')
    ];

    return $this->buildPage($this->data);
  }

  protected function permission_add()
  {
    checkPermission('All');

    if (requestMethod() == 'POST' && isAJAX()) {
      $data = [
        'name'    => getPost('name'),
        'actions' => getPost('action')
      ];

      DB::transStart();

      $insertID = Permission::add($data);

      DB::transComplete();

      if (DB::transStatus()) {
        $permission = Permission::getRow(['id' => $insertID]);

        addActivity("Permission {$permission->name} has been added.", [
          'add' => $permission
        ]);

        $this->response(201, ['message' => 'Permission has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addpermission');

    $this->response(200, ['content' => view('Setting/Permission/add', $this->data)]);
  }

  protected function permission_delete($id = NULL)
  {
    checkPermission('All');

    if (requestMethod() == 'POST' && isAJAX()) {
      $permission = Permission::getRow(['id' => $id]);

      if (!$permission) {
        $this->response(404, ['message' => 'Permission is not found.']);
      }

      DB::transStart();

      Permission::delete(['id' => $id]);

      DB::transComplete();

      if (DB::transStatus()) {
        addActivity("Permission {$permission->name} has been deleted.", [
          'delete' => $permission
        ]);

        $this->response(200, ['message' => 'Permission has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete permission.']);
  }

  protected function permission_edit($id = NULL)
  {
    checkPermission('All');

    $permission = Permission::getRow(['id' => $id]);

    if (requestMethod() == 'POST' && isAJAX()) {
      $data = [
        'name'    => getPost('name'),
        'actions' => getPost('action')
      ];

      DB::transStart();

      $res = Permission::update((int)$id, $data);

      DB::transComplete();

      if (DB::transStatus() && $res) {
        $newPermission = Permission::getRow(['id' => $id]);

        addActivity("Permission {$permission->name} has been updated.", [
          'edit' => [
            'new' => $newPermission,
            'old' => $permission
          ]
        ]);

        $this->response(200, ['message' => sprintf(lang('Msg.permissionEditOK'), $permission->name)]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['permission'] = $permission;
    $this->data['title']      = lang('App.editpermission');

    $this->response(200, ['content' => view('Setting/Permission/edit', $this->data)]);
  }

  public function sidebar()
  {
    checkPermission();

    $collapse = (getGet('collapse') == 1 ? 1 : 0);
    $userId = session('login')->user_id;

    $user = User::getRow(['id' => $userId]);
    $userJS = getJSON($user->json);

    $userJS->collapse = $collapse;

    User::update((int)$userId, [
      'json'      => json_encode($userJS),
      'json_data' => json_encode($userJS)
    ]);

    session('login')->collapse = $collapse;

    $this->response(200, ['message' => 'Success']);
  }

  public function theme()
  {
    checkPermission();

    $darkMode = (getGet('darkmode') == 1 ? 1 : 0);
    $userId = session('login')->user_id;

    User::update((int)$userId, ['dark_mode' => $darkMode]);
    session('login')->dark_mode = $darkMode;

    $this->response(200, ['message' => 'Success']);
  }
}
