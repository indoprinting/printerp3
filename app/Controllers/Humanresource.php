<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables, FileUpload};
use App\Models\{DB, User, UserGroup};

class Humanresource extends BaseController
{
  public function index()
  {
    checkPermission('UserGroup.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.humanResource'), 'slug' => 'hr', 'url' => '#'],
        ['name' => lang('App.userGroup'), 'slug' => 'usergroup', 'url' => '#']
      ],
      'content' => 'UserGroup/index',
      'title' => lang('App.userGroup')
    ];

    return $this->buildPage($this->data);
  }

  public function getUserGroups()
  {
    checkPermission('UserGroup.View');

    $dt = new DataTables('groups');
    $dt
      ->select("groups.id, groups.name, groups.permissions")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('humanresource/usergroup/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <a class="dropdown-item" href="' . base_url('humanresource/usergroup/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> Delete
              </a>
            </div>
          </div>';
      })
      ->editColumn('permissions', function ($data) {
        $permissions = getJSON($data['permissions']);
        $result = '';

        foreach ($permissions as $permission) {
          $perm = ucfirst($permission);
          $type = '';

          if ($perm == 'All') {
            $type = 'secondary';
          } else if (preg_match('/(.*).add/i', $perm)) {
            $type = 'success';
          } else if (preg_match('/(.*).delete/i', $perm)) {
            $type = 'danger';
          } else if (preg_match('/(.*).edit/i', $perm)) {
            $type = 'warning';
          } else if (preg_match('/(.*).view/i', $perm)) {
            $type = 'info';
          }

          $perms = ($this->data['permissions'][$perm] ?? '-');

          $result .= "<div class=\"badge bg-gradient-{$type}\">{$perms}</div> ";
        }

        return trim($result);
      })
      ->generate();
  }

  public function getUsers()
  {
    checkPermission('User.View');

    $dt = new DataTables('users');
    $dt
      ->select("users.id AS id, avatar_id, fullname, username, users.phone, gender, groups,
        billers.name AS biller_name, warehouses.name AS warehouse_name, users.active AS active")
      ->join('billers', 'billers.id = users.biller_id', 'left')
      ->join('warehouses', 'warehouses.id = users.warehouse_id', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('humanresource/user/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <a class="dropdown-item" href="' . base_url('humanresource/user/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> Delete
              </a>
            </div>
          </div>';
      })
      ->editColumn('avatar_id', function ($data) {
        if (!$data['avatar_id']) $data['avatar_id'] = 1;

        $avatar = DB::table('attachment')->getRow(['id' => $data['avatar_id']]);

        return '<img src="' . base_url('attachment/' . $avatar->hashname) . '" style="max-width:100px">';
      })
      ->editColumn('groups', function ($data) {
        $groupNames = explode(',', $data['groups']);
        $res = '';

        foreach ($groupNames as $groupName) {
          $badge = 'bg-gradient-navy';
          if (strcasecmp($groupName, 'OWNER') === 0) {
            $badge = 'bg-gradient-indigo';
          }
          $res .= "<div class=\"badge {$badge}\">{$groupName}</div>";
        }

        return $res;
      })
      ->editColumn('gender', function ($data) {
        return ucfirst($data['gender']);
      })
      ->editColumn('active', function ($data) {
        $type = ($data['active'] == 1 ? 'success' : 'danger');
        $status = ($data['active'] == 1 ? lang('App.active') : lang('App.inactive'));

        return "<div class=\"badge bg-gradient-{$type}\">{$status}</div>";
      })
      ->generate();
  }

  public function theme()
  {
    checkPermission();

    $darkMode = ($this->request->getGet('darkmode') == 1 ? 1 : 0);
    $userId = session('login')->user_id;

    $this->setting->updateUser($userId, ['dark_mode' => $darkMode]);
    session('login')->dark_mode = $darkMode;

    $this->response(200, ['message' => 'Success']);
  }

  public function usergroup()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('UserGroup.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.humanResource'), 'slug' => 'humanresource', 'url' => '#'],
        ['name' => lang('App.userGroup'), 'slug' => 'usergroup', 'url' => '#']
      ],
      'content' => 'HumanResource/UserGroup/index',
      'title' => lang('App.userGroup')
    ];

    return $this->buildPage($this->data);
  }

  protected function usergroup_add()
  {
    checkPermission('UserGroup.Add');

    if (requestMethod() == 'POST') {
      $userGroupData = [
        'name'        => getPost('groupname'),
        'permissions' => json_encode(getPost('permission') ?? [])
      ];

      if (UserGroup::add($userGroupData)) {
        $this->response(201, ['message' => 'User group has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addUserGroup');

    $this->response(200, ['content' => view('HumanResource/UserGroup/add', $this->data)]);
  }

  protected function usergroup_delete($userGroupId = NULL)
  {
    checkPermission('UserGroup.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      if (UserGroup::delete(['id' => $userGroupId])) {
        $this->response(200, ['message' => 'User group has been deleted.']);
      }
      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }
    $this->response(400, ['message' => 'Failed to delete user group.']);
  }

  protected function usergroup_edit($userGroupId = NULL)
  {
    checkPermission('UserGroup.Edit');

    $userGroup = UserGroup::getRow(['id' => $userGroupId]);

    if (requestMethod() == 'POST') {
      $userGroupData = [
        'name'        => getPost('groupname'),
        'permissions' => json_encode(getPost('permission') ?? [])
      ];

      if (UserGroup::update((int)$userGroup->id, $userGroupData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.userGroupEditOK'), $userGroup->name)]);
      }
      $this->response(400, ['message' => sprintf(lang('Msg.userGroupEditNO'), $userGroup->name)]);
    }

    $this->data['title'] = lang('App.editUserGroup');
    $this->data['userGroup'] = $userGroup;

    $this->response(200, ['content' => view('HumanResource/UserGroup/edit', $this->data)]);
  }

  public function user()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('User.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.humanResource'), 'slug' => 'humanresource', 'url' => '#'],
        ['name' => lang('App.user'), 'slug' => 'user', 'url' => '#']
      ],
      'content' => 'HumanResource/User/index',
      'title' => lang('App.user')
    ];

    return $this->buildPage($this->data);
  }

  protected function user_add()
  {
    checkPermission('User.Add');

    if (requestMethod() == 'POST') {
      $userData = [
        'active'        => getPost('active'),
        'biller_id'     => getPost('biller'),
        'company'       => getPost('division'),
        'fullname'      => getPost('fullName'),
        'gender'        => getPost('gender'),
        'groups'        => getPost('groups'),
        'password'      => getPost('password'),
        'phone'         => getPost('phone'),
        'username'      => getPost('userName'),
        'warehouse_id'  => getPost('warehouse'),
      ];

      // $this->response(400, ['message' => is_array($userData['groups'])]);

      $upload = new FileUpload();

      if ($upload->has('avatarImg')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.profileImgExceed')]);
        }

        $userData['avatar_id'] = $upload->storeRandom();
      } else {
        $userData['avatar_id'] = ($userData['gender'] == 'male' ? 1 : 2);
      }

      if (User::add($userData)) {
        $this->response(201, ['message' => sprintf(lang('Msg.userAddOK'), $userData['username'])]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addUser');

    $this->response(200, ['content' => view('HumanResource/User/add', $this->data)]);
  }

  protected function user_delete($userId = NULL)
  {
    checkPermission('User.Delete');

    if (requestMethod() != 'POST') {
      $this->response(405, ['message' => 'Method is not allowed.']);
    }

    if (User::delete(['id' => $userId])) {
      $this->response(200, ['message' => lang('Msg.userDeleteOK')]);
    }
    $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
  }

  protected function user_edit($userId = NULL)
  {
    checkPermission('User.Edit');

    $user = User::getRow(['id' => $userId]);

    if (!$user) {
      $this->response(404, ['message' => 'User is not exists.']);
    }

    if (requestMethod() == 'POST') {
      $userData = [
        'active'        => getPost('active'),
        'biller_id'     => getPost('biller'),
        'company'       => getPost('division'),
        'fullname'      => getPost('fullName'),
        'gender'        => getPost('gender'),
        'groups'        => getPost('groups'),
        'password'      => getPost('password'),
        'phone'         => getPost('phone'),
        'username'      => getPost('userName'),
        'warehouse_id'  => getPost('warehouse'),
      ];

      $upload = new FileUpload();

      if ($upload->has('avatarImg')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.profileImgExceed')]);
        }

        $userData['avatar_id'] = $upload->storeRandom();
      } else if ($user->avatar_id == 1 || $user->avatar_id == 2) {
        $userData['avatar_id'] = ($userData['gender'] == 'male' ? 1 : 2);
      }

      if (User::update((int)$userId, $userData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.userEditOK'), $user->fullname)]);
      }
      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.editUser');
    $this->data['user'] = $user;

    $this->response(200, ['content' => view('HumanResource/User/edit', $this->data)]);
  }

  protected function user_view($userId = NULL)
  {
  }
}
