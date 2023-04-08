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
            <td><?= $internalUse->id ?></td>
          </tr>
          <tr>
            <td><?= lang('App.date') ?></td>
            <td><?= formatDate($internalUse->date) ?></td>
          </tr>
          <tr>
            <td><?= lang('App.reference') ?></td>
            <td><?= $internalUse->reference ?></td>
          </tr>
          <tr>
            <td><?= lang('App.warehousefrom') ?></td>
            <td><?= \App\Models\Warehouse::getRow(['id' => $internalUse->from_warehouse_id])->name ?></td>
          </tr>
          <tr>
            <td><?= lang('App.warehouseto') ?></td>
            <td><?= \App\Models\Warehouse::getRow(['id' => $internalUse->to_warehouse_id])->name ?></td>
          </tr>
          <tr>
            <td><?= lang('App.category') ?></td>
            <td><?= renderStatus($internalUse->category) ?></td>
          </tr>
          <tr>
            <td><?= lang('App.status') ?></td>
            <td><?= renderStatus($internalUse->status) ?></td>
          </tr>
          <tr>
            <td><?= lang('App.note') ?></td>
            <td><?= $internalUse->note ?></td>
          </tr>
          <tr>
            <td><?= lang('App.createdat') ?></td>
            <td><?= formatDate($internalUse->created_at) ?></td>
          </tr>
          <tr>
            <td><?= lang('App.createdby') ?></td>
            <td><?= \App\Models\User::getRow(['id' => $internalUse->created_by])->fullname ?></td>
          </tr>
          <?php if ($internalUse->updated_at) : ?>
            <tr>
              <td><?= lang('App.updatedat') ?></td>
              <td><?= formatDate($internalUse->updated_at) ?></td>
            </tr>
          <?php endif; ?>
          <?php if ($internalUse->updated_by) : ?>
            <tr>
              <td><?= lang('App.updatedby') ?></td>
              <td><?= \App\Models\User::getRow(['id' => $internalUse->created_by])->fullname ?></td>
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
                <?php $items = \App\Models\Stock::get(['internal_use_id' => $internalUse->id]) ?>
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
  <?php if (hasAccess('InternalUse.Approval') && $internalUse->status == 'need_approval') : ?>
    <button type="button" class="btn bg-gradient-success"><i class="fad fa-fw fa-times"></i> <?= lang('App.approve') ?></button>
  <?php else if (hasAccess('InternalUse.Packing') && $internalUse->status == 'approved') : ?>
    <button type="button" class="btn bg-gradient-success"><i class="fad fa-fw fa-times"></i> <?= lang('Status.packing') ?></button>
  <?php else if ($internalUse->status == 'packing') : ?>
    <?php if (hasAccess('InternalUse.Cancel')) : ?>
      <button type="button" class="btn bg-gradient-success"><i class="fad fa-fw fa-times"></i> <?= lang('Status.cancel') ?></button>
    <?php endif; ?>
    <?php if (hasAccess('InternalUse.Install')) : ?>
      <button type="button" class="btn bg-gradient-success"><i class="fad fa-fw fa-times"></i> <?= lang('Status.install') ?></button>
    <?php endif; ?>
  <?php else if (hasAccess('InternalUse.Install') && $internalUse->status == 'packing') : ?>
  <?php endif; ?>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {

  });
</script>