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

    $data   = [];
    $option = [];

    if ($biller = getGet('biller')) {
      $option['biller'] = $biller;
    }

    if ($period = getGet('period')) {
      $option['period'] = $period;
    }

    switch ($mode) {
      case 'dailyPerformance':
        $data = $this->chartDailyPerformance($option);
        break;
      case 'monthlySales':
        $data = $this->chartMonthlySales();
        break;
      case 'revenueForecast':
        $data = $this->chartRevenueForecast($option);
        break;
      case 'targetRevenue':
        $data = $this->chartTargetRevenue();
    }

    $this->response(200, ['data' => $data, 'module' => 'echarts']);
  }

  protected function chartDailyPerformance($option = [])
  {
    $receivables  = [];
    $revenues     = [];
    $stockValues  = [];
    $labels       = [];
    $dailyData    = [];

    $res = cache('chartDailyPerformance');

    if ($res) {
      return $res;
    }

    if (isset($option['period'])) {
      $option['start_date'] = date('Y-m-', strtotime($option['period'])) . '01';
      $option['end_date']   = date('Y-m-d', strtotime($option['period']));
    }

    $option = getCurrentMonthPeriod($option);
    $beginDate = new \DateTime('2022-01-01 00:00:00');
    $billers = Biller::get(['active' => 1]);
    $ymPeriod = date('Y-m', strtotime($option['start_date']));

    for ($a = 1; $a < date('j', strtotime($option['end_date'])); $a++) {
      $dt = prependZero($a);
      $dtDaily = new \DateTime("{$ymPeriod}-{$dt}");
      $overTime = ((new \DateTime())->diff($dtDaily)->format('%R') == '+' ? true : false);

      foreach ($billers as $biller) {
        $billerJS = getJSON($biller->json);
        $warehouse = Warehouse::getRow(['code' => $biller->code]);

        if (empty($billerJS->target)) {
          continue;
        }

        if ($biller->code == 'LUC') {
          if (!$overTime) {
            $dailyRevenue = round(floatval(DB::table('product_transfer')
              ->selectSum('grand_total', 'total')
              ->where('warehouse_id_from', $warehouse->id)
              ->where("created_at LIKE '{$ymPeriod}-{$dt}%'")
              ->getRow()->total) ?? 0);

            $receivable = round(floatval(DB::table('product_transfer')
              ->selectSum('(grand_total - paid)', 'total')
              ->where('warehouse_id_from', $warehouse->id)
              ->where("created_at BETWEEN '{$beginDate->format('Y-m-d')} 00:00:00' AND '{$ymPeriod}-{$dt}%'")
              ->getRow()->total) ?? 0);
          } else {
            $dailyRevenue = 0;
            $receivable   = 0;
          }

          if ($warehouse) {
            $stockValue = getWarehouseStockValue((int)$warehouse->id, [
              'start_date'  => $beginDate->format('Y-m-d'),
              'end_date'    => "{$ymPeriod}-{$dt}"
            ]);
          } else {
            $stockValue = 0;
          }
        } else {
          if (!$overTime) {
            $dailyRevenue = round(floatval(DB::table('sales')
              ->selectSum('grand_total', 'total')
              ->notLike('status', 'need_payment')
              ->where('biller_id', $biller->id)
              ->where("date LIKE '{$ymPeriod}-{$dt}%'")
              ->getRow()->total) ?? 0);

            $receivable = round(floatval(DB::table('sales')
              ->selectSum('balance', 'total')
              ->where('biller_id', $biller->id)
              ->where("date BETWEEN '{$beginDate->format('Y-m-d')} 00:00:00' AND '{$ymPeriod}-{$dt}%'")
              ->getRow()->total) ?? 0);
          } else {
            $dailyRevenue = 0;
            $receivable   = 0;
          }

          if ($warehouse) {
            $stockValue = getWarehouseStockValue((int)$warehouse->id, [
              'start_date'  => $beginDate->format('Y-m-d'),
              'end_date'    => "{$ymPeriod}-{$dt}"
            ]);
          } else {
            $stockValue = 0;
          }
        }

        $dailyData[] = [
          'revenue'     => $dailyRevenue,
          'stock_value' => $stockValue,
          'receivable'  => $receivable
        ];

        $labels[] = lang('App.day') . ' ' . $dt;
      }
    }

    $res = [
      'legend' => [
        'data' => [
          lang('App.revenue'), lang('App.stockvalue'), lang('App.receivable')
        ]
      ],
      'series' => [
        [
          'name' => lang('App.revenue'),
          'data' => $revenues
        ],
        [
          'name' => lang('App.stockvalue'),
          'data' => $stockValues
        ],
        [
          'name' => lang('App.receivable'),
          'data' => $receivables
        ]
      ],
      'xAxis' => [
        'data' => $labels
      ]
    ];

    // cache()->save('chartDailyPerformance', $res);

    return $res;
  }

  protected function chartRevenueForecast($option = [])
  {
    $avgRevenues  = [];
    $revenues     = [];
    $targets      = [];
    $forecasts    = [];
    $labels       = [];

    $res = cache('chartRevenueForecast');

    if ($res) {
      return $res;
    }

    if (isset($option['period'])) {
      $period = new \DateTime($option['period'] . '-01');
      unset($option['period']);
    } else {
      $period = new \DateTime(date('Y-m-') . '01');
    }

    $startDate  = new \DateTime($period->format('Y-m-d'));
    $endDate    = new \DateTime($period->format('Y-m-t'));

    $option       = getCurrentMonthPeriod($option);
    $currentDate  = new \DateTime();
    $activeDays   = $startDate->diff($currentDate)->format('%a');
    $billers      = Biller::get(['active' => 1]);
    $daysInMonth  = getDaysInMonth($startDate->format('Y'), $startDate->format('n'));

    foreach ($billers as $biller) {
      $billerJS = getJSON($biller->json);

      if (empty($billerJS->target)) {
        continue;
      }

      if ($biller->code == 'LUC') {
        $revenue = (DB::table('product_transfer')->selectSum('grand_total', 'total')
          ->where("date BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'")
          ->getRow()->total ?? 0);
      } else {
        $revenue = (DB::table('sales')->selectSum('grand_total', 'total')
          ->where("date BETWEEN '{$startDate->format('Y-m-d')} 00:00:00' AND '{$endDate->format('Y-m-d')} 23:59:59'")
          ->where('biller', $biller->code)
          ->notLike('status', 'need_payment')
          ->getRow()->total ?? 0);
      }

      $avgRevenue = ($revenue / $activeDays);

      $labels[]       = $biller->name;
      $targets[]      = floatval($billerJS->target);
      $revenues[]     = floatval($revenue);
      $avgRevenues[]  = round($avgRevenue);
      $forecasts[]    = round($avgRevenue * $daysInMonth);
    }


    $res = [
      'legend' => [
        'data' => [
          lang('App.targetrevenue'), lang('App.revenue'), lang('App.averagerevenue'), lang('App.forecast')
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
          'name' => lang('App.averagerevenue'),
          'data' => $avgRevenues
        ],
        [
          'name' => lang('App.forecast'),
          'data' => $forecasts
        ],
      ],
      'xAxis' => [
        'data' => $labels
      ]
    ];

    // cache()->save('chartRevenueForecast', $res);

    return $res;
  }

  protected function chartMonthlySales()
  {
    $labels       = [];
    $revenues     = [];
    $paids        = [];
    $receivables  = [];

    $res = cache('chartMonthlySales');

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

    cache()->save('chartMonthlySales', $res);

    return $res;
  }

  protected function chartTargetRevenue()
  {
    $labels   = [];
    $targets  = [];
    $revenues = [];
    $paids = [];
    $startDate  = date('Y-m-') . '01';
    $endDate    = date('Y-m-d');

    $res = cache('chartTargetRevenue');

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

    cache()->save('chartTargetRevenue', $res);

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

        if ($term && is_string($term)) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('code', $term, 'both')
            ->orLike('name', $term, 'both')
            ->orLike('number', $term, 'both')
            ->groupEnd();
        } else if ($term && is_array($term)) {
          $q->groupStart()
            ->whereIn('id', $term)
            ->orWhereIn('code', $term)
            ->orWhereIn('name', $term)
            ->orWhereIn('number', $term)
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

        if ($term && is_string($term)) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('code', $term, 'both')
            ->orLike('name', $term, 'both')
            ->groupEnd();
        } else if ($term && is_array($term)) {
          $q->groupStart()
            ->whereIn('id', $term)
            ->orWhereIn('code', $term)
            ->orWhereIn('name', $term)
            ->groupEnd();
        }

        $results = $q->get();

        break;
      case 'customer':
        $q = Customer::select("phone id, (CASE WHEN company IS NOT NULL AND company <> '' THEN CONCAT(name, ' (', company, ')') ELSE name END) text")
          ->limit(10);

        if ($term && is_string($term)) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('name', $term, 'both')
            ->orLike('company', $term, 'both')
            ->orLike('phone', $term, 'none')
            ->groupEnd();
        } else if ($term && is_array($term)) {
          $q->groupStart()
            ->whereIn('id', $term)
            ->orWhereIn('name', $term)
            ->orWhereIn('company', $term)
            ->orWhereIn('phone', $term)
            ->groupEnd();
        }

        $results = $q->get();

        break;
      case 'product':
        $q = Product::select("code id, CONCAT('(', code, ') ', name) text")
          ->where('active', 1)
          ->limit(10);

        if ($term && is_string($term)) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('code', $term, 'both')
            ->orLike('name', $term, 'both')
            ->groupEnd();
        } else if ($term && is_array($term)) {
          $q->groupStart()
            ->whereIn('id', $term)
            ->orWhereIn('code', $term)
            ->orWhereIn('name', $term)
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

        if ($term && is_string($term)) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('name', $term, 'both')
            ->orLike('company', $term, 'both')
            ->groupEnd();
        } else if ($term && is_array($term)) {
          $q->groupStart()
            ->whereIn('id', $term)
            ->orWhereIn('name', $term)
            ->orWhereIn('company', $term)
            ->groupEnd();
        }

        $results = $q->get();

        break;
      case 'user':
        $q = User::select("phone id, fullname text")
          ->where('active', 1)
          ->limit(10);

        if ($term && is_string($term)) {
          $q->groupStart()
            ->where('id', $term)
            ->orWhere('phone', $term)
            ->orLike('fullname', $term, 'both')
            ->orLike('username', $term, 'both')
            ->groupEnd();
        } else if ($term && is_array($term)) {
          $q->groupStart()
            ->whereIn('id', $term)
            ->orWhereIn('phone', $term)
            ->orWhereIn('fullname', $term)
            ->orWhereIn('username', $term)
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

        if ($term && is_string($term)) {
          $q->groupStart()
            ->where('id', $term)
            ->orLike('code', $term, 'both')
            ->orLike('name', $term, 'both')
            ->groupEnd();
        } else if ($term && is_array($term)) {
          $q->groupStart()
            ->whereIn('id', $term)
            ->orWhereIn('code', $term)
            ->orWhereIn('name', $term)
            ->groupEnd();
        }

        $results = $q->get();

        break;
    }

    $this->response(200, ['results' => $results]);
  }
}
