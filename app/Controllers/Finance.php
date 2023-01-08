<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\{DataTables, FileUpload};
use App\Models\{Attachment, Bank, BankMutation, BankReconciliation, DB, Payment, PaymentValidation};

class Finance extends BaseController
{
  public function index()
  {
  }

  public function getBanks()
  {
    checkPermission('Bank.View');

    $dt = new DataTables('bank');
    $dt
      ->select("bank.id AS id, bank.code, bank.name, bank.number,
      bank.holder, bank.type, bank.amount, biller.name AS biller_name, bank.bic, bank.active")
      ->join('biller', 'biller.code = bank.biller', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/bank/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <a class="dropdown-item" href="' . base_url('finance/bank/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> Delete
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

  public function getMutations()
  {
    checkPermission('BankMutation.View');

    $dt = new DataTables('bankmutation');
    $dt
      ->select("bankmutation.id AS id, bankmutation.created_at, bankmutation.reference,
        bankfrom.name AS bankfrom_name, bankto.name AS bankto_name,
        bankmutation.note, bankmutation.amount, biller.name AS biller_name,
        creator.fullname, bankmutation.status,
        bankmutation.attachment")
      ->join('banks bankfrom', 'bankfrom.code = bankmutation.bankfrom', 'left')
      ->join('banks bankto', 'bankto.code = bankmutation.bankto', 'left')
      ->join('biller', 'biller.code = bankmutation.biller', 'left')
      ->join('users creator', 'creator.id = bankmutation.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/mutation/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <a class="dropdown-item" href="' . base_url('finance/mutation/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> Delete
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

    $dt = new DataTables('paymentvalidation');
    $dt
      ->select("paymentvalidation.id AS id, paymentvalidation.created_at,
        paymentvalidation.reference, creator.fullname, biller.name AS biller_name,
        IF(
          LENGTH(customer.company),
          CONCAT(customer.name, ' (', customer.company, ')'),
          customer.name
        ) AS customer_name, bank.name AS bank_name, bank.number AS bank_number,
        paymentvalidation.amount,
        (paymentvalidation.amount + paymentvalidation.unique) AS total,
        paymentvalidation.expired_at, paymentvalidation.transaction_at,
        paymentvalidation.unique,
        paymentvalidation.note, paymentvalidation.status,
        paymentvalidation.attachment")
      ->join('bank', 'bank.code = paymentvalidation.bank', 'left')
      ->join('biller', 'biller.code = paymentvalidation.biller', 'left')
      ->join('sale', 'sale.reference = paymentvalidation.sale', 'left')
      ->join('customer', 'customer.phone = sale.customer', 'left')
      ->join('bankmutation', 'bankmutation.reference = paymentvalidation.mutation', 'left')
      ->join('users creator', 'creator.id = paymentvalidation.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-page"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('finance/validation/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> Edit
              </a>
              <a class="dropdown-item" href="' . base_url('finance/validation/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> Delete
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

    $dt = new DataTables('bankreconciliation');
    $dt
      ->select("bank_name, number, amount_mb, amount, (amount_mb - amount) AS balance,
        acc_name_mb, acc_name, sync_at")
      ->editColumn('amount_mb', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount_mb']) . '</div>';
      })
      ->editColumn('amount', function ($data) {
        return '<div class="float-right">' . formatNumber($data['amount']) . '</div>';
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

    checkPermission('Bank.View');

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
    checkPermission('Bank.Add');

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

      if (Bank::add($billerData)) {
        $this->response(201, ['message' => 'Bank has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addbankaccount');

    $this->response(200, ['content' => view('Finance/Bank/add', $this->data)]);
  }

  protected function bank_balance($bankCode = NULL)
  {
    $bank = Bank::getRow(['code' => $bankCode]);

    if ($bank) {
      $this->response(200, ['data' => $bank->amount]);
    }

    $this->response(400, ['message' => 'Failed to get balance.']);
  }

  protected function bank_delete($bankId = NULL)
  {
    checkPermission('Bank.Delete');

    if (requestMethod() == 'POST' && isAJAX()) {
      if (Bank::delete(['id' => $bankId])) {
        $this->response(200, ['message' => 'Bank has been deleted.']);
      }
      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }
    $this->response(400, ['message' => 'Failed to delete bank.']);
  }

  protected function bank_edit($bankId = NULL)
  {
    checkPermission('Bank.Edit');

    $bank = Bank::getRow(['id' => $bankId]);

    if (!$bank) $this->response(404, ['message' => 'Bank is not found.']);

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

      $this->response(400, ['message' => var_dump($billerData)]);

      if (Bank::update((int)$bankId, $billerData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.bankEditOK'), $bank->name)]);
      }
      $this->response(400, ['message' => sprintf(lang('Msg.bankEditNO'), $bank->name)]);
    }

    $this->data['bank'] = $bank;
    $this->data['title'] = lang('App.editbankaccount');

    $this->response(200, ['content' => view('Finance/Bank/edit', $this->data)]);
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

        $attachmentId = $upload->store();
        $mutationData['attachment'] = Attachment::getRow(['id' => $attachmentId])->hashname;
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
      BankMutation::delete(['id' => $mutationId]);
      PaymentValidation::delete(['mutation' => $mutation->reference]);
      Payment::delete(['mutation_id' => $mutationId]);
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

    $this->response(501, ['message' => 'Not implemented']);

    $mutation = BankMutation::getRow(['id' => $mutationId]);

    if (!$mutation) $this->response(404, ['message' => 'Bank Mutation is not found.']);

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

      $this->response(400, ['message' => var_dump($billerData)]);

      if (BankMutation::update((int)$mutationId, $billerData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.bankEditOK'), $mutation->name)]);
      }
      $this->response(400, ['message' => sprintf(lang('Msg.bankEditNO'), $mutation->name)]);
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
        ['name' => lang('App.reconciliation'), 'slug' => 'reconciliation', 'url' => '#']
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

    if (!BankReconciliation::sync()) {
      $this->response(400, ['message' => 'Sync Bank Reconciliation failed.']);
    }

    $this->response(200, ['message' => 'Bank Reconciliation has been synced successfully.']);
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

    if (!$validation) $this->response(404, ['message' => 'Payment Validation is not found.']);

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

      $this->response(400, ['message' => var_dump($validationData)]);

      if (PaymentValidation::update((int)$validationId, $validationData)) {
        $this->response(200, ['message' => sprintf(lang('Msg.bankEditOK'), $validation->name)]);
      }
      $this->response(400, ['message' => sprintf(lang('Msg.bankEditNO'), $validation->name)]);
    }

    $this->data['validation'] = $validation;
    $this->data['title'] = lang('App.editpaymentvalidation');

    $this->response(200, ['content' => view('Finance/Validation/edit', $this->data)]);
  }
}
