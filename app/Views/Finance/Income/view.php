<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-magnifying-glass"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
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
        <?php $bank = \App\Models\Bank::getRow(['code' => $expense->bank]) ?>
        <td><?= ($bank->number ? $bank->name . " ({$bank->number})" : $bank->name) ?></td>
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
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>