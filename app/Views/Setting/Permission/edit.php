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
              <div class="col-md-12">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?></label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.permissionname') ?>" value="<?= $permission->name ?>" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="action"><?= lang('App.action') ?></label>
                  <select name="action[]" class="select-allow-clear-tags" data-placeholder="<?= lang('App.permissionaction') ?>" style="width:100%" multiple>
                    <option value="Add">Add</option>
                    <option value="Delete">Delete</option>
                    <option value="Edit">Edit</option>
                    <option value="View">View</option>
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
  <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let actions = <?= $permission->actions ?? '[]' ?>;

    if (actions) {
      for (let act of actions) {
        if (!['Add', 'Delete', 'Edit', 'View'].includes(act)) {
          $('select[name="action[]"]').append(`<option value="${act}">${act}</option>`);
        }
      }

      $('select[name="action[]"]').val(actions).trigger('change');
    }

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/setting/permission/edit/<?= $permission->id ?>'
    });
  });
</script>