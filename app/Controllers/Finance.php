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
      ->join('biller', 'biller.id = banks.biller_id', 'left')
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

        return "<div class=\"badge bg-gradient-{$type} p-2\">{$status}</div>";
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
          WHEN banks.number IS NOT null THEN CONCAT(banks.name, ' (', banks.number, ')')
          ELSE banks.name
        END) AS bank_name,
        creator.fullname, expenses.payment_date, expenses.status, expenses.payment_status,
        suppliers.name AS supplier_name, expenses.created_at, expenses.attachment")
      ->join('banks', 'banks.id = expenses.bank_id', 'left')
      ->join('biller', 'biller.id = expenses.biller_id', 'left')
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
          WHEN banks.number IS NOT null THEN CONCAT(banks.name, ' (', banks.number, ')')
          ELSE banks.name
        END) AS bank_name,
        creator.fullname, incomes.created_at, incomes.attachment")
      ->join('banks', 'banks.id = incomes.bank_id', 'left')
      ->join('biller', 'biller.id = incomes.biller_id', 'left')
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
      ->join('banks bankfrom', 'bankfrom.id = bank_mutations.bankfrom_id', 'left')
      ->join('banks bankto', 'bankto.id = bank_mutations.bankto_id', 'left')
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
      ->join('banks', 'banks.id = payment_validations.bank_id', 'left')
      ->join('biller', 'biller.id = payment_validations.biller_id', 'left')
      ->join('sales', 'sales.id = payment_validations.sale_id', 'left')
      ->join('customers', 'customers.id = sales.customer_id', 'left')
      ->join('bank_mutations', 'bank_mutations.id = payment_validations.mutation_id', 'left')
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

  protected function bank_activate($id = null)
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
        'biller_id' => getPost('biller'),
        'code'      => getPost('code'),
        'name'      => getPost('name'),
        'number'    => getPost('number'),
        'holder'    => getPost('holder'),
        'type'      => getPost('type'),
        'bic'       => getPost('bic'),
        'active'    => (getPost('active') == 1 ? 1 : 0)
      ];

      if (empty($data['code'])) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($data['name'])) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($data['biller_id'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      DB::transStart();

      $insertID = Bank::add($data);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Bank has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addbankaccount');

    $this->response(200, ['content' => view('Finance/Bank/add', $this->data)]);
  }

  protected function bank_balance($id = null)
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

  protected function bank_delete($bankId = null)
  {
    checkPermission('BankAccount.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $bank = Bank::getRow(['id' => $bankId]);

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
        $this->response(200, ['message' => 'Bank has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to delete bank.']);
  }

  protected function bank_edit($id = null)
  {
    checkPermission('BankAccount.Edit');

    $bank = Bank::getRow(['id' => $id]);

    if (!$bank) {
      $this->response(404, ['message' => 'Bank is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      $data = [
        'biller_id' => getPost('biller'),
        'code'      => getPost('code'),
        'name'      => getPost('name'),
        'number'    => getPost('number'),
        'holder'    => getPost('holder'),
        'type'      => getPost('type'),
        'bic'       => getPost('bic'),
        'active'    => (getPost('active') == 1 ? 1 : 0)
      ];

      if (empty($data['code'])) {
        $this->response(400, ['message' => 'Code is required.']);
      }

      if (empty($data['name'])) {
        $this->response(400, ['message' => 'Name is required.']);
      }

      if (empty($data['biller_id'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      DB::transStart();

      $res = Bank::update((int)$id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Bank has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['bank'] = $bank;
    $this->data['title'] = lang('App.editbankaccount');

    $this->response(200, ['content' => view('Finance/Bank/edit', $this->data)]);
  }

  protected function bank_view($id = null)
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
        'date'        => dateTimeJS(getPost('date')),
        'bank_id'     => getPost('bank'),
        'biller_id'   => getPost('biller'),
        'category_id' => getPost('category'),
        'supplier_id' => getPost('supplier'),
        'amount'      => filterDecimal(getPost('amount')),
        'note'        => stripTags(getPost('note'))
      ];

      if (empty($data['bank_id'])) {
        $this->response(400, ['message' => 'Bank is required.']);
      }

      if (empty($data['biller_id'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($data['category_id'])) {
        $this->response(400, ['message' => 'Category is required.']);
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

        $this->response(201, ['message' => 'Expense has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addexpense');

    $this->response(200, ['content' => view('Finance/Expense/add', $this->data)]);
  }

  protected function expense_approve($id = null)
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
        $data['approved_at'] = null;
        $data['approved_by'] = null;
      }

      DB::transStart();

      $res = Expense::update((int)$id, $data);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        if ($data['status'] == 'approved') {
          $this->response(200, ['message' => 'Expense has been approved.']);
        } else {
          $this->response(200, ['message' => 'Expense has been disapproved.']);
        }
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to approve expense.']);
  }

  protected function expense_delete($id = null)
  {
    checkPermission('Expense.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $expense    = Expense::getRow(['id' => $id]);

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
        $this->response(200, ['message' => 'Expense has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to delete expense.']);
  }

  protected function expense_edit($id = null)
  {
    checkPermission('Expense.Edit');

    $expense = Expense::getRow(['id' => $id]);

    if (!$expense) {
      $this->response(404, ['message' => 'Expense is not found.']);
    }

    if (requestMethod() == 'POST') {
      $data = [
        'date'        => dateTimeJS(getPost('date')),
        'bank_id'     => getPost('bank'),
        'biller_id'   => getPost('biller'),
        'category_id' => getPost('category'),
        'supplier_id' => getPost('supplier'),
        'amount'      => filterDecimal(getPost('amount')),
        'note'        => stripTags(getPost('note'))
      ];

      if (empty($data['bank_id'])) {
        $this->response(400, ['message' => 'Bank is required.']);
      }

      if (empty($data['biller_id'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($data['category_id'])) {
        $this->response(400, ['message' => 'Category is required.']);
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
          'bank_id'   => $data['bank_id'],
          'biller_id' => $data['biller_id'],
          'amount'    => $data['amount'],
          'note'      => $data['note']
        ];

        if (isset($data['attachment'])) {
          $paymentData['attachment'] = $data['attachment'];
        }

        $res = Payment::update((int)$payment->id, $paymentData);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }

        Bank::sync((int)$data['bank_id']);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Expense has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['expense']  = $expense;
    $this->data['title']    = lang('App.editexpense');

    $this->response(200, ['content' => view('Finance/Expense/edit', $this->data)]);
  }

  protected function expense_view($id = null)
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
        'bank_id'     => getPost('bank'),
        'biller_id'   => getPost('biller'),
        'category_id' => getPost('category'),
        'amount'      => filterDecimal(getPost('amount')),
        'note'        => stripTags(getPost('note'))
      ];

      if (empty($data['biller_id'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($data['category_id'])) {
        $this->response(400, ['message' => 'Category is required.']);
      }

      if (empty($data['bank_id'])) {
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
          'date'      => $income->date,
          'income_id' => $income->id,
          'bank_id'   => $income->bank_id,
          'biller_id' => $income->biller_id,
          'amount'    => $income->amount,
          'type'      => 'received'
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
        $this->response(201, ['message' => 'Income has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addincome');

    $this->response(200, ['content' => view('Finance/Income/add', $this->data)]);
  }

  protected function income_delete($id = null)
  {
    checkPermission('Income.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $income = Income::getRow(['id' => $id]);

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
        $this->response(200, ['message' => 'Income has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to delete income.']);
  }

  protected function income_edit($id = null)
  {
    checkPermission('Income.Edit');

    $income     = Income::getRow(['id' => $id]);

    if (!$income) {
      $this->response(404, ['message' => 'Income is not found.']);
    }

    if (requestMethod() == 'POST') {
      $attachment = Attachment::getRow(['hashname' => $income->attachment]);

      $data = [
        'date'        => dateTimeJS(getPost('date')),
        'bank_id'     => getPost('bank'),
        'biller_id'   => getPost('biller'),
        'category_id' => getPost('category'),
        'amount'      => filterDecimal(getPost('amount')),
        'note'        => stripTags(getPost('note'))
      ];

      if (empty($data['bank_id'])) {
        $this->response(400, ['message' => 'Bank is required.']);
      }

      if (empty($data['biller_id'])) {
        $this->response(400, ['message' => 'Biller is required.']);
      }

      if (empty($data['category_id'])) {
        $this->response(400, ['message' => 'Category is required.']);
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
          'bank_id'   => $data['bank_id'],
          'biller_id' => $data['biller_id'],
          'amount'    => $data['amount'],
          'note'      => $data['note']
        ];

        if (isset($data['attachment'])) {
          $paymentData['attachment'] = $data['attachment'];
        }

        Payment::update((int)$payment->id, $paymentData);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Income has been updated.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['income'] = $income;
    $this->data['title']  = lang('App.editincome');

    $this->response(200, ['content' => view('Finance/Income/edit', $this->data)]);
  }

  protected function income_view($id = null)
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
        'date'        => dateTimeJS(getPost('date')),
        'amount'      => filterDecimal(getPost('amount')),
        'biller_id'   => getPost('biller'),
        'bankfrom_id' => getPost('bankfrom'),
        'bankto_id'   => getPost('bankto'),
        'note'        => stripTags(getPost('note'))
      ];

      $skipPV = (getPost('skip_pv') == 1 ? true : false);

      if (empty($data['amount']) || $data['amount'] < 1) {
        $this->response(400, ['message' => 'Amount required.']);
      }

      if (empty($data['biller_id'])) {
        $this->response(400, ['message' => 'Biller required.']);
      }

      if (empty($data['bankfrom_id'])) {
        $this->response(400, ['message' => 'Bank from required.']);
      }

      if (empty($data['bankto_id'])) {
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
            'mutation_id' => $mutation->id,
            'amount'      => $data['amount'],
            'biller_id'   => $data['biller_id'],
            'attachment'  => ($data['attachment'] ?? null)
          ]);

          if (!$res) {
            $this->response(400, ['message' => getLastError()]);
          }

          BankMutation::update((int)$insertID, ['status' => 'waiting_transfer']);
        } else {
          $paymentOutID = Payment::add([
            'mutation_id' => $mutation->id,
            'bank_id'     => $data['bankfrom_id'],
            'biller_id'   => $data['biller_id'],
            'amount'      => $data['amount'],
            'type'        => 'sent',
            'attachment'  => ($data['attachment'] ?? null)
          ]);

          $paymentInID = Payment::add([
            'mutation_id'    => $mutation->id,
            'bank_id'        => $data['bankto_id'],
            'biller_id'      => $data['biller_id'],
            'amount'      => $data['amount'],
            'type'        => 'received',
            'attachment'  => ($data['attachment'] ?? null)
          ]);

          if (!$paymentOutID || !$paymentInID) {
            $this->response(400, ['message' => getLastError()]);
          }

          BankMutation::update((int)$insertID, ['status' => 'paid']);
        }
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Bank Mutation has been added.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->data['title'] = lang('App.addbankmutation');

    $this->response(200, ['content' => view('Finance/Mutation/add', $this->data)]);
  }

  protected function mutation_delete($mutationId = null)
  {
    checkPermission('BankMutation.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      $mutation = BankMutation::getRow(['id' => $mutationId]);

      if (!$mutation) {
        $this->response(404, ['message' => 'Mutation is not found.']);
      }

      DB::transStart();

      $res = BankMutation::delete(['id' => $mutation->id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      Attachment::delete(['hashname' => $mutation->attachment]);
      PaymentValidation::delete(['mutation_id' => $mutation->id]);
      Payment::delete(['mutation_id' => $mutation->id]);

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Bank mutation has been deleted.']);
      }

      $this->response(400, ['message' => getLastError()]);
    }

    $this->response(400, ['message' => 'Failed to delete bank mutation.']);
  }

  protected function mutation_edit($id = null)
  {
    checkPermission('BankMutation.Edit');

    $mutation = BankMutation::getRow(['id' => $id]);

    if (!$mutation) {
      $this->response(404, ['message' => 'Bank Mutation is not found.']);
    }

    if (requestMethod() == 'POST') {
      $data = [
        'date'        => dateTimeJS(getPost('date')),
        'amount'      => filterDecimal(getPost('amount')),
        'biller_id'   => getPost('biller_id'),
        'bankfrom_id' => getPost('bankfrom_id'),
        'bankto_id'   => getPost('bankto_id'),
        'note'        => stripTags(getPost('note'))
      ];

      $skipPV = (getPost('skip_pv') == 1 ? true : false);

      if (empty($data['amount']) || $data['amount'] < 1) {
        $this->response(400, ['message' => 'Amount required.']);
      }

      if (empty($data['biller_id'])) {
        $this->response(400, ['message' => 'Biller required.']);
      }

      if (empty($data['bankfrom_id'])) {
        $this->response(400, ['message' => 'Bank from required.']);
      }

      if (empty($data['bankto_id'])) {
        $this->response(400, ['message' => 'Bank to required.']);
      }

      DB::transStart();

      $data = $this->useAttachment($data, $mutation->attachment);

      // Automatic add new payment validation if amount changed.
      if (floatval($mutation->amount) != floatval($data['amount']) && !$skipPV) {
        // Delete old payment validation.
        Payment::delete(['mutation_id' => $mutation->id]);

        $res = PaymentValidation::add([
          'mutation_id' => $mutation->id,
          'amount'      => $data['amount'],
          'biller_id'   => $data['biller_id'],
          'attachment'  => ($data['attachment'] ?? null)
        ]);

        if (!$res) {
          $this->response(400, ['message' => getLastError()]);
        }

        $data['status'] = 'waiting_transfer';
      }

      $payments = Payment::get(['mutation_id' => $id]);

      if ($payments && $skipPV) {
        foreach ($payments as $payment) {
          Payment::update((int)$payment->id, [
            'amount'      => $data['amount'],
            'biller_id'   => $data['biller_id'],
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
        $this->response(200, ['message' => 'Bank mutation has been updated.']);
      }

      $this->response(400, ['message' => 'Failed to update bank mutation.']);
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
      $bankId = getPost('bank');

      if (empty($amount)) {
        $this->response(400, ['message' => 'Amount is empty.']);
      }

      if (empty($bankId)) {
        $this->response(400, ['message' => 'Bank is empty.']);
      }

      $option = [
        'date'        => $date,
        'mutation_id' => $pv->mutation_id,
        'sale_id'     => $pv->sale_id,
        'bank_id'     => $bankId,
        'biller_id'   => $pv->biller_id,
        'amount'      => $amount,
        'note'        => stripTags(getPost('note')),
        'manual'      => true // Required.
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
