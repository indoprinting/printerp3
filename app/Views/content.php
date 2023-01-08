<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>PrintERP 3</title>
  <link rel="icon" href="<?= base_url(); ?>/favicon.ico">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/fontawesome/css/all.min.css">
  <!-- Third party -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/chart.js/Chart.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/datatables/datatables.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/flag-icon/css/flag-icon.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/fullcalendar/lib/main.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/icheck/skins/all.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/jquery-ui/jquery-ui.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/overlayscrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/quill/quill.snow.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/modules/toastr/toastr.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/dist/css/adminlte.min.css">
  <!-- Custom style -->
  <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/app.css?v=<?= $resver ?>">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/common.css?v=<?= $resver ?>">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/loader.css?v=<?= $resver ?>">
  <script>
    const base_url = '<?= base_url(); ?>';
    const langId = '<?= session('login')->lang ?>';
    const lang = JSON.parse(atob('<?= $lang64 ?>'));
    window.Table = null;
  </script>
</head>

<body class="hold-transition layout-fixed layout-navbar-fixed sidebar-mini text-sm<?= session('login')->dark_mode ? ' dark-mode' : '' ?>">
  <div class="page-loader-wrapper">
    <div class="page-loader">
      <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
      </svg>
    </div>
  </div>
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand<?= session('login')->dark_mode ? ' navbar-dark bg-gradient-dark' : ' navbar-light bg-gradient-white' ?>">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-action="darkmode" href="#" role="button"><i class="fad <?= session('login')->dark_mode ? 'fa-sun' : 'fa-moon' ?>"></i></a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                  <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                  </button>
                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </li>

        <!-- Notifications Dropdown Menu -->
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('profile/notification') ?>" data-toggle="modal" data-target="#ModalDefault" data-modal-class="modal-dialog-centered modal-dialog-scrollable">
            <i class="far fa-info-circle"></i>
          </a>
        </li>

        <!-- Locale -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="flag-icon flag-icon-<?= App\Models\Locale::getRow(['code' => session('login')->lang])->flag ?>"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <?php
            foreach (App\Models\Locale::get() as $locale) :
              $active = '';

              if (session('login')->lang == $locale->code) $active = ' active'; ?>
              <a href="<?= base_url('lang/' . $locale->code) ?>" class="dropdown-item<?= $active ?> change-locale">
                <i class="flag-icon flag-icon-<?= $locale->flag ?> mr-2"></i> <?= $locale->name ?>
              </a>
            <?php endforeach; ?>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-action="logout" href="#" title="Logout">
            <i class="fad fa-door-open"></i>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar elevation-4<?= session('login')->dark_mode ? ' sidebar-dark-primary' : ' sidebar-light-primary' ?>">
      <!-- Brand Logo -->
      <a href="#" class="brand-link">
        <img src="<?= base_url() ?>/assets/dist/img/AdminLTELogo.png" alt="PrintERP 3" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">PrintERP 3</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="<?= base_url() ?>/attachment/<?= session('login')->avatar ?>" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a href="#" class="d-block"><?= session('login')->fullname ?></a>
          </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
          <div class="input-group" data-widget="sidebar-search">
            <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
              <button class="btn btn-sidebar">
                <i class="fas fa-search fa-fw"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu" data-accordion="true">
            <!-- Dashboard -->
            <li class="nav-item">
              <a href="<?= base_url() ?>" class="nav-link active" data-action="link" data-slug="dashboard">
                <i class="nav-icon fad fa-dashboard"></i>
                <p><?= lang('App.dashboard') ?></p>
              </a>
            </li>
            <?php if (hasAccess('All')) : ?>
              <!-- Debug -->
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="debug">
                  <i class="nav-icon fad fa-debug"></i>
                  <p>Debug <i class="fad fa-angle-right right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?= base_url('debug/modal') ?>" class="nav-link" data-toggle="modal" data-target="#ModalDefault">
                      <i class="nav-icon fad fa-window"></i>
                      <p>Modal</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url('debug/page') ?>" class="nav-link" data-action="link" data-slug="debug">
                      <i class="nav-icon fad fa-page"></i>
                      <p>Page</p>
                    </a>
                  </li>
                </ul>
              </li>
            <?php endif; ?>
            <?php if (hasAccess('Biller.View') || hasAccess('Warehouse.View')) : ?>
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="division">
                  <i class="nav-icon fad fa-building"></i>
                  <p><?= lang('App.division') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('Biller.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('division/biller') ?>" class="nav-link" data-action="link" data-slug="biller">
                        <i class="nav-icon fad fa-warehouse"></i>
                        <p><?= lang('App.biller') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Warehouse.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('division/warehouse') ?>" class="nav-link" data-action="link" data-slug="warehouse">
                        <i class="nav-icon fad fa-warehouse-alt"></i>
                        <p><?= lang('App.warehouse') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <?php if (hasAccess([
              'BankAccount.View', 'BankMutation.View', 'BankReconciliation.View',
              'Expense.View', 'Income.View', 'PaymentValidation.View'
            ])) : ?>
              <!-- Finance -->
              <li class="nav-item">
                <a href="#" class="nav-link" data-slug="finance">
                  <i class="nav-icon fad fa-usd"></i>
                  <p><?= lang('App.finance') ?> <i class="fad fa-angle-right right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <?php if (hasAccess('BankAccount.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/bank') ?>" class="nav-link" data-action="link" data-slug="bank">
                        <i class="nav-icon fad fa-landmark"></i>
                        <p><?= lang('App.bankaccount') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('BankMutation.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/mutation') ?>" class="nav-link" data-action="link" data-slug="mutation">
                        <i class="nav-icon fad fa-box-usd"></i>
                        <p><?= lang('App.bankmutation') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('BankReconciliation.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/reconciliation') ?>" class="nav-link" data-action="link" data-slug="reconciliation">
                        <i class="nav-icon fad fa-sync"></i>
                        <p><?= lang('App.bankreconciliation') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Expense.View')) : ?>
                    <li class="nav-item">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fad fa-arrow-alt-left"></i>
                        <p><?= lang('App.expense') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('Income.View')) : ?>
                    <li class="nav-item">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fad fa-arrow-alt-right"></i>
                        <p><?= lang('App.income') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (hasAccess('PaymentValidation.View')) : ?>
                    <li class="nav-item">
                      <a href="<?= base_url('finance/validation') ?>" class="nav-link" data-action="link" data-slug="validation">
                        <i class="nav-icon fad fa-check"></i>
                        <p><?= lang('App.paymentvalidation') ?></p>
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
            <!-- Human Resource -->
            <li class="nav-item">
              <a href="#" class="nav-link" data-slug="humanresource">
                <i class="nav-icon fad fa-users-cog"></i>
                <p><?= lang('App.humanresource') ?> <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url('humanresource/customer') ?>" class="nav-link" data-action="link" data-slug="customer">
                    <i class="nav-icon fad fa-user-tie-hair"></i>
                    <p><?= lang('App.customer') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url('humanresource/customergroup') ?>" class="nav-link" data-action="link" data-slug="customergroup">
                    <i class="nav-icon fad fa-users"></i>
                    <p><?= lang('App.customergroup') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url('humanresource/user') ?>" class="nav-link" data-action="link" data-slug="user">
                    <i class="nav-icon fad fa-user"></i>
                    <p><?= lang('App.user') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url('humanresource/usergroup') ?>" class="nav-link" data-action="link" data-slug="usergroup">
                    <i class="nav-icon fad fa-users"></i>
                    <p><?= lang('App.usergroup') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url('humanresource/supplier') ?>" class="nav-link" data-action="link" data-slug="supplier">
                    <i class="nav-icon fad fa-user-tie-hair"></i>
                    <p><?= lang('App.supplier') ?></p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Inventory -->
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fad fa-box-open-full"></i>
                <p><?= lang('App.inventory') ?> <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-sliders"></i>
                    <p><?= lang('App.adjustment') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-cloud-arrow-down"></i>
                    <p><?= lang('App.sync') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-hand-holding-box"></i>
                    <p><?= lang('App.internaluse') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-boxes-packing"></i>
                    <p><?= lang('App.category') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-cart-flatbed-boxes"></i>
                    <p><?= lang('App.mutation') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-box-up"></i>
                    <p><?= lang('App.product') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-box-check"></i>
                    <p><?= lang('App.stockopname') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-exchange"></i>
                    <p><?= lang('App.transfer') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-box-ballot"></i>
                    <p><?= lang('App.usagehistory') ?></p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Maintenance -->
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fad fa-cog"></i>
                <p><?= lang('App.maintenance') ?> <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-check-to-slot"></i>
                    <p><?= lang('App.equipmentcheck') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-th"></i>
                    <p><?= lang('App.maintenancelog') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-calendar"></i>
                    <p><?= lang('App.maintenanceschedule') ?></p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Procurement -->
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fad fa-shopping-cart"></i>
                <p><?= lang('App.procurement') ?> <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-cart-plus"></i>
                    <p><?= lang('App.purchase') ?></p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Production -->
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fad fa-scissors"></i>
                <p><?= lang('App.production') ?> <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-file-invoice"></i>
                    <p><?= lang('App.invoice') ?></p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- QMS -->
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fad fa-users-class"></i>
                <p>QMS <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-list"></i>
                    <p><?= lang('App.queue') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-user-headset"></i>
                    <p>Counter</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-desktop"></i>
                    <p><?= lang('App.display') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-file-alt"></i>
                    <p><?= lang('App.registration') ?></p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Report -->
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fad fa-file-chart-pie"></i>
                <p><?= lang('App.report') ?> <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-chart-mixed"></i>
                    <p><?= lang('App.dailyperformance') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-receipt"></i>
                    <p><?= lang('App.debt') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-money-bill-trend-up"></i>
                    <p><?= lang('App.incomestatement') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-box-dollar"></i>
                    <p><?= lang('App.inventorybalance') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-screwdriver-wrench"></i>
                    <p><?= lang('App.maintenance') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-money-bill-wave"></i>
                    <p><?= lang('App.payment') ?></p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-file-invoice-dollar"></i>
                    <p><?= lang('App.receivable') ?></p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Sales -->
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fad fa-cash-register"></i>
                <p><?= lang('App.sale') ?> <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-file-invoice"></i>
                    <p><?= lang('App.invoice') ?></p>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Setting -->
            <li class="nav-item">
              <a href="<?= base_url('setting') ?>" class="nav-link" data-slug="setting">
                <i class="nav-icon fad fa-cogs"></i>
                <p><?= lang('App.setting') ?> <i class="fad fa-angle-right right"></i></p>
              </a>
              <ul class="nav nav-treeview">
                <?php if (hasAccess('All')) : ?>
                  <li class="nav-item">
                    <a href="<?= base_url('setting/permission') ?>" class="nav-link" data-action="link" data-slug="permission">
                      <i class="nav-icon fad fa-user-lock"></i>
                      <p><?= lang('App.permission') ?></p>
                    </a>
                  </li>
                <?php endif; ?>
              </ul>
            </li>
            <!-- TrackingPOD -->
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="nav-icon fad fa-chart-network"></i>
                <p>TrackingPOD <i class="fad fa-angle-right right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fad fa-list"></i>
                    <p>TrackingPOD</p>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0" data-type="title"></h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right" data-type="breadcrumb">
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <div class="content" data-type="content">
        <div class="content-loader">
          <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
          </svg>
        </div>
      </div>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
      <!-- To the right -->
      <div class="float-right d-none d-sm-inline">
        <a href="https://www.ridintek.com" target="_blank">PrintERP version 3.0</a>
      </div>
      <!-- Default to the left -->
      <strong>Copyright &copy; <?= date('Y') ?> <a href="https://www.indoprinting.co.id">INDOPRINTING</a>.</strong>
    </footer>
    <div class="modal fade" id="ModalDefault">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-loader">
              <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
              </svg>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="ModalDefault2">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-loader">
              <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
              </svg>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="ModalStatic" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-loader">
              <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
              </svg>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="ModalStatic2" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="modal-loader">
              <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
              </svg>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
  </div>
  <!-- ./wrapper -->

  <!-- jQuery -->
  <script src="<?= base_url() ?>/assets/modules/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="<?= base_url() ?>/assets/modules/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- Application -->
  <script src="<?= base_url() ?>/assets/modules/bootstrap-validate/bootstrap-validate.js"></script>
  <script src="<?= base_url() ?>/assets/modules/bs-custom-file-input/bs-custom-file-input.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/chart.js/Chart.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/datatables/datatables.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/fullcalendar/lib/main.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/icheck/icheck.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/jquery-ui/jquery-ui.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/overlayscrollbars/js/OverlayScrollbars.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/quill/quill.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/select2/js/select2.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/sweetalert2/sweetalert2.min.js"></script>
  <script src="<?= base_url() ?>/assets/modules/toastr/toastr.min.js"></script>
  <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDP-hGcct-nQS50RHHdXjwXgNxi71jRaU8&libraries=places&v=weekly"></script>
  <!-- AdminLTE App -->
  <script src="<?= base_url() ?>/assets/dist/js/adminlte.min.js"></script>
  <!-- Custom -->
  <script src="<?= base_url() ?>/assets/app/js/app.js?v=<?= $resver ?>"></script>
  <script src="<?= base_url() ?>/assets/app/js/common.js?v=<?= $resver ?>"></script>
  <script>
    $(document).on('click', '[data-action="confirm"]', function(e) {
      e.preventDefault();

      let url = e.target.href;

      Swal.fire({
        icon: 'warning',
        text: lang.Msg.areYouSure,
        title: lang.Msg.areYouSure,
        showCancelButton: true,
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            data: {
              <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            error: (xhr) => {
              Swal.fire({
                icon: 'error',
                text: xhr.responseJSON.message,
                title: lang.App.failed
              });
            },
            method: 'POST',
            success: (data) => {
              Swal.fire({
                icon: 'success',
                text: data.message,
                title: lang.App.success
              });

              if (typeof Table !== 'undefined') Table.draw(false);
            },
            url: url
          });
        }
      });
    });
  </script>
</body>

</html>