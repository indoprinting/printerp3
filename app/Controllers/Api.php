<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{
  Customer,
  DB,
  PaymentValidation,
  PriceGroup,
  Product,
  ProductCategory,
  ProductPrice,
  Sale,
  Stock,
  Voucher,
  Warehouse,
  WarehouseProduct
};

class Api extends BaseController
{
  public function index()
  {
    // Do not use authentication checkPermission().
    // checkPermission();
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

  public function v2()
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

  protected function mutasibank_v2()
  {
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

    if ($total = PaymentValidation::validate($response)) { // Segala pengecekan dan validasi data di sini.
      $this->response(200, ['message' => 'Validated', 'data' => ['validated' => $total]]);
    } else {
      $this->response(406, ['message' => 'Not Validated']);
    }
  }

  protected function v1_product($mode = NULL)
  {
    if (requestMethod() == 'POST') {
      if (!$mode) {
        $this->product_add();
      } else if ($mode == 'delete') {
        $this->product_delete();
      }
    }

    $code = getGet('code');
    $cust = getGet('customer'); // id
    $id   = getGet('id');
    $wh   = getGet('warehouse');

    $clause = [];

    if ($code)  $clause['code'] = $code;
    if ($id)    $clause['id']   = $id;

    if (empty($code) && empty($id)) {
      $this->response(400, ['message' => 'Product code or id is required.']);
    }

    $product = Product::getRow($clause);

    if (!$product) {
      $this->response(404, ['message' => 'Product is not found.']);
    }

    if ($product) {
      $pcategory = ProductCategory::getRow(['id' => $product->category_id]);
      $priceGroup = null;

      $data = [
        'code'          => $product->code,
        'name'          => $product->name,
        'cost'          => floatval($product->cost),
        'price'         => floatval($product->price),
        'prices'        => [floatval($product->price)],
        'markon_price'  => floatval($product->markon_price),
        'category'      => $pcategory->code,
        'category_name' => $pcategory->name,
        'iuse_type'     => $product->iuse_type,
        'quantity'      => floatval($product->quantity),
        'ranges'        => getJSON($product->price_ranges_value),
        'type'          => $product->type,
        'warehouses'    => $product->warehouses,
      ];

      if ($warehouse = Warehouse::getRow(['code' => $wh])) {
        $priceGroup = PriceGroup::getRow(['id' => $warehouse->pricegroup]);
      }

      if ($customer = Customer::getRow(['id' => $cust])) {
        $priceGroup = PriceGroup::getRow(['id' => $customer->price_group_id]);
      }

      if ($priceGroup) {
        $productPrice = ProductPrice::getRow(['product_id' => $product->id, 'price_group_id' => $priceGroup->id]);

        if ($productPrice) {
          $data['prices'] = [
            floatval($productPrice->price), floatval($productPrice->price2), floatval($productPrice->price3),
            floatval($productPrice->price4), floatval($productPrice->price5), floatval($productPrice->price6)
          ];
        }
      }

      if ($whp = WarehouseProduct::getRow(['product_id' => $product->id, 'warehouse_code' => $wh])) {
        $data['quantity'] = floatval($whp->quantity);
      }

      $this->response(200, ['data' => $data]);
    }

    $this->response(404, ['message' => 'Product is not found.']);
  }

  protected function product_add()
  {
  }

  protected function product_delete()
  {
  }

  protected function v1_voucher($mode = NULL)
  {
    if (requestMethod() == 'POST') {
      if (!$mode) {
        $this->voucher_add();
      } else if ($mode == 'delete') {
        $this->voucher_delete();
      } else if ($mode == 'use') {
        $this->voucher_use();
      }
    }

    $code = getGet('code');

    if (!$code) {
      $this->response(400, ['message' => 'Voucher code is required.']);
    }

    $voucher = Voucher::getRow(['code' => $code]);

    if ($voucher) {
      $this->response(200, ['data' => [
        'code'        => $voucher->code,
        'name'        => $voucher->name,
        'amount'      => floatval($voucher->amount),
        'quota'       => floatval($voucher->quota),
        'valid_from'  => $voucher->valid_from,
        'valid_to'    => $voucher->valid_to
      ]]);
    }

    $this->response(404, ['message' => 'Voucher is not found.']);
  }

  protected function voucher_add()
  {
    $code       = getPost('code');
    $name       = getPost('name');
    $amount     = getPost('amount');
    $quota      = getPost('quota');
    $validFrom  = getPost('valid_from');
    $validTo    = getPost('valid_to');

    $voucher = Voucher::getRow(['code' => $code]);

    if ($voucher) {
      $this->response(400, ['message' => 'Voucher code is already present.']);
    }

    if (!$code) {
      $this->response(400, ['message' => 'Voucher code is required.']);
    }

    if (!$name) {
      $this->response(400, ['message' => 'Voucher name is required.']);
    }

    if (!$amount) {
      $this->response(400, ['message' => 'Voucher amount is required.']);
    }

    if (!strtotime($validFrom)) {
      $this->response(400, ['message' => 'Voucher valid_from is invalid.']);
    }

    // $this->response(400, ['valid_from' => $validFrom, 'valid_to' => $validTo]);

    if (!strtotime($validTo)) {
      $this->response(400, ['message' => 'Voucher valid_to is invalid.']);
    }

    $data = [
      'code'        => $code,
      'name'        => $name,
      'amount'      => floatval($amount),
      'quota'       => floatval($quota ? $quota : 1),
      'valid_from'  => ($validFrom ? $validFrom : date('Y-m-d H:i:s')),
      'valid_to'    => ($validTo ? $validTo : date('Y-m-d H:i:s', strtotime('+1 day')))
    ];

    DB::transStart();

    Voucher::add($data);

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(201, ['message' => 'Voucher has been created.', 'data' => $data]);
    }

    $this->response(400, ['message' => 'Failed to create voucher.']);
  }

  protected function voucher_delete()
  {
    $code = getPost('code');

    $voucher = Voucher::getRow(['code' => $code]);

    if (!$voucher) {
      $this->response(404, ['message' => 'Voucher is not found.']);
    }

    DB::transStart();

    Voucher::delete(['code' => $code]);

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Voucher has been deleted.']);
    }

    $this->response(400, ['message' => 'Failed to delete voucher']);
  }

  protected function voucher_use()
  {
    $code = getPost('code');
    $invoice = getPost('invoice');

    $voucher  = Voucher::getRow(['code' => $code]);
    $sale     = Sale::getRow(['reference' => $invoice]);

    if (!$voucher) {
      $this->response(404, ['message' => 'Voucher is not found.']);
    }

    if (!$sale) {
      $this->response(404, ['message' => 'Invoice is not found.']);
    }

    if (strtotime($voucher->valid_from) > time()) {
      $this->response(400, ['message' => 'Voucher is too early to be used.']);
    }

    if (strtotime($voucher->valid_to) < time()) {
      $this->response(400, ['message' => 'Voucher has been expired.']);
    }

    if (intval($voucher->quota) == 0) {
      $this->response(400, ['message' => 'Voucher quota has been exceeded']);
    }

    DB::transStart();

    $res = Sale::update((int)$sale->id, ['discount' => $voucher->amount]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    Sale::sync(['id' => $sale->id]);

    $res = Voucher::update((int)$voucher->id, ['quota' => $voucher->quota - 1]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Voucher has been used.']);
    }

    $this->response(400, ['message' => 'Failed to use voucher.']);
  }
}
