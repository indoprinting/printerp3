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
              <div class="col-md-6">
                <div class="form-group">
                  <label for="date"><?= lang('App.date') ?></label>
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="warehouse"><?= lang('App.warehouse') ?> *</label>
                  <select id="warehouse" name="warehouse" class="select" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%" placeholder="<?= lang('App.warehouse') ?>">
                    <option value=""></option>
                    <?php foreach (\App\Models\Warehouse::get(['active' => 1]) as $wh) : ?>
                      <option value="<?= $wh->code ?>"><?= $wh->name ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="mode"><?= lang('App.mode') ?> *</label>
                  <select class="select" name="mode" data-placeholder="<?= lang('App.mode') ?>" style="width:100%" placeholder="<?= lang('App.mode') ?>">
                    <option value="overwrite"><?= lang('App.overwrite') ?></option>
                    <option value="formula"><?= lang('App.formula') ?></option>
                  </select>
                </div>
              </div>
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
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-primary"><?= lang('App.product') ?></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <select id="product" class="select-product-standard" data-placeholder="<?= lang('App.product') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-12">
                <table id="table-stockadjustment" class="table">
                  <thead>
                    <tr>
                      <th><?= lang('App.name') ?></th>
                      <th><?= lang('App.quantity') ?></th>
                      <th><?= lang('App.currentstock') ?></th>
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
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.save') ?></button>
</div>
<script type="module">
  import {
    StockAdjustment
  } from "<?= base_url('assets/app/js/ridintek.js?v=' . $resver); ?>";

  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let sa = new StockAdjustment('#table-stockadjustment');
    let items = JSON.parse('<?= json_encode($items) ?>');

    if (items) {
      for (let item of items) {
        sa.addItem(item);
      }
    } else {
      console.warn('Items are empty.');
    }

    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#product').change(function() {
      if (!this.value) return false;

      let warehouse = $('#warehouse').val();

      if (!warehouse) {
        toastr.error('Warehouse is required.');

        $(this).val('').trigger('change');

        return false;
      }

      $.ajax({
        data: {
          code: this.value,
          warehouse: warehouse
        },
        success: (data) => {
          sa.addItem(data.data);

          $(this).val('').trigger('change');
        },
        url: base_url + '/api/v1/product'
      });
    });

    editor.root.innerHTML = `<?= $adjustment->note ?>`;
    $('#date').val('<?= dateTimeJS($adjustment->date) ?>').trigger('change');
    $('#mode').val('<?= $adjustment->mode ?>').trigger('change');
    $('#warehouse').val('<?= $adjustment->warehouse ?>').trigger('change');

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/inventory/stockadjustment/add'
    });
  });
</script>