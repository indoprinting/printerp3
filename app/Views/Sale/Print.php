<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $title ?></title>

  <!-- Google Font: Source Sans Pro -->
  <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"> -->
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/fontawesome/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/app.css">
  <style>
    @media print {
      .watermark {
        z-index: 1;
      }
    }

    .watermark {
      left: 10%;
      opacity: 0.1;
      position: absolute;
      width: 80%;
    }
  </style>
</head>

<body class="layout-top-nav">
  <div class="wrapper">
    <div class="content-wrapper">
      <div class="content">
        <div class="row">
          <div class="col-md-12">
            <h2 class="page-header">
              INVOICE
            </h2>
          </div>
        </div>
        <div class="row pb-2">
          <div class="col-md-8">
            <div class="row">
              <div class="col-md-4 text-bold"><?= lang('App.invoice') ?></div>
              <div class="col-md-8">: <?= $sale->reference ?></div>
            </div>
            <div class="row">
              <div class="col-md-4 text-bold"><?= lang('App.date') ?></div>
              <div class="col-md-8">: <?= formatDateTime($sale->date) ?></div>
            </div>
            <div class="row">
              <div class="col-md-4 text-bold"><?= lang('App.status') ?></div>
              <div class="col-md-8">: <?= lang('Status.' . $sale->status) ?></div>
            </div>
            <div class="row">
              <div class="col-md-4 text-bold"><?= lang('App.approvalstatus') ?></div>
              <?php $approvalStatus = ($saleJS->approved == 1 ? 'approved' : 'need_approval') ?>
              <div class="col-md-8">: <?= lang('Status.' . $approvalStatus) ?></div>
            </div>
            <div class="row">
              <div class="col-md-4 text-bold"><?= lang('App.paymentstatus') ?></div>
              <div class="col-md-8">: <?= lang('Status.' . $sale->payment_status) ?></div>
            </div>
            <div class="row">
              <div class="col-md-4 text-bold"><?= lang('App.paymentmethod') ?></div>
              <div class="col-md-8">: <?= $sale->payment_method ? lang('App.' . strtolower($sale->payment_method)) : '-' ?></div>
            </div>
            <div class="row">
              <div class="col-md-4 text-bold"><?= lang('App.productionplace') ?></div>
              <div class="col-md-8">: <?= \App\Models\Warehouse::getRow(['code' => $sale->warehouse])->name ?></div>
            </div>
            <div class="row">
              <div class="col-md-4 text-bold"><?= lang('App.source') ?></div>
              <div class="col-md-8">: <?= $saleJS->source ?></div>
            </div>
          </div>
          <div class="col-md-4 text-center">
            <label>Scan me to track order</label>
            <img src="<?= (new \chillerlan\QRCode\QRCode())->render('https://www.indoprinting.co.id/trackorder?inv=' . $sale->reference) ?>">
          </div>
        </div>
        <div class="row pb-5">
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
        <div class="row pb-2 text-center">
          <div class="col-md-12">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th><?= lang('App.pic') ?></th>
                  <th><?= lang('App.note') ?></th>
                  <th><?= lang('App.paymentdue') ?></th>
                  <th><?= lang('App.completeestimation') ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?= \App\Models\User::getRow(['id' => $sale->created_by])->fullname ?></td>
                  <td><?= htmlRemove($sale->note) ?></td>
                  <td><?= formatDate($saleJS->payment_due_date) ?></td>
                  <td><?= formatDate($saleJS->est_complete_date) ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="row">
          <img class="watermark" src="<?= base_url('assets/app/images/logo-indoprinting-300.png') ?>">
          <div class="col-md-12">
            <table class="table table-bordered table-striped text-center">
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
                <?php foreach ($saleItems as $saleItem) : ?>
                  <?php $saleItemJS = getJSON($saleItem->json) ?>
                  <tr>
                    <td><span class="float-left"><?= "({$saleItem->product_code}) $saleItem->product_name" ?></span></td>
                    <td><?= $saleItemJS->spec ?></td>
                    <td><?= $saleItemJS->w ?></td>
                    <td><?= $saleItemJS->l ?></td>
                    <td><?= $saleItemJS->sqty ?></td>
                    <td><span class="float-right"><?= formatNumber($saleItem->price) ?></span></td>
                    <td><span class="float-right"><?= formatNumber($saleItem->subtotal) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="row pb-5">
          <div class="col-md-8">
            <p class="lead text-bold"><?= lang('App.paymentmethod') ?></p>
            <p class="" style="margin-top: 10px;">
              Pembayaran dengan transfer dianggap sah jika ditransfer dengan kode unik pada rekening berikut:
            </p>
            <div class="row">
              <div class="col-md-2 text-bold">BCA</div>
              <div class="col-md-4">8030 200234</div>
              <div class="col-md-2 text-bold">Mandiri</div>
              <div class="col-md-4">1360 0005 5532 3</div>
            </div>
            <div class="row">
              <div class="col-md-2 text-bold">BNI</div>
              <div class="col-md-4">5592 09008</div>
              <div class="col-md-2 text-bold">BRI</div>
              <div class="col-md-4">0083 01 001092 56 5</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="table-responsive">
              <table class="table" style="background-color:transparent">
                <tr>
                  <th><?= lang('App.total') ?>:</th>
                  <td><span class="float-right"><?= formatCurrency($sale->grand_total) ?></span></td>
                </tr>
                <tr>
                  <th style="width:50%"><?= lang('App.discount') ?>:</th>
                  <td><span class="float-right"><?= formatCurrency($sale->discount) ?></span></td>
                </tr>
                <?php if ($sale->tax > 0) : ?>
                  <?php $tax = ($sale->tax / 100 * $sale->grand_total); ?>
                  <tr>
                    <th><?= lang('App.tax') ?> (<?= floatval($sale->tax) ?>%):</th>
                    <td><span class="float-right"><?= formatCurrency($tax) ?></span></td>
                  </tr>
                <?php else : ?>
                  <?php $tax = 0; ?>
                <?php endif; ?>
                <tr>
                  <th><?= lang('App.grandtotal') ?>:</th>
                  <td><span class="float-right"><?= formatCurrency($sale->grand_total + $tax) ?></span></td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        <div class="row pb-5 text-center">
          <div class="col-md-4">
            <?= lang('App.customer') ?>
          </div>
          <div class="col-md-4">
            <?= lang('App.cs') ?>
          </div>
          <div class="col-md-4">
            <?= lang('App.operator') ?>
          </div>
        </div>
        <div class="row text-center pb-4">
          <div class="col-md-4">..............................</div>
          <div class="col-md-4">..............................</div>
          <div class="col-md-4">..............................</div>
        </div>
        <div class="row text-center">
          <div class="col-md-12">
            Mohon cermati text, ukuran dan quantity pesanan anda, karena <span class="text-bold">nota tidak bisa dilakukan revisi setelah dicetak</span>.<br>
            Barang pesanan dalam waktu 1 bulan tidak diambil akan disumbangkan kepada yang membutuhkan.<br>
            <span class="font-italic text-bold">Terima kasih telah menjadi pelanggan kami, jika ada masukkan silakan WhatsApp ke 081 327 043 234<span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // window.addEventListener("load", window.print());
  </script>
</body>

</html>