<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{Auth, DB, User, UserGroup};

class Profile extends BaseController
{
  public function getNotifications()
  {
    checkPermission('Notification.View');

    $dt = new DataTables('notification');
    $dt
      ->select("type, created_at, title, note")
      ->where('status', 'active');

    $dt->generate();
  }

  public function index()
  {
    checkPermission();

    if (requestMethod() == 'POST' && isAJAX()) {
      $data = [
        'username'  => getPost('username'),
        'fullname'  => getPost('fullname'),
        'phone'     => getPost('phone'),
        'gender'    => getPost('gender'),
        'company'   => getPost('division'),
      ];

      DB::transStart();

      $res = User::update((int)session('login')->user_id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Profile has been updated.']);
      }

      $this->response(400, ['message' => 'Failed to update profile.']);
    }

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

  public function notification()
  {
    $this->data['title'] = lang('App.notification');

    $this->response(200, ['content' => view('Profile/notification', $this->data)]);
  }

  public function security()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $currentPass  = getPost('currentpass');
      $pass         = getPost('password');

      if (empty($pass)) {
        $this->response(400, ['message' => 'Password is empty.']);
      }

      if (!Auth::verify($currentPass)) {
        $this->response(400, ['message' => 'Last password is wrong.']);
      }

      $res = User::update((int)session('login')->user_id, ['password' => trim($pass)]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      $this->response(200, ['message' => 'Password has been updated.']);
    }
  }
}
