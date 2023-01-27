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
          <td><?= $income->id ?></td>
        </tr>
        <tr>
          <td><?= lang('App.date') ?></td>
          <td><?= formatDate($income->date) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.reference') ?></td>
          <td><?= $income->reference ?></td>
        </tr>
        <tr>
          <td><?= lang('App.bankaccount') ?></td>
          <td><?= \App\Models\Bank::getRow(['code' => $income->bank])->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.biller') ?></td>
          <td><?= \App\Models\Biller::getRow(['code' => $income->biller])->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.category') ?></td>
          <td><?= \App\Models\IncomeCategory::getRow(['code' => $income->category])->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.amount') ?></td>
          <td><?= formatCurrency($income->amount) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.note') ?></td>
          <td><?= $income->note ?></td>
        </tr>
        <tr>
          <td><?= lang('App.status') ?></td>
          <td><?= renderStatus($income->status) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.paymentstatus') ?></td>
          <td><?= renderStatus($income->payment_status) ?></td>
        </tr>
        <?php if ($income->payment_date) : ?>
          <tr>
            <td><?= lang('App.paymentdate') ?></td>
            <td><?= formatDate($income->payment_date) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($income->approved_at) : ?>
          <tr>
            <td><?= lang('App.approvedat') ?></td>
            <td><?= formatDate($income->approved_at) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($income->approved_by) : ?>
          <tr>
            <td><?= lang('App.approvedby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $income->approved_by])->fullname ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($income->supplier_id) : ?>
          <?php $supplier = \App\Models\Supplier::getRow(['id' => $income->supplier_id]); ?>
          <tr>
            <td><?= lang('App.supplier') ?></td>
            <td><?= ($supplier->company ? "{$supplier->name} ({$supplier->company})" : $supplier->name) ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td><?= lang('App.createdat') ?></td>
          <td><?= formatDate($income->created_at) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdby') ?></td>
          <td><?= \App\Models\User::getRow(['id' => $income->created_by])->fullname ?></td>
        </tr>
        <?php if ($income->updated_at) : ?>
          <tr>
            <td><?= lang('App.updatedat') ?></td>
            <td><?= formatDate($income->updated_at) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($income->updated_by) : ?>
          <tr>
            <td><?= lang('App.updatedby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $income->created_by])->fullname ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <?php if (hasAccess('Income.Approve') && $income->status == 'need_approval') : ?>
    <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.approve') ?></button>
  <?php endif; ?>
  <?php if (hasAccess('Income.Disapprove') && $income->status == 'approved') : ?>
    <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.disapprove') ?></button>
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
      url: base_url + '/finance/income/approve/<?= $income->id ?>'
    });
  });
</script>