<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-magnifying-glass"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <table class="table table-hover table-sm table-striped">
            <tbody>
              <tr>
                <td><?= lang('App.id') ?></td>
                <td><?= $mutation->id ?></td>
              </tr>
              <tr>
                <td><?= lang('App.date') ?></td>
                <td><?= formatDateTime($mutation->date) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.warehousefrom') ?></td>
                <td><?= \App\Models\Warehouse::getRow(['id' => $mutation->from_warehouse_id])->name ?></td>
              </tr>
              <tr>
                <td><?= lang('App.warehouseto') ?></td>
                <td><?= \App\Models\Warehouse::getRow(['id' => $mutation->to_warehouse_id])->name ?></td>
              </tr>
              <tr>
                <td><?= lang('App.status') ?></td>
                <td><?= renderStatus($mutation->status) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.note') ?></td>
                <td><?= $mutation->note ?></td>
              </tr>
              <tr>
                <td><?= lang('App.createdat') ?></td>
                <td><?= formatDateTime($mutation->created_at) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.createdby') ?></td>
                <td><?= \App\Models\User::getRow(['id' => $mutation->created_by])->fullname ?></td>
              </tr>
              <?php if ($mutation->updated_at) : ?>
                <tr>
                  <td><?= lang('App.updatedat') ?></td>
                  <td><?= formatDateTime($mutation->updated_at) ?></td>
                </tr>
              <?php endif; ?>
              <?php if ($mutation->updated_by) : ?>
                <tr>
                  <td><?= lang('App.updatedby') ?></td>
                  <td><?= \App\Models\User::getRow(['id' => $mutation->created_by])->fullname ?></td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-warning"><?= lang('App.item') ?></div>
        <div class="card-body">
          <form id="form">
            <?= csrf_field() ?>
            <input id="status" name="status" type="hidden" value="">
            <table class="table table-bordered table-hover table-sm table-striped text-center">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Quantity</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (\App\Models\ProductMutationItem::get(['pm_id' => $mutation->id]) as $item) : ?>
                  <?php $product = \App\Models\Product::getRow(['id' => $item->product_id]); ?>
                  <tr>
                    <td><?= $item->id ?></td>
                    <td><?= "($product->code) " . $product->name ?></td>
                    <td><?= formatNumber($item->quantity) ?></td>
                  </tr>
                  <?php if (isCompleted($mutation->status)) : ?>
                    <tr>
                      <td colspan="3">
                        <div class="row text-bold">
                          <div class="col-md-6"><?= lang('App.item') ?></div>
                          <div class="col-md-3"><?= lang('App.quantity') ?></div>
                          <div class="col-md-3"><?= lang('App.status') ?></div>
                        </div>
                        <?php $stocks = \App\Models\Stock::get(['pm_id' => $mutation->id, 'product_id' => $product->id]) ?>
                        <?php foreach ($stocks as $stock) : ?>
                          <?php $itemName = "({$stock->product_code}) {$stock->product_name}"; ?>
                          <div class="row mb-1">
                            <!-- <div><?= $stock->id ?></div> -->
                            <div class="col-md-6 use-tooltip" title="<?= '(' . $stock->product_code . ') ' . $stock->product_name ?>">
                              <?= getExcerpt($itemName, 30) ?>
                            </div>
                            <div class="col-md-3"><?= $stock->quantity ?></div>
                            <div class="col-md-3"><?= renderStatus($stock->status) ?></div>
                          </div>
                        <?php endforeach ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <?php if ($mutation->status == 'packing' && hasAccess('ProductMutation.Complete')) : ?>
    <button type="button" class="btn bg-gradient-primary commit-status status-completed"><i class="fad fa-fw fa-check"></i> <?= lang('App.complete') ?></button>
  <?php endif; ?>
  <?php if ($mutation->status == 'completed' && hasAccess('ProductMutation.Packing')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-packing"><i class="fad fa-fw fa-box-open-full"></i> <?= lang('Status.packing') ?></button>
  <?php endif; ?>
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    $('.commit-status').click(function() {
      let status = '';

      if (this.classList.contains('status-packing')) {
        status = 'packing';
      } else if (this.classList.contains('status-completed')) {
        status = 'completed';
      }

      $('#status').val(status);
    });

    initModalForm({
      form: '#form',
      submit: '.commit-status',
      url: base_url + '/inventory/mutation/status/<?= $mutation->id ?>'
    });
  });
</script>