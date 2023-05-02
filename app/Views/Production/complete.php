<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-user-plus"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form method="post" enctype="multipart/form-data" id="form">
    <?= csrf_field() ?>
    <input name="_dbg" type="hidden" value="0">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="completedate"><?= lang('App.completedate') ?></label>
                  <input type="datetime-local" id="completedate" name="completedate" class="form-control form-control-border form-control-sm">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="operator"><?= lang('App.operator') ?></label>
                  <select id="operator" name="operator" class="select-operator" data-placeholder="<?= lang('App.operator') ?>" style="width:100%">
                  </select>
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
          <div class="card-header bg-gradient-primary"><?= lang('App.saleitem') ?></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <table id="table-saleitem" class="table table-hover table-sm table-striped">
                  <thead>
                    <tr>
                      <th><?= lang('App.invoice') ?></th>
                      <th><?= lang('App.name') ?></th>
                      <th><?= lang('App.quantity') ?></th>
                      <th><?= lang('App.completedqty') ?></th>
                      <th><?= lang('App.quantity') ?></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td colspan="6" class="text-center"><?= lang('App.processing') ?></td>
                    </tr>
                  </tbody>
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
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.complete') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>
<script type="module">
  import {
    SaleItem
  } from "<?= base_url('assets/app/js/ridintek.js?v=' . $resver); ?>";

  $(document).ready(function() {
    let query = '';

    $('.checkbox').each(function() {
      if (this.checked) {
        query += 'id[]=' + this.value + '&';
      }
    });

    if (query) {
      fetch(base_url + '/api/v1/saleitem?' + query)
        .then(response => response.json())
        .then((response) => {
          let items = response.data;

          SaleItem.table('#table-saleitem').clear();

          for (let item of items) {
            SaleItem.table('#table-saleitem').addItem(item);
          }
        })
        .catch((error) => {
          console.warn(error);
        });
    } else {
      SaleItem.table('#table-saleitem')
        .clear()
        .addRow('<td class="text-center" colspan="6">No sale items.</td>', 6);
    }

    $('#completedate').val('<?= dateTimeJS() ?>');
    preSelect2('user', '#operator', '<?= session('login')->user_id ?>');

    if (erp.debug) {
      $('[name="_dbg"]').val(1);
    }

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/production/complete'
    });
  });
</script>