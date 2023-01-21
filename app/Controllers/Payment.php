<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{BankMutation, DB, Expense, Income, Payment as PaymentModel, ProductPurchase, ProductTransfer, Sale};

class Payment extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  /**
   * Add payment
   * @param null|string $mode Payment mode (expense, income, mutation, purchase, sale, transfer)
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
        $data['expense']        = $inv->reference;
        $data['expense_id']     = $inv->id;
        break;
      case 'income':
        $inv = Income::getRow(['id' => $id]);
        $modeLang = lang('App.income');
        $data['income']     = $inv->reference;
        $data['income_id']  = $inv->id;
        break;
      case 'mutation':
        $inv = BankMutation::getRow(['id' => $id]);
        $modeLang = lang('App.bankmutation');
        $data['mutation']     = $inv->reference;
        $data['mutation_id']  = $inv->id;
        break;
      case 'purchase':
        $inv = ProductPurchase::getRow(['id' => $id]);
        $modeLang = lang('App.productpurchase');
        $data['purchase']     = $inv->reference;
        $data['purchase_id']  = $inv->id;
        break;
      case 'sale':
        $inv = Sale::getRow(['id' => $id]);
        $modeLang = lang('App.sale');
        $data['sale']     = $inv->reference;
        $data['sale_id']  = $inv->id;
        break;
      case 'transfer':
        $inv = ProductTransfer::getRow(['id' => $id]);
        $modeLang = lang('App.producttransfer');
        $data['transfer']     = $inv->reference;
        $data['transfer_id']  = $inv->id;
        break;
      default:
        $modeLang = '';
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $data['amount']         = filterDecimal(getPost('amount'));
      $data['date']           = dateTimeJS('amount');
      $data['reference']      = $inv->reference;
      $data['reference_date'] = $inv->date;
      $data['bank']           = getPost('bank');
      $data['biller']         = getPost('biller');
      $data['method']         = getPost('method'); // Cash / Transfer
      $data['note']           = getPost('note');

      DB::transStart();

      PaymentModel::add($data);

      DB::transComplete();
    }

    $this->data['id']       = $id;
    $this->data['mode']     = $mode;
    $this->data['modeLang'] = $modeLang;
    $this->data['title']    = lang('App.addpayment');

    $this->response(200, ['content' => view('Payment/add', $this->data)]);
  }

  public function view($mode = NULL, $id = NULL)
  {
  }
}
