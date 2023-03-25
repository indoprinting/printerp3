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
          <td><?= $voucher->id ?></td>
        </tr>
        <tr>
          <td><?= lang('App.code') ?></td>
          <td><?= $voucher->code ?></td>
        </tr>
        <tr>
          <td><?= lang('App.name') ?></td>
          <td><?= $voucher->name ?></td>
        </tr>
        <tr>
          <td><?= lang('App.amount') ?></td>
          <td><?= formatCurrency($voucher->amount) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.quota') ?></td>
          <td><?= $voucher->quota ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdat') ?></td>
          <td><?= formatDate($voucher->created_at) ?></td>
        </tr>
        <tr>
          <td><?= lang('App.createdby') ?></td>
          <td><?= \App\Models\User::getRow(['id' => $voucher->created_by])->fullname ?></td>
        </tr>
        <?php if ($voucher->updated_at) : ?>
          <tr>
            <td><?= lang('App.updatedat') ?></td>
            <td><?= formatDate($voucher->updated_at) ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($voucher->updated_by) : ?>
          <tr>
            <td><?= lang('App.updatedby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $voucher->created_by])->fullname ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/sale/voucher/view/<?= $voucher->id ?>'
    });
  });
</script>