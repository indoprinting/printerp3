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
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-gradient-success"><?= lang('App.profile') ?></div>
          <div class="card-body">
            <div class="form-group">
              <label for="avatarImg"><?= lang('App.profileimage') ?></label>
              <div class="text-center">
                <div class="btn btn-default btn-file">
                  <img id="avatar" class="profile-user-img img-fluid" src="<?= base_url('attachment/avatarmale') ?>">
                  <input type="file" id="avatarImg" name="avatarImg" accept=".png, .jpg, .jpeg">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="fullname"><?= lang('App.fullname') ?></label>
              <input id="fullname" name="fullname" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.fullname') ?>" required>
            </div>
            <div class="form-group">
              <label for="phone"><?= lang('App.phone') ?>/WA</label>
              <input id="phone" name="phone" class="form-control form-control-border form-control-sm" placeholder="Ex. 0823xxxx2064">
            </div>
            <div class="form-group">
              <label for="gender"><?= lang('App.gender') ?></label>
              <select id="gender" name="gender" class="form-control form-control-border select" data-placeholder="<?= lang('App.gender') ?>" style="width:100%">
                <option value="male"><?= lang('App.male') ?></option>
                <option value="female"><?= lang('App.female') ?></option>
              </select>
            </div>
            <div class="form-group">
              <label for="division"><?= lang('App.division') ?></label>
              <input id="division" name="division" class="form-control form-control-border form-control-sm" placeholder="Ex. IT Developer">
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-gradient-primary"><?= lang('App.account') ?></div>
          <div class="card-body">
            <div class="form-group">
              <label for="groups"><?= lang('App.usergroup') ?></label>
              <select id="groups" name="groups[]" class="form-control select" data-placeholder="<?= lang('App.usergroup') ?>" style="width:100%" multiple required>
                <?php foreach (\App\Models\UserGroup::get() as $group) : ?>
                  <option value="<?= $group->name ?>"><?= $group->name ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="username"><?= lang('App.username') ?></label>
              <input id="username" name="username" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.username') ?>" required>
            </div>
            <div class="form-group">
              <label for="password"><?= lang('App.password') ?></label>
              <div class="input-group input-group-sm">
                <input type="password" name="password" id="password" class="form-control form-control-border pass" placeholder="<?= lang('App.password') ?>" autocomplete="new-password" required>
                <div class="input-group-append">
                  <span class="input-group-text bg-gradient-warning">
                    <i class="fad fa-fw fa-eye-slash show-pass"></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="active"><?= lang('App.status') ?></label>
              <select id="active" name="active" class="select" style="width:100%;">
                <option value="1"><?= lang('App.active') ?></option>
                <option value="0"><?= lang('App.inactive') ?></option>
              </select>
            </div>
            <div class="form-group">
              <label for="biller"><?= lang('App.biller') ?></label>
              <select id="biller" name="biller" class="form-control select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%">
              </select>
            </div>
            <div class="form-group">
              <label for="warehouse"><?= lang('App.warehouse') ?></label>
              <select id="warehouse" name="warehouse" class="form-control select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%">
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-gradient-warning"><?= lang('App.misc') ?></div>
          <div class="card-body">
            <div class="form-group">
              <label for="billers"><?= lang('App.billeraccess') ?></label>
              <select name="billers[]" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%" multiple>
              </select>
            </div>
            <div class="form-group">
              <label for="warehouses"><?= lang('App.warehouseaccess') ?></label>
              <select name="warehouses[]" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%" multiple>
              </select>
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
    let gender = 'male';
    let oldAvatar = null;

    $('#avatarImg').change(function() {
      if ($(this)[0].files.length) {
        oldAvatar = URL.createObjectURL($(this)[0].files[0]);
        $('#avatar').prop('src', oldAvatar);
      } else {
        $('#avatar').prop('src', base_url + `/attachment/avatar${gender}`);
      }
    });

    $('#gender').change(function() {
      gender = this.value;

      if (!$('#avatarImg')[0].files.length) {
        $('#avatar').prop('src', base_url + `/attachment/avatar${gender}`);
      }
    });

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/humanresource/user/add'
    });
  });
</script>