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
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm" value="<?= dateTimeJS($mutation->date) ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="biller"><?= lang('App.biller') ?> *</label>
                  <select id="biller" name="biller" class="select" data-placeholder="<?= lang('App.biller') ?>" style=" width:100%">
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
                  <input id="amount" name="amount" class="form-control form-control-border form-control-sm currency" value="<?= $mutation->amount ?>">
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
                  <label for="bankfrom"><?= lang('App.from') ?></label>
                  <select id="bankfrom" name="bankfrom" class="select" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%">
                    <option value=""></option>
                    <?php foreach (\App\Models\Bank::get(['active' => 1]) as $bk) : ?>
                      <option value="<?= $bk->code ?>"><?= (empty($bk->number) ? $bk->name : "{$bk->name} ({$bk->number})") ?></option>
                    <?php endforeach; ?>
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
                  <label for="bankto"><?= lang('App.to') ?></label>
                  <select id="bankto" name="bankto" class="select" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%">
                    <option value=""></option>
                    <?php foreach (\App\Models\Bank::get(['active' => 1]) as $bk) : ?>
                      <option value="<?= $bk->code ?>"><?= (empty($bk->number) ? $bk->name : "{$bk->name} ({$bk->number})") ?></option>
                    <?php endforeach; ?>
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
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let bankToVal = '';
    let bankFromVal = '';

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

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
    $('#biller').val('<?= $mutation->biller ?>').trigger('change');
    $('#bankfrom').val('<?= $mutation->bankfrom ?>').trigger('change');
    $('#bankto').val('<?= $mutation->bankto ?>').trigger('change');

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/finance/mutation/edit/<?= $mutation->id ?>'
    });
  });
</script>