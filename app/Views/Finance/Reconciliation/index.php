<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success" href="<?= base_url('finance/reconciliation/sync') ?>" data-action="confirm" data-widget="tooltip" title="Sync Bank Reconciliation">
              <i class="fad fa-sync"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-bordered table-hover" style="width:100%;">
            <thead>
              <tr>
                <th>MB Bank Name</th>
                <th><?= lang('App.accountnumber'); ?></th>
                <th>MB Amount</th>
                <th>ERP Amount</th>
                <th><?= lang('App.balance'); ?></th>
                <th>MB Account Name</th>
                <th>ERP Account Name</th>
                <th>Last MB Sync</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>MB Bank Name</th>
                <th><?= lang('App.accountnumber'); ?></th>
                <th>MB Amount</th>
                <th>ERP Amount</th>
                <th><?= lang('App.balance'); ?></th>
                <th>MB Account Name</th>
                <th>ERP Account Name</th>
                <th>Last MB Sync</th>
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
        url: base_url + '/finance/getReconciliations'
      },
      columnDefs: [],
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
      scrollX: false,
      searchDelay: 1000,
      serverSide: true,
      stateSave: false
    });
  });
</script>