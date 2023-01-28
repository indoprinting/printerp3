<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables, FileUpload};
use App\Models\{Attachment, Bank, BankMutation, BankReconciliation, DB, Expense, Income, Payment, PaymentValidation};

class Finance extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getBanks()
  {
    checkPermission('Bank.View');

    $dt = new DataTables('banks');
    $dt
      ->select("banks.id AS id, banks.code, banks.name, banks.number,
      banks.holder, banks.type, banks.amount, biller.name AS biller_name, banks.bic, banks.active")
      ->join('biller', 'biller.id = banks.biller_id', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/bank/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('finance/bank/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('active', function ($data) {
        $type = ($data['active'] == 1 ? 'success' : 'danger');
        $status = ($data['active'] == 1 ? lang('App.active') : lang('App.inactive'));

        return "<div class=\"badge bg-gradient-{$type}\">{$status}</div>";
      })
      ->editColumn('amount', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount']) . '</div>';
      })
      ->generate();
  }

  public function getExpenses()
  {
    checkPermission('Expense.View');

    $dt = new DataTables('expenses');
    $dt
      ->select("expenses.id AS id, expenses.date, expenses.reference, biller.name AS biller_name,
        expense_categories.name AS category_name, expenses.amount, expenses.note,
        (CASE
          WHEN banks.number IS NOT NULL THEN CONCAT(banks.name, ' (', banks.number, ')')
          ELSE banks.name
        END) AS bank_name,
        creator.fullname, expenses.payment_date, expenses.status, expenses.payment_status,
        suppliers.name AS supplier_name, expenses.created_at, expenses.attachment")
      ->join('banks', 'banks.code = expenses.bank', 'left')
      ->join('biller', 'biller.code = expenses.biller', 'left')
      ->join('expense_categories', 'expense_categories.id = expenses.category_id', 'left')
      ->join('suppliers', 'suppliers.id = expenses.supplier_id', 'left')
      ->join('users creator', 'creator.id = expenses.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/expense/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('finance/expense/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('payment/add/expense/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.addpayment') . '
              </a>
              <a class="dropdown-item" href="' . base_url('payment/view/expense/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalDefault"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.viewpayment') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('finance/expense/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('payment_status', function ($data) {
        return renderStatus($data['payment_status']);
      })
      ->editColumn('amount', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount']) . '</div>';
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->generate();
  }

  public function getIncomes()
  {
    checkPermission('Income.View');

    $dt = new DataTables('incomes');
    $dt
      ->select("incomes.id AS id, incomes.date, incomes.reference, biller.name AS biller_name,
        income_categories.name AS category_name, incomes.amount, incomes.note,
        (CASE
          WHEN banks.number IS NOT NULL THEN CONCAT(banks.name, ' (', banks.number, ')')
          ELSE banks.name
        END) AS bank_name,
        creator.fullname, incomes.created_at, incomes.attachment")
      ->join('banks', 'banks.code = incomes.bank', 'left')
      ->join('biller', 'biller.code = incomes.biller', 'left')
      ->join('income_categories', 'income_categories.id = incomes.category_id', 'left')
      ->join('users creator', 'creator.id = incomes.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/income/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('finance/income/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('payment/view/income/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalDefault"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.viewpayment') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('finance/income/delete/' . $data['id']) . '"
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
      ->generate();
  }

  public function getMutations()
  {
    checkPermission('BankMutation.View');

    $dt = new DataTables('bank_mutations');
    $dt
      ->select("bank_mutations.id AS id, bank_mutations.date, bank_mutations.reference,
        bankfrom.name AS bankfrom_name, bankto.name AS bankto_name,
        bank_mutations.note, bank_mutations.amount, biller.name AS biller_name,
        creator.fullname, bank_mutations.status, bank_mutations.created_at,
        bank_mutations.attachment")
      ->join('banks bankfrom', 'bankfrom.id = bank_mutations.from_bank_id', 'left')
      ->join('banks bankto', 'bankto.id = bank_mutations.to_bank_id', 'left')
      ->join('biller', 'biller.id = bank_mutations.biller_id', 'left')
      ->join('users creator', 'creator.id = bank_mutations.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/mutation/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('finance/mutation/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('amount', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount']) . '</div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->generate();
  }

  public function getPaymentValidations()
  {
    checkPermission('PaymentValidation.View');

    $dt = new DataTables('payment_validations');
    $dt
      ->select("payment_validations.id AS id, payment_validations.created_at,
      payment_validations.reference, creator.fullname, biller.name AS biller_name,
        IF(
          LENGTH(customers.company),
          CONCAT(customers.name, ' (', customers.company, ')'),
          customers.name
        ) AS customer_name, banks.name AS bank_name, banks.number AS bank_number,
        payment_validations.amount,
        (payment_validations.amount + payment_validations.unique_code) AS total,
        payment_validations.expired_at, payment_validations.transaction_at,
        payment_validations.verified_at, payment_validations.unique,
        payment_validations.note, payment_validations.status,
        payment_validations.attachment")
      ->join('banks', 'banks.code = payment_validations.bank', 'left')
      ->join('biller', 'biller.code = payment_validations.biller', 'left')
      ->join('sales', 'sales.reference = payment_validations.sale', 'left')
      ->join('customers', 'customers.phone = sales.customer', 'left')
      ->join('bank_mutations', 'bank_mutations.reference = payment_validations.mutation', 'left')
      ->join('users creator', 'creator.id = payment_validations.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/validation/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('finance/validation/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('amount', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount']) . '</div>';
      })
      ->editColumn('total', function ($data) {
        return '<div class="float-right">' . formatNumber($data['total']) . '</div>';
      })
      ->editColumn('status', function ($data) {
        return renderStatus($data['status']);
      })
      ->editColumn('attachment', function ($data) {
        return renderAttachment($data['attachment']);
      })
      ->generate();
  }

  public function getReconciliations()
  {
    checkPermission('BankReconciliation.View');

    $dt = new DataTables('bank_reconciliations');
    $dt
      ->select("mb_bank_name, account_no, amount_mb, amount_erp, (amount_mb - amount_erp) AS balance,
        mb_acc_name, erp_acc_name, last_sync_date")
      ->editColumn('amount_mb', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount_mb']) . '</div>';
      })
      ->editColumn('amount_erp', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount_erp']) . '</div>';
      })
      ->editColumn('balance', function ($data) {
        return '<div class="float-right">' . formatNumber($data['balance']) . '</div>';
      })
      ->generate();
  }

  public function bank()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('BankAccount.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.finance'), 'slug' => 'finance', 'url' => '#'],
        ['name' => lang('App.bank'), 'slug' => 'bank', 'url' => '#']
      ],
      'content' => 'Finance/Bank/index',
      'title' => lang('App.bankaccount')
    ];

    return $this->buildPage($this->data);
  }

  protected function bank_add()
  {
    checkPermission('BankAccount.Add');

    if (requestMethod() == 'POST') {
      $data = [
        'biller'  => getPost('biller'),
        'code'    => getPost('code'),
        'name'    => getPost('name'),
        'number'  => getPost('number'),
        'holder'  => getPost('holder'),
        'type'    => getPost('type'),
        'bic'     => getPost('bic'),
        'active'  => (getPost('active') == 1 ? 1 : 0)
      ];

      DB::transStart();

      $insertID = Bank::add($data);

      DB::transComplete();

      if (DB::transStatus()) {
        $bank = Bank::getRow(['id' => $insertID]);

        addActivity("Bank ({$bank->code}) {$bank->name} has been added.", [
          'add' => $bank
        ]);

        $this->response(201, ['message' => 'Bank has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addbankaccount');

    $this->response(200, ['content' => view('Finance/Bank/add', $this->data)]);
  }

  protected function bank_balance($id = NULL)
  {
    $bank = Bank::select('*')->where('id', $id)->orWhere('code', $id)->getRow();

    if ($bank) {
      $this->response(200, ['data' => $bank->amount]);
    }

    $this->response(404, ['message' => 'Bank not found.']);
  }

  protected function bank_delete($bankId = NULL)
  {
    checkPermission('BankAccount.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $bank     = Bank::getRow(['id' => $bankId]);
      $payments = Payment::get(['bank_id' => $bankId]);

      if (!$bank) {
        $this->response(404, ['message' => 'Bank is not found.']);
      }

      DB::transStart();

      Bank::delete(['id' => $bankId]);
      Payment::delete(['bank_id' => $bankId]);

      DB::transComplete();

      if (DB::transStatus()) {
        addActivity("Bank ({$bank->code}) {$bank->name} has been deleted.", [
          'delete' => $bank
        ]);

        foreach ($payments as $payment) {
          addActivity("Payment {$payment->reference} has been deleted.", [
            'delete' => $payment
          ]);
        }

        $this->response(200, ['message' => 'Bank has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete bank.']);
  }

  protected function bank_edit($id = NULL)
  {
    checkPermission('BankAccount.Edit');

    $bank = Bank::getRow(['id' => $id]);

    if (!$bank) $this->response(404, ['message' => 'Bank is not found.']);

    if (requestMethod() == 'POST') {
      $data = [
        'biller'  => getPost('biller'),
        'code'    => getPost('code'),
        'name'    => getPost('name'),
        'number'  => getPost('number'),
        'holder'  => getPost('holder'),
        'type'    => getPost('type'),
        'bic'     => getPost('bic'),
        'active'  => (getPost('active') == 1 ? 1 : 0)
      ];

      DB::transStart();

      Bank::update((int)$id, $data);

      DB::transComplete();

      if (DB::transStatus()) {
        $bankNew = Bank::getRow(['id' => $id]);

        addActivity("Bank ({$bank->code}) {$bank->name} has been updated.", [
          'edit' => [
            'new' => $bankNew,
            'old' => $bank
          ]
        ]);

        $this->response(200, ['message' => sprintf(lang('Msg.bankEditOK'), $bank->name)]);
      }

      $this->response(400, ['message' => sprintf(lang('Msg.bankEditNO'), $bank->name)]);
    }

    $this->data['bank'] = $bank;
    $this->data['title'] = lang('App.editbankaccount');

    $this->response(200, ['content' => view('Finance/Bank/edit', $this->data)]);
  }

  public function expense()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Expense.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.finance'), 'slug' => 'finance', 'url' => '#'],
        ['name' => lang('App.expense'), 'slug' => 'expense', 'url' => '#']
      ],
      'content' => 'Finance/Expense/index',
      'title' => lang('App.expense')
    ];

    return $this->buildPage($this->data);
  }

  protected function expense_add()
  {
    checkPermission('Expense.Add');

    if (requestMethod() == 'POST' && isAJAX()) {
      $data = [
        'date'        => dateTimeJS(getPost('date')),
        'bank'        => getPost('bank'),
        'biller'      => getPost('biller'),
        'category'    => getPost('category'),
        'supplier'    => getPost('supplier'),
        'amount'      => filterDecimal(getPost('amount')),
        'note'        => stripTags(getPost('note'))
      ];

      if (empty($data['biller'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($data['category'])) {
        $this->response(400, ['message' => 'Category is required.']);
      }

      if (empty($data['bank'])) {
        $this->response(400, ['message' => 'Bank is required.']);
      }

      if (empty($data['amount'])) {
        $this->response(400, ['message' => 'Amount is required.']);
      }

      DB::transStart();

      $upload = new FileUpload();

      if ($upload->has('attachment')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.attachmentExceed')]);
        }

        $data['attachment'] = $upload->store();
      }

      $insertID = Expense::add($data);

      DB::transComplete();

      if (DB::transStatus()) {
        $expense = Expense::getRow(['id' => $insertID]);

        addActivity("Expense {$expense->reference} has been added.", [
          'add' => $expense
        ]);

        $this->response(201, ['message' => 'Expense has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addexpense');

    $this->response(200, ['content' => view('Finance/Expense/add', $this->data)]);
  }

  protected function expense_approve($id = NULL)
  {
    checkPermission('Expense.Approval');

    $expense = Expense::getRow(['id' => $id]);

    if (!$expense) {
      $this->response(404, ['message' => 'Expense is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $data = [
        'approved_at' => date('Y-m-d H:i:s'),
        'approved_by' => session('login')->user_id
      ];

      if ($expense->status == 'need_approval') {
        $data['status'] = 'approved';
      } else {
        $data['status'] = 'need_approval';
        $data['approved_at'] = NULL;
        $data['approved_by'] = NULL;
      }

      DB::transStart();

      Expense::update((int)$id, $data);

      DB::transComplete();

      if (DB::transStatus()) {
        $newExpense = Expense::getRow(['id' => $id]);

        if ($data['status'] == 'approved') {
          addActivity("Expense {$expense->reference} has been approved.", [
            'approve' => $newExpense
          ]);

          $this->response(200, ['message' => 'Expense has been approved.']);
        } else {
          addActivity("Expense {$expense->reference} has been disapproved.", [
            'disapprove' => $newExpense
          ]);

          $this->response(200, ['message' => 'Expense has been disapproved.']);
        }
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to approve expense.']);
  }

  protected function expense_delete($id = NULL)
  {
    checkPermission('Expense.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $expense    = Expense::getRow(['id' => $id]);
      $attachment = Attachment::getRow(['hashname' => $expense->attachment]);
      $payment    = Payment::getRow(['expense_id' => $expense->id]);

      if (!$expense) {
        $this->response(404, ['message' => 'Expense is not found.']);
      }

      DB::transStart();

      Attachment::delete(['hashname' => $expense->attachment]);
      Expense::delete(['id' => $id]);
      Payment::delete(['expense_id' => $expense->id]);

      DB::transComplete();

      if (DB::transStatus()) {
        addActivity("Expense {$expense->reference} has been deleted.", [
          'delete' => $expense
        ]);

        if ($attachment) {
          addActivity("Attachment {$attachment->filename} has been deleted.", [
            'delete' => $attachment
          ]);
        }

        if ($payment) {
          addActivity("Payment {$payment->reference} has been deleted.", [
            'delete' => $payment
          ]);
        }

        $this->response(200, ['message' => 'Expense has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete expense.']);
  }

  protected function expense_edit($id = NULL)
  {
    checkPermission('Expense.Edit');

    $expense  = Expense::getRow(['id' => $id]);
    $payment  = Payment::getRow(['expense_id' => $id]);

    if (!$expense) {
      $this->response(404, ['message' => 'Expense is not found.']);
    }

    if (requestMethod() == 'POST') {
      $data = [
        'date'      => dateTimeJS(getPost('date')),
        'bank'      => getPost('bank'),
        'biller'    => getPost('biller'),
        'category'  => getPost('category'),
        'supplier'  => getPost('supplier'),
        'amount'    => filterDecimal(getPost('amount')),
        'note'      => stripTags(getPost('note'))
      ];

      if (empty($data['biller'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($data['category'])) {
        $this->response(400, ['message' => 'Category is required.']);
      }

      if (empty($data['bank'])) {
        $this->response(400, ['message' => 'Bank is required.']);
      }

      if (empty($data['amount'])) {
        $this->response(400, ['message' => 'Amount is required.']);
      }

      DB::transStart();

      $upload = new FileUpload();

      if ($upload->has('attachment')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.attachmentExceed')]);
        }

        $data['attachment'] = $upload->store(NULL, $expense->attachment);
      }

      Expense::update((int)$id, $data);

      if ($payment) {
        $paymentData = [
          'bank'    => $data['bank'],
          'biller'  => $data['biller'],
          'amount'  => $data['amount'],
          'note'    => $data['note']
        ];

        if (isset($data['attachment'])) {
          $paymentData['attachment'] = $data['attachment'];
        }

        Payment::update((int)$payment->id, $paymentData);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        addActivity("Expense {$expense->reference} has been updated.", [
          'edit' => [
            'old' => $expense,
            'new' => $data
          ]
        ]);

        $this->response(201, ['message' => 'Expense has been updated.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['expense']  = $expense;
    $this->data['title']    = lang('App.editexpense');

    $this->response(200, ['content' => view('Finance/Expense/edit', $this->data)]);
  }

  protected function expense_view($id = NULL)
  {
    checkPermission('Expense.View');

    $expense = Expense::getRow(['id' => $id]);

    if (!$expense) {
      $this->response(404, ['message' => 'Expense is not found.']);
    }

    $this->data['expense']  = $expense;
    $this->data['title']    = lang('App.viewexpense');

    $this->response(200, ['content' => view('Finance/Expense/view', $this->data)]);
  }

  public function income()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('Income.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.finance'), 'slug' => 'finance', 'url' => '#'],
        ['name' => lang('App.income'), 'slug' => 'income', 'url' => '#']
      ],
      'content' => 'Finance/Income/index',
      'title' => lang('App.income')
    ];

    return $this->buildPage($this->data);
  }

  protected function income_add()
  {
    checkPermission('Income.Add');

    if (requestMethod() == 'POST') {
      $data = [
        'date'        => dateTimeJS(getPost('date')),
        'bank'        => getPost('bank'),
        'biller'      => getPost('biller'),
        'category'    => getPost('category'),
        'amount'      => filterDecimal(getPost('amount')),
        'note'        => stripTags(getPost('note'))
      ];

      if (empty($data['biller'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($data['category'])) {
        $this->response(400, ['message' => 'Category is required.']);
      }

      if (empty($data['bank'])) {
        $this->response(400, ['message' => 'Bank is required.']);
      }

      if (empty($data['amount'])) {
        $this->response(400, ['message' => 'Amount is required.']);
      }

      DB::transStart();

      $upload = new FileUpload();

      if ($upload->has('attachment')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.attachmentExceed')]);
        }

        $data['attachment'] = $upload->store();
      }

      $id = Income::add($data);

      if ($id) {
        $income = Income::getRow(['id' => $id]);

        $paymentData = [
          'date'    => $income->date,
          'income'  => $income->reference,
          'bank'    => $income->bank,
          'biller'  => $income->biller,
          'amount'  => $income->amount,
          'type'    => 'received'
        ];

        if (isset($data['attachment'])) {
          $paymentData['attachment'] = $data['attachment'];
        }

        Payment::add($paymentData);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Income has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addincome');

    $this->response(200, ['content' => view('Finance/Income/add', $this->data)]);
  }

  protected function income_delete($id = NULL)
  {
    checkPermission('Income.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $income = Income::getRow(['id' => $id]);

      if (!$income) {
        $this->response(404, ['message' => 'Income is not found.']);
      }

      DB::transStart();
      Attachment::delete(['hashname' => $income->attachment]);
      Income::delete(['id' => $id]);
      Payment::delete(['income_id' => $income->id]);
      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Income has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete income.']);
  }

  protected function income_edit($id = NULL)
  {
    checkPermission('Income.Edit');

    $income = Income::getRow(['id' => $id]);

    if (!$income) {
      $this->response(404, ['message' => 'Income is not found.']);
    }

    if (requestMethod() == 'POST') {
      $data = [
        'date'        => dateTimeJS(getPost('date')),
        'bank'        => getPost('bank'),
        'biller'      => getPost('biller'),
        'category'    => getPost('category'),
        'amount'      => filterDecimal(getPost('amount')),
        'note'        => stripTags(getPost('note'))
      ];

      if (empty($data['biller'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($data['category'])) {
        $this->response(400, ['message' => 'Category is required.']);
      }

      if (empty($data['bank'])) {
        $this->response(400, ['message' => 'Bank is required.']);
      }

      if (empty($data['amount'])) {
        $this->response(400, ['message' => 'Amount is required.']);
      }

      DB::transStart();

      $upload = new FileUpload();

      if ($upload->has('attachment')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.attachmentExceed')]);
        }

        $data['attachment'] = $upload->store(NULL, $income->attachment);
      }

      Income::update((int)$id, $data);

      DB::transComplete();

      if (DB::transStatus()) {
        $payment = Payment::getRow(['income_id' => $id]);

        if ($payment) {
          $paymentData = [
            'bank'    => $data['bank'],
            'biller'  => $data['biller'],
            'amount'  => $data['amount']
          ];

          if (isset($data['attachment'])) {
            $paymentData['attachment'] = $data['attachment'];
          }

          Payment::update((int)$payment->id, $paymentData);
        }

        $this->response(201, ['message' => 'Income has been updated.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['income'] = $income;
    $this->data['title']  = lang('App.editincome');

    $this->response(200, ['content' => view('Finance/Income/edit', $this->data)]);
  }

  protected function income_view($id = NULL)
  {
    checkPermission('Income.View');

    $income = Income::getRow(['id' => $id]);

    if (!$income) {
      $this->response(404, ['message' => 'Income is not found.']);
    }

    $this->data['income'] = $income;
    $this->data['title']  = lang('App.viewincome');

    $this->response(200, ['content' => view('Finance/Income/view', $this->data)]);
  }

  public function mutation()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('BankMutation.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.finance'), 'slug' => 'finance', 'url' => '#'],
        ['name' => lang('App.mutation'), 'slug' => 'mutation', 'url' => '#']
      ],
      'content' => 'Finance/Mutation/index',
      'title' => lang('App.bankmutation')
    ];

    return $this->buildPage($this->data);
  }

  protected function mutation_add()
  {
    checkPermission('BankMutation.Add');

    if (requestMethod() == 'POST') {
      $mutationData = [
        'date'      => dateTimeJS(getPost('date')),
        'amount'    => filterDecimal(getPost('amount')),
        'biller'    => getPost('biller'),
        'bankfrom'  => getPost('bankfrom'),
        'bankto'    => getPost('bankto'),
        'note'      => stripTags(getPost('note'))
      ];

      if (empty($mutationData['amount']) || $mutationData['amount'] < 1) {
        $this->response(400, ['message' => 'Amount required.']);
      }

      if (empty($mutationData['biller'])) {
        $this->response(400, ['message' => 'Biller required.']);
      }

      if (empty($mutationData['bankfrom'])) {
        $this->response(400, ['message' => 'Bank from required.']);
      }

      if (empty($mutationData['bankto'])) {
        $this->response(400, ['message' => 'Bank to required.']);
      }

      DB::transStart();

      $upload = new FileUpload();

      if ($upload->has('attachment')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.attachmentExceed')]);
        }

        $mutationData['attachment'] = $upload->store();
      }

      BankMutation::add($mutationData);

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Bank Mutation has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addbankmutation');

    $this->response(200, ['content' => view('Finance/Mutation/add', $this->data)]);
  }

  protected function mutation_delete($mutationId = NULL)
  {
    checkPermission('BankMutation.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $mutation = BankMutation::getRow(['id' => $mutationId]);

      if (!$mutation) {
        $this->response(404, ['message' => 'Mutation is not found.']);
      }

      DB::transStart();
      Attachment::delete(['hashname' => $mutation->attachment]);
      BankMutation::delete(['id' => $mutation->id]);
      PaymentValidation::delete(['mutation' => $mutation->reference]);
      Payment::delete(['mutation' => $mutation->reference]);
      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Bank mutation has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete bank mutation.']);
  }

  protected function mutation_edit($mutationId = NULL)
  {
    checkPermission('BankMutation.Edit');

    $mutation = BankMutation::getRow(['id' => $mutationId]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Bank Mutation is not found.']);
    }

    if (requestMethod() == 'POST') {
      $mutationData = [
        'date'      => dateTimeJS(getPost('date')),
        'amount'    => filterDecimal(getPost('amount')),
        'biller'    => getPost('biller'),
        'bankfrom'  => getPost('bankfrom'),
        'bankto'    => getPost('bankto'),
        'note'      => stripTags(getPost('note'))
      ];

      if (empty($mutationData['amount']) || $mutationData['amount'] < 1) {
        $this->response(400, ['message' => 'Amount required.']);
      }

      if (empty($mutationData['biller'])) {
        $this->response(400, ['message' => 'Biller required.']);
      }

      if (empty($mutationData['bankfrom'])) {
        $this->response(400, ['message' => 'Bank from required.']);
      }

      if (empty($mutationData['bankto'])) {
        $this->response(400, ['message' => 'Bank to required.']);
      }

      DB::transStart();

      $upload = new FileUpload();

      if ($upload->has('attachment')) {
        if ($upload->getSize('mb') > 2) {
          $this->response(400, ['message' => lang('Msg.attachmentExceed')]);
        }

        $mutationData['attachment'] = $upload->store(NULL, $mutation->attachment);
      }

      // We have to add new payment validation dan last payment (if any) if amount changed.
      if ($mutation->amount != $mutationData['amount']) {
        Payment::delete(['mutation_id' => $mutation->id]);
        $mutationData['status'] = 'waiting_transfer';

        PaymentValidation::add([
          'mutation'    => $mutationData['reference'],
          'amount'      => $mutationData['amount'],
          'biller'      => $mutationData['biller'],
          'attachment'  => ($mutationData['attachment'] ?? NULL)
        ]);
      }

      BankMutation::update((int)$mutationId, $mutationData);

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => sprintf(lang('Msg.mutationEditOK'), $mutation->reference)]);
      }

      $this->response(400, ['message' => sprintf(lang('Msg.mutationEditNO'), $mutation->reference)]);
    }

    $this->data['mutation'] = $mutation;
    $this->data['title'] = lang('App.editbankmutation');

    $this->response(200, ['content' => view('Finance/Mutation/edit', $this->data)]);
  }

  public function reconciliation()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('BankReconciliation.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.finance'), 'slug' => 'finance', 'url' => '#'],
        ['name' => lang('App.bankreconciliation'), 'slug' => 'reconciliation', 'url' => '#']
      ],
      'content' => 'Finance/Reconciliation/index',
      'title' => lang('App.bankreconciliation')
    ];

    return $this->buildPage($this->data);
  }

  protected function reconciliation_sync()
  {
    if (!Bank::sync()) {
      $this->response(400, ['message' => 'Sync Bank amount failed.']);
    }

    if (BankReconciliation::sync()) {
      addActivity('Bank Reconciliation has been synced.');

      $this->response(200, ['message' => 'Bank Reconciliation has been synced successfully.']);
    }

    $this->response(400, ['message' => 'Sync Bank Reconciliation failed.']);
  }

  public function validation()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('PaymentValidation.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.finance'), 'slug' => 'finance', 'url' => '#'],
        ['name' => lang('App.paymentvalidation'), 'slug' => 'validation', 'url' => '#']
      ],
      'content' => 'Finance/Validation/index',
      'title' => lang('App.paymentvalidation')
    ];

    return $this->buildPage($this->data);
  }

  protected function validation_add()
  {
    checkPermission('PaymentValidation.Add');

    $this->response(501, ['message' => 'Not implemented']);

    if (requestMethod() == 'POST') {
      $billerData = [
        'biller'  => getPost('biller'),
        'code'    => getPost('code'),
        'name'    => getPost('name'),
        'number'  => getPost('number'),
        'holder'  => getPost('holder'),
        'type'    => getPost('type'),
        'bic'     => getPost('bic'),
        'active'  => (getPost('active') == 1 ? 1 : 0)
      ];

      if (PaymentValidation::add($billerData)) {
        $this->response(201, ['message' => 'Bank Mutation has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addbankmutation');

    $this->response(200, ['content' => view('Finance/Validation/add', $this->data)]);
  }

  protected function validation_delete($validationId = NULL)
  {
    checkPermission('PaymentValidation.Delete');

    $this->response(501, ['message' => 'Not implemented']);

    if (requestMethod() == 'POST' && isAJAX()) {
      $validation = PaymentValidation::getRow(['id' => $validationId]);

      if (!$validation) {
        $this->response(404, ['message' => 'Payment Validation is not found.']);
      }

      if (PaymentValidation::delete(['id' => $validationId])) {
        $this->response(200, ['message' => 'Payment Validation has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete Payment Validation.']);
  }

  protected function validation_edit($validationId = NULL)
  {
    checkPermission('PaymentValidation.Edit');

    $this->response(501, ['message' => 'Not implemented']);

    $validation = PaymentValidation::getRow(['id' => $validationId]);

    if (!$validation) {
      $this->response(404, ['message' => 'Payment Validation is not found.']);
    }

    if (requestMethod() == 'POST') {
      $validationData = [
        'biller'  => getPost('biller'),
        'code'    => getPost('code'),
        'name'    => getPost('name'),
        'number'  => getPost('number'),
        'holder'  => getPost('holder'),
        'type'    => getPost('type'),
        'bic'     => getPost('bic'),
        'active'  => (getPost('active') == 1 ? 1 : 0)
      ];

      DB::transStart();

      PaymentValidation::update((int)$validationId, $validationData);

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => sprintf(lang('Msg.bankEditOK'), $validation->name)]);
      }

      $this->response(400, ['message' => sprintf(lang('Msg.bankEditNO'), $validation->name)]);
    }

    $this->data['validation'] = $validation;
    $this->data['title'] = lang('App.editpaymentvalidation');

    $this->response(200, ['content' => view('Finance/Validation/edit', $this->data)]);
  }
}
