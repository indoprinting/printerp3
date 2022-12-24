<div class="modal-header bg-gradient-indigo">
  <h5 class="modal-title"><i class="fad fa-user-plus"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form method="post" enctype="multipart/form-data" id="form">
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
        <div class="form-group">
          <label for="type"><?= lang('App.groupType') ?></label>
          <select id="type" name="type" class="select2" style="width:100%;">
            <?php foreach ($types as $key => $value): ?>
              <option value="<?= $value ?>"><?= lang('App.' . $key) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-indigo">
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
                  <td class="font-weight-bold"><?= lang('App.classRoom') ?></td>
                  <td><input type="checkbox" name="permission[]" value="ClassRoom.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="ClassRoom.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="ClassRoom.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="ClassRoom.View"></td>
                </tr>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.educationLevel') ?></td>
                  <td><input type="checkbox" name="permission[]" value="EducationLevel.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="EducationLevel.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="EducationLevel.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="EducationLevel.View"></td>
                </tr>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.employee') ?></td>
                  <td><input type="checkbox" name="permission[]" value="Employee.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="Employee.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="Employee.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="Employee.View"></td>
                </tr>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.room') ?></td>
                  <td><input type="checkbox" name="permission[]" value="Room.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="Room.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="Room.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="Room.View"></td>
                </tr>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.schoolYear') ?></td>
                  <td><input type="checkbox" name="permission[]" value="SchoolYear.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="SchoolYear.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="SchoolYear.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="SchoolYear.View"></td>
                </tr>
                <tr>
                  <td class="font-weight-bold"><?= lang('App.student') ?></td>
                  <td><input type="checkbox" name="permission[]" value="Student.Add"></td>
                  <td><input type="checkbox" name="permission[]" value="Student.Delete"></td>
                  <td><input type="checkbox" name="permission[]" value="Student.Edit"></td>
                  <td><input type="checkbox" name="permission[]" value="Student.View"></td>
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
    <?= csrf_field() ?>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-indigo"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/settings/usergroup/add'
    });
  });
</script>