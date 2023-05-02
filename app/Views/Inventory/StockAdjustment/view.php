<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-magnifying-glass"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12">
      <table class="table table-hover table-sm table-striped">
        <tbody>
          <tr>
            <td><?= lang('App.id') ?></td>
            <td><?= $adjustment->id ?></td>
          </tr>
          <tr>
            <td><?= lang('App.date') ?></td>
            <td><?= formatDate($adjustment->date) ?></td>
          </tr>
          <tr>
            <td><?= lang('App.reference') ?></td>
            <td><?= $adjustment->reference ?></td>
          </tr>
          <tr>
            <td><?= lang('App.warehouse') ?></td>
            <td><?= \App\Models\Warehouse::getRow(['id' => $adjustment->warehouse_id])->name ?></td>
          </tr>
          <tr>
            <td><?= lang('App.mode') ?></td>
            <td><?= renderStatus($adjustment->mode) ?></td>
          </tr>
          <tr>
            <td><?= lang('App.note') ?></td>
            <td><?= $adjustment->note ?></td>
          </tr>
          <tr>
            <td><?= lang('App.createdat') ?></td>
            <td><?= formatDate($adjustment->created_at) ?></td>
          </tr>
          <tr>
            <td><?= lang('App.createdby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $adjustment->created_by])->fullname ?></td>
          </tr>
          <?php if ($adjustment->updated_at) : ?>
            <tr>
              <td><?= lang('App.updatedat') ?></td>
              <td><?= formatDate($adjustment->updated_at) ?></td>
            </tr>
          <?php endif; ?>
          <?php if ($adjustment->updated_by) : ?>
            <tr>
              <td><?= lang('App.updatedby') ?></td>
              <td><?= \App\Models\User::getRow(['id' => $adjustment->created_by])->fullname ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-warning"><?= lang('App.item') ?></div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-sm table-striped">
              <thead>
                <tr class="text-center">
                  <th>ID</th>
                  <th>Name</th>
                  <th>Quantity</th>
                  <th>Adjustment Qty</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php $items = \App\Models\Stock::get(['adjustment_id' => $adjustment->id]) ?>
                <?php foreach ($items as $item) : ?>
                  <tr>
                    <td><span class="float-right"><?= $item->id ?></span></td>
                    <td><?= "($item->product_code) " . $item->product_name ?></td>
                    <td><span class="float-right"><?= formatNumber($item->quantity) ?></span></td>
                    <td><span class="float-right"><?= formatNumber($item->adjustment_qty) ?></span></td>
                    <td><?= renderStatus($item->status) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {});
</script>