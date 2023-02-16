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
          <td><?= $bank->id ?></td>
        </tr>
        <tr>
          <td><?= lang('App.code') ?></td>
          <td><?= $bank->code ?></td>
        </tr>
        <tr>
          <td><?= lang('App.name') ?></td>
          <td><?= $bank->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.number') ?></td>
          <td><?= $bank->number ?></td>
        </tr>
        <tr>
          <td><?= lang('App.holder') ?></td>
          <td><?= $bank->holder ?></td>
        </tr>
        <tr>
          <td><?= lang('App.type') ?></td>
          <td><?= $bank->type ?></td>
        </tr>
        <tr>
          <td><?= lang('App.balance') ?></td>
          <td><?= formatCurrency($bank->amount) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.biccode') ?></td>
          <td><?= $bank->bic ?></td>
        </tr>
        <tr>
          <td><?= lang('App.biller') ?></td>
          <td><?= \App\Models\Biller::getRow(['id' => $bank->biller_id])->name ?></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <?php if (hasAccess('BankAccount.Activate') && $bank->active == 0) : ?>
    <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.activate') ?></button>
  <?php endif; ?>
  <?php if (hasAccess('Expense.Deactivate') && $bank->active == 1) : ?>
    <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.deactivate') ?></button>
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
      url: base_url + '/finance/bank/activate/<?= $bank->id ?>'
    });
  });
</script>