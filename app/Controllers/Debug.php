<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{DB, Test1, Test2};

class Debug extends BaseController
{
  public function log()
  {
    log_message('info', 'INFO OK');
    log_message('debug', 'DEBUG OK');
    log_message('error', 'ERROR OK');
    log_message('notice', 'NOTICE OK');
    log_message('warning', 'WARNING OK');
    $s = "RizonBarns";


    echo $s;
  }

  public function array_object()
  {
    $rows = [
      ['id' => 1, 'name' => 'Riyan'],
      ['id' => 2, 'name' => 'Rizon'],
      ['id' => 3, 'name' => 'Ridintek']
    ];

    array_splice($rows, 1, 1);

    dbgprint($rows);
  }

  public function cache()
  {
    echo '<pre>';
    print_r(cache('debug'));
    echo '</pre>';
    echo '<pre>';
    print_r(cache('dailyPerformance22023-03'));
    echo '</pre>';
    echo '<pre>';
    print_r(cache('revenueForecast2023-03'));
    echo '</pre>';
  }

  public function datetime2()
  {
    $dt = new \DateTime(''); // Return current date.
    echo $dt->format('Y-m-d H:i:s');

    // $dt = new \DateTime('xvsdklf'); // Throw an exception.
    // echo $dt->format('Y-m-d H:i:s');
  }

  public function fix_duplicate_sales()
  {
    $duplicates = DB::table('sales')
      ->select('id, reference, count(reference) duplicate')
      ->groupBy('reference')
      ->having('duplicate > 1')
      ->get();

    $lastReference = '';
    $counter = 1;
    $msg = '';

    foreach ($duplicates as $duplicate) {
      $sales = DB::table('sales')->where('reference', $duplicate->reference)->get();

      foreach ($sales as $sale) {
        if (strcmp($lastReference, $sale->reference) != 0) {
          $lastReference = $sale->reference;
          $msg .= "Real {$sale->id}: {$lastReference}<br>";
          $counter = 1;
        } else {
          $msg .= "Update {$sale->id}: {$lastReference} to {$lastReference}_{$counter}<br>";
          // DB::table('sales')->update(['reference' => $lastReference . "_{$counter}"], ['id' => $sale->id]);
          $counter++;
        }
      }
    }

    echo $msg;
  }

  public function fix_suppliers()
  {
    $suppliers = DB::table('suppliers')->get();
    $msg = '';

    foreach ($suppliers as $supplier) {
      if (strpos($supplier->phone, ' ') !== false) {
        $phone = explode(' ', $supplier->phone)[0];

        $msg .= "Supplier {$supplier->id}: {$supplier->phone} => {$phone}<br>";

        try {
          DB::table('suppliers')->update(['phone' => $phone], ['id' => $supplier->id]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
          echo $e->getMessage();
        }
      }

      if (substr($supplier->name, 0, 1) == ' ') {
        $supplierName = trim($supplier->name);

        $msg .= "Supplier {$supplier->id}: {$supplier->name} => {$supplierName}<br>";

        try {
          DB::table('suppliers')->update(['name' => $supplierName], ['id' => $supplier->id]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
          echo $e->getMessage();
        }
      }
    }

    echo $msg;
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

  public function nullcoalesce()
  {
    $orang = (object)[];

    if (isset($orang->kepala)) {
      echo 'ada kepala';
    } else {
      $orang->kepala = 'botak';
      echo 'Kepala: ' . $orang->kepala . '<br>';
    }

    $badan = ($orang->badan ?? 'gak ada'); // OK
    // $badan = $orang?->badan // Error

    echo 'Badan: ' . $badan;
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
