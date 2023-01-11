<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success" href="<?= base_url('humanresource/user/add') ?>"
              data-toggle="modal" data-target="#ModalStatic"
              data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-plus-circle"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-hover table-sm" style="width:100%;">
            <thead>
              <tr>
                <th></th>
                <th><?= lang('App.profileimage'); ?></th>
                <th><?= lang('App.fullname'); ?></th>
                <th><?= lang('App.username'); ?></th>
                <th><?= lang('App.phone'); ?></th>
                <th><?= lang('App.gender'); ?></th>
                <th><?= lang('App.groups'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.warehouse'); ?></th>
                <th><?= lang('App.status'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.profileimage'); ?></th>
                <th><?= lang('App.fullname'); ?></th>
                <th><?= lang('App.username'); ?></th>
                <th><?= lang('App.phone'); ?></th>
                <th><?= lang('App.gender'); ?></th>
                <th><?= lang('App.groups'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.warehouse'); ?></th>
                <th><?= lang('App.status'); ?></th>
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
        data: {
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        method: 'POST',
        url: base_url + '/humanresource/getUsers'
      },
      columnDefs: [{
          targets: [0, 1],
          orderable: false
        }
      ],
      fixedHeader: false,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, lang.App.all]],
      order: [
        [2, 'asc']
      ],
      processing: true,
      responsive: true,
      scrollX: false,
      searchDelay: 1000,
      serverSide: true,
      stateSave: false
    });
  });
</script>