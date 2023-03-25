<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{
  Attachment,
  Bank,
  Biller,
  Customer,
  DB,
  ExpenseCategory,
  Locale,
  Product,
  ProductTransfer,
  Sale,
  Supplier,
  User,
  UserGroup,
  Voucher,
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

  public function attachment($hashName = null)
  {
    $download   = getGet('d');
    $cacheKey   = 'attachment_' . $hashName;
    $cache      = cache($cacheKey);

    if ($cache) {
      $attachment = $cache;
    } else {
      $attachment = Attachment::getRow(['hashname' => $hashName]);
    }


    if ($attachment) {
      if ($download == 1) {
        header("Content-Disposition: attachment; filename=\"{$attachment->filename}\"");
      }

      $cacheLifeTime = 86400;

      header('Cache-Control: max-age=' . $cacheLifeTime);
      header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheLifeTime) . ' GMT');
      header('Content-Type: ' . $attachment->mime);
      header('Content-Size: ' . $attachment->size);

      if (!$cache) {
        cache()->save($cacheKey, $attachment);
      }

      die($attachment->data);
    } else {
      $this->response(404, ['message' => 'File not found.']);
    }
  }

  public function chart($mode = null)
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

    $cacheKey = $mode . ($option['biller'] ?? '') . ($option['period'] ?? '');

    $cache = cache($cacheKey);

    if (!$cache) {
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

      cache()->save($cacheKey, $data, 300);
    } else {
      $data = $cache;
    }


    $this->response(200, ['data' => $data, 'message' => getLastError(), 'module' => 'echarts']);
  }

  /**
   * Get Daily Performance chart.
   * Only one biller allowed.
   */
  protected function chartDailyPerformance($option = [])
  {
    $receivables  = [];
    $revenues     = [];
    $stockValues  = [];
    $labels       = [];

    if (isset($option['period'])) {
      $period = new \DateTime($option['period'] . '-01');
    } else {
      $period = new \DateTime(date('Y-m-') . '01');
    }

    $startDate  = new \DateTime($period->format('Y-m-d'));
    $endDate    = new \DateTime($period->format('Y-m-t'));

    if (!isset($option['biller'])) {
      setLastError('Biller is not set.');
      return false;
    }

    $biller = Biller::getRow(['id' => $option['biller']]);

    if (!$biller) {
      setLastError('Biller is not found.');
      return false;
    }

    $warehouse = Warehouse::getRow(['code' => $biller->code]);

    $option = getCurrentMonthPeriod($option);
    $beginDate = new \DateTime('2022-01-01 00:00:00');
    $ymPeriod = $startDate->format('Y-m'); // date('Y-m', strtotime($option['start_date']));

    for ($a = 1; $a <= $endDate->format('j'); $a++) {
      $dt = prependZero($a);

      if ($biller->code == 'LUC') {
        $dailyRevenue = round(floatval(DB::table('product_transfer')
          ->selectSum('grand_total', 'total')
          ->where('warehouse_id_from', $warehouse->id)
          ->where("date LIKE '{$ymPeriod}-{$dt}%'")
          ->getRow()->total) ?? 0);

        $receivable = round(floatval(DB::table('product_transfer')
          ->selectSum('(grand_total - paid)', 'total')
          ->where('warehouse_id_from', $warehouse->id)
          ->where("date BETWEEN '{$beginDate->format('Y-m-d')} 00:00:00' AND '{$ymPeriod}-{$dt}%'")
          ->getRow()->total) ?? 0);

        if ($warehouse) {
          $stockValue = getWarehouseStockValue((int)$warehouse->id, [
            'start_date'  => $beginDate->format('Y-m-d'),
            'end_date'    => "{$ymPeriod}-{$dt}"
          ]);
        } else {
          $stockValue = 0;
        }
      } else {
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

        if ($warehouse) {
          $stockValue = round(getWarehouseStockValue((int)$warehouse->id, [
            'start_date'  => $beginDate->format('Y-m-d'),
            'end_date'    => "{$ymPeriod}-{$dt}"
          ]));
        } else {
          $stockValue = 0;
        }
      }

      $revenues[]     = $dailyRevenue;
      $stockValues[]  = $stockValue;
      $receivables[]  = $receivable;

      $labels[] = $dt;
    }

    setLastError('success');

    $res = [
      'legend' => [
        'data' => [
          lang('App.revenue'), lang('App.stockvalue'), lang('App.receivable')
        ]
      ],
      'series' => [
        [
          'name' => lang('App.stockvalue'),
          'data' => $stockValues
        ],
        [
          'name' => lang('App.revenue'),
          'data' => $revenues
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

    return $res;
  }

  protected function chartRevenueForecast($option = [])
  {
    $avgRevenues  = [];
    $revenues     = [];
    $targets      = [];
    $forecasts    = [];
    $labels       = [];

    if (isset($option['period'])) {
      $period = new \DateTime($option['period'] . '-01');
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

    setLastError('success');

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

    return $res;
  }

  protected function chartMonthlySales()
  {
    $labels       = [];
    $revenues     = [];
    $paids        = [];
    $receivables  = [];

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

    setLastError('success');

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

    return $res;
  }

  protected function chartTargetRevenue()
  {
    $labels     = [];
    $targets    = [];
    $revenues   = [];
    $paids      = [];
    $startDate  = date('Y-m-') . '01';
    $endDate    = date('Y-m-d');

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

    setLastError('success');

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

  public function select2($mode = null, $submode = null)
  {
    if (!$mode) {
      $this->response(400, ['message' => 'Bad request.']);
    }

    $results  = [];

    switch (strtolower($mode)) {
      case 'bank':
        $results = $this->select2_bank($submode);
        break;
      case 'biller':
        $results = $this->select2_biller();
        break;
      case 'customer':
        $results = $this->select2_customer();
        break;
      case 'expense':
        $results = $this->select2_expense($submode);
        break;
      case 'product':
        $results = $this->select2_product();
        break;
      case 'supplier':
        $results = $this->select2_supplier();
        break;
      case 'user':
        $results = $this->select2_user();
        break;
      case 'usergroup':
        $results = $this->select2_usergroup();
        break;
      case 'voucher':
        $results = $this->select2_voucher();
        break;
      case 'warehouse':
        $results = $this->select2_warehouse();
        break;
    }

    $this->response(200, ['results' => $results]);
  }

  protected function select2_bank($mode = null)
  {
    $biller = getGet('biller');
    $limit  = getGet('limit');
    $term   = getGet('term');
    $type   = getGet('type');

    if ($mode == 'type') {
      $q = Bank::select('type id, type text')->distinct();

      return $q->get();
    }

    $q = Bank::select("id, (CASE WHEN number IS NOT NULL THEN CONCAT(name, ' (', number, ')') ELSE name END) text")
      ->where('active', 1);

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

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

    if ($biller) {
      $q->whereIn('biller_id', $biller);
    }

    if ($type) {
      $q->whereIn('type', $type);
    }

    return $q->get();
  }

  protected function select2_biller()
  {
    $limit  = getGet('limit');
    $term   = getGet('term');

    $q = Biller::select("id, name text")
      ->where('active', 1);

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

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

    return $q->get();
  }

  protected function select2_customer()
  {
    $limit  = getGet('limit');
    $term   = getGet('term');

    $q = Customer::select("id, (CASE WHEN company IS NOT NULL AND company <> '' THEN CONCAT(name, ' (', company, ')') ELSE name END) text");

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

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

    return $q->get();
  }

  protected function select2_expense($submode = null)
  {
    $limit  = getGet('limit');
    $term   = getGet('term');

    if ($submode == 'category') {
      $q = ExpenseCategory::select("id, name text");

      if ($limit) {
        $q->limit(intval($limit));
      } else {
        $q->limit(30);
      }

      if ($term && is_string($term)) {
        $q->groupStart()
          ->where('id', $term)
          ->orLike('name', $term, 'both')
          ->groupEnd();
      } else if ($term && is_array($term)) {
        $q->groupStart()
          ->whereIn('id', $term)
          ->orWhereIn('name', $term)
          ->groupEnd();
      }

      return $q->get();
    }

    return []; // Reserved
  }

  protected function select2_product()
  {
    $limit  = getGet('limit');
    $term   = getGet('term');
    $types  = getGet('type');

    $q = Product::select("id, CONCAT('(', code, ') ', name) text")
      ->where('active', 1);

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

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
      $q->whereIn('type', $types);
    }

    return $q->get();
  }

  protected function select2_supplier()
  {
    $limit  = getGet('limit');
    $term   = getGet('term');

    $q = Supplier::select("id, (CASE WHEN company IS NOT NULL AND company <> '' THEN CONCAT(name, ' (', company, ')') ELSE name END) text ");

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

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

    return $q->get();
  }

  protected function select2_user()
  {
    $billers    = getGet('biller');
    $limit      = getGet('limit');
    $term       = getGet('term');
    $warehouses = getGet('warehouse');

    $q = User::select("id, fullname text")
      ->where('active', 1);

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

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
      $q->whereIn('biller_id', $billers);
    }

    if ($warehouses) {
      $q->whereIn('warehouse_id', $warehouses);
    }

    return $q->get();
  }

  protected function select2_usergroup()
  {
    $limit  = getGet('limit');
    $term   = getGet('term');

    $q = UserGroup::select("id, name text");

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

    if ($term && is_string($term)) {
      $q->groupStart()
        ->where('id', $term)
        ->orLike('name', $term, 'both')
        ->groupEnd();
    } else if ($term && is_array($term)) {
      $q->groupStart()
        ->whereIn('id', $term)
        ->orWhereIn('name', $term)
        ->groupEnd();
    }

    return $q->get();
  }

  protected function select2_voucher()
  {
    $limit  = getGet('limit');
    $term   = getGet('term');

    $currentDate = date('Y-m-d H:i:s');

    $q = Voucher::select("id, code text")
      ->where('quota > 0')
      ->where("valid_from < '{$currentDate}'")
      ->where("valid_to > '{$currentDate}'");

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

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

    return $q->get();
  }

  protected function select2_warehouse()
  {
    $limit  = getGet('limit');
    $term   = getGet('term');

    $q = Warehouse::select("id, name text ")
      ->where('active', 1);

    if ($limit) {
      $q->limit(intval($limit));
    } else {
      $q->limit(30);
    }

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

    return $q->get();
  }
}
