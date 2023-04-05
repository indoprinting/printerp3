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
    checkPermission('Payment.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.finance'), 'slug' => 'finance', 'url' => '#'],
        ['name' => lang('App.payment'), 'slug' => 'payment', 'url' => '#']
      ],
      'content' => 'Payment/index',
      'title' => lang('App.payment')
    ];

    return $this->buildPage($this->data);
  }

  public function getModalPayments()
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
        biller.name, payments.amount, payments.type, creator.fullname, payments.attachment")
      ->join('banks', 'banks.id = payments.bank_id', 'left')
      ->join('biller', 'biller.id = payments.biller_id', 'left')
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

  public function getPayments()
  {
    checkPermission('Report.Payment');

    $accountNo  = getPost('account_no');
    $bankId     = getPost('bank_id');
    $expenseId  = getPost('expense_id');
    $incomeId   = getPost('income_id');
    $mutationId = getPost('mutation_id');
    $saleId     = getPost('sale_id');

    $startDate  = getPost('start_date');
    $endDate    = getPost('end_date');

    $dt = new DataTables('payments');
    $dt->select("payments.id, payments.date, payments.reference_date, payments.reference,
        creator.fullname AS creator_name, biller.name AS biller_name,
        customers.name AS customer_name,
        (CASE
          WHEN banks.number IS NULL THEN banks.name
          WHEN banks.number IS NOT NULL THEN CONCAT(banks.name, ' (', banks.number, ')')
        END) bank_account, payments.method, payments.amount, payments.type, payments.note,
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
        $data['expense_id'] = $inv->id;
        $data['type']       = 'sent';

        $this->data['inv']    = $inv;
        $this->data['amount'] = $inv->amount;

        if ($inv->status != 'approved') {
          $this->response(403, ['message' => 'Expense is not approved.']);
        }

        if ($inv->payment_status == 'paid') {
          $this->response(400, ['message' => 'Expense is already paid.']);
        }

        break;
        // case 'income':
        // $inv = Income::getRow(['id' => $id]);
        // $modeLang = lang('App.income');
        // $data['income']       = $inv->reference;
        // $data['income_id']    = $inv->id;
        // $data['type']         = 'received';
        // $this->data['amount'] = $inv->amount;
        // $this->data['biller'] = $inv->biller;
        // $this->data['bank']   = $inv->bank;
        // break;
        // case 'mutation':
        // $inv = BankMutation::getRow(['id' => $id]);
        // $modeLang = lang('App.bankmutation');
        // $data['mutation']     = $inv->reference;
        // $data['mutation_id']  = $inv->id;
        // break;
      case 'purchase': // NOT IMPLEMENTED
        // $inv = ProductPurchase::getRow(['id' => $id]);
        // $modeLang = lang('App.productpurchase');
        // $data['purchase']     = $inv->reference;
        // $data['purchase_id']  = $inv->id;
        // $data['type']         = 'sent';
        // $this->data['amount'] = ($inv->grand_total - $inv->paid - $inv->discount);
        // $this->data['bank']   = $inv->bank;
        break;
      case 'sale':
        $inv = Sale::getRow(['id' => $id]);
        $modeLang = lang('App.sale');
        $tax = ($inv->grand_total * 0.01 * $inv->tax);
        $data['sale_id']    = $inv->id;
        $data['biller_id']  = $inv->biller_id;
        $data['type']       = 'received';

        $this->data['inv']    = $inv;
        $this->data['amount'] = $inv->grand_total;
        break;
      case 'transfer': // NOT IMPLEMENTED
        // $inv = ProductTransfer::getRow(['id' => $id]);
        // $modeLang = lang('App.producttransfer');
        // $data['transfer']     = $inv->reference;
        // $data['transfer_id']  = $inv->id;
        // $data['type']         = 'sent';
        // $this->data['amount'] = ($inv->grand_total - $inv->paid);
        // $this->data['bank']   = $inv->bank;
        break;
      default:
        $modeLang = '';
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $data['amount']         = filterDecimal(getPost('amount'));
      $data['date']           = dateTimePHP(getPost('date'));
      $data['reference']      = $inv->reference;
      $data['reference_date'] = $inv->date;
      $data['bank_id']        = getPost('bank');
      $data['method']         = getPost('method'); // Cash / EDC / Transfer
      $data['note']           = getPost('note');

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

      $nonValidation = (isset($data['expense_id']) || isset($data['purchase_id']) || isset($data['transfer_id']));

      if ($skipValidation || $nonValidation || $data['method'] != 'Transfer') {
        $res = PaymentModel::add($data);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }
      } else { // Use payment validation. (Sale only)
        $res = PaymentValidation::add([
          'sale_id'     => $inv->id,
          'biller_id'   => $data['biller_id'],
          'amount'      => $data['amount'],
          'attachment'  => ($data['attachment'] ?? NULL)
        ]);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }
      }

      if (isset($data['expense_id'])) {
        Expense::update((int)$inv->id, ['payment_status' => 'paid']);
      }

      if (isset($data['sale_id'])) {
        Sale::sync(['id' => $inv->id]);
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

    // Expense, Sale, Purchase, Transfer
    $mode = ucfirst(strtolower($mode));

    $this->response(200, ['content' => view("Payment/{$mode}/add", $this->data)]);
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
        $res = Expense::update((int)$payment->expense_id, ['payment_date' => null, 'payment_status' => 'pending']);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }
      } else if (!empty($payment->income)) {
      } else if (!empty($payment->mutation)) {
      } else if (!empty($payment->purchase)) {
      } else if (!empty($payment->sale)) {
        Sale::sync(['id' => $payment->sale_id]);
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

    if ($payment->expense_id) {
      $this->response(400, ['message' => 'Edit from Expense']);
    } else if ($payment->income_id) {
      $this->response(400, ['message' => 'Edit from Income']);
    } else if ($payment->mutation_id) {
      $this->response(400, ['message' => 'Edit from Bank Mutation']);
    } else if ($payment->purchase_id) {
    } else if ($payment->sale_id) {
      $inv = Sale::getRow(['id' => $payment->sale_id]);
      $this->data['modeLang'] = lang('App.invoice');
    } else if ($payment->transfer_id) {
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $data['amount']         = filterDecimal(getPost('amount'));
      $data['date']           = dateTimePHP(getPost('date'));
      $data['reference']      = $inv->reference;
      $data['reference_date'] = $inv->date;
      $data['bank_id']        = getPost('bank');
      $data['biller_id']      = getPost('biller');
      $data['method']         = getPost('method'); // Cash / EDC / Transfer
      $data['note']           = getPost('note');

      DB::transStart();

      $data = $this->useAttachment($data);

      $res = PaymentModel::update((int)$payment->id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        if ($payment->sale_id) {
          Sale::sync(['id' => $inv->id]);
        }

        $this->response(200, ['message' => 'Payment has been updated.']);
      }

      $this->response(400, ['message' => 'Failed to update payment.']);
    }

    $this->data['payment']  = $payment;
    $this->data['title']    = lang('App.editpayment');

    $this->response(200, ['content' => view('Payment/edit', $this->data)]);
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
