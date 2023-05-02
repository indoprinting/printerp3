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
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="date"><?= lang('App.date') ?></label>
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="pic"><?= lang('App.pic') ?> *</label>
                  <select id="pic" name="pic" class="select-user" data-placeholder="<?= lang('App.pic') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="warehouse"><?= lang('App.warehouse') ?> *</label>
                  <select id="warehouse" name="warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="cycle"><?= lang('App.cycle') ?></label>
                  <input id="cycle" name="cycle" class="form-control form-control-border form-control-sm" readonly>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-primary"><?= lang('App.product') ?></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <a href="<?= base_url('inventory/stockopname/suggestion') ?>" class="btn btn-primary btn-sm use-tooltip" data-action="http-get" title="<?= lang('App.suggestion') ?>"><i class="fad fa-magnifying-glass-plus"></i></a>
                    </div>
                    <select id="product" class="select-product" data-placeholder="<?= lang('App.product') ?>" style="width:100%">
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <table id="table-stockopname" class="table">
                  <thead>
                    <tr>
                      <th><?= lang('App.name') ?></th>
                      <th><?= lang('App.unit') ?></th>
                      <th><?= lang('App.quantity') ?></th>
                      <th><?= lang('App.reject') ?></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-success"><?= lang('App.misc') ?></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="attachment"><?= lang('App.attachment') ?></label>
                  <div class="custom-file">
                    <input type="file" id="attachment" name="attachment" class="custom-file-input">
                    <label for="attachment" class="custom-file-label"><?= lang('App.choosefile') ?></label>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="editor"><?= lang('App.note') ?></label>
                  <div id="editor"></div>
                  <input type="hidden" name="note">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>
<script type="module">
  import {
    StockOpname
  } from "<?= base_url('assets/app/js/ridintek.js?v=' . $resver); ?>";

  $(document).ready(function() {
    erp.select2.product.type = ['service', 'standard'];

    if (!hasAccess('StockOpname.Edit')) {
      erp.select2.user.id = [erp.user.id];
      erp.select2.product.type = ['none'];
      erp.select2.warehouse.id = [erp.warehouse.id];
    }

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#product').change(function() { // Not used in StockOpname.
      if (!this.value) return false;

      let warehouse = $('#warehouse').val();

      if (!warehouse) {
        toastr.error('Warehouse is required.');

        $(this).val('').trigger('change');

        return false;
      }

      $.ajax({
        data: {
          id: this.value,
          warehouse: warehouse
        },
        success: (data) => {
          let item = data.data[0];

          StockOpname.table('#table-stockopname').addItem({
            id: item.id,
            code: item.code,
            name: item.name,
            quantity: 0,
            reject: 0,
            unit: item.unit
          });

          initControls();

          $(this).val('').trigger('change');
        },
        url: base_url + '/api/v1/product'
      });
    });

    $('#pic').change(function() {
      erp.http.get = {
        pic: this.value,
        warehouse: $('#warehouse').val()
      };
    });

    $('#warehouse').change(function() {
      erp.http.get = {
        pic: $('#pic').val(),
        warehouse: this.value
      };
    });

    erp.http.callback = function(response) {
      $('#cycle').val(response.data.cycle);
      let so = StockOpname.table('#table-stockopname');

      so.clear();

      for (let item of response.data.items) {
        so.addItem({
          id: item.id,
          code: item.code,
          name: item.name,
          quantity: 0,
          reject: 0,
          unit: item.unit
        });
      }

      initControls();
    }

    preSelect2('user', '#pic', erp.user.id).catch(err => console.warn(err));
    preSelect2('warehouse', '#warehouse', erp.warehouse.id).catch(err => console.warn(err));

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/inventory/stockopname/add'
    });
  });
</script>