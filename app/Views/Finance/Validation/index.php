<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-bordered table-hover" style="width:100%;">
            <thead>
              <tr>
                <th></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.customer'); ?></th>
                <th><?= lang('App.bankaccount'); ?></th>
                <th><?= lang('App.number'); ?></th>
                <th><?= lang('App.amount'); ?></th>
                <th><?= lang('App.total'); ?></th>
                <th><?= lang('App.expireddate'); ?></th>
                <th><?= lang('App.transactiondate'); ?></th>
                <th><?= lang('App.verifiedat'); ?></th>
                <th><?= lang('App.uniquecode'); ?></th>
                <th><?= lang('App.description'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.customer'); ?></th>
                <th><?= lang('App.bankaccount'); ?></th>
                <th><?= lang('App.number'); ?></th>
                <th><?= lang('App.amount'); ?></th>
                <th><?= lang('App.total'); ?></th>
                <th><?= lang('App.expireddate'); ?></th>
                <th><?= lang('App.transactiondate'); ?></th>
                <th><?= lang('App.verifiedat'); ?></th>
                <th><?= lang('App.uniquecode'); ?></th>
                <th><?= lang('App.description'); ?></th>
                <th><?= lang('App.status'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
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
        url: base_url + '/finance/getPaymentValidations'
      },
      columnDefs: [{
        targets: [0, 16],
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