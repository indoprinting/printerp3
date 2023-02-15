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
  <style>
    .watermark {
      position: absolute;
      width: 80%;
      opacity: 0.1;
    }
  </style>
</head>

<body>
  <div class="wrapper">
    <!-- Main content -->
    <section class="invoice">
      <!-- title row -->
      <div class="row">
        <div class="col-md-12">
          <h2 class="page-header">
            INVOICE
          </h2>
        </div>
        <!-- /.col -->
      </div>
      <!-- info row -->
      <div class="row invoice-info">
        <div class="col-md-3 invoice-col">
          <?= lang('App.from') ?>
          <address>
            <strong><?= $biller->company ?></strong><br>
            <?= lang('App.address') ?>: <?= $biller->address ?><br>
            <?= lang('App.phone') ?>: <?= $biller->phone ?><br>
            <?= lang('App.email') ?>: <?= $biller->email ?>
          </address>
        </div>
        <div class="col-md-3 invoice-col">
          <?= lang('App.to') ?>
          <address>
            <strong><?= $customer->name . ($customer->company ? " ({$customer->company})" : '') ?></strong><br>
            <?= lang('App.address') ?>: <?= $customer->address ?><br>
            <?= lang('App.phone') ?>: <?= $customer->phone ?><br>
            <?= lang('App.email') ?>: <?= $customer->email ?>
          </address>
        </div>
        <div class="col-md-3 invoice-col">
          <b><?= lang('App.invoice') . ' ' . $sale->reference ?></b><br>
          <br>
          <b>Payment Due:</b> <?= formatDate($saleJS->payment_due_date) ?>
        </div>
        <div class="col-md-3 invoice-col">
          <img src="<?= (new \chillerlan\QRCode\QRCode())->render('https://www.indoprinting.co.id/trackorder?inv=' . $sale->reference) ?>">
        </div>
      </div>
      <!-- /.row -->

      <!-- Table row -->
      <div class="row">
        <div class="col-md-12">
          <!-- <img class="watermark" src="<?= base_url('assets/app/images/logo-indoprinting-300.png') ?>"> -->
          <table class="table table-striped">
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
                  <td><?= "({$saleItem->product_code}) $saleItem->product_name" ?></td>
                  <td><?= $saleItemJS->spec ?></td>
                  <td><?= $saleItemJS->w ?></td>
                  <td><?= $saleItemJS->l ?></td>
                  <td><?= $saleItemJS->sqty ?></td>
                  <td><?= formatNumber($saleItem->price) ?></td>
                  <td><?= formatNumber($saleItem->subtotal) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

      <div class="row">
        <!-- accepted payments column -->
        <div class="col-6">
          <p class="lead">Payment Methods:</p>

          <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
            Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles, weebly ning heekya handango imeem plugg dopplr
            jibjab, movity jajah plickers sifteo edmodo ifttt zimbra.
          </p>
        </div>
        <!-- /.col -->
        <div class="col-6">
          <div class="table-responsive">
            <table class="table">
              <tr>
                <th style="width:50%"><?= lang('App.discount') ?>:</th>
                <td><?= formatNumber($sale->discount) ?></td>
              </tr>
              <tr>
                <th>Tax (9.3%)</th>
                <td>$10.34</td>
              </tr>
              <tr>
                <th>Shipping:</th>
                <td>$5.80</td>
              </tr>
              <tr>
                <th>Total:</th>
                <td>$265.24</td>
              </tr>
            </table>
          </div>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- ./wrapper -->
  <!-- Page specific script -->
  <script>
    // window.addEventListener("load", window.print());
  </script>
</body>

</html>