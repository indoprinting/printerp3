<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success" href="<?= base_url('finance/bank/add') ?>" data-toggle="modal" data-target="#ModalStatic" data-modal-class="modal-dialog-centered modal-dialog-scrollable">
              <i class="fad fa-plus-circle"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-bordered table-hover" style="width:100%;">
            <thead>
              <tr>
                <th></th>
                <th><?= lang('App.code'); ?></th>
                <th><?= lang('App.name'); ?></th>
                <th><?= lang('App.number'); ?></th>
                <th><?= lang('App.holder'); ?></th>
                <th><?= lang('App.type'); ?></th>
                <th><?= lang('App.balance'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.biccode'); ?></th>
                <th class="col-sm-2"><?= lang('App.status'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.code'); ?></th>
                <th><?= lang('App.name'); ?></th>
                <th><?= lang('App.number'); ?></th>
                <th><?= lang('App.holder'); ?></th>
                <th><?= lang('App.type'); ?></th>
                <th><?= lang('App.balance'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.biccode'); ?></th>
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

    erp.table = $('#Table').DataTable({
      ajax: {
        data: {
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        method: 'POST',
        url: base_url + '/finance/getBanks'
      },
      columnDefs: [{
        targets: [0],
        orderable: false
      }],
      fixedHeader: false,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, lang.App.all]
      ],
      order: [
        [1, 'asc']
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