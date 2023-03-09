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
                  <label for="date"><?= lang('App.date') ?> *</label>
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="biller"><?= lang('App.biller') ?> *</label>
                  <select id="biller" name="biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bank"><?= lang('App.bankaccount') ?> *</label>
                  <select id="bank" name="bank" class="select-bank" data-placeholder="<?= lang('App.bankaccount') ?>" style="width:100%">
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
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="amount"><?= lang('App.amount') ?> *</label>
                  <input id="amount" name="amount" class="form-control form-control-border form-control-sm currency" value="0">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="category"><?= lang('App.category') ?> *</label>
                  <select id="category" name="category" class="select" data-placeholder="<?= lang('App.category') ?>" style=" width:100%">
                    <?php foreach (\App\Models\ExpenseCategory::select('*')->orderBy('name', 'ASC')->get() as $excat) : ?>
                      <option value="<?= $excat->id ?>"><?= $excat->name ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="supplier"><?= lang('App.supplier') ?></label>
                  <select id="supplier" name="supplier" class="select-supplier" data-placeholder="<?= lang('App.supplier') ?>" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="attachment"><?= lang('App.attachment') ?></label>
                  <div class="custom-file">
                    <input id="attachment" name="attachment" class="custom-file-input" type="file">
                    <label for="attachment" class="custom-file-label"><?= lang('App.choosefile') ?></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 text-center">
                <div class="form-group">
                  <img class="attachment-preview" src="<?= base_url('assets/app/images/picture.png') ?>" style="max-width:300xp">
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
    erp.select2.bank.biller = [0];

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

    $('#bank').change(function() {
      if (!this.value) return false;

      $.ajax({
        success: (data) => {
          $('#bankbalance').val(formatCurrency(data.data));
        },
        url: base_url + '/finance/bank/balance/' + this.value
      })
    });

    $('#biller').change(function() {
      erp.select2.bank.biller = [this.value];
    });

    if (erp.biller.id) {
      preSelect2('biller', '#biller', erp.biller.id);
    }

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/finance/expense/add'
    });
  });
</script>