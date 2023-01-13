<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{PaymentValidation, Sale};

class Api extends BaseController
{
  public function index()
  {
  }

  private function http_get($url, $header = [])
  {
    if (!function_exists('curl_init')) {
      throw new \Exception('CURL is not installed.');
      die();
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    if (!empty($header)) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($res = curl_exec($ch)) {
      return $res;
    } else {
      return curl_error($ch);
    }
  }

  public function v1()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    $this->response(404, ['message' => 'Not Found']);
  }

  public function mutasibank_accounts()
  {
    $data = [];

    $account = $this->http_get('https://mutasi.indoprinting.co.id/api/accounts_list', [
      'Authorization: Bearer tikXCBSpl2JGVr49ILhme7dHfbaQuOPFYNozMEc6'
    ]);

    $acc = json_decode($account);

    if ($acc && $acc->status == TRUE) {
      foreach ($acc->data as $row) {
        $data[] = [
          'id'                => $row->id,
          'account_name'      => $row->account_name,
          'account_no'        => $row->account_number,
          'balance'           => $row->balance,
          'bank'              => $row->bank_name,
          'module'            => $row->module_name,
          'last_bot_activity' => $row->last_run
        ];
      }
    }

    $this->response(200, ['data' => $data]);
  }

  public function mutasibank_accountStatements()
  {
  }

  public function mutasibank_manualValidation()
  {
    $amount    = getPOST('amount');
    $accountNo = getPOST('account_no');
    $invoice   = getPOST('invoice');
    $note      = getPOST('note');
    $trDate    = getPOST('transaction_date');

    $sale = Sale::getRow(['reference' => $invoice]);

    if (!$sale) $this->response(404, ['message' => 'Sale is not valid.']);

    if (empty($trDate)) $this->response(400, ['message' => 'Transaction date is invalid.']);

    $transDate = new \DateTime($trDate);

    $data = (object)[
      'account_number' => $accountNo,
      'data_mutasi' => [
        (object)[
          'transaction_date' => ($transDate ? $transDate->format('Y-m-d H:i:s') : date('Y-m-d H:i:s')),
          'type'             => 'CR',
          'amount'           => filterDecimal($amount),
          'description'      => $note
        ]
      ]
    ];

    $response = json_encode($data);

    $validationOptions = [
      'manual' => TRUE, /* Optional, but required for manual validation. */
      'sale_id' => $sale->id
    ];

    $uploader = new \FileUpload();

    if ($uploader->has('attachment_id')) {
      if ($uploader->getSize('mb') > 2) {
        $this->response(400, ['message' => 'Attachment size is exceed more than 2MB.']);
      }

      $validationOptions['attachment_id'] = $uploader->store();
    }

    if (PaymentValidation::validate($response, $validationOptions)) {
      sendJSON(['error' => 0, 'msg' => 'Payment has been validated successfully.']);
    }
    sendJSON(['error' => 1, 'msg' => 'Failed to validate payment.']);
  }

  protected function v1_mutasibank($mode = NULL)
  {
    if ($mode == 'accounts') {
      $this->mutasibank_accounts();
      die();
    }

    if ($mode == 'accountStatements') {
      $this->mutasibank_accountStatements();
      die();
    }

    if ($mode == 'manualValidation') {
      $this->mutasibank_manualValidation();
      die();
    }

    $response = file_get_contents('php://input');

    if (PaymentValidation::validate($response)) { // Segala pengecekan dan validasi data di sini.
      $this->response(200, ['message' => 'Validated']);
    } else {
      $this->response(406, ['message' => 'Not Validated']);
    }
  }
}
