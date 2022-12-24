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
          <label for="groupname"><?= lang('App.groupName') ?></label>
          <input id="groupname" name="groupname" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.groupName') ?>" required>
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
            <table class="table table-condensed">
              <thead>
                <tr>
                  <th><?= lang('App.permission') ?></th>
                  <th><?= lang('App.add') ?></th>
                  <th><?= lang('App.delete') ?></th>
                  <th><?= lang('App.edit') ?></th>
                  <th><?= lang('App.view') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (hasAccess('All')) : ?>
                  <tr>
                    <td class="font-weight-bold"><?= lang('App.allAccess') ?></td>
                    <td colspan="4" class="text-center">
                      <input type="checkbox" name="permission[]" value="All">
                    </td>
                  </tr>
                <?php endif; ?>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.bankAccount') ?></td>
                  <td><input type="checkbox" name="permission[]" value="BankAccount.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="BankAccount.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="BankAccount.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="BankAccount.View"></td>
                </tr>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.bankMutation') ?></td>
                  <td><input type="checkbox" name="permission[]" value="BankMutation.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="BankMutation.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="BankMutation.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="BankMutation.View"></td>
                </tr>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.user') ?></td>
                  <td><input type="checkbox" name="permission[]" value="User.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="User.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="User.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="User.View"></td>
                </tr>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.userGroup') ?></td>
                  <td><input type="checkbox" name="permission[]" value="UserGroup.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="UserGroup.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="UserGroup.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="UserGroup.View"></td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <th><?= lang('App.permission') ?></th>
                  <th><?= lang('App.add') ?></th>
                  <th><?= lang('App.delete') ?></th>
                  <th><?= lang('App.edit') ?></th>
                  <th><?= lang('App.view') ?></th>
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
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/humanresource/usergroup/add'
    });
  });
</script>