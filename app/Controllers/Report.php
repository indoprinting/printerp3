<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables, Spreadsheet};
use App\Models\Bank;
use App\Models\DB;
use App\Models\Jobs;
use App\Models\Notification;
use App\Models\User;
use App\Models\WAJob;

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

  /**
   * Get daily performance report.
   */
  public function getDailyPerformanceReport()
  {
    $period = getGET('period'); // 2022-11
    $xls    = (getGET('xls') == 1 ? true : false);

    $opt = [];

    $opt['period'] = ($period ?? date('Y-m')); // Default current year and month.

    if (!$xls) { // Send to DataTables.
      $this->response(200, [
        'data' => getDailyPerformanceReport($opt) // Helper
      ]);
    } else { // Save as Excel
      $ddGrid = [
        ['F', 'G', 'H'], ['I', 'J', 'K'], ['L', 'M', 'N'], ['O', 'P', 'Q'], ['R', 'S', 'T'],
        ['U', 'V', 'W'], ['X', 'Y', 'Z'], ['AA', 'AB', 'AC'], ['AD', 'AE', 'AF'], ['AG', 'AH', 'AI'],
        ['AJ', 'AK', 'AL'], ['AM', 'AN', 'AO'], ['AP', 'AQ', 'AR'], ['AS', 'AT', 'AU'], ['AV', 'AW', 'AX'],
        ['AY', 'AZ', 'BA'], ['BB', 'BC', 'BD'], ['BE', 'BF', 'BG'], ['BH', 'BI', 'BJ'], ['BK', 'BL', 'BM'],
        ['BN', 'BO', 'BP'], ['BQ', 'BR', 'BS'], ['BT', 'BU', 'BV'], ['BW', 'BX', 'BY'], ['BZ', 'CA', 'CB'],
        ['CC', 'CD', 'CE'], ['CF', 'CG', 'CH'], ['CI', 'CJ', 'CK'], ['CL', 'CM', 'CN'], ['CO', 'CP', 'CQ'],
        ['CR', 'CS', 'CT']
      ];

      $dailyPerfData = getDailyPerformanceReport($opt);

      $sheet = new Spreadsheet();
      $sheet->loadFile(FCPATH . 'files/templates/DailyPerformance_Report.xlsx');

      $sheet->setTitle('Period ' . $opt['period']);

      $r1 = 3; // 3rd row.

      foreach ($dailyPerfData as $dp) {
        $sheet->setCellValue('A' . $r1, $dp['biller']);
        $sheet->setCellValue('B' . $r1, $dp['target']);
        $sheet->setCellValue('C' . $r1, $dp['revenue']);
        $sheet->setCellValue('D' . $r1, $dp['avg_revenue']);
        $sheet->setCellValue('E' . $r1, $dp['forecast']);

        $r2 = 0;
        foreach ($dp['daily_data'] as $dd) {
          $sheet->setCellValue($ddGrid[$r2][0] . $r1, $dd['revenue']);
          $sheet->setCellValue($ddGrid[$r2][1] . $r1, $dd['stock_value']);
          $sheet->setCellValue($ddGrid[$r2][2] . $r1, $dd['piutang']);

          $r2++;
        }

        $r1++;
      }

      $last = $r1 - 1;

      $sheet->setCellValue('A' . $r1, 'GRAND TOTAL');
      $sheet->setCellValue('B' . $r1, "=SUM(B3:B{$last})");
      $sheet->setCellValue('C' . $r1, "=SUM(C3:C{$last})");
      $sheet->setCellValue('D' . $r1, "=SUM(D3:D{$last})");
      $sheet->setCellValue('E' . $r1, "=SUM(E3:E{$last})");

      $sheet->setBold('A' . $r1);

      $name = session('login')->fullname;

      $sheet->export('PrintERP-DailyPerformance-' . date('Ymd_His') . "-($name)");
    }
  }

  public function getPayments()
  {
    checkPermission('Report.Payment');

    $bank       = getPostGet('bank');
    $biller     = getPostGet('biller');
    $createdBy  = getPostGet('created_by');
    $customer   = getPostGet('customer');
    $status     = getPostGet('status');

    $startDate  = getPostGet('start_date');
    $endDate    = getPostGet('end_date');

    $dt = new DataTables('payments');
    $dt->select("payments.id, payments.date, payments.reference_date, payments.reference,
        creator.fullname AS creator_name, biller.name AS biller_name,
        customers.name AS customer_name,
        (CASE
          WHEN banks.number IS NULL THEN banks.name
          WHEN banks.number IS NOT NULL THEN CONCAT(banks.name, ' (', banks.number, ')')
        END) bank_name, payments.method, payments.amount, payments.type, payments.note,
        payments.created_at, payments.attachment")
      ->join('banks', 'banks.id = payments.bank_id', 'left')
      ->join('biller', 'biller.id = payments.biller_id', 'left')
      ->join('sales', 'sales.id = payments.sale_id', 'left')
      ->join('customers', 'customers.id = sales.customer_id', 'left')
      ->join('users creator', 'creator.id = payments.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('payment/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic2"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('payment/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('amount', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount']) . '</div>';
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->editColumn('type', function ($data) {
        return renderStatus($data['type']);
      });

    if ($bank) {
      $dt->whereIn('payments.bank_id', $bank);
    }

    if ($biller) {
      $dt->whereIn('payments.biller_id', $biller);
    }

    if ($createdBy) {
      $dt->whereIn('payments.created_by', $createdBy);
    }

    if ($customer) {
      $dt->whereIn('customers.id', $customer);
    }

    if ($status) {
      $dt->whereIn('payments.type', $status);
    }

    if ($startDate) {
      $dt->where("payments.date >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("payments.date <= '{$endDate} 23:59:59'");
    }

    $dt->generate();
  }

  public function index()
  {
    echo "OK";
  }

  public static function callback($job, string $response)
  {
    $user = User::getRow(['id' => $job->created_by]);

    if (!$user) {
      return false;
    }

    $msg = '<a href="' . $response . '">' . $response . '</a>';

    Notification::add([
      'title'   => 'Export report',
      'note'    => 'Report has been created: ' . $msg,
      'scopes'  => json_encode(['users' => [$user->id]]),
      'status'  => 'active'
    ]);

    WAJob::add(['phone' => $user->phone, 'message' => "Report has been created: {$response}."]);
  }

  /**
   * Called by client side using POST ajax or fetch. JSON as data.
   */
  public function export(string $name = null)
  {
    $param = file_get_contents('php://input'); // JSON data.

    if (empty($name)) {
      $this->response(400, ['message' => 'Report name is required.']);
    }

    if (empty($param)) {
      $this->response(400, ['message' => 'Param is required.']);
    }

    // PrintERP Job service 'printerp-job' must be run to running the export jobs.
    // Make sure 'systemctl status printerp-job' is running.
    $insertId = Jobs::add([
      'class'     => '\App\Controllers\Report::job_' . $name,
      'callback'  => '\App\Controllers\Report::callback',
      'param'     => $param
    ]);

    if (!$insertId) {
      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(200, ['message' => 'Job report has been created.']);
  }

  // Called by service.
  public static function job_dailyPerformance(string $response = null)
  {
    if (!isCLI()) {
      self::response(400, ['message' => 'Bad request']);
    }

    $param = getJSON($response);

    $opt = [
      'period' => ($param->period ?? date('Y-m')) // Default current year and month.
    ];

    $ddGrid = [
      ['F', 'G', 'H'], ['I', 'J', 'K'], ['L', 'M', 'N'], ['O', 'P', 'Q'], ['R', 'S', 'T'],
      ['U', 'V', 'W'], ['X', 'Y', 'Z'], ['AA', 'AB', 'AC'], ['AD', 'AE', 'AF'], ['AG', 'AH', 'AI'],
      ['AJ', 'AK', 'AL'], ['AM', 'AN', 'AO'], ['AP', 'AQ', 'AR'], ['AS', 'AT', 'AU'], ['AV', 'AW', 'AX'],
      ['AY', 'AZ', 'BA'], ['BB', 'BC', 'BD'], ['BE', 'BF', 'BG'], ['BH', 'BI', 'BJ'], ['BK', 'BL', 'BM'],
      ['BN', 'BO', 'BP'], ['BQ', 'BR', 'BS'], ['BT', 'BU', 'BV'], ['BW', 'BX', 'BY'], ['BZ', 'CA', 'CB'],
      ['CC', 'CD', 'CE'], ['CF', 'CG', 'CH'], ['CI', 'CJ', 'CK'], ['CL', 'CM', 'CN'], ['CO', 'CP', 'CQ'],
      ['CR', 'CS', 'CT']
    ];

    $dailyPerfData = getDailyPerformanceReport($opt);

    $sheet = new Spreadsheet();
    $sheet->loadFile(FCPATH . 'files/templates/DailyPerformance_Report.xlsx');

    $sheet->setTitle('Period ' . $param->period);

    $r1 = 3; // 3rd row.

    foreach ($dailyPerfData as $dp) {
      $sheet->setCellValue('A' . $r1, $dp['biller']);
      $sheet->setCellValue('B' . $r1, $dp['target']);
      $sheet->setCellValue('C' . $r1, $dp['revenue']);
      $sheet->setCellValue('D' . $r1, $dp['avg_revenue']);
      $sheet->setCellValue('E' . $r1, $dp['forecast']);

      $r2 = 0;
      foreach ($dp['daily_data'] as $dd) {
        $sheet->setCellValue($ddGrid[$r2][0] . $r1, $dd['revenue']);
        $sheet->setCellValue($ddGrid[$r2][1] . $r1, $dd['stock_value']);
        $sheet->setCellValue($ddGrid[$r2][2] . $r1, $dd['piutang']);

        $r2++;
      }

      $r1++;
    }

    $last = $r1 - 1;

    $sheet->setCellValue('A' . $r1, 'GRAND TOTAL');
    $sheet->setCellValue('B' . $r1, "=SUM(B3:B{$last})");
    $sheet->setCellValue('C' . $r1, "=SUM(C3:C{$last})");
    $sheet->setCellValue('D' . $r1, "=SUM(D3:D{$last})");
    $sheet->setCellValue('E' . $r1, "=SUM(E3:E{$last})");

    $sheet->setBold('A' . $r1);

    return $sheet->export('PrintERP-DailyPerformance-' . date('Ymd_His'));
  }

  // Called by service.
  public static function job_getPayments(string $response = null)
  {
    if (!isCLI()) {
      self::response(400, ['message' => 'Bad request']);
    }

    $param = getJSON($response);

    $q = DB::table('payments')
      ->select("payments.id, payments.date, payments.reference_date, payments.reference,
        creator.fullname AS creator_name, biller.name AS biller_name,
        customers.name AS customer_name,
        (CASE
          WHEN banks.number IS NULL THEN banks.name
          WHEN banks.number IS NOT NULL THEN CONCAT(banks.name, ' (', banks.number, ')')
        END) bank_name, payments.method, payments.amount, payments.type, payments.note,
        payments.created_at, payments.attachment")
      ->join('banks', 'banks.id = payments.bank_id', 'left')
      ->join('biller', 'biller.id = payments.biller_id', 'left')
      ->join('sales', 'sales.id = payments.sale_id', 'left')
      ->join('customers', 'customers.id = sales.customer_id', 'left')
      ->join('users creator', 'creator.id = payments.created_by', 'left');

    if (!empty($param->bank)) {
      $q->whereIn('payments.bank_id', $param->bank);
    }

    if (!empty($param->biller)) {
      $q->whereIn('payments.biller_id', $param->biller);
    }

    if (!empty($param->created_by)) {
      $q->whereIn('payments.created_by', $param->created_by);
    }

    if (!empty($param->customer)) {
      $q->whereIn('customers.id', $param->customer);
    }

    if (!empty($param->status)) {
      $q->whereIn('payments.status', $param->status);
    }

    if (!empty($param->start_date)) {
      $q->where("payments.date >= '{$param->start_date} 00:00:00'");
    }

    if (!empty($param->end_date)) {
      $q->where("payments.date <= '{$param->end_date} 23:59:59'");
    }

    $sheet = new Spreadsheet();
    $sheet->loadFile(FCPATH . 'files/templates/Payment_Report.xlsx');
    $sheet->setTitle('Payment Report');

    $r = 2;

    foreach ($q->get() as $payment) {

      $sheet->setCellValue('A' . $r, $payment->date);
      $sheet->setCellValue('B' . $r, $payment->reference_date);
      $sheet->setCellValue('C' . $r, $payment->reference);
      $sheet->setCellValue('D' . $r, $payment->creator_name);
      $sheet->setCellValue('E' . $r, $payment->biller_name);
      $sheet->setCellValue('F' . $r, $payment->customer_name);
      $sheet->setCellValue('G' . $r, $payment->bank_name);
      $sheet->setCellValue('H' . $r, $payment->method);
      $sheet->setCellValue('I' . $r, $payment->amount);
      $sheet->setCellValue('J' . $r, $payment->type);
      $sheet->setCellValue('K' . $r, html2Note($payment->note));
      $sheet->setCellValue('L' . $r, $payment->created_at);

      if ($payment->attachment) {
        $sheet->setCellValue('M' . $r, lang('App.view'));
        $sheet->setUrl('M' . $r, 'https://erp.indoprinting.co.id/attachment/' . $payment->attachment);
      }

      $r++;
    }

    return $sheet->export('PrintERP-PaymentReport-' . date('Ymd_His'));
  }

  public function payment()
  {
    checkPermission('Report.Payment');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.report'), 'slug' => 'report', 'url' => '#'],
        ['name' => lang('App.payment'), 'slug' => 'payment', 'url' => '#']
      ],
      'content' => 'Report/Payment/index',
      'title' => lang('App.payment')
    ];

    return $this->buildPage($this->data);
  }
}
