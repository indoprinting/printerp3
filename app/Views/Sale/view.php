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
          <th><?= lang('App.area') ?></th>
          <th><?= lang('App.quantity') ?></th>
          <th><?= lang('App.price') ?></th>
          <th><?= lang('App.subtotal') ?></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="7"><span class="float-right"><?= lang('App.grandtotal') ?></span></td>
          <td><span class="float-right grandtotal"></span></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let itemCodes = $('[name="item[code][]"]');
    let itemNames = $('[name="item[name][]"]');
    let itemSpecs = $('[name="item[spec][]"]');
    let itemWidths = $('[name="item[width][]"]');
    let itemLengths = $('[name="item[length][]"]');
    let itemAreas = $('[name="item[area][]"]');
    let itemQuantities = $('[name="item[quantity][]"]');
    let itemPrices = $('[name="item[price][]"]');
    let tbody = $('#table-preview tbody');
    let gtotal = $('#table-preview .grandtotal');
    let grandTotal = 0;

    for (let a = 0; a < itemCodes.length; a++) {
      let price     = filterDecimal(itemPrices[a].value);
      let subTotal  = (itemWidths[a].value * itemLengths[a].value * itemQuantities[a].value * price);
      grandTotal    += subTotal;

      tbody.append(`
        <tr>
          <td>(${itemCodes[a].value}) ${itemNames[a].value}</td>
          <td>${itemSpecs[a].value}</td>
          <td><span class="float-right">${itemWidths[a].value}</span></td>
          <td><span class="float-right">${itemLengths[a].value}</span></td>
          <td><span class="float-right">${itemAreas[a].value}</span></td>
          <td><span class="float-right">${itemQuantities[a].value}</span></td>
          <td><span class="float-right">${formatCurrency(price)}</span></td>
          <td><span class="float-right">${formatCurrency(subTotal)}</span></td>
        </tr>
      `);

      gtotal.html(formatCurrency(grandTotal));
    }
  });
</script>