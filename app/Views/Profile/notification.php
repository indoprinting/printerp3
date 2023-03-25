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
          <div class="card-body table-responsive">
            <table class="table table-condensed table-hover table-striped" id="TableModal">
              <thead>
                <tr>
                  <th><?= lang('App.createdat') ?></th>
                  <th><?= lang('App.title') ?></th>
                  <th><?= lang('App.content') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($notifications as $notify) : ?>
                  <?php $scope = getJSON($notify->scope) ?>
                  <?php if (!empty($scope->users)) : ?>
                    <?php if (!in_array(session('login')->user_id, $scope->users)) : ?>
                      <?php continue; ?>
                    <?php endif; ?>
                  <?php endif; ?>
                  <?php if (!empty($scope->usergroups)) : ?>
                    <?php if (!in_array(\App\Models\UserGroup::getRow(['name' => session('login')->groups[0]])->id, $scope->usergroups)) : ?>
                      <?php continue; ?>
                    <?php endif; ?>
                  <?php endif; ?>
                  <?php if (!empty($scope->billers)) : ?>
                    <?php if (!in_array(session('login')->biller_id, $scope->billers)) : ?>
                      <?php continue; ?>
                    <?php endif; ?>
                  <?php endif; ?>
                  <?php if (!empty($scope->warehouses)) : ?>
                    <?php if (!in_array(session('login')->warehouse_id, $scope->warehouses)) : ?>
                      <?php continue; ?>
                    <?php endif; ?>
                  <?php endif; ?>
                  <tr>
                    <td><?= $notify->created_at ?></td>
                    <td class="bg-gradient-<?= $notify->type ?> font-weight-bolder"><?= $notify->title ?></td>
                    <td><?= $notify->note ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <th><?= lang('App.createdat') ?></th>
                  <th><?= lang('App.title') ?></th>
                  <th><?= lang('App.content') ?></th>
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
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    "use strict";

    erp.tableModal = $('#TableModal').DataTable({
      columnDefs: [{
        targets: [1, 2],
        orderable: false
      }],
      fixedHeader: false,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, lang.App.all]
      ],
      order: [
        [0, 'desc']
      ],
      processing: true,
      responsive: true,
      rowCallback: (row, data) => {
        if (data[0] == 'danger') {
          $(row).addClass('bg-gradient-danger');
        }
        if (data[0] == 'info') {
          $(row).addClass('bg-gradient-info');
        }
        if (data[0] == 'success') {
          $(row).addClass('bg-gradient-success');
        }
        if (data[0] == 'warning') {
          $(row).addClass('bg-gradient-warning');
        }
      },
      scrollX: false,
      searchDelay: 1000,
      stateSave: false
    });
  });
</script>