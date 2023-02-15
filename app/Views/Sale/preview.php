<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-magnifying-glass"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form id="form">
    <?= csrf_field() ?>
    <table id="table-preview" class="table table-bordered table-hover table-sm table-striped">
      <thead>
        <tr>
          <th><?= lang('App.product') ?></th>
          <th><?= lang('App.spec') ?></th>
          <th><?= lang('App.width') ?></th>
          <th><?= lang('App.length') ?></th>
          <th><?= lang('App.quantity') ?></th>
          <th><?= lang('App.price') ?></th>
          <th><?= lang('App.subtotal') ?></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6"><span class="float-right"><?= lang('App.grandtotal') ?></span></td>
          <td><span class="float-right"></span></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let itemCodes = $('[name="item[code][]"]');
    let itemNames = $('[name="item[name][]"]');
    let tbody = $('#table-preview tbody');

    tbody.append(`
      <tr>
        <td></td>
      </tr>
    `);
    
    console.log(items.length);
  });
</script>