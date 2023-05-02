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
            <div class="row bank-account" style="display: none">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bank"><?= lang('App.bankaccount') ?> *</label>
                  <select id="bank" name="bank" class="select-bank" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
            <div class="row payment-validation" style="display: none">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="skip_validation"><?= lang('App.paymentvalidation') ?></label>
                  <div class="input-group">
                    <input type="checkbox" id="skip_validation" name="skip_validation" value="1">
                    <label for="skip_validation"><?= lang('App.skippaymentvalidation') ?></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="amount"><?= lang('App.amount') ?> *</label>
                  <input id="amount" name="amount" class="form-control form-control-border form-control-sm currency" value="<?= $amount ?>">
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
              <div class="col-md-12 text-center">
                <div class="form-group">
                  <img class="attachment-preview" src="<?= base_url('assets/app/images/picture.png') ?>" style="max-width:300px">
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
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    erp.select2.bank.biller = ['<?= $inv->biller_id ?>'];

    let hasSkipValidation = <?= hasAccess('PaymentValidation.Skip') ? 'true' : 'false' ?>;

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#attachment').change(function() {
      let src = '';

      if (this.files.length) {
        src = URL.createObjectURL(this.files[0]);
      } else {
        src = base_url + '/assets/app/images/picture.png';
      }

      $('.attachment-preview').prop('src', src);
    });

    $('#biller').change(function() {
      erp.select2.bank.biller = [this.value];
    });

    $('#bank').change(function() {
      if (!this.value.length) {
        return false;
      }

      $.ajax({
        success: (data) => {
          $('#bankbalance').val(formatCurrency(data.data));
        },
        url: base_url + '/finance/bank/balance/' + this.value
      });
    });

    // Saat ubah method. Ubah juga bank.
    $('#method').change(function() {
      erp.select2.bank.type = [this.value];

      $('#bank').val('').trigger('change');

      if (this.value == 'Transfer' && hasSkipValidation) {
        $('.payment-validation').slideDown();
      } else {
        $('.payment-validation').slideUp();
      }

      if (this.value != 'Transfer') {
        $('.bank-account').slideDown();
      } else {
        if (!$('#skip_validation').is(':checked')) {
          $('.bank-account').slideUp();
        }
      }
    });

    $('#skip_validation').change(function() {
      if (this.checked) {
        $('.bank-account').slideDown();
      } else {
        $('.bank-account').slideUp();
      }
    });

    if (!hasSkipValidation) {
      $('#skip_validation').iCheck('disable');
    }

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/payment/add/<?= $mode ?>/<?= $id ?>'
    });
  });
</script>