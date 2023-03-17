<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <div class="card-tools">
            <a id="export" class="btn btn-tool bg-gradient-success" href="<?= base_url('report/export/getPayments') ?>" data-action="export">
              <i class="fad fa-download"></i>
            </a>
            <a class="btn btn-tool bg-gradient-warning" href="#" data-widget="control-sidebar" data-toggle="tooltip" title="Filter" data-slide="true">
              <i class="fad fa-filter"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="Table" class="table table-bordered table-hover" style="width:100%;">
            <thead>
              <tr>
                <th></th>
                <th><?= lang('App.date'); ?></th>
                <th><?= lang('App.referencedate'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.customer'); ?></th>
                <th><?= lang('App.bankaccount'); ?></th>
                <th><?= lang('App.method'); ?></th>
                <th><?= lang('App.amount'); ?></th>
                <th><?= lang('App.type'); ?></th>
                <th><?= lang('App.note'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th></th>
                <th><?= lang('App.date'); ?></th>
                <th><?= lang('App.referencedate'); ?></th>
                <th><?= lang('App.reference'); ?></th>
                <th><?= lang('App.createdby'); ?></th>
                <th><?= lang('App.biller'); ?></th>
                <th><?= lang('App.customer'); ?></th>
                <th><?= lang('App.bankaccount'); ?></th>
                <th><?= lang('App.method'); ?></th>
                <th><?= lang('App.amount'); ?></th>
                <th><?= lang('App.type'); ?></th>
                <th><?= lang('App.note'); ?></th>
                <th><?= lang('App.createdat'); ?></th>
                <th><?= lang('App.attachment'); ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Control Sidebar -->
<aside class="control-sidebar">
  <!-- Control sidebar content goes here -->
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-indigo">
          <div class="card-title"><i class="fad fa-filter"></i> <?= lang('App.filter') ?></div>
        </div>
        <div class="card-body control-sidebar-content" style="max-height:400px">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-biller"><?= lang('App.biller') ?></label>
                <select id="filter-biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-status"><?= lang('App.status') ?></label>
                <select id="filter-status" class="select-allow-clear" data-placeholder="<?= lang('App.status') ?>" style="width:100%" multiple>
                  <option value="received"><?= lang('Status.received') ?></option>
                  <option value="sent"><?= lang('Status.sent') ?></option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-createdby"><?= lang('App.createdby') ?></label>
                <select id="filter-createdby" class="select-user" data-placeholder="<?= lang('App.createdby') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-customer"><?= lang('App.customer') ?></label>
                <select id="filter-customer" class="select-customer" data-placeholder="<?= lang('App.customer') ?>" style="width:100%" multiple>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-startdate"><?= lang('App.startdate') ?></label>
                <input type="date" id="filter-startdate" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-enddate"><?= lang('App.enddate') ?></label>
                <input type="date" id="filter-enddate" class="form-control">
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn btn-warning filter-clear"><?= lang('App.clear') ?></button>
          <button class="btn btn-primary filter-apply"><?= lang('App.apply') ?></button>
        </div>
      </div>
    </div>
  </div>
</aside>
<!-- /.control-sidebar -->
<script type="module">
  import {
    TableFilter
  } from '<?= base_url('assets/app/js/ridintek.js?v=') . $resver ?>';

  TableFilter
    .bind('apply', '.filter-apply')
    .bind('clear', '.filter-clear')
    .on('clear', () => {
      $('#filter-biller').val([]).trigger('change');
      $('#filter-warehouse').val([]).trigger('change');
      $('#filter-status').val([]).trigger('change');
      $('#filter-paymentstatus').val([]).trigger('change');
      $('#filter-createdby').val([]).trigger('change');
      $('#filter-receivable').iCheck('uncheck');
      $('#filter-startdate').val('');
      $('#filter-enddate').val('');
    });
</script>
<script>
  $(document).ready(function() {
    "use strict";

    erp.table = $('#Table').DataTable({
      ajax: {
        data: (data) => {
          data.__ = __;

          let billers = $('#filter-biller').val();
          let createdBy = $('#filter-createdby').val();
          let customers = $('#filter-customer').val();
          let status = $('#filter-status').val();
          let startDate = $('#filter-startdate').val();
          let endDate = $('#filter-enddate').val();

          if (billers) {
            data.biller = billers;
          }

          if (createdBy) {
            data.created_by = createdBy;
          }

          if (customers) {
            data.customer = customers;
          }

          if (status) {
            data.status = status;
          }

          if (startDate) {
            data.start_date = startDate;
          }

          if (endDate) {
            data.end_date = endDate;
          }

          return data;
        },
        method: 'POST',
        url: base_url + '/report/getPayments'
      },
      columnDefs: [{
        targets: [0, 13],
        orderable: false
      }],
      fixedHeader: false,
      footerCallback: function(row, data, start, end, display) {
        let api = this.api();
        let columns = api.columns([9, 10]).data();
        let total = 0;

        for (let a = 0; a < columns[0].length; a++) {
          if (columns[1][a].search(/received/i) >= 0 || columns[1][a].search(/diterima/i) >= 0) {
            total += filterNumber(columns[0][a]);
          } else if (columns[1][a].search(/sent/i) >= 0 || columns[1][a].search(/terkirim/i) >= 0) {
            total -= filterNumber(columns[0][a]);
          } else {
            console.warn('Type is not received nor sent: ' + columns[1][a]);
          }
        }

        $(api.column(9).footer()).html(`<span class="float-right">${formatNumber(total)}</span>`);
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