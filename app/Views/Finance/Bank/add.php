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
                  <input id="code" name="code" class="form-control form-control-border form-control-sm" placeholder="Ex. BCARIYAN">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?> *</label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="Ex. BCA">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="number"><?= lang('App.number') ?></label>
                  <input id="number" name="number" class="form-control form-control-border form-control-sm" placeholder="Ex. 62502645xx">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="holder"><?= lang('App.holder') ?></label>
                  <input id="holder" name="holder" class="form-control form-control-border form-control-sm" placeholder="Ex. Riyan Widiyanto">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="type"><?= lang('App.type') ?></label>
                  <select name="type" class="select-bank-type" data-placeholder="<?= lang('App.type') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="biller"><?= lang('App.biller') ?> *</label>
                  <select name="biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%">
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bic"><?= lang('App.biccode') ?></label>
                  <input id="bic" name="bic" class="form-control form-control-border form-control-sm" placeholder="Ex. CENAIDJA">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <input type="checkbox" id="active" name="active" value="1" checked>
                  <label for="active"><?= lang('App.active') ?></label>
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
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/finance/bank/add'
    });
  });
</script>