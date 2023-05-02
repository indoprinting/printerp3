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
                <td><?= $internalUse->id ?></td>
              </tr>
              <tr>
                <td><?= lang('App.date') ?></td>
                <td><?= formatDateTime($internalUse->date) ?></td>
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
                <td><?= formatDateTime($internalUse->created_at) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.createdby') ?></td>
                <td><?= \App\Models\User::getRow(['id' => $internalUse->created_by])->fullname ?></td>
              </tr>
              <?php if ($internalUse->updated_at) : ?>
                <tr>
                  <td><?= lang('App.updatedat') ?></td>
                  <td><?= formatDateTime($internalUse->updated_at) ?></td>
                </tr>
              <?php endif; ?>
              <?php if ($internalUse->updated_by) : ?>
                <tr>
                  <td><?= lang('App.updatedby') ?></td>
                  <td><?= \App\Models\User::getRow(['id' => $internalUse->updated_by])->fullname ?></td>
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
            <table class="table table-hover table-sm table-striped">
              <thead>
                <tr class="text-center">
                  <th>ID</th>
                  <th>Name</th>
                  <th>Machine</th>
                  <th>Unique Code</th>
                  <th>Unique Code Replacement</th>
                  <th>Counter</th>
                  <th>Quantity</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php $items = \App\Models\Stock::get(['internal_use_id' => $internalUse->id]) ?>
                <?php $ro = ($internalUse->status != 'packing' ? 'readonly' : '') ?>
                <?php foreach ($items as $item) : ?>
                  <?php $mach = \App\Models\Product::getRow(['id' => $item->machine_id]); ?>
                  <?php $machine = ($mach ? $mach->name : '-'); ?>
                  <tr>
                    <td>
                      <input name="item[id][]" type="hidden" value="<?= $item->product_id ?>">
                      <input name="item[code][]" type="hidden" value="<?= $item->product_code ?>">
                      <span class="float-right"><?= $item->id ?></span>
                    </td>
                    <td><?= "($item->product_code) " . $item->product_name ?></td>
                    <td><?= $machine ?></td>
                    <td><?= $item->unique_code ?></td>
                    <td><?= $item->ucr ?></td>
                    <td><input name="item[counter][]" class="form-control form-control-border form-control-sm" value="<?= $item->spec ?>" <?= $ro ?>></td>
                    <td><span class="float-right"><?= formatNumber($item->quantity) ?></span></td>
                    <td><?= renderStatus($item->status) ?></td>
                  </tr>
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
  <?php if ($internalUse->category == 'sparepart') : ?>
    <?php if ($internalUse->status != 'need_approval' && hasAccess('InternalUse.NeedApproval')) : ?>
      <button type="button" class="btn bg-gradient-warning commit-status status-need_approval"><i class="fad fa-fw fa-check-circle"></i> <?= lang('Status.need_approval') ?></button>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($internalUse->status == 'need_approval' && hasAccess('InternalUse.Approve')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-approved"><i class="fad fa-fw fa-check-circle"></i> <?= lang('App.approve') ?></button>
  <?php elseif ($internalUse->status == 'approved' && hasAccess('InternalUse.Packing')) : ?>
    <button type="button" class="btn bg-gradient-success commit-status status-packing"><i class="fad fa-fw fa-box-open-full"></i> <?= lang('Status.packing') ?></button>
  <?php elseif ($internalUse->status == 'packing') : ?>
    <?php if (hasAccess('InternalUse.Cancel')) : ?>
      <button type="button" class="btn bg-gradient-warning commit-status status-cancelled"><i class="fad fa-fw fa-undo-alt"></i> <?= lang('App.cancel') ?></button>
    <?php endif; ?>
    <?php if (hasAccess('InternalUse.Install')) : ?>
      <button type="button" class="btn bg-gradient-success commit-status status-installed"><i class="fad fa-fw fa-screwdriver-wrench"></i> <?= lang('App.install') ?></button>
    <?php endif; ?>
  <?php elseif ($internalUse->status == 'cancelled' && hasAccess('InternalUse.Return')) : ?>
    <button type="button" class="btn bg-gradient-primary commit-status status-returned"><i class="fad fa-fw fa-undo"></i> <?= lang('App.return') ?></button>
  <?php elseif ($internalUse->status == 'installed' && hasAccess('InternalUse.Complete')) : ?>
    <button type="button" class="btn bg-gradient-primary commit-status status-completed"><i class="fad fa-fw fa-check"></i> <?= lang('App.complete') ?></button>
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

      if (this.classList.contains('status-approved')) {
        status = 'approved';
      } else if (this.classList.contains('status-packing')) {
        status = 'packing';
      } else if (this.classList.contains('status-cancelled')) {
        status = 'cancelled';
      } else if (this.classList.contains('status-installed')) {
        status = 'installed';
      } else if (this.classList.contains('status-returned')) {
        status = 'returned';
      } else if (this.classList.contains('status-completed')) {
        status = 'completed';
      } else if (this.classList.contains('status-need_approval')) {
        status = 'need_approval';
      }

      $('#status').val(status);
    });

    initModalForm({
      form: '#form',
      submit: '.commit-status',
      url: base_url + '/inventory/internaluse/status/<?= $internalUse->id ?>'
    });
  });
</script>