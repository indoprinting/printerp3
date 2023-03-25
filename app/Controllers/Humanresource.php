<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables, FileUpload};
use App\Models\{Attachment, Customer, CustomerGroup, DB, Supplier, User, UserGroup};

class Humanresource extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getCustomers()
  {
    checkPermission('Customer.View');

    $dt = new DataTables('customers');
    $dt
      ->select("customers.id AS id, customers.name, customers.company, customers.phone, customers.email,
        customergroup.name AS customer_group_name, pricegroup.name AS price_group_name")
      ->join('customergroup', 'customergroup.id = customers.customer_group_id', 'left')
      ->join('pricegroup', 'pricegroup.id = customers.price_group_id', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('humanresource/customer/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('humanresource/customer/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('customer_group_name', function ($data) {
        $name = $data['customer_group_name'];
        $type = 'primary';

        switch ($name) {
          case 'Reguler':
            $type = 'primary';
            break;
          case 'Privilege':
            $type = 'warning';
            break;
          case 'TOP':
            $type = 'dark';
            break;
        }

        return "<div class=\"badge bg-gradient-{$type} m-1 p-2\">{$name}</div>";
      })
      ->generate();
  }

  public function getCustomerGroups()
  {
    checkPermission('CustomerGroup.View');

    $dt = new DataTables('customergroup');
    $dt
      ->select("customergroup.id AS id, customergroup.name,
        customergroup.allow_delivery, customergroup.allow_production")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('humanresource/customergroup/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('humanresource/customergroup/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('allow_delivery', function ($data) {
        $allow = ($data['allow_delivery'] == 1 ? lang('App.yes') : lang('App.no'));
        $type  = ($data['allow_delivery'] == 1 ? 'success' : 'danger');

        return "<div class=\"badge bg-gradient-{$type} m-1 p-2\">{$allow}</div> ";
      })
      ->editColumn('allow_production', function ($data) {
        $allow = ($data['allow_production'] == 1 ? lang('App.yes') : lang('App.no'));
        $type  = ($data['allow_production'] == 1 ? 'success' : 'danger');

        return "<div class=\"badge bg-gradient-{$type} m-1 p-2\">{$allow}</div> ";
      })
      ->generate();
  }

  public function getSuppliers()
  {
    checkPermission('Supplier.View');

    $dt = new DataTables('suppliers');
    $dt
      ->select("suppliers.id AS id, suppliers.name, suppliers.company, suppliers.address,
      suppliers.phone, suppliers.email, suppliers.city, suppliers.country")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('humanresource/supplier/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('humanresource/supplier/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->generate();
  }

  public function getUserGroups()
  {
    checkPermission('UserGroup.View');

    $dt = new DataTables('usergroup');
    $dt
      ->select("usergroup.id, usergroup.name, usergroup.permissions")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('humanresource/usergroup/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('humanresource/usergroup/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('permissions', function ($data) {
        $permissions = getJSON($data['permissions']);
        $result = '';

        foreach ($permissions as $permission) {
          $perm = ucfirst($permission);
          $type = 'navy';

          if ($perm == 'All') {
            $type = 'indigo';
          } else if (preg_match('/(.*).add/i', $perm)) {
            $type = 'success';
          } else if (preg_match('/(.*).delete/i', $perm)) {
            $type = 'danger';
          } else if (preg_match('/(.*).edit/i', $perm)) {
            $type = 'warning';
          } else if (preg_match('/(.*).view/i', $perm)) {
            $type = 'info';
          }

          $result .= "<div class=\"badge bg-gradient-{$type} m-1 p-2\">{$perm}</div> ";
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
      ->select("users.id AS id, avatar, fullname, username, users.phone, gender, users.groups,
        billers.name AS biller_name, warehouses.name AS warehouse_name, users.active AS active")
      ->join('billers', 'billers.code = users.biller', 'left')
      ->join('warehouses', 'warehouses.code = users.warehouse', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('humanresource/user/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('humanresource/user/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalDefault"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('humanresource/user/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('avatar', function ($data) {
        if (empty($data['avatar'])) {
          $data['avatar'] = ($data['gender'] == 'male' ? 'avatarmale' : 'avatarfemale');
        }

        return '<img src="' . base_url('attachment/' . $data['avatar']) . '" style="max-width:100px">';
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

  public function customer()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Customer.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.humanresource'), 'slug' => 'humanresource', 'url' => '#'],
        ['name' => lang('App.customer'), 'slug' => 'customer', 'url' => '#']
      ],
      'content' => 'HumanResource/Customer/index',
      'title' => lang('App.customer')
    ];

    return $this->buildPage($this->data);
  }

  protected function customer_add()
  {
    checkPermission('Customer.Add');

    if (requestMethod() == 'POST') {
      $name   = getPost('name');
      $phone  = filterNumber(getPost('phone'));

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($phone)) {
        $this->response(400, ['message' => 'Phone number is required.']);
      }

      $customer = Customer::getRow(['phone' => $phone]);

      if ($customer) {
        $this->response(400, ['message' => "Customer phone {$phone} is already present."]);
      }

      $customerData = [
        'customer_group_id' => getPost('group'),
        'price_group_id'    => getPost('pricegroup'),
        'name'              => trim($name),
        'company'           => getPost('company'),
        'email'             => getPost('email'),
        'phone'             => $phone,
        'address'           => getPost('address'),
        'city'              => getPost('city'),
        'json'              => json_encode([])
      ];

      DB::transStart();

      $insertId = Customer::add($customerData);

      if (!$insertId) {
        $this->response(400, ['message' =>  getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => sprintf(lang('Msg.customerAddOK'), $customerData['name'])]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addcustomer');

    $this->response(200, ['content' => view('HumanResource/Customer/add', $this->data)]);
  }

  protected function customer_delete($customerId = NULL)
  {
    checkPermission('Customer.Delete');

    if (requestMethod() != 'POST') {
      $this->response(405, ['message' => 'Method is not allowed.']);
    }

    DB::transStart();

    $res = Customer::delete(['id' => $customerId]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => lang('Msg.customerDeleteOK')]);
    }

    $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
  }

  protected function customer_edit($customerId = NULL)
  {
    checkPermission('Customer.Edit');

    $customer = Customer::getRow(['id' => $customerId]);

    if (!$customer) {
      $this->response(404, ['message' => 'Customer is not exists.']);
    }

    if (requestMethod() == 'POST') {
      $name   = getPost('name');
      $phone  = filterNumber(getPost('phone'));

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($phone)) {
        $this->response(400, ['message' => 'Phone number is required.']);
      }

      $customerData = [
        'customer_group_id' => getPost('group'),
        'price_group_id'    => getPost('pricegroup'),
        'name'              => trim($name),
        'company'           => getPost('company'),
        'email'             => getPost('email'),
        'phone'             => trim($phone),
        'address'           => getPost('address'),
        'city'              => getPost('city'),
        'json'              => json_encode([])
      ];

      DB::transStart();

      $res = Customer::update((int)$customerId, $customerData);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => sprintf(lang('Msg.customerEditOK'), $customer->name)]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.editcustomer');
    $this->data['customer'] = $customer;

    $this->response(200, ['content' => view('HumanResource/Customer/edit', $this->data)]);
  }

  public function customergroup()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('CustomerGroup.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.humanresource'), 'slug' => 'humanresource', 'url' => '#'],
        ['name' => lang('App.customergroup'), 'slug' => 'customergroup', 'url' => '#']
      ],
      'content' => 'HumanResource/CustomerGroup/index',
      'title' => lang('App.customergroup')
    ];

    return $this->buildPage($this->data);
  }

  protected function customergroup_add()
  {
    checkPermission('CustomerGroup.Add');

    if (requestMethod() == 'POST') {
      $customerGroupData = [
        'name'              => getPost('name'),
        'allow_delivery'    => (getPost('delivery') == 1 ? 1 : 0),
        'allow_production'  => (getPost('production') == 1 ? 1 : 0)
      ];

      DB::transStart();

      $insertId = CustomerGroup::add($customerGroupData);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => sprintf(lang('Msg.customerGroupAddOK'), $customerGroupData['name'])]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addcustomergroup');

    $this->response(200, ['content' => view('HumanResource/CustomerGroup/add', $this->data)]);
  }

  protected function customergroup_delete($customerGroupId = NULL)
  {
    checkPermission('CustomerGroup.Delete');

    if (requestMethod() != 'POST') {
      $this->response(405, ['message' => 'Method is not allowed.']);
    }

    DB::transStart();

    $res = CustomerGroup::delete(['id' => $customerGroupId]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => lang('Msg.customerGroupDeleteOK')]);
    }

    $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
  }

  protected function customergroup_edit($customerGroupId = NULL)
  {
    checkPermission('CustomerGroup.Edit');

    $customerGroup = CustomerGroup::getRow(['id' => $customerGroupId]);

    if (!$customerGroup) {
      $this->response(404, ['message' => 'Customer group is not exists.']);
    }

    if (requestMethod() == 'POST') {
      $customerGroupData = [
        'name'              => getPost('name'),
        'allow_delivery'    => (getPost('delivery') == 1 ? 1 : 0),
        'allow_production'  => (getPost('production') == 1 ? 1 : 0)
      ];

      DB::transStart();

      $res = CustomerGroup::update((int)$customerGroupId, $customerGroupData);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => sprintf(lang('Msg.customerGroupEditOK'), $customerGroup->name)]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.editcustomergroup');
    $this->data['customerGroup'] = $customerGroup;

    $this->response(200, ['content' => view('HumanResource/CustomerGroup/edit', $this->data)]);
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
        ['name' => lang('App.humanresource'), 'slug' => 'humanresource', 'url' => '#'],
        ['name' => lang('App.usergroup'), 'slug' => 'usergroup', 'url' => '#']
      ],
      'content' => 'HumanResource/UserGroup/index',
      'title' => lang('App.usergroup')
    ];

    return $this->buildPage($this->data);
  }

  protected function usergroup_add()
  {
    checkPermission('UserGroup.Add');

    if (requestMethod() == 'POST') {
      $userGroupData = [
        'name'        => getPost('name'),
        'permissions' => json_encode(getPost('permission') ?? [])
      ];

      DB::transStart();

      $insertId = UserGroup::add($userGroupData);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'User group has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addusergroup');

    $this->response(200, ['content' => view('HumanResource/UserGroup/add', $this->data)]);
  }

  protected function usergroup_delete($userGroupId = NULL)
  {
    checkPermission('UserGroup.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      $res = UserGroup::delete(['id' => $userGroupId]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
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
        'name'        => getPost('name'),
        'permissions' => json_encode(getPost('permission') ?? [])
      ];

      if (UserGroup::update((int)$userGroup->id, $userGroupData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.userGroupEditOK'), $userGroup->name)]);
      }

      $this->response(400, ['message' => sprintf(lang('Msg.userGroupEditNO'), $userGroup->name)]);
    }

    $this->data['title'] = lang('App.editusergroup');
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
        ['name' => lang('App.humanresource'), 'slug' => 'humanresource', 'url' => '#'],
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
      $json = json_encode([
        'acc_no'      => getPost('accno'),
        'billers'     => getPost('billers'),
        'warehouses'  => getPost('warehouses'),
        'so_cycle'    => getPost('socycle'),
      ]);

      $data = [
        'active'        => getPost('active'),
        'biller_id'     => getPost('biller'),
        'company'       => getPost('division'),
        'fullname'      => getPost('fullname'),
        'gender'        => getPost('gender'),
        'groups'        => getPost('groups'),
        'password'      => getPost('password'),
        'phone'         => preg_replace('/([^0-9])/', '', getPost('phone')),
        'username'      => getPost('username'),
        'warehouse_id'  => getPost('warehouse'),
        'json'          => $json,
        'json_data'     => $json
      ];

      $upload = new FileUpload();

      if ($upload->has('avatarImg')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.profileImgExceed')]);
        }

        $data['avatar'] = Attachment::getRow(['id' => $upload->storeRandom()])->hashname;
      } else {
        $data['avatar'] = ($data['gender'] == 'male' ? 'avatarmale' : 'avatarfemale');
      }

      DB::transStart();

      $insertId = User::add($data);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => sprintf(lang('Msg.userAddOK'), $data['username'])]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.adduser');

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
      $json = json_encode([
        'acc_no'      => getPost('accno'),
        'billers'     => getPost('billers'),
        'warehouses'  => getPost('warehouses'),
        'so_cycle'    => getPost('socycle'),
      ]);

      $data = [
        'active'        => getPost('active'),
        'biller_id'     => getPost('biller'),
        'company'       => getPost('division'),
        'fullname'      => getPost('fullname'),
        'gender'        => getPost('gender'),
        'groups'        => getPost('groups'),
        'phone'         => preg_replace('/([^0-9])/', '', getPost('phone')),
        'username'      => getPost('username'),
        'warehouse_id'  => getPost('warehouse'),
        'json'          => $json,
        'json_data'     => $json
      ];

      if ($pass = getPost('password')) {
        $data['password'] = $pass;
      }

      DB::transStart();

      $upload = new FileUpload();

      if ($upload->has('avatarImg')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.profileImgExceed')]);
        }

        if ($user->avatar) {
          $avatar = Attachment::getRow(['hashname' => $user->avatar]);

          if ($avatar && $avatar->hashname != 'avatarmale' && $avatar->hashname != 'avatarfemale') {
            $upload->store(NULL, $avatar->hashname); // Update current record.
          } else {
            $data['avatar'] = Attachment::getRow(['id' => $upload->storeRandom()])->hashname;
          }
        }
      } else {
        $data['avatar'] = ($data['gender'] == 'male' ? 'avatarmale' : 'avatarfemale');
      }

      $res = User::update((int)$userId, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => sprintf(lang('Msg.userEditOK'), $user->fullname)]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title']  = lang('App.edituser');
    $this->data['user']   = $user;
    $this->data['userJS'] = getJSON($user->json);

    $this->response(200, ['content' => view('HumanResource/User/edit', $this->data)]);
  }

  protected function user_view($userId = NULL)
  {
    $user = User::getRow(['id' => $userId]);

    $this->data['title'] = lang('App.viewuser');
    $this->data['user'] = $user;

    $this->response(200, ['content' => view('HumanResource/User/view', $this->data)]);
  }

  public function supplier()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Supplier.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.humanresource'), 'slug' => 'humanresource', 'url' => '#'],
        ['name' => lang('App.supplier'), 'slug' => 'supplier', 'url' => '#']
      ],
      'content' => 'HumanResource/Supplier/index',
      'title' => lang('App.supplier')
    ];

    return $this->buildPage($this->data);
  }

  protected function supplier_add()
  {
    checkPermission('Supplier.Add');

    if (requestMethod() == 'POST') {
      $name     = getPost('name');
      $company  = getPost('company');
      $phone    = filterNumber(getPost('phone'));

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($company)) {
        $this->response(400, ['message' => 'Company is required.']);
      }

      if (empty($phone)) {
        $this->response(400, ['message' => 'Phone number is required.']);
      }

      $supplierData = [
        'name'    => $name,
        'company' => $company,
        'email'   => getPost('email'),
        'phone'   => $phone,
        'address' => getPost('address'),
        'city'    => getPost('city'),
        'country' => getPost('country'),
        'json'    => json_encode([
          'acc_name'        => getPost('accname'),
          'acc_no'          => getPost('accno'),
          'acc_holder'      => getPost('accholder'),
          'acc_bic'         => getPost('accbic'),
          'cycle_purchase'  => getPost('purchase_cycle'),
          'delivery_time'   => getPost('delivery_time'),
          'visit_days'      => implode(',', (getPost('visit_days') ?? [])),
          'visit_weeks'     => implode(',', (getPost('visit_weeks') ?? [])),
        ])
      ];

      DB::transStart();

      $insertId = Supplier::add($supplierData);

      if (!$insertId) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => sprintf(lang('Msg.supplierAddOK'), $supplierData['name'])]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addsupplier');

    $this->response(200, ['content' => view('HumanResource/Supplier/add', $this->data)]);
  }

  protected function supplier_delete($supplierId = NULL)
  {
    checkPermission('Supplier.Delete');

    if (requestMethod() != 'POST') {
      $this->response(405, ['message' => 'Method is not allowed.']);
    }

    DB::transStart();

    $res = Supplier::delete(['id' => $supplierId]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (Supplier::delete(['id' => $supplierId])) {
      $this->response(200, ['message' => lang('Msg.supplierDeleteOK')]);
    }

    $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
  }

  protected function supplier_edit($supplierId = NULL)
  {
    checkPermission('Supplier.Edit');

    $supplier = Supplier::getRow(['id' => $supplierId]);

    if (!$supplier) {
      $this->response(404, ['message' => 'Supplier is not exists.']);
    }

    if (requestMethod() == 'POST') {
      $name     = getPost('name');
      $company  = getPost('company');
      $phone    = filterNumber(getPost('phone'));

      if (empty($name)) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($company)) {
        $this->response(400, ['message' => 'Company is required.']);
      }

      if (empty($phone)) {
        $this->response(400, ['message' => 'Phone number is required.']);
      }

      $supplierData = [
        'name'    => $name,
        'company' => $company,
        'email'   => getPost('email'),
        'phone'   => $phone,
        'address' => getPost('address'),
        'city'    => getPost('city'),
        'country' => getPost('country'),
        'json'    => json_encode([
          'acc_name'        => getPost('accname'),
          'acc_no'          => getPost('accno'),
          'acc_holder'      => getPost('accholder'),
          'acc_bic'         => getPost('accbic'),
          'cycle_purchase'  => getPost('purchase_cycle'),
          'delivery_time'   => getPost('delivery_time'),
          'visit_days'      => implode(',', (getPost('visit_days') ?? [])),
          'visit_weeks'     => implode(',', (getPost('visit_weeks') ?? [])),
        ])
      ];

      if (Supplier::update((int)$supplierId, $supplierData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.supplierEditOK'), $supplier->name)]);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.editsupplier');
    $this->data['supplier'] = $supplier;
    $this->data['supplierJS'] = getJSON($supplier->json);

    $this->response(200, ['content' => view('HumanResource/Supplier/edit', $this->data)]);
  }
}
