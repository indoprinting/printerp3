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
  PaymentValidation,
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
          WHEN banks.number IS null THEN banks.name
          WHEN banks.number IS NOT null THEN CONCAT(banks.name, ' (', banks.number, ')')
        END) bank_name,
        biller.name, payments.amount, payments.type, payments.attachment")
      ->join('banks', 'banks.id = payments.bank_id', 'left')
      ->join('biller', 'biller.id = payments.biller_id', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('payment/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic2"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
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
  public function add(string $mode = null, int $id = null)
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
        checkPermission('Expense.Payment.Add');

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
        // $inv = Income::getRow(['id' => $id]);
        // $modeLang = lang('App.income');
        // $data['income']       = $inv->reference;
        // $data['income_id']    = $inv->id;
        // $data['type']         = 'received';
        // $this->data['amount'] = $inv->amount;
        // $this->data['biller'] = $inv->biller;
        // $this->data['bank']   = $inv->bank;
        break;
      case 'mutation':
        // $inv = BankMutation::getRow(['id' => $id]);
        // $modeLang = lang('App.bankmutation');
        // $data['mutation']     = $inv->reference;
        // $data['mutation_id']  = $inv->id;
        break;
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
      $data['method']         = getPost('method'); // Cash / EDC / Transfer
      $data['note']           = getPost('note');
      $data['type']           = 'sent';

      // Used by Sale. Bank mutation has payment ui itself.
      $skipValidation = (getPost('skip_validation') == 1);

      if (empty($data['method'])) {
        $this->response(400, ['message' => 'Please select payment method.']);
      }

      if (empty($data['note'])) {
        $data['note'] = $inv->note;
      }

      DB::transStart();

      $data = $this->useAttachment($data, $inv->attachment);

      $nonValidation = (isset($data['expense']) || isset($data['purchase']) || isset($data['transfer']));

      if ($skipValidation || $nonValidation) {
        $res = PaymentModel::add($data);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }

        if (isset($data['expense'])) {
          Expense::update((int)$inv->id, ['payment_status' => 'paid']);
        }
      } else { // Use payment validation. (Sale only)
        $res = PaymentValidation::add([
          'mutation'    => $inv->reference,
          'amount'      => $data['amount'],
          'biller'      => $data['biller'],
          'attachment'  => ($data['attachment'] ?? NULL)
        ]);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }

        if (isset($data['sale'])) {
          Sale::sync(['id' => $data['sale_id']]);
        }
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

  public function delete($id = null)
  {
    checkPermission('Payment.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $payment = PaymentModel::getRow(['id' => $id]);

      if (!$payment) {
        $this->response(404, ['message' => 'Payment is not found.']);
      }

      DB::transStart();

      $res = PaymentModel::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      if (!empty($payment->expense)) {
        Expense::update((int)$payment->expense_id, ['payment_date' => null, 'payment_status' => 'pending']);
      } else if (!empty($payment->income)) {
      } else if (!empty($payment->mutation)) {
      } else if (!empty($payment->purchase)) {
      } else if (!empty($payment->sale)) {
      } else if (!empty($payment->transfer)) {
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Payment has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to delete payment.']);
  }

  public function edit($id = null)
  {
    checkPermission('Payment.Edit');

    if (!$id) {
      $this->response(400, ['message' => 'ID is empty']);
    }

    $payment = PaymentModel::getRow(['id' => $id]);

    if (!$payment) {
      $this->response(404, ['message' => 'Payment is not found.']);
    }

    if (!empty($payment->expense)) {
      $this->response(400, ['message' => 'Edit from Expense']);
    } else if (!empty($payment->income)) {
      $this->response(400, ['message' => 'Edit from Income']);
    } else if (!empty($payment->mutation)) {
      $this->response(400, ['message' => 'Edit from Bank Mutation']);
    } else if (!empty($payment->purchase)) {
    } else if (!empty($payment->sale)) {
    } else if (!empty($payment->transfer)) {
    }

    if (requestMethod() == 'POST' && isAJAX()) {
    }

    $this->data['payment']  = $payment;
    $this->data['title']    = lang('editpayment');

    $this->response(200, ['content' => view('Payment/view', $this->data)]);
  }

  public function view($mode = null, $id = null)
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
