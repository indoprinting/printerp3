<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{DB, Test1, Test2};

class Debug extends BaseController
{
  public function datetime2()
  {
    $dt = new \DateTime(''); // Return current date.
    echo $dt->format('Y-m-d H:i:s');

    // $dt = new \DateTime('xvsdklf'); // Throw an exception.
    // echo $dt->format('Y-m-d H:i:s');
  }

  public function dbtrans()
  {
    DB::transStart();

    $insertId = Test1::add(['name' => 'RIYAN']);

    if (!$insertId) {
      $this->response(400, ['message' => 'error 1: ' . getLastError()]);
    }

    $insertId2 = Test2::add(['test1_id' => $insertId, 'name' => 'WIDIYANTO']);

    if (!$insertId2) {
      $this->response(400, ['message' => 'error 2: ' . getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      echo "Success";
    } else {
      echo "FAILED: {$insertId}:{$insertId2} => " . DB::error()['message'];
    }
  }

  public function invoice()
  {
    $this->response(200, [
      'content' => view('Debug/invoice')
    ]);
  }

  public function datetime()
  {
    $dt = date('Y-m-d H:i:s', strtotime('2023-01-20LSDFJ17:00:00'));

    dd($dt);
  }

  public function modal()
  {
    $this->response(200, [
      'content' => view('Debug/modal')
    ]);
  }

  public function modal2()
  {
    $this->response(200, [
      'content' => view('Debug/modal2')
    ]);
  }

  public function model()
  {
    \App\Models\Debug::add('HALO');
  }

  public function page()
  {
    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.debug'), 'slug' => 'debug', 'url' => '#'],
        ['name' => lang('App.page'), 'slug' => 'page', 'url' => '#']
      ],
      'content' => 'Debug/page',
      'title' => lang('App.debug')
    ];

    $this->buildPage($this->data);
  }

  public function password(string $pass = 'Durian100')
  {
    echo password_hash($pass, PASSWORD_DEFAULT);
  }

  public function session()
  {
    echo ('<pre>');
    print_r(session('login'));
    echo ('</pre>');
  }
}
