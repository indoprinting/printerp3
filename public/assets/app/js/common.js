'use strict';

toastr.options.preventDuplicates = true;
toastr.options.progressBar = true;
toastr.options.timeOut = 2000;

(function (w, d) {
  w.addEventListener('load', (e) => {
    let loader = d.querySelector('.page-loader-wrapper');

    if (loader) {
      loader.style.opacity = 0;

      setTimeout(() => {
        loader.style.display = 'none';
      }, 300);
    }
  });
})(this, document);

function dtRenderCheck(id) {
  return `<div class="text-center"><input class="icheck-tbody" data-row-id="${id}" type="checkbox"></div>`;
}

function dtRenderAvatar(img) {
  if (!img) img = 'default-male.png';
  let image = base_url + '/assets/app/img/avatar/' + img;
  return `<img src="${image}">`;
}

function dtRenderLabel(label) {
  if (typeof label === 'string') {
    return ucfirst(label);
  }
  return null;
}

function dtRenderStatus(status) {
  let classStatus = 'default';
  let danger = ['danger', 'owner'];
  let info = ['info'];
  let primary = ['primary'];
  let success = ['success'];
  let warning = ['warning'];

  if (danger.includes(status)) {
    classStatus = 'danger';
  } else if (info.includes(status)) {
    classStatus = 'info';
  } else if (primary.includes(status)) {
    classStatus = 'primary';
  } else if (success.includes(status)) {
    classStatus = 'success';
  } else if (warning.includes(status)) {
    classStatus = 'warning';
  }

  return `<div class="text-center"><span class="badge badge-${classStatus}">${uc(status)}</span></div>`;
}

function initControls() {
  if (isFunction('bsCustomFileInput.init')) bsCustomFileInput.init();

  if (!isObject($.fn) || isEmpty($.fn.jquery)) {
    console.error('%cjQuery', 'font-weight:bold', ' is not installed');
  }

  if (isFunction('$.fn.iCheck')) {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%'
    });
  }

  if (isFunction('$.fn.select2')) {
    $('.select2').select2();
    $('.select2-allow-clear').select2({ allowClear: true });
  }

  if (isFunction('$.fn.datepicker')) {
    $('.datepicker').datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'yy-mm-dd',
      dayNames: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
      showAnim: 'drop'
    });
  }

  if (isFunction('$.fn.overlayScrollbars')) {
    // $('.dataTables_scrollBody').overlayScrollbars({
    //   sizeAutoCapable: true
    // });
    // $('.modal-body').overlayScrollbars({
    //   sizeAutoCapable: true
    // });
  }
}

function initModalForm(opt = {}) {
  let requiredParam = true;

  if (isEmpty(opt.submit) || isEmpty(opt.form) || isEmpty(opt.url)) {
    requiredParam = false;
  }

  $(opt.form).on('submit', function () {
    $(opt.submit).trigger('click');
    return false;
  });

  $(opt.submit).click(function () {
    if (!requiredParam) {
      toastr.error('Require params <b>form</b>, <b>submit</b> and <b>url</b>.',
        'common.js: initModalForm()');

      return false;
    }

    $(this).prepend(`<i class="fad fa-spinner fa-spin"></i> `);
    $(this).prop('disabled', true);

    let formData = new FormData(typeof opt.form == 'string' ? $(opt.form)[0] : opt.form);

    $.ajax({
      contentType: false,
      data: formData,
      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          text: xhr.responseJSON.message,
          title: xhr.statusText
        });

        $(opt.submit).prop('disabled', false);
        $(opt.submit).find('i').remove();
      },
      method: 'POST',
      processData: false,
      success: function (data) {
        if (isObject(data)) {
          Swal.fire({
            icon: 'success',
            text: data.message,
            title: 'Success'
          });

          reDrawDataTable();

          $(window.modal[window.modal.length - 1]).modal('hide');
        }

        $(opt.submit).prop('disabled', false);
        $(opt.submit).find('i').remove();
      },
      url: opt.url
    });
  });
}

function isArray(data) {
  return (data instanceof Array && Array.isArray(data));
}

function isEmpty(data) {
  return (
    data == false || typeof data == 'undefined' ||
    (isObject(data) ? Object.keys(data).length == 0 : false) ||
    (typeof data == 'number' ? isNaN(data) : false)
  );
}

function isFunction(data) {
  if (typeof data == 'object') return false;
  return eval(`typeof ${data} == 'function'`);
}

function isObject(data) {
  return (data instanceof Object && !Array.isArray(data));
}

function isString(data) {
  return (data instanceof String || typeof data == 'string');
}

function lc(str) {
  return str.toLowerCase();
}

/**
 * Pre-Select2
 * @param {*} $elm 
 * @param {object} $id param ID
 * @param {string} $type API type
 */
function preSelect2($elm, $id, $type) {

}

/**
 * Redraw Table from DataTable instance.
 * @param {object} table DataTable instance. If omitted, it will use window.Table variable.
 */
function reDrawDataTable(table = null) {
  if (isFunction(table?.draw)) table.draw(false);
  if (isFunction(window?.Table?.draw)) window.Table.draw(false);
}

function showPass(show = false) {
  if (show) {
    $('.pass').prop('type', 'text');
    $('.fa-eye-slash').addClass('fa-eye').removeClass('fa-eye-slash');
  } else {
    $('.pass').prop('type', 'password');
    $('.fa-eye').addClass('fa-eye-slash').removeClass('fa-eye');
  }
}

function templateAcademic(data, permissions = []) {
  if (isArray(data)) {
    let html = '';

    for (let row of data) {
      html +=
        `<div class="callout callout-danger bg-gradient-white">
          <h5 class="font-weight-bold">${row.title}</h5>
          <p>
            ${lang.App.publishedBy} ${row.created_by} ${lang.App.at} ${row.created_at}
          </p>
          <p>
            <i class="fad fa-file"></i>
            <a href="${base_url}/filemanager/view/${row.attachments.filename}" data-toggle="modal"
              data-target="#ModalDefault" data-modal-class="modal-lg">
              ${row.attachments.origin_name}
            </a>
          </p>`;

      if (isArray(permissions) && (permissions.indexOf('All') >= 0 || permissions.indexOf('AcademicInfo.Edit') >= 0)) {
        html +=
          `<div>
            <a class="btn bg-gradient-danger text-white"
              href="${base_url}/academicinfo/delete/${row.id}" data-action="confirm"
              data-text="${lang.Msg.academicInfoDeleteConfirm}" data-title="${lang.App.deleteAcademicInfo}">
              <i class="fad fa-trash"></i>
            </a>
            <a class="btn bg-gradient-dark text-white"
              href="${base_url}/academicinfo/edit/${row.id}" data-toggle="modal"
              data-target="#ModalDefault" data-modal-class="modal-dialog-centered">
              <i class="fad fa-edit"></i>
            </a>
          </div>`;
      }

      html +=
        '</div>';
    }

    return html;
  }
}

function uc(str) {
  return str.toUpperCase();
}

function ucfirst(word) {
  return word.charAt(0).toUpperCase() + word.substr(1);
}

function ucwords(words, delimiter = ' \,\-\_\t\r\n') {
  let s = '';
  let w = words.split(new RegExp('[' + delimiter + ']'));

  for (let word of w) {
    s += ucfirst(word) + ' ';
  }

  return s.trim();
}

function validateData(data) {
  if (isObject(data)) {
    for (let a in data) {
      if (data[a] == false) return false;
    }
  }

  return true;
}

let UserAgent = function () {
  this.desktop = ['Linux', 'Macintosh', 'Ubuntu', 'Windows', 'X11'];
  this.mobile = ['Android', 'Blackberry', 'iPhone']
  this.userAgent = navigator.userAgent;

  this.isDesktop = function () {

  }

  this.isMobile = function () {

  }
};

$(document).on('mousedown', '.show-pass', function () {
  showPass(1);
});

$(document).on('touchstart', '.show-pass', function () {
  showPass(1);
});

$(document).on('touchend', '.show-pass', function () {
  showPass(0);
});

$(document).on('mouseup', '.show-pass', function () {
  showPass(0);
});
