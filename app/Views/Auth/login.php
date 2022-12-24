<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title>App Login</title>
  <link rel="icon" href="<?= base_url(); ?>/favicon.ico">
  <link rel="stylesheet" href="<?= base_url(); ?>/assets/modules/icheck/skins/square/blue.css">
  <link rel="stylesheet" href="<?= base_url(); ?>/assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>/assets/modules/icheck/skins/square/_all.css">
  <link rel="stylesheet" href="<?= base_url(); ?>/assets/modules/toastr/toastr.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>/assets/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= base_url(); ?>/assets/app/css/common.css?v=<?= $resver ?>">
  <link rel="stylesheet" href="<?= base_url(); ?>/assets/app/css/loader.css?v=<?= $resver ?>">
  <link rel="stylesheet" href="<?= base_url(); ?>/assets/app/css/login.css?v=<?= $resver ?>">
  <script>
    const base_url = '<?= base_url() ?>';
  </script>
</head>

<body class="login-page dark-mode">
  <div class="page-loader-wrapper">
    <div class="page-loader">
      <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
      </svg>
    </div>
  </div>
  <div class="login-box">
    <div class="card bg-gradient-dark" style="opacity:0.9">
      <div class="card-body">
        <form id="form" enctype="application/x-www-form-urlencoded">
          <h2 class="mb-2 text-center">PRINTERP 3</h2>
          <div class="mb-4 text-center">Login with your account</div>

          <div class="input-group mb-3">
            <input type="text" class="form-control bg-transparent id" name="id" placeholder="ID">
          </div>

          <div class="input-group mb-3">
            <input type="password" class="form-control bg-transparent pass" name="pass" placeholder="Password">
            <div class="input-group-append">
              <div class="input-group-text bg-transparent">
                <i class="fas fa-eye-slash show-pass"></i>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-8 mb-2">
              <div class="form-group">
                <input type="checkbox" name="remember" id="remember" value="1">
                <label for="remember" class="form-check-label">
                  Remember me
                </label>
              </div>
            </div>
            <!-- /.col -->
          </div>

          <div class="row">
            <div class="col-12">
              <button type="button" class="btn bg-gradient-primary btn-block sign-in font-weight-bold">
                <i class="fad fa-right-to-bracket"></i> LOGIN
              </button>
            </div>
          </div>

          <?= csrf_field() ?>
        </form>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>

  <div class="background-image"></div>
  <style>
    .background-image {
      background-image: url(<?= base_url('assets/app/images/login/' . random_int(1, 4) . '.jpg') ?>);
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      opacity: 0.8;
      position: absolute;
      height: 100vh;
      width: 100vw;
    }
  </style>
  <!-- /.login-box -->
  <script src="<?= base_url(); ?>/assets/modules/jquery/jquery.min.js"></script>
  <script src="<?= base_url(); ?>/assets/modules/icheck/icheck.js"></script>
  <script src="<?= base_url(); ?>/assets/modules/toastr/toastr.min.js"></script>
  <script src="<?= base_url(); ?>/assets/app/js/common.js?v=<?= $resver ?>"></script>
  <script>
    $(document).ready(function() {
      $('#form').on('keypress', function(e) {
        if (e.keyCode == 13) e.preventDefault();
      });

      $(document).on('keypress', '.id, .pass', function(e) {
        if (e.keyCode == 13) {
          doLogin();
        }
      });

      $(document).on('click', '.sign-in', function() {
        doLogin();
      });

      $('.back').click(function() {
        location.href = '<?= base_url('/auth/login') ?>';
      });

      $('.id').focus();

      function doLogin() {
        let signIn = $('.sign-in');

        signIn.html('<i class="fad fa-spin fa-spinner"></i> LOGIN');

        let formData = new FormData(document.getElementById('form'));

        $.ajax({
          contentType: false,
          data: formData,
          error: function(data) {
            if (data.responseJSON) {
              toastr.error(data.responseJSON.message, data.responseJSON.code);
            }
            $('.sign-in').html('<i class="fad fa-right-to-bracket"></i> LOGIN');
          },
          method: 'POST',
          processData: false,
          success: function(data) {
            if (isObject(data)) {
              if (data.code == 200) {
                $('.sign-in').html('<i class="fad fa-check"></i> LOGIN');
                toastr.success(data.message);
                setTimeout(() => location.reload(), 0);
                return true;
              }

              toastr.error(data.message);
            } else {
              toastr.error('Something wrong.');
            }
            $('.sign-in').html('<i class="fad fa-right-to-bracket"></i> LOGIN');
          },
          url: '<?= base_url('auth/login') ?>'
        });
      }

      $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        increaseArea: '20%'
      });
    });
  </script>
</body>

</html>