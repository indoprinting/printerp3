<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success" href="#">
              <i class="fad fa-plus-circle"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-bordered table-hover" style="width:100%;">
            <thead>
              <tr>
                <th></th>
                <th><?= lang('App.date'); ?></th>
                <th><?= lang('App.calldate'); ?></th>
                <th><?= lang('App.servedate'); ?></th>
                <th><?= lang('App.enddate'); ?></th>
                <th><?= lang('App.customer'); ?></th>
                <th><?= lang('App.ticket'); ?></th>
                <th><?= lang('App.category'); ?></th>
                <th><?= lang('App.warehouse'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.counter'); ?></th>
                <th><?= lang('App.cs'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.date'); ?></th>
                <th><?= lang('App.calldate'); ?></th>
                <th><?= lang('App.servedate'); ?></th>
                <th><?= lang('App.enddate'); ?></th>
                <th><?= lang('App.customer'); ?></th>
                <th><?= lang('App.ticket'); ?></th>
                <th><?= lang('App.category'); ?></th>
                <th><?= lang('App.warehouse'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.counter'); ?></th>
                <th><?= lang('App.cs'); ?></th>
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
        url: base_url + '/qms/getQueueTickets'
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
        [1, 'desc']
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