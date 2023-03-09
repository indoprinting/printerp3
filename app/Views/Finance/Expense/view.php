<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-magnifying-glass"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form id="form">
    <?= csrf_field() ?>
    <table class="table table-hover table-sm table-striped">
      <tbody>
        <tr>
          <td><?= lang('App.id') ?></td>
          <td><?= $expense->id ?></td>
        </tr>
        <tr>
          <td><?= lang('App.date') ?></td>
          <td><?= formatDate($expense->date) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.reference') ?></td>
          <td><?= $expense->reference ?></td>
        </tr>
        <tr>
          <td><?= lang('App.bankaccount') ?></td>
          <?php $bank = \App\Models\Bank::getRow(['id' => $expense->bank_id]) ?>
          <td><?= ($bank->number ? $bank->name . " ({$bank->number})" : $bank->name) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.biller') ?></td>
          <td><?= \App\Models\Biller::getRow(['id' => $expense->biller_id])->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.category') ?></td>
          <td><?= \App\Models\ExpenseCategory::getRow(['id' => $expense->category_id])->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.amount') ?></td>
          <td><?= formatCurrency($expense->amount) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.note') ?></td>
          <td><?= $expense->note ?></td>
        </tr>
        <tr>
          <td><?= lang('App.status') ?></td>
          <td><?= renderStatus($expense->status) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.paymentstatus') ?></td>
          <td><?= renderStatus($expense->payment_status) ?></td>
        </tr>
        <?php if ($expense->payment_date) : ?>
          <tr>
            <td><?= lang('App.paymentdate') ?></td>
            <td><?= formatDate($expense->payment_date) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($expense->approved_at) : ?>
          <tr>
            <td><?= lang('App.approvedat') ?></td>
            <td><?= formatDate($expense->approved_at) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($expense->approved_by) : ?>
          <tr>
            <td><?= lang('App.approvedby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $expense->approved_by])->fullname ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($expense->supplier_id) : ?>
          <?php $supplier = \App\Models\Supplier::getRow(['id' => $expense->supplier_id]); ?>
          <tr>
            <td><?= lang('App.supplier') ?></td>
            <td><?= ($supplier->company ? "{$supplier->name} ({$supplier->company})" : $supplier->name) ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td><?= lang('App.createdat') ?></td>
          <td><?= formatDate($expense->created_at) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdby') ?></td>
          <td><?= \App\Models\User::getRow(['id' => $expense->created_by])->fullname ?></td>
        </tr>
        <?php if ($expense->updated_at) : ?>
          <tr>
            <td><?= lang('App.updatedat') ?></td>
            <td><?= formatDate($expense->updated_at) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($expense->updated_by) : ?>
          <tr>
            <td><?= lang('App.updatedby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $expense->created_by])->fullname ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($expense->attachment) : ?>
          <tr>
            <td><?= lang('App.attachment') ?></td>
            <td><img src="<?= base_url('attachment/' . $expense->attachment) ?>" style="max-width:300px; width:100%"></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
  <?php if (hasAccess('Expense.Approve') && $expense->status == 'need_approval') : ?>
    <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-check"></i> <?= lang('App.approve') ?></button>
  <?php endif; ?>
  <?php if (hasAccess('Expense.Disapprove') && $expense->status == 'approved') : ?>
    <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-times"></i> <?= lang('App.disapprove') ?></button>
  <?php endif; ?>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/finance/expense/approve/<?= $expense->id ?>'
    });
  });
</script>