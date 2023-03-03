<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-money-bill"></i> <?= $title . " ({$modeLang})" ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <table id="ModalTable" class="table table-bordered table-hover">
    <thead>
      <tr>
        <th class="col-sm-1"></th>
        <th><?= lang('App.date') ?></th>
        <th><?= lang('App.reference') ?></th>
        <th><?= lang('App.bankaccount') ?></th>
        <th><?= lang('App.biller') ?></th>
        <th><?= lang('App.amount') ?></th>
        <th><?= lang('App.type') ?></th>
        <th><?= lang('App.attachment') ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th></th>
        <th><?= lang('App.date') ?></th>
        <th><?= lang('App.reference') ?></th>
        <th><?= lang('App.bankaccount') ?></th>
        <th><?= lang('App.biller') ?></th>
        <th></th>
        <th><?= lang('App.type') ?></th>
        <th><?= lang('App.attachment') ?></th>
      </tr>
    </tfoot>
  </table>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    'use strict';

    let tableData = JSON.parse(`<?= json_encode($params) ?>`);

    tableData.<?= csrf_token() ?> = '<?= csrf_hash() ?>';

    erp.tableModal = $('#ModalTable').DataTable({
      ajax: {
        data: tableData,
        method: 'POST',
        url: base_url + '/payment/getPayments'
      },
      columnDefs: [{
        targets: [7],
        orderable: false
      }],
      fixedHeader: false,
      footerCallback: function(row, data, start, end, display) {
        let api = this.api();
        let columns = api.columns([5, 6]).data();
        let total = 0;

        for (let a = 0; a < columns[0].length; a++) {
          if (columns[1][a].search(/received/i) >= 0) {
            total += filterNumber(columns[0][a]);
          } else if (columns[1][a].search(/sent/i) >= 0) {
            total -= filterNumber(columns[0][a]);
          } else {
            console.warn('Type is not received nor sent.');
          }
        }

        $(api.column(5).footer()).html(`<span class="float-right">${formatNumber(total)}</span>`);
      },
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