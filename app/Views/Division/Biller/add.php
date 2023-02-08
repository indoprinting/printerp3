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
                  <input id="code" name="code" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.code') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?> *</label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.name') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="address"><?= lang('App.address') ?></label>
                  <input id="address" name="address" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="city"><?= lang('App.city') ?></label>
                  <input id="city" name="city" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.city') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="phone"><?= lang('App.phone') ?></label>
                  <input id="phone" name="phone" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.phone') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email"><?= lang('App.email') ?></label>
                  <input id="email" name="email" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.email') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="target"><?= lang('App.targetrevenue') ?></label>
                  <input id="target" name="target" class="form-control form-control-border form-control-sm currency" placeholder="<?= lang('App.targetrevenue') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <input type="checkbox" name="active" value="1" checked>
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
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/division/biller/add'
    });
  });
</script>