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
          <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.groupname') ?>" value="<?= $userGroup->name ?>">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-primary">
            <div class="card-title"><?= lang('App.permission') ?></div>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-condensed table-hover table-striped" id="TableModal">
              <thead>
                <tr>
                  <th><?= lang('App.permission') ?></th>
                  <th><?= lang('App.add') ?></th>
                  <th><?= lang('App.delete') ?></th>
                  <th><?= lang('App.edit') ?></th>
                  <th><?= lang('App.view') ?></th>
                  <th><?= lang('App.misc') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (\App\Models\Permission::get() as $permission) : ?>
                  <tr>
                    <?php $actions = getJSON($permission->actions); ?>
                    <?php if (strcasecmp($permission->name, 'All') === 0) : ?>
                      <td class="font-weight-bold"><?= lang('App.allaccess') ?></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td><label>
                          <input type="checkbox" name="permission[]" value="All"> <?= lang('App.all') ?>
                        </label>
                      </td>
                    <?php else : ?>
                      <td class="font-weight-bold"><?= lang('App.' . strtolower($permission->name)) ?></td>
                      <?php if (in_array('Add', $actions)) : ?>
                        <td><input type="checkbox" name="permission[]" value="<?= $permission->name ?>.Add"></td>
                      <?php else : ?>
                        <td></td>
                      <?php endif; ?>
                      <?php if (in_array('Delete', $actions)) : ?>
                        <td><input type="checkbox" name="permission[]" value="<?= $permission->name ?>.Delete"></td>
                      <?php else : ?>
                        <td></td>
                      <?php endif; ?>
                      <?php if (in_array('Edit', $actions)) : ?>
                        <td><input type="checkbox" name="permission[]" value="<?= $permission->name ?>.Edit"></td>
                      <?php else : ?>
                        <td></td>
                      <?php endif; ?>
                      <?php if (in_array('View', $actions)) : ?>
                        <td><input type="checkbox" name="permission[]" value="<?= $permission->name ?>.View"></td>
                      <?php else : ?>
                        <td></td>
                      <?php endif; ?>
                      <td>
                        <?php foreach ($actions as $act) : ?>
                          <?php if (!in_array($act, ['Add', 'Delete', 'Edit', 'View'])) : ?>
                            <label><input type="checkbox" name="permission[]" value="<?= $permission->name ?>.<?= $act ?>"> <?= lang('App.' . strtolower($act)) ?></label>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <th><?= lang('App.permission') ?></th>
                  <th><?= lang('App.add') ?></th>
                  <th><?= lang('App.delete') ?></th>
                  <th><?= lang('App.edit') ?></th>
                  <th><?= lang('App.view') ?></th>
                  <th><?= lang('App.misc') ?></th>
                </tr>
              </tfoot>
            </table>
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
    let permissions = <?= $userGroup->permissions ?? '[]' ?>;

    for (let p of permissions) {
      $(`[value="${p}"]`).iCheck('check');
    }

    $('#TableModal').DataTable({
      columnDefs: [{
        targets: [1, 2, 3, 4, 5],
        orderable: false
      }],
      order: [
        [0, 'asc']
      ],
      paging: false,
      processing: true
    });

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/humanresource/usergroup/edit/<?= $userGroup->id ?>'
    });
  });
</script>