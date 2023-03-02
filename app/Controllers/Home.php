<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{
  Attachment,
  Bank,
  Biller,
  Customer,
  DB,
  Locale,
  Product,
  ProductTransfer,
  Sale,
  Supplier,
  User,
  Warehouse
};

class Home extends BaseController
{
  public function index()
  {
    checkPermission();

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.dashboard'), 'slug' => 'dashboard', 'url' => '#']
      ],
      'content' => 'dashboard',
      'title' => lang('App.dashboard')
    ];

    return $this->buildPage($this->data);
  }

  public function attachment($hashName = NULL)
  {
    $download = getGet('d');
    $attachment = Attachment::getRow(['hashname' => $hashName]);

    if ($attachment) {
      if ($download == 1) {
        header("Content-Disposition: attachment; filename=\"{$attachment->filename}\"");
      }

      $cacheLifeTime = 86400;

      header('Cache-Control: max-age=' . $cacheLifeTime);
      header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheLifeTime) . ' GMT');
      header('Content-Type: ' . $attachment->mime);
      header('Content-Size: ' . $attachment->size);

      die($attachment->data);
    } else {
      $this->response(404, ['message' => 'File not found.']);
    }
  }

  public function chart($mode = NULL)
  {
    if (!$mode) {
      $this->response(400, ['message' => 'Bad request.']);
    }

    $data = [];

    switch ($mode) {
      case 'monthlySales':
        $data = $this->getMonthlySales();
        break;
      case 'targetRevenue':
        $data = $this->getTargetRevenue();
    }

    $this->response(200, ['data' => $data, 'module' => 'echarts']);
  }

  protected function getMonthlySales()
  {
    $labels       = [];
    $revenues     = [];
    $paids        = [];
    $receivables  = [];

    $res = cache('monthlySales');

    if ($res) {
      return $res;
    }

    // 12 = 12 month ago, if 24 then take data from 24 month ago.
    for ($a = 12; $a >= 0; $a--) {
      $dateMonth = date('Y-m', strtotime('-' . $a . ' month', strtotime(date('Y-m-') . '01')));

      $row = DB::table('sales')
        ->select("COALESCE(SUM(grand_total), 0) AS revenue, COALESCE(SUM(paid), 0) AS paid, COALESCE(SUM(balance), 0) AS receivable")
        ->where("date LIKE '{$dateMonth}%'")
        ->getRow();

      if ($row) {
        $labels[]       = date('Y M', strtotime($dateMonth));
        $revenues[]     = floatval($row->revenue);
        $paids[]        = floatval($row->paid);
        $receivables[]  = floatval($row->receivable > 0 ? $row->receivable * -1 : $row->receivable);
      }
    }

    $res = [
      'legend' => [
        'data' => [
          lang('App.revenue'), lang('Status.paid'), lang('App.receivable')
        ]
      ],
      'series' => [
        [
          'name' => lang('App.revenue'),
          'data' => $revenues
        ],
        [
          'name' => lang('Status.paid'),
          'data' => $paids
        ],
        [
          'name' => lang('App.receivable'),
          'data' => $receivables
        ],
      ],
      'xAxis' => [
        'data' => $labels
      ]
    ];

    cache()->save('monthlySales', $res);

    return $res;
  }

  protected function getTargetRevenue()
  {
    $labels   = [];
    $targets  = [];
    $revenues = [];
    $paids = [];
    $startDate  = date('Y-m-') . '01';
    $endDate    = date('Y-m-d');

    $res = cache('targetRevenue');

    if ($res) {
      return $res;
    }

    $billers = Biller::get(['active' => 1]);

    foreach ($billers as $biller) {
      if (substr($biller->code, 0, 3) == 'IDS') continue; // Prevent Indostore.

      $billerJS = getJSON($biller->json);

      if (strcasecmp($biller->code, 'LUC') != 0) {
        $inv = Sale::select('SUM(grand_total) AS revenue, SUM(paid) AS paid')
          ->where('biller', $biller->code)
          ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
          ->notLike('status', 'need_payment')
          ->getRow();
      } else { // Lucretai
        $inv = ProductTransfer::select('SUM(grand_total) AS revenue, SUM(paid) AS paid')
          ->where("date BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'")
          ->getRow();
      }

      $labels[]   = $biller->name;
      $targets[]  = floatval($billerJS->target);
      $revenues[] = floatval($inv->revenue);
      $paids[]    = floatval($inv->paid);
    }

    $res = [
      'legend' => [
        'data' => [
          lang('App.targetrevenue'), lang('App.revenue'), lang('Status.paid')
        ]
      ],
      'series' => [
        [
          'name' => lang('App.targetrevenue'),
          'data' => $targets
        ],
        [
          'name' => lang('App.revenue'),
          'data' => $revenues
        ],
        [
          'name' => lang('Status.paid'),
          'data' => $paids
        ],
      ],
      'xAxis' => [
        'data' => $labels
      ]
    ];

    cache()->save('targetRevenue', $res);

    return $res;
  }

  public function lang($localeCode = 'id')
  {
    checkPermission();

    foreach (Locale::get() as $locale) {
      if ($locale->code == $localeCode) {
        $login = session('login');

        User::update((int)$login->user_id, ['lang' => $locale->code]);
        $login->lang = $locale->code;

        session()->set('login', $login);

        $this->response(200, ['message' => 'Change language success', 'data' => $locale->code]);
      }
    }

    $this->response(404, ['message' => 'Language not found']);
  }

  public function mutasi()
  {
    $log = new \FileLogger(WRITEPATH . 'logs/mutasi-' . date('Y-m-d') . '.log');

    $log->write($this->request->getRawInput());

    echo "OK";
  }

  public function select2($mode = NULL)
  {
    if (!$mode) {
      $this->response(400, ['message' => 'Bad request.']);
    }

    $mode       = strtolower($mode);
    $results    = [];
    $term       = getGet('term');
    $billers    = getGet('biller');
    $warehouses = getGet('warehouse');
    $types      = getGet('type');

    switch ($mode) {
      case 'bank':
        $q = Bank::select("code id, (CASE WHEN number IS NOT NULL THEN CONCAT(name, ' (', number, ')') ELSE name END) text")
          ->where('active', 1)
          ->limit(10);

        if ($term) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('code', $term, 'both')
            ->orLike('name', $term, 'both')
            ->orLike('number', $term, 'both')
            ->groupEnd();
        }

        if ($billers) {
          if (!is_array($billers)) {
            $billers = [$billers];
          }

          $q->whereIn('biller', $billers);
        }

        if ($types) {
          if (!is_array($types)) {
            $types = [$types];
          }

          $q->whereIn('type', $types);
        }

        $results = $q->get();

        break;
      case 'biller':
        $q = Biller::select("code id, name text")
          ->where('active', 1)
          ->limit(10);

        if ($term) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('code', $term, 'both')
            ->orLike('name', $term, 'both')
            ->groupEnd();
        }

        if ($biller = session('login')->biller) {
          $q->where('code', $biller);
        }

        $results = $q->get();

        break;
      case 'customer':
        $q = Customer::select("phone id, (CASE WHEN company IS NOT NULL AND company <> '' THEN CONCAT(name, ' (', company, ')') ELSE name END) text")
          ->limit(10);

        if ($term) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('name', $term, 'both')
            ->orLike('company', $term, 'both')
            ->orLike('phone', $term, 'none')
            ->groupEnd();
        }

        $results = $q->get();

        break;
      case 'product':
        $q = Product::select("code id, CONCAT('(', code, ') ', name) text")
          ->where('active', 1)
          ->limit(10);

        if ($term) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('code', $term, 'both')
            ->orLike('name', $term, 'both')
            ->groupEnd();
        }

        if ($types) {
          if (!is_array($types)) {
            $types = [$types];
          }

          $q->whereIn('type', $types);
        }

        $results = $q->get();

        break;
      case 'supplier':
        $q = Supplier::select("phone id, (CASE WHEN company IS NOT NULL AND company <> '' THEN CONCAT(name, ' (', company, ')') ELSE name END) text ")
          ->limit(10);

        if ($term) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('name', $term, 'both')
            ->orLike('company', $term, 'both')
            ->groupEnd();
        }

        $results = $q->get();

        break;
      case 'user':
        $q = User::select("phone id, fullname text")
          ->where('active', 1)
          ->limit(10);

        if ($term) {
          $q->groupStart()
            ->where('id', $term)
            ->orWhere('phone', $term)
            ->orLike('fullname', $term, 'both')
            ->orLike('username', $term, 'both')
            ->groupEnd();
        }

        if ($billers) {
          $q->whereIn('biller', $billers);
        }

        if ($warehouses) {
          $q->whereIn('warehouse', $warehouses);
        }

        $results = $q->get();

        break;
      case 'warehouse':
        $q = Warehouse::select("code id, name text ")
          ->where('active', 1)
          ->limit(10);

        if ($term) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('code', $term, 'both')
            ->orLike('name', $term, 'both')
            ->groupEnd();
        }

        // Do not filter, we need for Sale TB.
        // if ($warehouse = session('login')->warehouse) {
        //   $q->where('code', $warehouse);
        // }

        $results = $q->get();

        break;
    }

    $this->response(200, ['results' => $results]);
  }
}
