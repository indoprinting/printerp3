<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-money-bill"></i> <?= $title . " ({$modeLang})" ?></h5>
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
                  <label for="date"><?= lang('App.date') ?> *</label>
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="biller"><?= lang('App.biller') ?> *</label>
                  <select id="biller" name="biller" class="select" data-placeholder="<?= lang('App.biller') ?>" style="width:100%">
                    <option value=""></option>
                    <?php foreach (\App\Models\Biller::get(['active' => 1]) as $bl) : ?>
                      <?php if (!empty(session('login')->biller) && session('login')->biller != $bl->code) continue; ?>
                      <option value="<?= $bl->code ?>"><?= $bl->name ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="amount"><?= lang('App.amount') ?> *</label>
                  <input id="amount" name="amount" class="form-control form-control-border form-control-sm currency" value="<?= $amount ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="method"><?= lang('App.method') ?> *</label>
                  <select id="method" name="method" class="select" data-placeholder="<?= lang('App.method') ?>" style=" width:100%">
                    <option value=""></option>
                    <?php $bankTypes = \App\Models\Bank::select('type')->distinct()->get(['active' => 1]); ?>
                    <?php foreach ($bankTypes as $bankType) : ?>
                      <option value="<?= $bankType->type ?>"><?= lang('App.' . strtolower($bankType->type)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bank"><?= lang('App.bankaccount') ?> *</label>
                  <select id="bank" name="bank" class="select" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%">
                    <option value=""></option>
                    <?php foreach (\App\Models\Bank::get(['active' => 1]) as $bk) : ?>
                      <?php if (!empty(session('login')->biller) && session('login')->biller != $bk->biller) continue; ?>
                      <option value="<?= $bk->code ?>"><?= (empty($bk->number) ? $bk->name : "{$bk->name} ({$bk->number})") ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bankbalance"><?= lang('App.currentbalance') ?></label>
                  <input id="bankbalance" class="form-control form-control-border form-control-sm float-right" readonly>
                </div>
              </div>
            </div>
            <div class="row payment-validation" style="display: none">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="skip_validation"><?= lang('App.paymentvalidation') ?></label>
                  <div class="input-group">
                    <input type="checkbox" id="skip_validation" name="skip_validation">
                    <label for="skip_validation"><?= lang('App.skippaymentvalidation') ?></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="attachment"><?= lang('App.attachment') ?></label>
                  <div class="custom-file">
                    <input type="file" id="attachment" name="attachment" class="custom-file-input">
                    <label for="attachment" class="custom-file-label"><?= lang('App.choosefile') ?></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="editor"><?= lang('App.note') ?></label>
                  <div id="editor"></div>
                  <input type="hidden" name="note">
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
  <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let hasSkipValidation = <?= hasAccess('PaymentValidation.Skip') ? 'true' : 'false' ?>;

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#bank').change(function() {
      $.ajax({
        success: (data) => {
          $('#bankbalance').val(formatCurrency(data.data));
        },
        url: base_url + '/finance/bank/balance/' + this.value
      });
    });

    $('#method').change(function() {
      if (this.value == 'Transfer' && hasSkipValidation) {
        $('.payment-validation').slideDown();
      } else {
        $('.payment-validation').slideUp();
      }
    });

    if (!hasSkipValidation) {
      $('#skip_validation').iCheck('disable');
    }

    $('#bank').val('<?= $bank ?>').trigger('change');
    $('#biller').val('<?= $biller ?>').trigger('change');

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/payment/add/<?= $mode ?>/<?= $id ?>'
    });
  });
</script>