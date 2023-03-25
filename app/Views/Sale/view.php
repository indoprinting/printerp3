<?php $deliveryNote = (getGet('deliverynote') == 1) ?>
<?php $biller = \App\Models\Biller::getRow(['code' => $sale->biller]) ?>
<?php $customer = \App\Models\Customer::getRow(['phone' => $sale->customer]) ?>
<?php $paymentValidation = \App\Models\PaymentValidation::getRow(['sale' => $sale->reference]) ?>
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
                <td><?= lang('App.invoice') ?></td>
                <td><?= $sale->reference ?></td>
              </tr>
              <tr>
                <td><?= lang('App.date') ?></td>
                <td><?= formatDateTime($sale->date) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.status') ?></td>
                <td><?= renderStatus($sale->status) ?></td>
              </tr>
              <tr>
                <?php $approvalStatus = ($saleJS->approved == 1 ? 'approved' : 'need_approval') ?>
                <td><?= lang('App.approvalstatus') ?></td>
                <td><?= renderStatus($approvalStatus) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.paymentstatus') ?></td>
                <td><?= renderStatus($sale->payment_status) ?></td>
              </tr>
              <tr>
                <td><?= lang('App.paymentmethod') ?></td>
                <td><?= $sale->payment_method ? lang('App.' . strtolower($sale->payment_method)) : '-' ?></td>
              </tr>
              <tr>
                <td><?= lang('App.productionplace') ?></td>
                <td><?= \App\Models\Warehouse::getRow(['code' => $sale->warehouse])->name ?></td>
              </tr>
              <?php if (!empty($saleJS->cashier_by)) : ?>
                <tr>
                  <td><?= lang('App.cashier') ?></td>
                  <td><?= \App\Models\User::getRow(['id' => $saleJS->cashier_by])->fullname ?></td>
                </tr>
              <?php endif; ?>
              <tr>
                <td><?= lang('App.source') ?></td>
                <td><?= $saleJS->source ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <span class="text-bold"><?= lang('App.from') ?>:</span>
              <address>
                <div class="font-italic text-bold text-decoration-underline"><?= $biller->company ?></div>
                <div class="row">
                  <div class="col-md-2"><?= lang('App.address') ?></div>
                  <div class="col-md-10">: <?= $biller->address ?></div>
                </div>
                <div class="row">
                  <div class="col-md-2"><?= lang('App.phone') ?></div>
                  <div class="col-md-10">: <?= $biller->phone ?></div>
                </div>
                <div class="row">
                  <div class="col-md-2"><?= lang('App.email') ?></div>
                  <div class="col-md-10">: <?= $biller->email ?></div>
                </div>
              </address>
            </div>
            <div class="col-md-6">
              <span class="text-bold"><?= lang('App.to') ?>:</span>
              <address>
                <div class="font-italic text-bold text-decoration-underline">
                  <?= $customer->name . ($customer->company ? " ({$customer->company})" : '') ?>
                </div>
                <div class="row">
                  <div class="col-md-2"><?= lang('App.address') ?></div>
                  <div class="col-md-10">: <?= $customer->address ?></div>
                </div>
                <div class="row">
                  <div class="col-md-2"><?= lang('App.phone') ?></div>
                  <div class="col-md-10">: <?= $customer->phone ?></div>
                </div>
                <div class="row">
                  <div class="col-md-2"><?= lang('App.email') ?></div>
                  <div class="col-md-10">: <?= $customer->email ?></div>
                </div>
              </address>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <table class="table table-bordered table-striped text-center">
            <thead>
              <tr>
                <th class="col-md-3"><?= lang('App.pic') ?></th>
                <th class="col-md-3"><?= lang('App.note') ?></th>
                <th class="col-md-2"><?= lang('App.paymentdue') ?></th>
                <th class="col-md-2"><?= lang('App.completeestimation') ?></th>
                <?php if ($paymentValidation) : ?>
                  <th class="col-md-2"><?= lang('App.transferlimit') ?></th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= \App\Models\User::getRow(['id' => $sale->created_by])->fullname ?></td>
                <td><?= htmlRemove($sale->note) ?></td>
                <td><?= ($saleJS->payment_due_date ? formatDate($saleJS->payment_due_date) : '-') ?></td>
                <td><?= ($saleJS->est_complete_date ? formatDate($saleJS->est_complete_date) : '-') ?></td>
                <?php if ($paymentValidation) : ?>
                  <td><?= formatDateTime($paymentValidation->expired_at) ?></td>
                <?php endif; ?>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <table class="table table-bordered table-striped text-center">
            <thead>
              <tr>
                <th><?= lang('App.operator') ?></th>
                <th><?= lang('App.product') ?></th>
                <th><?= lang('App.spec') ?></th>
                <th><?= lang('App.width') ?></th>
                <th><?= lang('App.length') ?></th>
                <th><?= lang('App.quantity') ?></th>
                <?php if (!$deliveryNote) : ?>
                  <th><?= lang('App.price') ?></th>
                  <th><?= lang('App.subtotal') ?></th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($saleItems as $saleItem) : ?>
                <?php $saleItemJS = getJSON($saleItem->json) ?>
                <?php $operator = \App\Models\User::getRow(['id' => $saleItemJS->operator_id]); ?>
                <tr>
                  <td><?= ($operator ? $operator->fullname : '') ?></td>
                  <td><span class="float-left"><?= "({$saleItem->product_code}) $saleItem->product_name" ?></span></td>
                  <td><?= $saleItemJS->spec ?></td>
                  <td><?= filterDecimal($saleItemJS->w) ?></td>
                  <td><?= filterDecimal($saleItemJS->l) ?></td>
                  <td><?= filterDecimal($saleItemJS->sqty) ?></td>
                  <?php if (!$deliveryNote) : ?>
                    <td><span class="float-right"><?= formatNumber($saleItem->price) ?></span></td>
                    <td><span class="float-right"><?= formatNumber($saleItem->subtotal) ?></span></td>
                  <?php endif; ?>
                </tr>
                <?php if (isCompleted($saleItem->status)) : ?>
                  <tr>
                    <td colspan="8">
                      <table class="table">
                        <thead>
                          <tr>
                            <th><?= lang('App.id') ?></th>
                            <th><?= lang('App.item') ?></th>
                            <th><?= lang('App.completedate') ?></th>
                            <th><?= lang('App.completedqty') ?></th>
                            <th><?= lang('App.status') ?></th>
                            <th><?= lang('App.createdby') ?></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $stocks = \App\Models\Stock::get(['saleitem_id' => $saleItem->id]) ?>
                          <?php foreach ($stocks as $stock) : ?>
                            <?php $creator = \App\Models\User::getRow(['id' => $stock->created_by]); ?>
                            <tr>
                              <td><?= $stock->id ?></td>
                              <td><?= "({$stock->product_code}) " . $stock->product_name ?></td>
                              <td><?= $stock->date ?></td>
                              <td><?= $stock->quantity ?></td>
                              <td><?= renderStatus($stock->status) ?></td>
                              <td><?= $creator->fullname ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="row pb-5">
    <?php if (!$deliveryNote) : ?>
      <div class="col-md-8">
      </div>
      <div class="col-md-4">
        <div class="table-responsive">
          <table class="table table-hover table-sm table-striped">
            <tr>
              <th><?= lang('App.total') ?>:</th>
              <td><span class="float-right"><?= formatCurrency($sale->total) ?></span></td>
            </tr>
            <tr>
              <th style="width:50%"><?= lang('App.discount') ?>:</th>
              <td><span class="float-right"><?= formatCurrency($sale->discount) ?></span></td>
            </tr>
            <?php if ($sale->tax > 0) : ?>
              <?php $tax = ($sale->total * $sale->tax * 0.01); ?>
              <tr>
                <th><?= lang('App.tax') ?> (<?= floatval($sale->tax) ?>%):</th>
                <td><span class="float-right"><?= formatCurrency($tax) ?></span></td>
              </tr>
            <?php endif; ?>
            <tr>
              <th><?= lang('App.grandtotal') ?>:</th>
              <td><span class="float-right"><?= formatCurrency($sale->grand_total) ?></span></td>
            </tr>
            <tr>
              <th><?= lang('App.paid') ?>:</th>
              <td><span class="float-right"><?= formatCurrency($sale->paid) ?></span></td>
            </tr>
            <tr>
              <th><?= lang('App.debt') ?>:</th>
              <td><span class="float-right"><?= formatCurrency($sale->balance) ?></span></td>
            </tr>
            <?php if ($paymentValidation) : ?>
              <tr>
                <th><?= lang('App.uniquecode') ?>:</th>
                <td><span class="float-right"><?= formatCurrency($paymentValidation->unique) ?></span></td>
              </tr>
              <tr>
                <th><?= lang('App.transfer') ?>:</th>
                <td><span class="float-right"><?= formatCurrency($paymentValidation->amount + $paymentValidation->unique) ?></span></td>
              </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>