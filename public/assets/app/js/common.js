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

function initControls() {

  if (isFunction('bsCustomFileInput.init')) bsCustomFileInput.init();

  if (!isObject($.fn) || isEmpty($.fn.jquery)) {
    console.error('%cjQuery', 'font-weight:bold', ' is not installed');
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
    $('.select-product-standard').select2({
      allowClear: true,
      ajax: {
        data: (params) => {
          params.type = 'standard';

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

  if (isFunction('formatCurrency')) {
    $('.currency').val(formatCurrency($('.currency').val()));
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

    $(this).prepend(`<i class="fad fa-spinner-third fa-spin"></i> `);
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
