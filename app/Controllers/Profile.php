<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{User, UserGroup};

class Profile extends BaseController
{
  public function index()
  {
    checkPermission();

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.profile'), 'slug' => 'profile', 'url' => '#']
      ],
      'content' => 'Profile/index',
      'title' => lang('App.profile')
    ];

    $user = User::getRow(['id' => session('login')->user_id]);

    $this->data['user'] = $user;

    foreach (explode(',', $user->groups) as $group) {
      $userGroups[] = UserGroup::getRow(['name' => $group]);
    }

    $this->data['userGroups'] = $userGroups;

    return $this->buildPage($this->data);
  }

  public function getNotifications()
  {
    checkPermission('Notification.View');

    $dt = new DataTables('notification');
    $dt
      ->select("notification.id, notification.note")
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('humanresource/usergroup/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <a class="dropdown-item" href="' . base_url('humanresource/usergroup/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> Delete
              </a>
            </div>
          </div>';
      })
      ->generate();
  }

  public function notification()
  {
    $this->data['title'] = lang('App.notification');

    $this->response(200, ['content' => view('Profile/notification', $this->data)]);
  }
}
