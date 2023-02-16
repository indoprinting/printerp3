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
              <div class="col-md-4">
                <div class="form-group">
                  <label for="date"><?= lang('App.date') ?></label>
                  <input type="datetime-local" id="date" name="date" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="biller"><?= lang('App.biller') ?> *</label>
                  <select id="biller" name="biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%" placeholder="<?= lang('App.biller') ?>">
                    <option value=""></option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="warehouse"><?= lang('App.warehouse') ?> *</label>
                  <select id="warehouse" name="warehouse" class="select-warehouse" data-placeholder="<?= lang('App.warehouse') ?>" style="width:100%" placeholder="<?= lang('App.warehouse') ?>">
                    <option value=""></option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="cashier"><?= lang('App.cashier') ?> *</label>
                  <select id="cashier" name="cashier" class="select-user" data-placeholder="<?= lang('App.cashier') ?>" style="width:100%" placeholder="<?= lang('App.cashier') ?>">
                    <option value=""></option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="customer"><?= lang('App.customer') ?> *</label>
                  <div class="input-group">
                    <?php if (hasAccess('Customer.Add')) : ?>
                      <div class="input-group-prepend">
                        <a href="<?= base_url('humanresource/customer/add') ?>" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#ModalDefault"><i class="fad fa-user-plus"></i></a>
                      </div>
                    <?php endif; ?>
                    <select id="customer" name="customer" class="select-customer" data-placeholder="<?= lang('App.customer') ?>" style="width:100%" placeholder="<?= lang('App.customer') ?>">
                      <option value=""></option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="duedate"><?= lang('App.duedate') ?></label>
                  <input type="datetime-local" id="duedate" name="duedate" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="attachment"><?= lang('App.attachment') ?></label>
                  <div class="custom-file">
                    <input type="file" id="attachment" name="attachment" class="custom-file-input">
                    <label for="attachment" class="custom-file-label"><?= lang('App.choosefile') ?></label>
                  </div>
                </div>
              </div>
              <?php if (hasAccess(['Sale.Approve', 'Sale.RawMaterial'])) : ?>
                <div class="col-md-4">
                  <?php if (hasAccess('Sale.RawMaterial')) : ?>
                    <div class="form-group">
                      <input type="checkbox" id="rawmaterial" name="rawmaterial">
                      <label for="rawmaterial"><?= lang('App.rawmaterial') ?></label>
                    </div>
                  <?php endif; ?>
                  <?php if (hasAccess('Sale.Approve')) : ?>
                    <div class="form-group">
                      <input type="checkbox" id="approve" name="approve" value="1">
                      <label for="approve"><?= lang('App.approve') ?></label>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="col-md-4">
                  <?php if (hasAccess('Sale.Approve')) : ?>
                    <div class="form-group">
                      <input type="checkbox" id="transfer" name="transfer" value="1">
                      <label for="transfer"><?= lang('App.transfer') ?></label>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
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
                  <select id="product" class="select-product-sale" data-placeholder="<?= lang('App.product') ?>" style="width:100%">
                  </select>
                </div>
              </div>
              <div class="col-md-12">
                <div class="table-responsive">
                  <table id="table-sale" class="table table-bordered table-hover table-sm">
                    <thead>
                      <tr>
                        <th><?= lang('App.name') ?></th>
                        <th><?= lang('App.option') ?></th>
                        <th><?= lang('App.subtotal') ?></th>
                        <th><i class="fad fa-trash"></i></th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                      <tr>
                        <td colspan="2"><span class="float-right"><?= lang('App.grandtotal') ?></span></td>
                        <td><span class="float-right sale-grandtotal">Rp 0</span></td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
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
  <a href="<?= base_url('sale/preview') ?>" class="btn bg-gradient-info" data-toggle="modal" data-target="#ModalDefault" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
    <i class="fad fa-fw fa-magnifying-glass"></i> <?= lang('App.preview') ?>
  </a>
  <button type="button" id="draft" class="btn bg-gradient-warning"><i class="fad fa-fw fa-pen-ruler"></i> <?= lang('App.draft') ?></button>
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
    Sale
  } from "<?= base_url('assets/app/js/ridintek.js?v=' . $resver); ?>";

  $(document).ready(function() {
    let editor = new Quill('#editor', {
      theme: 'snow'
    });

    window.saleUseRawMaterial = false;

    $('#rawmaterial').on('change', function() {
      if (this.checked) {
        saleUseRawMaterial = true;
      } else {
        saleUseRawMaterial = false;
      }
    });

    editor.on('text-change', (delta, oldDelta, source) => {
      $('[name="note"]').val(editor.root.innerHTML);
    });

    $('#duedate').val('<?= dateTimeJS(date('Y-m-d H:i', strtotime('+7 day'))) ?>');

    $('#product').change(function() {
      if (!this.value) return false;

      let customerId = $('#customer').val();
      let warehouse = $('#warehouse').val();

      if (!customerId) {
        toastr.error('Customer is required.');

        $(this).val('').trigger('change');

        return false;
      }

      if (!warehouse) {
        toastr.error('Warehouse is required.');

        $(this).val('').trigger('change');

        return false;
      }

      $.ajax({
        data: {
          code: this.value,
          customer: customerId,
          warehouse: warehouse
        },
        success: (data) => {
          let sa = new Sale('#table-sale');

          sa.addItem({
            code: data.data.code,
            name: data.data.name,
            category: data.data.category,
            width: 1,
            length: 1,
            quantity: 1,
            spec: '',
            type: data.data.type,
            prices: data.data.prices,
            ranges: data.data.ranges
          }, true);

          initControls();

          $(this).val('').trigger('change');
        },
        url: base_url + '/api/v1/product'
      });
    });

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/sale/add'
    });
  });
</script>