<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{
  Bank,
  BankMutation,
  DB,
  Expense,
  Income,
  Payment as PaymentModel,
  ProductPurchase,
  ProductTransfer,
  Sale
};

class Payment extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getPayments()
  {
    checkPermission('Payment.View');

    $accountNo  = getPost('account_no');
    $bankId     = getPost('bank_id');
    $expenseId  = getPost('expense_id');
    $incomeId   = getPost('income_id');
    $mutationId = getPost('mutation_id');
    $saleId     = getPost('sale_id');

    $startDate  = getPost('start_date');
    $endDate    = getPost('end_date');

    $dt = new DataTables('payments');
    $dt->select("payments.id, payments.date, payments.reference,
        (CASE
          WHEN banks.number IS NULL THEN banks.name
          WHEN banks.number IS NOT NULL THEN CONCAT(banks.name, ' (', banks.number, ')')
        END) bank_name,
        biller.name, payments.amount, payments.type, payments.attachment")
      ->join('banks', 'banks.id = payments.bank_id', 'left')
      ->join('biller', 'biller.id = payments.biller_id', 'left')
      ->editColumn('amount', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount']) . '</div>';
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->editColumn('type', function ($data) {
        return renderStatus($data['type']);
      });

    if ($accountNo) {
      $dt->where('banks.number', $accountNo);
    }

    if ($bankId) {
      $dt->where('payments.bank_id', $bankId);
    }

    if ($expenseId) {
      $dt->where('payments.expense_id', $expenseId);
    }

    if ($incomeId) {
      $dt->where('payments.income_id', $incomeId);
    }

    if ($mutationId) {
      $dt->where('payments.mutation_id', $mutationId);
    }

    if ($saleId) {
      $dt->where('payments.sale_id', $saleId);
    }

    if ($startDate) {
      $dt->where("payments.date >= '{$startDate} 00:00:00'");
    }

    if ($endDate) {
      $dt->where("payments.date <= '{$endDate} 23:59:59'");
    }

    $dt->generate();
  }

  /**
   * Add payment
   * @param null|string $mode Payment mode (expense, income, purchase, sale, transfer)
   * @param null|int $id Mode ID.
   */
  public function add(string $mode = NULL, int $id = NULL)
  {
    if (!$mode) {
      $this->response(400, ['message' => 'Payment mode is empty']);
    }

    if (!$id) {
      $this->response(400, ['message' => 'ID is empty']);
    }

    $data = [];

    switch ($mode) {
      case 'expense':
        $inv = Expense::getRow(['id' => $id]);
        $modeLang = lang('App.expense');
        $data['expense']      = $inv->reference;
        $data['expense_id']   = $inv->id;
        $data['type']         = 'sent';
        $this->data['amount'] = $inv->amount;
        $this->data['biller'] = $inv->biller;
        $this->data['bank']   = $inv->bank;

        if ($inv->status != 'approved') {
          $this->response(403, ['message' => 'Expense is not approved.']);
        }

        if ($inv->payment_status == 'paid') {
          $this->response(400, ['message' => 'Expense is already paid.']);
        }
        break;
      case 'income':
        $inv = Income::getRow(['id' => $id]);
        $modeLang = lang('App.income');
        $data['income']       = $inv->reference;
        $data['income_id']    = $inv->id;
        $data['type']         = 'received';
        $this->data['amount'] = $inv->amount;
        $this->data['biller'] = $inv->biller;
        $this->data['bank']   = $inv->bank;
        break;
        // case 'mutation':
        //   $inv = BankMutation::getRow(['id' => $id]);
        //   $modeLang = lang('App.bankmutation');
        //   $data['mutation']     = $inv->reference;
        //   $data['mutation_id']  = $inv->id;
        //   break;
      case 'purchase':
        $inv = ProductPurchase::getRow(['id' => $id]);
        $modeLang = lang('App.productpurchase');
        $data['purchase']     = $inv->reference;
        $data['purchase_id']  = $inv->id;
        $data['type']         = 'sent';
        $this->data['amount'] = ($inv->grand_total - $inv->paid - $inv->discount);
        $this->data['biller'] = $inv->biller;
        $this->data['bank']   = $inv->bank;
        break;
      case 'sale':
        $inv = Sale::getRow(['id' => $id]);
        $modeLang = lang('App.sale');
        $data['sale']         = $inv->reference;
        $data['sale_id']      = $inv->id;
        $data['type']         = 'received';
        $this->data['amount'] = ($inv->grand_total - $inv->paid - $inv->discount);
        $this->data['biller'] = $inv->biller;
        $this->data['bank']   = $inv->bank;
        break;
      case 'transfer':
        $inv = ProductTransfer::getRow(['id' => $id]);
        $modeLang = lang('App.producttransfer');
        $data['transfer']     = $inv->reference;
        $data['transfer_id']  = $inv->id;
        $this->data['amount'] = ($inv->grand_total - $inv->paid);
        $this->data['biller'] = $inv->biller;
        $this->data['bank']   = $inv->bank;
        break;
      default:
        $modeLang = '';
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $data['amount']         = filterDecimal(getPost('amount'));
      $data['date']           = dateTimeJS(getPost('date'));
      $data['reference']      = $inv->reference;
      $data['reference_date'] = $inv->date;
      $data['bank']           = getPost('bank');
      $data['biller']         = getPost('biller');
      $data['method']         = getPost('method') ?? 'Cash'; // Cash / Transfer
      $data['note']           = getPost('note');
      $data['type']           = 'sent';

      if (empty($data['method'])) {
        $this->response(400, ['message' => 'Please select payment method.']);
      }

      if (empty($data['note'])) {
        $data['note'] = $inv->note;
      }

      DB::transStart();

      $data = $this->useAttachment($data, $inv->attachment);

      if (!PaymentModel::add($data)) {
        DB::transComplete();
        $this->response(400, ['message' => getLastError()]);
      }

      if (isset($data['expense'])) {
        Expense::update((int)$inv->id, ['payment_status' => 'paid']);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Payment has been added.']);
      }

      $this->response(400, ['message' => 'Failed to add payment.']);
    }

    $this->data['id']       = $id;
    $this->data['mode']     = $mode;
    $this->data['modeLang'] = $modeLang;
    $this->data['title']    = lang('App.addpayment');

    $this->response(200, ['content' => view('Payment/add', $this->data)]);
  }

  public function view($mode = NULL, $id = NULL)
  {
    if (!$mode) {
      $this->response(400, ['message' => 'Payment mode is empty']);
    }

    if (!$id) {
      $this->response(400, ['message' => 'ID is empty']);
    }

    $data = [];

    switch ($mode) {
      case 'accountno':
        $bank = Bank::getRow(['number' => $id]);
        $data['account_no'] = $id;
        $this->data['modeLang'] = $bank->holder . ($bank->number ? " ($bank->number)" : '');
        break;
      case 'bank':
        $bank = Bank::getRow(['id' => $id]);
        $data['bank_id'] = $id;
        $this->data['modeLang'] = $bank->name . ($bank->number ? " ($bank->number)" : '');
        break;
      case 'expense':
        $data['expense_id'] = $id;
        $this->data['modeLang'] = lang('App.expense');
        break;
      case 'income':
        $data['income_id'] = $id;
        $this->data['modeLang'] = lang('App.income');
        break;
      case 'mutation':
        $data['mutation_id'] = $id;
        $this->data['modeLang'] = lang('App.bankmutation');
        break;
      case 'purchase':
        $data['purchase_id'] = $id;
        $this->data['modeLang'] = lang('App.productpurchase');
        break;
      case 'sale':
        $data['sale_id'] = $id;
        $this->data['modeLang'] = lang('App.sale');
        break;
      case 'transfer':
        $data['transfer_id'] = $id;
        $this->data['modeLang'] = lang('App.producttransfer');
        break;
    }

    $this->data['title']  = lang('App.viewpayment');
    $this->data['params'] = $data;

    $this->response(200, ['content' => view('Payment/view', $this->data)]);
  }
}
