<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-indigo">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success" href="<?= base_url('settings/usergroup/add') ?>" data-toggle="modal"
              data-target="#ModalStatic" data-modal-class="modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-plus-circle"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-bordered table-hover" style="width:100%;">
            <thead>
              <tr>
                <th></th>
                <th><?= lang('App.groupName'); ?></th>
                <th><?= lang('App.groupType'); ?></th>
                <th><?= lang('App.permission'); ?></th>
                <th><?= lang('App.createdAt'); ?></th>
                <th><?= lang('App.createdBy'); ?></th>
                <th><?= lang('App.updatedAt'); ?></th>
                <th><?= lang('App.updatedBy'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.groupName'); ?></th>
                <th><?= lang('App.groupType'); ?></th>
                <th><?= lang('App.permission'); ?></th>
                <th><?= lang('App.createdAt'); ?></th>
                <th><?= lang('App.createdBy'); ?></th>
                <th><?= lang('App.updatedAt'); ?></th>
                <th><?= lang('App.updatedBy'); ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    "use strict";

    window.Table = $('#Table').DataTable({
      ajax: {
        data: function(data) {
          data['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
        },
        method: 'POST',
        url: base_url + '/settings/getUserGroups'
      },
      columnDefs: [{
        targets: 0,
        orderable: false
      }],
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, lang.App.all]
      ],
      order: [
        [1, 'asc']
      ],
      processing: true,
      scrollX: true,
      searchDelay: 750,
      serverSide: true,
      stateSave: true
    });
  });
</script>