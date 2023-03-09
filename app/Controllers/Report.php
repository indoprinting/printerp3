<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Spreadsheet;
use App\Models\Notification;

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


  public static function callback($response = null)
  {
    Notification::add(['note' => 'Report has been created: ' . $response]);
  }

  public static function excel()
  {
    // sleep(5);
    return 'https://erp.indoprinting.co.id/report/main.xlsx';
  }
}
