<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DB;

class Settings extends BaseController
{
  public function index()
  {
    checkPermission('Setting.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.settings'), 'slug' => 'settings', 'url' => '#'],
        ['name' => lang('App.general'), 'slug' => 'general', 'url' => '#']
      ],
      'content' => 'Settings/index',
      'title' => lang('App.general')
    ];

    return $this->buildPage($this->data);
  }

  public function theme()
  {
    checkPermission();

    $darkMode = (getGet('darkmode') == 1 ? 1 : 0);
    $userId = session('login')->user_id;

    DB::table('users')->update(['dark_mode' => $darkMode], ['id' => $userId]);
    session('login')->dark_mode = $darkMode;

    $this->response(200, ['message' => 'Success']);
  }
}
