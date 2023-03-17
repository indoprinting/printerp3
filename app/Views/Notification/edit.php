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
                  <label for="type"><?= lang('App.type') ?> *</label>
                  <select id="type" name="type" class="select" style="width:100%">
                    <option value="info"><?= lang('Status.info') ?></option>
                    <option value="danger"><?= lang('Status.danger') ?></option>
                    <option value="success"><?= lang('Status.success') ?></option>
                    <option value="warning"><?= lang('Status.warning') ?></option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="status"><?= lang('App.status') ?> *</label>
                  <select id="status" name="status" class="select" style="width:100%">
                    <option value="active"><?= lang('Status.active') ?></option>
                    <option value="pending"><?= lang('Status.pending') ?></option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="title"><?= lang('App.title') ?> *</label>
                  <input id="title" name="title" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.title') ?>">
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
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-primary">
            <?= lang('App.scope') ?>
          </div>
          <div class="card-body">
            <input id="scope" name="scope" type="hidden" value="">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="biller"><?= lang('App.biller') ?></label>
                  <select id="biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%" multiple>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="warehouse"><?= lang('App.warehouse') ?></label>
                  <select id="warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%" multiple>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="usergroup"><?= lang('App.usergroup') ?></label>
                  <select id="usergroup" class="select-usergroup" data-placeholder="<?= lang('App.usergroup') ?>" style="width:100%" multiple>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="user"><?= lang('App.user') ?></label>
                  <select id="user" class="select-user" data-placeholder="<?= lang('App.user') ?>" style="width:100%" multiple>
                  </select>
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
    $('#scope').val('<?= $notification->scopes ?>');
    $('#status').val('<?= $notification->status ?>').trigger('change');
    $('#title').val('<?= $notification->title ?>');
    $('#type').val('<?= $notification->type ?>').trigger('change');

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.root.innerHTML = `<?= $notification->note ?>`;

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#biller').change(function() {
      let v = JSON.parse($('#scope').val());
      v.billers = $(this).val();
      $('#scope').val(JSON.stringify(v));
    });

    $('#user').change(function() {
      let v = JSON.parse($('#scope').val());
      v.users = $(this).val();
      $('#scope').val(JSON.stringify(v));
    });

    $('#usergroup').change(function() {
      let v = JSON.parse($('#scope').val());
      v.usergroups = $(this).val();
      $('#scope').val(JSON.stringify(v));
    });

    $('#warehouse').change(function() {
      let v = JSON.parse($('#scope').val());
      v.warehouses = $(this).val();
      $('#scope').val(JSON.stringify(v));
    });

    let scope = JSON.parse($('#scope').val());

    preSelect2('biller', '#biller', scope.billers).catch(err => console.warn(err));;
    preSelect2('user', '#user', scope.users).catch(err => console.warn(err));;
    preSelect2('usergroup', '#usergroup', scope.usergroups).catch(err => console.warn(err));
    preSelect2('warehouse', '#warehouse', scope.warehouses).catch(err => console.warn(err));;

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/notification/edit/<?= $notification->id ?>'
    });
  });
</script>