<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-user-plus"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form method="post" enctype="multipart/form-data" id="form">
    <?= csrf_field() ?>
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <label for="name"><?= lang('App.groupname') ?></label>
          <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.groupname') ?>" value="<?= $customerGroup->name ?>">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-primary">
            <div class="card-title"><?= lang('App.option') ?></div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <input type="checkbox" id="delivery" name="delivery" value="1">
                <label for="delivery"><?= lang('App.allowdelivery') ?></label>
              </div>
              <div class="col-md-6">
                <input type="checkbox" id="production" name="production" value="1">
                <label for="production"><?= lang('App.allowproduction') ?></label>
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
    let delivery = <?= $customerGroup->allow_delivery ?>;
    let production = <?= $customerGroup->allow_production ?>;

    if (delivery) {
      $('#delivery').iCheck('check');
    }

    if (production) {
      $('#production').iCheck('check');
    }

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/humanresource/customergroup/edit/<?= $customerGroup->id ?>'
    });
  });
</script>