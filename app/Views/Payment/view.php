<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-money-bill"></i> <?= $title . " ({$modeLang})" ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th><?= lang('App.reference') ?></th>
        <th><?= lang('App.date') ?></th>
        <th><?= lang('App.bankaccount') ?></th>
        <th><?= lang('App.biller') ?></th>
        <th><?= lang('App.amount') ?></th>
        <th><?= lang('App.type') ?></th>
        <th><?= lang('App.attachment') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($payments as $payment) : ?>
        <?php $bank   = \App\Models\Bank::getRow(['id' => $payment->bank_id]); ?>
        <?php $biller = \App\Models\Biller::getRow(['id' => $payment->biller_id]); ?>
        <tr>
          <td><?= $payment->id ?></td>
          <td><?= $payment->reference ?></td>
          <td><?= $payment->date ?></td>
          <td><?= $bank->name . ($bank->number ? " ({$bank->number})" : '') ?></td>
          <td><?= $biller->name ?></td>
          <td><?= formatCurrency($payment->amount) ?></td>
          <td><?= renderStatus($payment->type) ?></td>
          <td><?= renderAttachment($payment->attachment) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$payments) : ?>
        <tr>
          <td class="text-center" colspan="8"><?= lang('App.nopayment') ?></td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>