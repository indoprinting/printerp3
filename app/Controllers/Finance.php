<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{
  Attachment,
  Bank,
  BankMutation,
  BankReconciliation,
  DB,
  Expense,
  Income,
  Payment,
  PaymentValidation,
  Sale
};

class Finance extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function getBanks()
  {
    checkPermission('BankAccount.View');

    $dt = new DataTables('banks');
    $dt
      ->select("banks.id AS id, banks.code, banks.name, banks.number,
      banks.holder, banks.type, banks.amount, biller.name AS biller_name, banks.bic, banks.active")
      ->join('biller', 'biller.code = banks.biller', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/bank/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('finance/bank/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('payment/view/bank/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.viewpayment') . '
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
      });

    if ($biller = session('login')->biller) {
      $dt->where('banks.biller', $biller);
    }

    $dt->generate();
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
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
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
                data-toggle="modal" data-target="#ModalStatic"
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
      });

    if ($biller = session('login')->biller) {
      $dt->where('expense.biller', $biller);
    }

    $dt->generate();
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
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
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
                data-toggle="modal" data-target="#ModalStatic"
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
      });

    if ($biller = session('login')->biller) {
      $dt->where('incomes.biller', $biller);
    }

    $dt->generate();
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
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/mutation/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('payment/view/mutation/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-money-bill"></i> ' . lang('App.viewpayment') . '
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
      });

    if ($biller = session('login')->biller) {
      $dt->where('bank_mutations.biller', $biller);
    }

    $dt->generate();
  }

  public function getPaymentValidations()
  {
    checkPermission('PaymentValidation.View');

    $dt = new DataTables('payment_validations');
    $dt
      ->select("payment_validations.id AS id, payment_validations.date,
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
        payment_validations.created_at,
        payment_validations.attachment")
      ->join('banks', 'banks.code = payment_validations.bank', 'left')
      ->join('biller', 'biller.code = payment_validations.biller', 'left')
      ->join('sales', 'sales.reference = payment_validations.sale', 'left')
      ->join('customers', 'customers.phone = sales.customer', 'left')
      ->join('bank_mutations', 'bank_mutations.reference = payment_validations.mutation', 'left')
      ->join('users creator', 'creator.id = payment_validations.created_by', 'left')
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
      });

    if ($biller = session('login')->biller) {
      $dt->where('payment_validations.biller', $biller);
    }

    $dt->generate();
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
        $url = base_url('payment/view/accountno/' . $data['account_no']);

        return '<div class="float-right">' .
          "<a href=\"{$url}\" data-toggle=\"modal\" data-target=\"#ModalDefault\" data-modal-class=\"modal-lg modal-dialog-centered modal-dialog-scrollable\">" .
          formatNumber($data['amount_erp']) .
          '</a></div>';
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
        ['name' => lang('App.bankaccount'), 'slug' => 'bank', 'url' => '#']
      ],
      'content' => 'Finance/Bank/index',
      'title' => lang('App.bankaccount')
    ];

    return $this->buildPage($this->data);
  }

  protected function bank_activate($id = NULL)
  {
    $bank = Bank::getRow(['id' => $id]);

    if (!$bank) {
      $this->response(404, ['message' => 'Bank is not found.']);
    }

    if ($bank->active) {
      Bank::update((int)$id, ['active' => 0]);
      $this->response(200, ['message' => 'Bank has been deactivated.']);
    }

    Bank::update((int)$id, ['active' => 1]);
    $this->response(200, ['message' => 'Bank has been activated.']);
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

      if (empty($data['code'])) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($data['name'])) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($data['biller'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      DB::transStart();

      $insertID = Bank::add($data);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $bank = Bank::getRow(['id' => $insertID]);

        addActivity("Add bank {$bank->code}.", [
          'data' => $bank
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
    if ($amount = cache('bank_balance_' . $id)) {
      $this->response(200, ['data' => floatval($amount)]);
    }

    $bank = Bank::select('*')->where('id', $id)->orWhere('code', $id)->getRow();

    if ($bank) {
      Bank::sync((int)$bank->id);
      $bank = Bank::getRow(['id' => $bank->id]);

      cache()->save('bank_balance_' . $id, floatval($bank->amount));

      $this->response(200, ['data' => floatval($bank->amount)]);
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

      $res = Bank::delete(['id' => $bankId]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      $res = Payment::delete(['bank_id' => $bankId]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        addActivity("Delete bank {$bank->code}.", [
          'data'      => $bank,
          'payments'  => $payments
        ]);

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

    if (!$bank) {
      $this->response(404, ['message' => 'Bank is not found.']);
    }

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

      if (empty($data['code'])) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($data['name'])) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($data['biller'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      DB::transStart();

      $res = Bank::update((int)$id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $bankNew = Bank::getRow(['id' => $id]);

        addActivity("Edit bank {$bank->code}.", [
          'data' => [
            'after'   => $bankNew,
            'before'  => $bank
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

  protected function bank_view($id = NULL)
  {
    checkPermission('BankAccount.View');

    $bank = Bank::getRow(['id' => $id]);

    if (!$bank) {
      $this->response(404, ['message' => 'Bank Account is not found.']);
    }

    $this->data['bank']   = $bank;
    $this->data['title']  = lang('App.viewbankaccount');

    $this->response(200, ['content' => view('Finance/Bank/view', $this->data)]);
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

      $data = $this->useAttachment($data);

      $insertID = Expense::add($data);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $expense = Expense::getRow(['id' => $insertID]);
        $attachment = Attachment::getRow(['hashname' => $expense->attachment]);

        if ($attachment) {
          $attachment->data = base64_encode($attachment->data);
        }

        addActivity("Add expense {$expense->reference}.", [
          'data'        => $expense,
          'attachment'  => $attachment
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

      $res = Expense::update((int)$id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $newExpense = Expense::getRow(['id' => $id]);

        if ($data['status'] == 'approved') {
          addActivity("Approve expense {$expense->reference}.", [
            'data' => [
              'after'   => $newExpense,
              'before'  => $expense
            ]
          ]);

          $this->response(200, ['message' => 'Expense has been approved.']);
        } else {
          addActivity("Disapprove expense {$expense->reference}.", [
            'data' => [
              'after'   => $newExpense,
              'before'  => $expense
            ]
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

      $res = Expense::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      Attachment::delete(['hashname' => $expense->attachment]);
      Payment::delete(['expense_id' => $expense->id]);

      DB::transComplete();

      if (DB::transStatus()) {
        if ($attachment) {
          $attachment->data = base64_encode($attachment->data);
        }

        addActivity("Delete expense {$expense->reference}.", [
          'data'        => $expense,
          'attachment'  => $attachment,
          'payment'     => $payment
        ]);

        $this->response(200, ['message' => 'Expense has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete expense.']);
  }

  protected function expense_edit($id = NULL)
  {
    checkPermission('Expense.Edit');

    $expense = Expense::getRow(['id' => $id]);

    if (!$expense) {
      $this->response(404, ['message' => 'Expense is not found.']);
    }

    if (requestMethod() == 'POST') {
      $attachment = Attachment::getRow(['hashname' => $expense->attachment]);

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

      $data = $this->useAttachment($data, $expense->attachment);

      $res = Expense::update((int)$id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      if ($payment = Payment::getRow(['expense_id' => $id])) {
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
        $newExpense = Expense::getRow(['id' => $id]);
        $newAttachment = Attachment::getRow(['hashname' => $newExpense->attachment]);
        $newPayment = Payment::getRow(['expense_id' => $id]);

        $attachment->data = base64_encode($attachment->data);
        $newAttachment->data = base64_encode($newAttachment->data);

        addActivity("Edit expense {$expense->reference}.", [
          'data' => [
            'after'   => $newExpense,
            'before'  => $expense
          ],
          'attachment' => [
            'after'   => $newAttachment,
            'before'  => $attachment
          ],
          'payment' => [
            'after'   => $newPayment,
            'before'  => $payment
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
        'date'      => dateTimeJS(getPost('date')),
        'bank'      => getPost('bank'),
        'biller'    => getPost('biller'),
        'category'  => getPost('category'),
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

      $data = $this->useAttachment($data);

      $insertID = Income::add($data);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      if ($insertID) {
        $income = Income::getRow(['id' => $insertID]);

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

        $paymentID = Payment::add($paymentData);

        if (!$paymentID) {
          $this->response(400, ['message' => 'Failed to add payment.']);
        }
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $attachment = Attachment::getRow(['hashname' => $income->attachment]);
        $payment = Payment::getRow(['id' => $paymentID]);

        if ($attachment) {
          $attachment->data = base64_encode($attachment->data);
        }

        addActivity("Add income {$income->reference}.", [
          'data'        => $income,
          'attachment'  => $attachment,
          'payment'     => $payment
        ]);

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
      $income     = Income::getRow(['id' => $id]);
      $attachment = Attachment::getRow(['hashname' => $income->attachment]);
      $payment    = Payment::getRow(['income_id' => $income->id]);

      if (!$income) {
        $this->response(404, ['message' => 'Income is not found.']);
      }

      DB::transStart();

      $res = Income::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      Attachment::delete(['hashname' => $income->attachment]);
      Payment::delete(['income_id' => $income->id]);

      DB::transComplete();

      if (DB::transStatus()) {
        if ($attachment) {
          $attachment->data = base64_encode($attachment->data);
        }

        addActivity("Delete income {$income->hashname}.", [
          'data'        => $income,
          'attachment'  => $attachment,
          'payment'     => $payment
        ]);

        $this->response(200, ['message' => 'Income has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete income.']);
  }

  protected function income_edit($id = NULL)
  {
    checkPermission('Income.Edit');

    $income     = Income::getRow(['id' => $id]);

    if (!$income) {
      $this->response(404, ['message' => 'Income is not found.']);
    }

    if (requestMethod() == 'POST') {
      $attachment = Attachment::getRow(['hashname' => $income->attachment]);

      $data = [
        'date'      => dateTimeJS(getPost('date')),
        'bank'      => getPost('bank'),
        'biller'    => getPost('biller'),
        'category'  => getPost('category'),
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

      $data = $this->useAttachment($data, $income->attachment);

      $res = Income::update((int)$id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      if ($payment = Payment::getRow(['income_id' => $id])) {
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

      DB::transComplete();

      if (DB::transStatus()) {
        $newIncome  = Income::getRow(['id' => $id]);
        $newAttachment  = Attachment::getRow(['hashname' => $newIncome->attachment]);
        $newPayment = Payment::getRow(['income_id' => $id]);

        addActivity("Edit income {$income->reference}.", [
          'data' => [
            'after'   => $newIncome,
            'before'  => $income
          ],
          'attachment' => [
            'after'   => $newAttachment,
            'before'  => $attachment,
          ],
          'payment' => [
            'after'   => $newPayment,
            'before'  => $payment
          ]
        ]);

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
    $this->data['title'] = lang('App.viewincome');

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
      $data = [
        'date'      => dateTimeJS(getPost('date')),
        'amount'    => filterDecimal(getPost('amount')),
        'biller'    => getPost('biller'),
        'bankfrom'  => getPost('bankfrom'),
        'bankto'    => getPost('bankto'),
        'note'      => stripTags(getPost('note'))
      ];

      $skipPV = (getPost('skip_pv') == 1 ? true : false);

      if (empty($data['amount']) || $data['amount'] < 1) {
        $this->response(400, ['message' => 'Amount required.']);
      }

      if (empty($data['biller'])) {
        $this->response(400, ['message' => 'Biller required.']);
      }

      if (empty($data['bankfrom'])) {
        $this->response(400, ['message' => 'Bank from required.']);
      }

      if (empty($data['bankto'])) {
        $this->response(400, ['message' => 'Bank to required.']);
      }

      DB::transStart();

      $data = $this->useAttachment($data);

      $insertID = BankMutation::add($data);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      if ($mutation = BankMutation::getRow(['id' => $insertID])) {
        if (!$skipPV) {
          $res = PaymentValidation::add([
            'mutation'    => $mutation->reference,
            'amount'      => $data['amount'],
            'biller'      => $data['biller'],
            'attachment'  => ($data['attachment'] ?? NULL)
          ]);

          if (!$res) {
            $this->response(400, ['message' => getLastError()]);
          }

          BankMutation::update((int)$insertID, ['status' => 'waiting_transfer']);
        } else {
          $paymentOutID = Payment::add([
            'mutation'    => $mutation->reference,
            'bank'        => $data['bankfrom'],
            'biller'      => $data['biller'],
            'amount'      => $data['amount'],
            'type'        => 'sent',
            'attachment'  => ($data['attachment'] ?? NULL)
          ]);

          $paymentInID = Payment::add([
            'mutation'    => $mutation->reference,
            'bank'        => $data['bankto'],
            'biller'      => $data['biller'],
            'amount'      => $data['amount'],
            'type'        => 'received',
            'attachment'  => ($data['attachment'] ?? NULL)
          ]);

          if (!$paymentOutID || !$paymentInID) {
            $this->response(400, ['message' => getLastError()]);
          }

          BankMutation::update((int)$insertID, ['status' => 'paid']);
        }
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $mutation           = BankMutation::getRow(['id' => $insertID]);
        $attachment         = Attachment::getRow(['hashname' => $mutation->attachment]);
        $payments           = Payment::get(['mutation' => $mutation->reference]);
        $paymentValidation  = PaymentValidation::getRow(['mutation_id' =>  $insertID]);

        if ($attachment) {
          $attachment->data = base64_encode($attachment->data);
        }

        addActivity("Add bank mutation {$mutation->reference}.", [
          'data'                => $mutation,
          'attachment'          => $attachment,
          'payment_validation'  => $paymentValidation,
          'payment'             => $payments
        ]);

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
      $mutation           = BankMutation::getRow(['id' => $mutationId]);
      $attachment         = Attachment::getRow(['hashname' => $mutation->attachment]);
      $payments           = Payment::get(['mutation' => $mutation->reference]);
      $paymentValidation  = PaymentValidation::getRow(['mutation' => $mutation->reference]);

      if (!$mutation) {
        $this->response(404, ['message' => 'Mutation is not found.']);
      }

      DB::transStart();

      $res = BankMutation::delete(['id' => $mutation->id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      Attachment::delete(['hashname' => $mutation->attachment]);
      PaymentValidation::delete(['mutation' => $mutation->reference]);
      Payment::delete(['mutation' => $mutation->reference]);

      DB::transComplete();

      if (DB::transStatus()) {
        if ($attachment) {
          $attachment->data = base64_encode($attachment->data);
        }

        addActivity("Delete bank mutation {$mutation->reference}.", [
          'data'                => $mutation,
          'attachment'          => $attachment,
          'payment_validation'  => $paymentValidation,
          'payment'             => $payments
        ]);

        $this->response(200, ['message' => 'Bank mutation has been deleted.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete bank mutation.']);
  }

  protected function mutation_edit($id = NULL)
  {
    checkPermission('BankMutation.Edit');

    $mutation = BankMutation::getRow(['id' => $id]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Bank Mutation is not found.']);
    }

    if (requestMethod() == 'POST') {
      $attachment         = Attachment::getRow(['hashname' => $mutation->attachment]);
      $paymentValidation  = PaymentValidation::getRow(['mutation' => $mutation->reference]);
      $payments           = Payment::get(['mutation' => $mutation->reference]);

      $data = [
        'date'      => dateTimeJS(getPost('date')),
        'amount'    => filterDecimal(getPost('amount')),
        'biller'    => getPost('biller'),
        'bankfrom'  => getPost('bankfrom'),
        'bankto'    => getPost('bankto'),
        'note'      => stripTags(getPost('note'))
      ];

      $skipPV = (getPost('skip_pv') == 1 ? true : false);

      if (empty($data['amount']) || $data['amount'] < 1) {
        $this->response(400, ['message' => 'Amount required.']);
      }

      if (empty($data['biller'])) {
        $this->response(400, ['message' => 'Biller required.']);
      }

      if (empty($data['bankfrom'])) {
        $this->response(400, ['message' => 'Bank from required.']);
      }

      if (empty($data['bankto'])) {
        $this->response(400, ['message' => 'Bank to required.']);
      }

      DB::transStart();

      $data = $this->useAttachment($data, $mutation->attachment);

      // Automatic add new payment validation if amount changed.
      if (floatval($mutation->amount) != floatval($data['amount']) && !$skipPV) {
        // Delete old payment validation.
        Payment::delete(['mutation_id' => $mutation->id]);

        $res = PaymentValidation::add([
          'mutation'    => $mutation->reference,
          'amount'      => $data['amount'],
          'biller'      => $data['biller'],
          'attachment'  => ($data['attachment'] ?? NULL)
        ]);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }

        $data['status'] = 'waiting_transfer';
      }
      if ($payments && $skipPV) {
        foreach ($payments as $payment) {
          Payment::update((int)$payment->id, [
            'amount'      => $data['amount'],
            'biller'      => $data['biller'],
            'attachment'  => $mutation->attachment
          ]);
        }
      }

      $res = BankMutation::update((int)$id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $newMutation          = BankMutation::getRow(['id' => $id]);
        $newAttachment        = Attachment::getRow(['hashname' => $mutation->attachment]);
        $newPaymentValidation = PaymentValidation::getRow(['mutation' => $mutation->reference]);
        $newPayments          = Payment::get(['mutation' => $mutation->reference]);

        if ($attachment) {
          $attachment->data = base64_encode($attachment->data);
        }

        if ($newAttachment) {
          $newAttachment->data = base64_encode($newAttachment->data);
        }

        addActivity("Edit bank mutation {$mutation->reference}.", [
          'data'  => [
            'after'   => $newMutation,
            'before'  => $mutation
          ],
          'attachment'  => [
            'after'   => $newAttachment,
            'before'  => $attachment,
          ],
          'payment_validation'  => [
            'after'   => $newPaymentValidation,
            'before'  => $paymentValidation,
          ],
          'payment' => [
            'after'   => $newPayments,
            'before'  => $payments
          ]
        ]);

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
    checkPermission('BankReconciliation.Sync');

    if (!Bank::sync()) {
      $this->response(400, ['message' => 'Sync Bank amount failed.']);
    }

    $recon = BankReconciliation::get();

    if (BankReconciliation::sync()) {
      $newRecon = BankReconciliation::get();

      addActivity('Sync bank reconciliation.', [
        'data' => [
          'after'   => $newRecon,
          'before'  => $recon
        ]
      ]);

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

  protected function validation_manual(string $mode = null, $id = null)
  {
    checkPermission('PaymentValidation.Manual');

    $pv = null;

    if (!$mode || ($mode != 'sale' && $mode != 'mutation')) {
      $this->response(400, ['message' => 'Manual validation mode must be SALE or MUTATION.']);
    }

    if ($mode == 'sale') {
      $pv = PaymentValidation::select('*')->where('sale_id', $id)->orderBy('id', 'DESC')->getRow();
      $sale = Sale::getRow(['id' => $id]);
      $this->data['sale']       = $sale;
      $this->data['reference']  = $sale->reference;
    } else if ($mode == 'expense') {
      $pv = PaymentValidation::select('*')->where('expense_id', $id)->orderBy('id', 'DESC')->getRow();
      $mutation = BankMutation::getRow(['id' => $id]);
      $this->data['mutation']   = $mutation;
      $this->data['reference']  = $mutation->reference;
    }

    if (!$pv) {
      $this->response(404, ['message' => 'Payment Validation is not found.']);
    }

    if ($pv->status == 'verified') {
      $this->response(400, ['message' => 'Payment Validation is already verified.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $date   = dateTimeJS(getPost('date'));
      $amount = filterDecimal(getPost('amount'));
      $bank   = getPost('bank');

      if (empty($amount)) {
        $this->response(400, ['message' => 'Amount is empty.']);
      }

      if (empty($bank)) {
        $this->response(400, ['message' => 'Bank is empty.']);
      }

      $option = [
        'date'      => $date,
        'reference' => $pv->reference,
        'bank'      => $bank,
        'biller'    => $pv->biller,
        'amount'    => $amount,
        'note'      => stripTags(getPost('note')),
        'manual'    => true // Required.
      ];

      DB::transStart();

      $option = $this->useAttachment($option);

      $res = PaymentValidation::validate($option);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Payment has been validated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['pv']     = $pv;
    $this->data['mode']   = $mode;
    $this->data['id']     = $id;
    $this->data['title']  = lang('App.manualvalidation');

    $this->response(200, ['content' => view('Finance/Validation/manual', $this->data)]);
  }
}
