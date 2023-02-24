'use strict';

if (typeof toastr !== 'undefined') {
  toastr.options.preventDuplicates = true;
  toastr.options.progressBar = true;
  toastr.options.timeOut = 2000;
}

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

function appendZero(number) { // Return as string, you can convert to number with parseInt().
  if (number < 10) {
    return '0' + number;
  }
  return number;
}

/**
 * Calculate sale item by table sale.
 */
function calculateSale() {
  let table = $('#table-sale');

  if (!table) {
    return false;
  }

  let amount = 0;
  let subTotals = table.find('.saleitem-subtotal');
  let grandTotal = table.find('.sale-grandtotal');

  subTotals.each(function () {
    amount += filterDecimal(this.innerHTML);
  });

  grandTotal.html(formatCurrency(amount));

  return amount;
}

/**
 * Control sidebar.
 * @param {string} action Sidebar action (collapse, show, toggle).
 */
function controlSidebar(action = 'toggle') {
  $('[data-widget="control-sidebar"]').ControlSidebar(action);
}

/**
 * Create google maps.
 * @param {Object} options { element: {map, searchBox, latitude = null, longitude = null}, lat, lon }
 */
function createGoogleMaps(options) {
  if (typeof options.lat != 'undefined') {
    let v = parseFloat(options.lat);
    options.lat = (isNaN(v) ? null : v);
  }

  if (typeof options.lon != 'undefined') {
    let v = parseFloat(options.lon);
    options.lon = (isNaN(v) ? null : v);
  }

  let input = $(options.element.searchBox)[0];
  let geocoder = new google.maps.Geocoder();
  let map = new google.maps.Map($(options.element.map)[0], {
    center: {
      lat: (options.lat ?? 0),
      lng: (options.lon ?? 0)
    },
    zoom: 15
  });
  let marker = null;

  function setMarker(map, latLng) {
    let lat = latLng.lat;
    let lon = latLng.lng;

    if (options.element.latitude) {
      $(options.element.latitude).val(lat);
    }

    if (options.element.longitude) {
      $(options.element.longitude).val(lon);
    }

    if (geocoder) {
      geocoder.geocode({
        location: {
          lat: lat,
          lng: lon
        }
      }).then((response) => {
        if (response.results[0]) {
          $(options.element.searchBox).val(response.results[0].formatted_address);
        } else {
          window.alert("No results found");
        }
      });
    }

    if (marker) {
      marker.setPosition({
        lat: lat,
        lng: lon
      });
    } else {
      marker = new google.maps.Marker({
        map: map,
        position: {
          lat: lat,
          lng: lon
        }
      });
    }
  }

  let searchBox = new google.maps.places.SearchBox(input);

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition((pos) => {
      let lat = (options.lat ?? pos.coords.latitude);
      let lon = (options.lon ?? pos.coords.longitude);

      map.setCenter({
        lat: lat,
        lng: lon
      });

      setMarker(map, {
        lat: lat,
        lng: lon
      });
    });
  }

  map.addListener('click', (event) => {
    let lat = event.latLng.lat();
    let lon = event.latLng.lng();

    setMarker(map, {
      lat: lat,
      lng: lon
    });
  })

  // Bias the SearchBox results towards current map's viewport.
  map.addListener("bounds_changed", () => {
    searchBox.setBounds(map.getBounds());
  });

  // Listen for the event fired when the user selects a prediction and retrieve
  // more details for that place.
  searchBox.addListener("places_changed", () => {
    const places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }

    // For each place, get the icon, name and location.
    const bounds = new google.maps.LatLngBounds();

    places.forEach((place) => {
      if (!place.geometry || !place.geometry.location) {
        console.warn("Returned place contains no geometry");
        return;
      }

      let lat = place.geometry.location.lat();
      let lon = place.geometry.location.lng();

      setMarker(map, {
        lat: lat,
        lng: lon
      });

      if (place.geometry.viewport) {
        // Only geocodes have viewport.
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });

    map.fitBounds(bounds);
  });
}

function dtRenderCheck(id) {
  return `<div class="text-center"><input class="icheck-tbody" data-row-id="${id}" type="checkbox"></div>`;
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

function filterDecimal(str) {
  if (str == null) str = 0;
  if (str.toString().length == 0) str = 0;
  if (typeof str == 'string') str = str.replaceAll(/([^0-9\.\-])/g, '');
  if (isNaN(parseFloat(str))) str = 0;

  return parseFloat(str);
}

function filterNumber(str) {
  if (str == null) str = 0;
  if (str.toString().length == 0) str = 0;
  if (typeof str == 'string') str = str.replaceAll(/([^0-9])/g, '');
  if (isNaN(parseFloat(str))) str = 0;

  return parseFloat(str);
}

function formatCurrency(str) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency', currency: 'IDR', currencyDisplay: 'narrowSymbol',
    maximumFractionDigits: 2, minimumFractionDigits: 0
  }).format(filterDecimal(str));
}

function formatNumber(str) {
  return new Intl.NumberFormat('en-US', {
    style: 'decimal', maximumFractionDigits: 2, minimumFractionDigits: 0
  }).format(filterDecimal(str));
}

function getSalePrice(quantity, ranges = [], prices = []) {
  if (isEmpty(prices)) {
    console.error('Ranges are empty.');
    console.error(ranges);
    return false;
  }

  if (isEmpty(ranges)) {
    console.warn('Ranges are empty. Use default price.');
    console.warn(ranges);
    return prices[0];
  }

  for (let a = ranges.length; a >= 0; a--) {
    if (quantity >= ranges[a]) {
      return prices[a + 1];
    }
  }

  return prices[0];
}

/**
 * Get time difference.
 * @param {string} timestr1 Time string at first time. Ex. 00:20:43
 * @param {string} timestr2 Time string at last time. Ex. 00:31:22
 * @return {string} Return Time string difference. Ex. 01:20:30
 */
function getTimeDifference(timestr1, timestr2) {
  let time1 = timestr1.split(':');
  let time2 = timestr2.split(':');
  let time1sec = (parseInt(time1[0]) * 3600) + (parseInt(time1[1]) * 60) + parseInt(time1[2]);
  let time2sec = (parseInt(time2[0]) * 3600) + (parseInt(time2[1]) * 60) + parseInt(time2[2]);
  let diff = time2sec - time1sec;
  let hours = Math.floor(diff / 3600);
  let minutes = Math.floor((diff % 3600) / 60);
  let seconds = Math.floor(diff % 60);

  return `${appendZero(hours)}:${appendZero(minutes)}:${appendZero(seconds)}`;
}

function hasAccess(access) {
  if (typeof permissions == 'undefined' || !isArray(permissions)) {
    console.error('Const permissions is not defined.');
  }

  if (permissions.indexOf('All') >= 0) {
    return true;
  }

  if (isArray(access)) {
    access.forEach((value) => {
      if (permissions.indexOf(value) >= 0) {
        return true;
      }
    });
  }

  if (isString(access)) {
    if (permissions.indexOf(access) >= 0) {
      return true;
    }
  }

  return false;
}

function initControls() {
  if (isFunction('bsCustomFileInput.init')) bsCustomFileInput.init();

  if (!isObject($.fn) || isEmpty($.fn.jquery)) {
    console.error('%cjQuery', 'font-weight:bold', ' is not installed');
  }

  if (isFunction('$.fn.overlayScrollbars')) {
    $('body').overlayScrollbars({
      scrollbars: {
        autoHide: 'l'
      }
    });

    $('.modal-body').css('min-height', '400px').overlayScrollbars({
      scrollbars: {
        autoHide: 'l'
      }
    });
  }

  if (isFunction('$.fn.iCheck')) {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '0%'
    });
  }

  if (isFunction('$.fn.select2')) {
    /** Do no use class name .select2, use .select instead. */
    $('.select').select2();
    $('.select-allow-clear').select2({ allowClear: true });
    $('.select-tags').select2({ tags: true });
    $('.select-allow-clear-tags').select2({ allowClear: true, tags: true });
    $('.select-bank').select2({
      allowClear: true,
      ajax: {
        data: (params) => {
          if (erp?.bank?.biller) {
            params.biller = erp.bank.biller;
          }

          if (erp?.bank?.type) {
            params.type = erp.bank.type;
          }

          return params;
        },
        delay: 1000,
        url: base_url + '/select2/bank'
      }
    });
    $('.select-biller').select2({
      allowClear: true,
      ajax: {
        delay: 1000,
        url: base_url + '/select2/biller'
      }
    });
    $('.select-customer').select2({
      allowClear: true,
      ajax: {
        delay: 1000,
        url: base_url + '/select2/customer'
      }
    });
    $('.select-product').select2({
      allowClear: true,
      ajax: {
        delay: 1000,
        url: base_url + '/select2/product'
      }
    });
    $('.select-product-sale').select2({
      allowClear: true,
      ajax: {
        data: (params) => {
          params.type = ['combo', 'service'];

          if (typeof erp?.sale?.useRawMaterial !== 'undefined') {
            if (erp.sale.useRawMaterial) {
              params.type.push('standard');
            } else if (params.type.indexOf('standard') > 0) {
              params.type.pop();
            }
          }

          return params;
        },
        delay: 1000,
        url: base_url + '/select2/product'
      }
    });
    $('.select-product-standard').select2({
      allowClear: true,
      ajax: {
        data: (params) => {
          params.type = ['standard'];

          return params;
        },
        delay: 1000,
        url: base_url + '/select2/product'
      }
    });
    $('.select-supplier').select2({
      allowClear: true,
      ajax: {
        delay: 1000,
        url: base_url + '/select2/supplier'
      }
    });
    $('.select-user').select2({
      allowClear: true,
      ajax: {
        delay: 1000,
        url: base_url + '/select2/user'
      }
    });
    $('.select-warehouse').select2({
      allowClear: true,
      ajax: {
        delay: 1000,
        url: base_url + '/select2/warehouse'
      }
    });
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

  if (isFunction('formatCurrency')) {
    let currency = $('.currency');

    currency.each(function () {
      this.value = formatCurrency(this.value);
    });
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

    let icon = $(this).find('i');
    let oldClass = '';

    if (icon.length) {
      oldClass = icon.prop('class');

      icon.removeClass().addClass('fad fa-fw fa-spinner-third fa-spin');
    } else {
      $(this).prepend(`<i class="fad fa-fw fa-spinner-third fa-spin"></i> `);
    }

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

        if (icon.length) {
          icon.removeClass().addClass(oldClass);
        } else {
          $(opt.submit).find('i').remove();
        }
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

          // Pre-select customer after add from add customer button.
          if ($('#customer').length && $('#phone').length) {
            preSelect2('customer', '#customer', $('#phone').val());
          }

          reDrawDataTable();

          $(window.modal[window.modal.length - 1]).modal('hide');
        }

        $(opt.submit).prop('disabled', false);

        if (icon.length) {
          icon.removeClass().addClass(oldClass);
        } else {
          $(opt.submit).find('i').remove();
        }
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

function isObjectDifferent(obj1, obj2) {
  return (JSON.stringify(obj1) === JSON.stringify(obj2) ? false : true);
}

function isString(data) {
  return (data instanceof String || typeof data == 'string');
}

function lc(str) {
  return str.toLowerCase();
}

/**
 * Pre-Select2
 * @param {string} mode Mode (biller, customer, product supplier, warehouse).
 * @param {*} elm Element to change.
 * @param {*} id Id of mode.
 */
async function preSelect2(mode, elm, id) {
  return new Promise((resolve, reject) => {
    if (isEmpty(id)) {
      console.warn(`preSelect2: id for ${mode}:${elm}.`);
      reject(false);
    }

    $.ajax({
      error: (xhr) => {
        toastr.error(xhr.responseJSON.message, xhr.status);

        reject(false);
      },
      success: (data) => {
        let opt = new Option(data.results[0].text, data.results[0].id, true, true);

        $(elm).html('').append(opt).trigger('change');

        resolve(true);
      },
      url: base_url + `/select2/${mode}?term=${id}`
    });
  });
}

function randomString(length = 8) {
  let chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
  let buff = '';

  for (let a = 0; a < length; a++) {
    buff += chars.charAt(Math.floor(Math.random() * chars.length) % chars.length);
  }

  return buff;
}

/**
 * Redraw Table from DataTable instance.
 * @param {object} table DataTable instance. If omitted, it will use window.Table variable.
 */
function reDrawDataTable(table = null) {
  if (isFunction(table?.draw)) table.draw(false);
  if (isFunction(window?.Table?.draw)) window.Table.draw(false);
}

function separateChar(char) {
  let buff = '';
  if (char.length > 0) {
    for (let a = 0; a < char.length; a++) {
      buff = buff + char[a] + ', ';
    }
  }
  return buff;
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

/**
 * Convert string to unix time miliseconds.
 * @param {string} time Time string.
 */
function strtotime(time) {
  return Date.parse(time);
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

function uuid() {
  let buff = '';
  let bytes = randomString(16);

  for (let a = 0; a < bytes.length; a++) {
    buff += bytes.charCodeAt(a).toString(16);

    if (a == 3 || a == 5 || a == 7 || a == 9) {
      buff += '-';
    }
  }

  return buff;
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
