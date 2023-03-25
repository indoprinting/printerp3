<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-user-plus"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form method="post" enctype="multipart/form-data" id="form">
    <?= csrf_field() ?>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="code"><?= lang('App.code') ?> *</label>
                  <input id="code" name="code" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?> *</label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="method"><?= lang('App.method') ?> *</label>
                  <select id="method" name="method" class="select" data-placeholder="<?= lang('App.method') ?>" style="width:100%">
                    <option value="currency"><?= lang('App.currency') ?></option>
                    <option value="percent"><?= lang('App.percent') ?></option>
                  </select>
                </div>
              </div>
              <div class="col-md-4 amount">
                <div class="form-group">
                  <label for="amount"><?= lang('App.amount') ?> *</label>
                  <input id="amount" name="amount" class="form-control form-control-border form-control-sm currency">
                </div>
              </div>
              <div class="col-md-4 percent" style="display:none">
                <div class="form-group">
                  <label for="percent"><?= lang('App.percent') ?> *</label>
                  <input id="percent" name="percent" class="form-control form-control-border form-control-sm" min="0" max="999" type="number">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="number"><?= lang('App.quota') ?> *</label>
                  <input id="quota" name="quota" class="form-control form-control-border form-control-sm" min="0" type="number">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="validfrom"><?= lang('App.validfrom') ?> *</label>
                  <input id="validfrom" name="validfrom" class="form-control form-control-border form-control-sm" type="datetime-local">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="validto"><?= lang('App.validto') ?> *</label>
                  <input id="validto" name="validto" class="form-control form-control-border form-control-sm" type="datetime-local">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    $('#method').change(function() {
      if (this.value == 'currency') {
        $('.percent').slideUp(() => {
          $('.amount').slideDown();
        });
      } else if (this.value == 'percent') {
        $('.amount').slideUp(() => {
          $('.percent').slideDown();
        });
      }
    });

    $('#code').val('<?= $voucher->code ?>');
    $('#name').val('<?= $voucher->name ?>');
    $('#amount').val('<?= $voucher->amount ?>');
    $('#percent').val('<?= $voucher->percent ?>');
    $('#method').val('<?= $voucher->method ?>').trigger('change');;
    $('#quota').val('<?= $voucher->quota ?>');
    $('#validfrom').val('<?= dateTimeJS($voucher->valid_from) ?>');
    $('#validto').val('<?= dateTimeJS($voucher->valid_to) ?>');

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/sale/voucher/edit/<?= $voucher->id ?>'
    });
  });
</script>