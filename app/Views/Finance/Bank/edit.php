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
                  <label for="code"><?= lang('App.code') ?></label>
                  <input id="code" name="code" class="form-control form-control-border form-control-sm" placeholder="Ex. BCARIYAN" value="<?= $bank->code ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?></label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="Ex. BCA" value="<?= $bank->name ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="number"><?= lang('App.number') ?></label>
                  <input id="number" name="number" class="form-control form-control-border form-control-sm" placeholder="Ex. 62502645xx" value="<?= $bank->number ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="holder"><?= lang('App.holder') ?></label>
                  <input id="holder" name="holder" class="form-control form-control-border form-control-sm" placeholder="Ex. Riyan Widiyanto" value="<?= $bank->holder ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="type"><?= lang('App.type') ?></label>
                  <select class="select-tags" id="type" name="type" style="width:100%">
                    <?php foreach (\App\Models\DB::table('banks')->select('type')->distinct()->get() as $bn) : ?>
                      <option value="<?= $bn->type ?>"><?= $bn->type ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email"><?= lang('App.biller') ?></label>
                  <select class="select" id="biller" name="biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%">
                    <option value=""></option>
                    <?php foreach (\App\Models\Biller::get(['active' => 1]) as $bl) : ?>
                      <option value="<?= $bl->code ?>"><?= $bl->name ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="bic"><?= lang('App.biccode') ?></label>
                  <input id="bic" name="bic" class="form-control form-control-border form-control-sm" placeholder="Ex. CENAIDJA" value="<?= $bank->bic ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <input type="checkbox" id="active" name="active" value="1">
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
  <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let active = <?= $bank->active ?>;

    if (active) {
      $('#active').iCheck('check');
    }

    $('#biller').val('<?= $bank->biller ?>').trigger('change');
    $('#type').val('<?= $bank->type ?>').trigger('change');

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/finance/bank/edit/<?= $bank->id ?>'
    });
  });
</script>