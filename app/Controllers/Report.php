<?php

declare(strict_types=1);

namespace App\Controllers;

class Report extends BaseController
{
  public function dailyperformance()
  {
    checkPermission();

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => lang('App.dailyperformance'), 'slug' => 'dailyperformance', 'url' => '#']
      ],
      'content' => 'Report/DailyPerformance/index',
      'title' => lang('App.dailyperformance')
    ];

    return $this->buildPage($this->data);
  }

  public function getDailyPerformance()
  {
  }
}
