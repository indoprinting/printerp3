<?php
$hash = (!empty($hash) ? $hash : bin2hex(random_bytes(4)));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Form Registrasi</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="<?= base_url('assets/pwa/images/favicon.ico') ?>" />
  <link href="<?= base_url('assets/qms/fonts/fontawesome/css/all.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/modules/jquery-ui/jquery-ui.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/modules/jquery-ui/jquery-ui.structure.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/modules/jquery-ui/jquery-ui.theme.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/dist/css/adminlte.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/modules/select2/css/select2.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/modules/toastr/toastr.min.css'); ?>" rel="stylesheet" />
  <link href="<?= base_url('assets/qms/css/ridintek.css?v=') . $resver; ?>" rel="stylesheet" />
  <script>
    let base_url = '<?= base_url() ?>';
  </script>
</head>
<style>
  body {
    background-color: rgba(0, 0, 0, .9);
  }

  #attention_frame {
    transition: all 0.5s;
  }

  .card-header h2 {
    font-weight: bold;
  }

  .text-white {
    color: white;
  }

  .text-yellow {
    color: yellow;
  }

  /* Hack select2 height */
  .select2-container .select2-selection--single {
    height: calc(1.5em + .75rem + 2px);
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: calc(1.5em + .75rem + 2px);
  }
</style>

<body>
  <div class="ajax-loader-img" id="loader"></div>
  <div class="container" style="position: relative; top: 30px">
    <div class="card transparent-black">
      <div class="card-header text-center">
        <h2>FORM REGISTRASI NOMOR ANTRIAN<br>OUTLET <?= strtoupper($warehouse->name) ?></h2>
      </div>
      <div class="card-body">
        <div id="attention_frame" style="border:5px solid red;color:yellow;font-size:18px;font-weight:bold;padding:5px;">
          <span style="color:red">PERHATIAN !</span> Bagi yang membawa file siap cetak atau design, dimohon untuk mempersiapkannya
          di Flashdisk atau telah mengirimkan file tersebut melalui email Indoprinting di
          <span style="color:white">idp.<?= strtolower($warehouse->name) ?>@gmail.com</span>
          sebelum CS kami memanggil nomor antrian anda. Terima kasih.
        </div><br />
        <select class="form-control" id="phone" placeholder="No. HP" style="width:100%;"></select><br><br>
        <input class="form-control" id="name" placeholder="Nama Lengkap" type="text" value="" />
      </div>
      <div class="card-body pt-2">
        <div class="row">
          <div class="col-sm btn-group">
            <button id="btn_cetak" class="btn bg-gradient-danger btn-block btn-lg btn-reg">SIAP CETAK</button>
          </div>
          <div class="col-sm btn-group">
            <button id="btn_design" class="btn bg-gradient-success btn-block btn-lg btn-reg">EDIT DESIGN</button>
          </div>
          <!--
            <div class="col-sm btn-group">
              <button id="btn_xpressdesign" class="btn btn-primary btn-block btn-lg btn-reg">XPRESS DESIGN</button>
            </div>
            <div class="col-sm btn-group">
              <button id="btn_priority" class="btn btn-warning btn-block btn-lg btn-reg">PRIORITY</button>
            </div>-->
        </div>
      </div>
    </div>
    <div class="card transparent-black">
      <div class="card-body p-2">
        <h5 class="card-title bg-danger p-2 text-white btn-reg">SIAP CETAK</h5>
        <div class="card-text text-white">Anda hanya cukup membawa file image yang siap cetak (PDF/JPG/TIFF/PNG/CDR Convert)
          tanpa harus ada revisi atau edit ulang.<br>
          Tambahan waktu 10 menit/Rp 10.000,- bisa diberikan saat tidak ada antrian. Free Design. Waktu pelayanan di CS 10 menit.
        </div>
      </div>
      <div class="card-body p-2">
        <h5 class="card-title bg-success p-2 text-white btn-reg">EDIT DESIGN</h5>
        <div class="card-text text-white">Anda bisa meminta kepada CS untuk mendesain produk baru
          yang anda inginkan dengan harga mulai Rp 20.000,-.<br>
          Tambahan waktu 10 menit/Rp 10.000,- bisa diberikan saat tidak ada antrian.
          Waktu pelayanan di CS 20 menit.
        </div>
      </div>
      <!--<div class="card-body p-2">
        <h5 class="card-title bg-primary p-2 text-white btn-reg">XPRESS DESIGN</h5>
        <div class="card-text text-white">Tidak berbeda dengan Edit Design, hanya saja kami lebih prioritaskan
        dalam antrian dengan harga pelayanan/design mulai dari Rp 50.000,- dan waktu pelayanan di CS 20 menit. Harga per item menggunakan harga XPRESS.
        </div>
      </div>
      <div class="card-body p-2">
        <h5 class="card-title p-2 bg-warning btn-reg">PRIORITY</h5>
        <div class="card-text text-white">Jika anda memesan produk kami dengan nilai mulai
          dari Rp 500.000,-, maka anda menjadi prioritas utama dalam pelayanan kami.
        </div>
      </div>-->
    </div>
  </div>
  <div class="modal fade" id="email_modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title text-center">Alamat Email Indoprinting</h3>
        </div>
        <div class="modal-body">
          <ul>
            <li>idp.durian@gmail.com</li>
            <li>idp.tembalang@gmail.com</li>
            <li>idp.ngesrep@gmail.com</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="ticket_modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title text-center"><span data-content="modal-title"></span></h3>
          <button class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body text-center">
          <div><span data-content="modal-body"></span></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" data-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
  <script src="<?= base_url('assets/modules/jquery/jquery.min.js'); ?>"></script>
  <script src="<?= base_url('assets/modules/jquery-ui/jquery-ui.min.js'); ?>"></script>
  <script src="<?= base_url('assets/modules/bootstrap/js/bootstrap.min.js'); ?>"></script>
  <script src="<?= base_url('assets/modules/select2/js/select2.full.min.js'); ?>"></script>
  <script src="<?= base_url('assets/modules/toastr/toastr.min.js'); ?>"></script>
  <script>
    $(document).on("select2:open", () => {
      setTimeout(() => {
        document.querySelector(".select2-container--open .select2-search__field").focus()
      }, 10);
    });
  </script>
  <script type="module">
    import {
      QueueHttp,
      QueueNotify
    } from '<?= base_url('assets/app/js/ridintek.js?v=') . $resver ?>';

    /**
     * data.title
     * data.body
     */
    function show_modal(data) {
      $('[data-content=modal-title]').html(data.title);
      $('[data-content=modal-body]').html(data.body);

      $('#ticket_modal').modal('show');

      setTimeout(() => {
        $('#ticket_modal').modal('hide');
      }, 30 * 1000);
    }

    async function add_queue_ticket(category_id) {
      if (!$('#phone').val()) {
        QueueNotify.error('Mohon masukkan nomor handphone anda.')
        return false;
      }

      if (!$('#name').val()) {
        QueueNotify.error('Mohon masukkan nama anda.');
        return false;
      }

      $('#loader').show();

      let data = {
        name: $('#name').val(),
        phone: $('#phone').val(),
        category: category_id,
        warehouse: <?= $warehouse->id ?>,
        <?= csrf_token(); ?>: '<?= csrf_hash(); ?>'
      };

      try {
        let response = await QueueHttp.send('POST', '<?= base_url('qms/addQueueTicket') ?>', data);
        if (typeof response === 'object') {
          $('#loader').fadeOut();

          $('#phone').val('').trigger('change');
          $('#name').val('');

          let ticket = response.data;

          QueueNotify.audio.success.play();

          show_modal({
            title: `NO ANTRIAN ${ticket.queue_category_name.toUpperCase()} ${ticket.token}`,
            body: `<strong>SELAMAT DATANG DI INDOPRINTING</strong><br><br>` +
              `Terima kasih telah melakukan registrasi pelayanan di INDOPRINTING.<br>` +
              `Nomor pelayanan anda adalah <strong>${ticket.queue_category_name.toUpperCase()} ${ticket.token}</strong><br>` +
              `(Tercatat di <strong>DAFTAR TUNGGU</strong> monitor antrian).<br><br>` +
              `Terima kasih telah menjadi pelanggan INDOPRINTING.`
          });
        }
      } catch (e) {
        $('#loader').fadeOut();

        $('#phone').val('').trigger('change');
        $('#name').val('');

        QueueNotify.error(JSON.parse(e).message);
      }
    }

    $(document).dblclick(() => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
      } else {
        document.exitFullscreen();
      }
    });

    $(document).ready(function() {
      let toggleFrame = true;

      setInterval(() => {
        if (toggleFrame) {
          document.querySelector('#attention_frame').style.border = '5px solid white';
          toggleFrame = false;
        } else {
          document.querySelector('#attention_frame').style.border = '5px solid red';
          toggleFrame = true;
        }
      }, 1000);

      $('#btn_cetak').on('click', async function() {
        add_queue_ticket(1); // 1 = Siap Cetak.
      });

      $('#btn_design').on('click', async function() {
        add_queue_ticket(2); // 2 = Edit Design.
      });

      // delay 1000 = prevent DDoS
      $('#phone').select2({
        ajax: {
          delay: 1000,
          url: '<?= base_url('qms/getCustomers') ?>',
          dataType: 'json'
        },
        allowClear: true,
        placeholder: 'No. HP',
        tags: true,
        templateResult: function(state) {
          if (!state.id) {
            return state.text;
          }

          let result = {
            id: state.id,
            text: state.text + (state.name ? ' (' + state.name + ')' : '')
          };
          return result.text;
        }
      });

      $('#phone').on('select2:opening', function(e) { // On clear.
        $('#phone').empty(); // Important since got problem 2021-12-18
        $('#phone').val('').trigger('change');
        $('#name').val('').prop('readonly', false);
      });

      $('#phone').on('select2:select', function(e) {
        console.log('select');
        let data = e.params.data;
        if (data.name) {
          $('#name').val(data.name).prop('readonly', true);
        }
      });

      // https://stackoverflow.com/questions/50683916/select2-js-search-input-to-allow-only-numbers
      $(document).on('keypress', '.select2-search__field', function() {
        $(this).val($(this).val().replace(/[^\d].+/, ""));
        if ((event.which < 48 || event.which > 57)) {
          event.preventDefault();
        }
      });
    });
  </script>
</body>

</html>