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
                  <label for="date"><?= lang('App.date') ?></label>
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="biller"><?= lang('App.biller') ?> *</label>
                  <select id="biller" name="biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style=" width:100%">
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="amount"><?= lang('App.amount') ?> *</label>
                  <input id="amount" name="amount" class="form-control form-control-border form-control-sm currency" value="0">
                </div>
              </div>
              <div class="col-md-6">
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
                  <div id="editor"></div>
                  <input type="hidden" name="note">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-primary">
            <div class="card-title"><?= lang('App.bankaccount') ?></div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bankfrom"><?= lang('App.from') ?> *</label>
                  <select id="bankfrom" name="bankfrom" class="select-bank-from" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bankfrombalance"><?= lang('App.currentbalance') ?></label>
                  <input id="bankfrombalance" class="form-control form-control-border form-control-sm float-right" readonly>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bankto"><?= lang('App.to') ?> *</label>
                  <select id="bankto" name="bankto" class="select-bank-to" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="banktobalance"><?= lang('App.currentbalance') ?></label>
                  <input id="banktobalance" class="form-control form-control-border form-control-sm float-right" readonly>
                </div>
              </div>
            </div>
            <?php if (hasAccess('PaymentValidation.Skip')) : ?>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <input type="checkbox" id="skip_pv" name="skip_pv" value="1">
                    <label for="skip_pv"><?= lang('App.skippaymentvalidation') ?></label>
                  </div>
                </div>
              </div>
            <?php endif; ?>
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
    let bankToVal = '';
    let bankFromVal = '';

    $('#date').val('<?= dateTimeJS($mutation->date) ?>');

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#amount').val(formatCurrency('<?= $mutation->amount ?>'));

    $('#bankfrom').change(function() {
      let bankTo = $('#bankto').val();

      if (bankTo) {
        if (this.value == bankTo) {
          $(this).val(bankFromVal).trigger('change');
          toastr.error('Akun bank tidak boleh sama.');
          return false;
        }
      }

      $.ajax({
        success: (data) => {
          bankFromVal = this.value;
          $('#bankfrombalance').val(formatCurrency(data.data));
        },
        url: base_url + '/finance/bank/balance/' + this.value
      })
    });

    $('#bankto').change(function() {
      let bankFrom = $('#bankfrom').val();

      if (bankFrom) {
        if (this.value == bankFrom) {
          $(this).val(bankToVal).trigger('change');
          toastr.error('Akun bank tidak boleh sama.');
          return false;
        }
      }

      $.ajax({
        success: (data) => {
          bankToVal = this.value;
          $('#banktobalance').val(formatCurrency(data.data));
        },
        url: base_url + '/finance/bank/balance/' + this.value
      })
    });

    editor.root.innerHTML = '<?= $mutation->note ?>';

    preSelect2('biller', '#biller', '<?= $mutation->biller_id ?>');
    preSelect2('bank', '#bankfrom', '<?= $mutation->bankfrom_id ?>');
    preSelect2('bank', '#bankto', '<?= $mutation->bankto_id ?>');

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/finance/mutation/edit/<?= $mutation->id ?>'
    });
  });
</script>