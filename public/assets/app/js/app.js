/**
 * Author: Riyan Widiyanto
 * Copyright (C) 2021 Ridintek Industri.
 */

'use strict';

(function (factory) {
  let App = function () {
    this._bc = $('[data-type="breadcrumb"]');
    this._content = $('[data-type="content"]');
    this._title = $('[data-type="title"]');
    this._icon = null;
  }

  App.prototype._setBreadcrumb = function (bc) {
    if (bc instanceof Array && bc.length) {
      this._bc.empty();

      $('a[data-slug]').removeClass('active');

      for (let b of bc) {
        let active = '';
        let link = '';

        if (b.url == '#') { // Active link (selected page).
          active = ' active';
          link = b.name;
        } else { // Inactive link (not selected page).
          link = `<a href="${b.url}" data-action="link">${b.name}</a>`;
        }

        this._icon = $(`a[data-slug="${b.slug}"]`).find('i');

        $(`a[data-slug="${b.slug}"]`).addClass('active');
        $(`a[data-slug="${b.slug}"]`).closest('.nav-item')
          .addClass('menu-open').find('.nav-treeview').css('display', 'block');

        this._bc.append(`<li class="breadcrumb-item${active}">${link}</li>`);
      }
    }

    return this;
  }

  App.prototype._setContent = function (content) {
    this._content.html(content);
    return this;
  }

  App.prototype._setTitle = function (title) {
    if (this._icon.length) {
      let className = this._icon[0].className.replace('nav-icon', '');
      this._title.html(`<i class="${className} mr-2"></i>${title}`);
    } else {
      this._title.html(`${title}`);
    }

    document.title = title;
    return this;
  }

  App.prototype._setURL = function (url) {
    history.pushState(null, '', url);
    return this;
  }

  App.prototype.loadPage = function (url, setURL = true) {
    this._setContent('<div class="content-loader">\
       <svg class="circular" viewBox="25 25 50 50">\
         <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />\
       </svg></div>');

    $.ajax({
      error: (xhr) => {
        if (isObject(xhr.responseJSON)) {
          toastr.error(xhr.responseJSON.message, xhr.responseJSON.title);

          if (xhr.status == 401) {
            location.reload();
          }
        } else {
          toastr.error(xhr.statusText, xhr.status);
        }
      },
      success: (data) => {
        if (isObject(data)) {
          if (data.code == 200) {
            this._setBreadcrumb(data.bc)
              ._setContent(data.content)
              ._setTitle(data.title);

            if (setURL) this._setURL(data.url);

            initControls();
          }
        } else {
          toastr.error(data, lang.Msg.cannotLoadPage);
        }
      },
      url: url
    });
  }

  factory(new App);
}(function (app) {
  // If content loaded automatically without
  // user interaction (click) then do not pushState.
  app.loadPage(location.href, false);

  $(document).on('click', 'a[data-action="link"]', function (e) {
    e.preventDefault();

    let url = $(this).prop('href');

    // If sidebar opened (default close) then close sidebar.
    if (document.querySelector('body').classList.contains('sidebar-open')) {
      $('body').removeClass('sidebar-open').addClass('sidebar-closed sidebar-collapse');
    }

    // pushState true because this is user interaction.
    app.loadPage(url);
  });

  // Important for back state reload. No pushState.
  $(window).on('popstate', function (e) {
    app.loadPage(document.location.href, false);
  });
}));

$(document).ready(function () {
  initControls();

  // Fix select2 on jQuery 3.6.x
  $(document).on("select2:open", () => {
    setTimeout(() => {
      document.querySelector(".select2-container--open .select2-search__field").focus()
    }, 10);
  });

  // Auto hide control-sidebar.
  $(document).on('click', '.container-fluid', () => {
    if ($('body').hasClass('control-sidebar-slide-open')) {
      controlSidebar('collapse');
    }
  });

  // iCheck multicheck.
  $(document).on('change', '.checkbox-parent', function (e) {
    let that = this;

    if (this.checked) {
      $('.checkbox-parent').iCheck('check');
    } else {
      $('.checkbox-parent').iCheck('uncheck');
    }

    $('.checkbox').each(function () {
      if (that.checked) {
        $(this).iCheck('check');
      } else {
        $(this).iCheck('uncheck');
      }
    });
  });

  $(document).on('change', '.saleitem', function (e) {
    let area = $(this).closest('tr').find('[name="item[area][]"]');
    let price = $(this).closest('tr').find('[name="item[price][]"]');
    let prices = $(this).closest('tr').find('[name="item[prices][]"]');
    let ranges = $(this).closest('tr').find('[name="item[ranges][]"]');
    let length = $(this).closest('tr').find('[name="item[length][]"]');
    let width = $(this).closest('tr').find('[name="item[width][]"]');
    let type = $(this).closest('tr').find('[name="item[type][]"]');
    let quantity = $(this).closest('tr').find('[name="item[quantity][]"]');
    let subTotal = $(this).closest('tr').find('.saleitem-subtotal');

    if (quantity.val() < 0) {
      quantity.val(0);

      SweetAlert.fire({
        icon: 'error',
        title: 'Error',
        text: 'Quantity cannot be less than zero.'
      });
    }

    if (type.val() == 'service' && quantity.val().indexOf('.') >= 0) {
      quantity.val(1);

      SweetAlert.fire({
        icon: 'error',
        title: 'Gagal',
        text: 'Item jasa tidak bisa berupa floating point.'
      });

      return false;
    }

    prices = JSON.parse(prices.val());
    ranges = JSON.parse(ranges.val());

    area.val(width.val() * length.val());

    if (this.name != 'item[price][]') {
      price.val(formatCurrency(getSalePrice(area.val() * quantity.val(), ranges, prices)));
    }

    subTotal.html(formatCurrency(length.val() * width.val() * quantity.val() * filterDecimal(price.val())));

    calculateSale();
  });

  $(document).on('click', '[data-action="clear-notification"]', function (e) {
    e.preventDefault();

    $('[data-type="notification"]').slideUp(function () {
      $(this).empty();
    })
  });

  $(document).on('click', '[data-action="confirm"]', function (e) {
    e.preventDefault();

    let text            = this.dataset.text;
    let title           = this.dataset.title;
    let url             = this.href;
    let fa              = $(this).find('i')[0];
    let faClass         = fa.className;
    let faClassProgress = 'fad fa-spinner-third fa-spin';

    if (this.dataset.progress == 'true') {
      return false;
    }

    Swal.fire({
      icon: 'warning',
      text: (text ?? lang.Msg.areYouSure),
      title: (title ?? lang.Msg.areYouSure),
      showCancelButton: true,
    }).then((result) => {
      if (result.isConfirmed) {
        this.dataset.progress = 'true';

        $(fa).removeClass(faClass).addClass(faClassProgress);

        let data = {
          id: [],
          __: __
        };

        $('.checkbox').each(function () {
          if (this.checked) {
            data.id.push(this.value);
          }
        });

        $.ajax({
          data: data,
          error: (xhr) => {
            Swal.fire({
              icon: 'error',
              text: xhr.responseJSON.message,
              title: lang.App.failed
            });


            $(fa).removeClass(faClassProgress).addClass(faClass);
            delete this.dataset.progress;
          },
          method: 'POST',
          success: (data) => {
            Swal.fire({
              icon: 'success',
              text: data.message,
              title: lang.App.success
            });

            $(fa).removeClass(faClassProgress).addClass(faClass);
            delete this.dataset.progress;

            if (typeof erp.table !== 'undefined') erp.table.draw(false);
            if (typeof erp.modalTable !== 'undefined') erp.modalTable.draw(false);
          },
          url: url
        });
      }
    });
  });

  $(document).on('click', '[data-action="darkmode"]', function () {
    let darkMode = 0;

    if ($('body').hasClass('dark-mode')) {
      darkMode = 0;
      $(this).find('i').removeClass('fa-sun').addClass('fa-moon');
      $('body').removeClass('dark-mode');
      $('.main-header').removeClass('navbar-dark bg-gradient-dark').addClass('navbar-light bg-gradient-white');
      $('.main-sidebar').removeClass('sidebar-dark-primary').addClass('sidebar-light-primary');
    } else {
      darkMode = 1;
      $(this).find('i').removeClass('fa-moon').addClass('fa-sun');
      $('body').addClass('dark-mode');
      $('.main-header').removeClass('navbar-light bg-gradient-white').addClass('navbar-dark bg-gradient-dark');
      $('.main-sidebar').removeClass('sidebar-light-primary').addClass('sidebar-dark-primary');
    }

    $.ajax({
      error: (xhr) => {
        if (xhr.status == 401) location.reload();
      },
      success: (data) => {

      },
      url: base_url + '/setting/theme?darkmode=' + darkMode
    });
  });

  $(document).on('click', '[data-action="http-get"]', function (e) {
    e.preventDefault();

    let url = this.href;
    let fa = $(this).find('i')[0];
    let faClass = fa.className;
    let faClassProgress = 'fad fa-spinner-third fa-spin';

    if (this.dataset.progress == 'true') {
      return false;
    }

    this.dataset.progress = 'true';

    $(fa).removeClass(faClass).addClass(faClassProgress);

    $.ajax({
      error: (xhr) => {
        Swal.fire({
          icon: 'error',
          text: xhr.responseJSON.message,
          title: lang.App.failed
        });

        $(fa).removeClass(faClassProgress).addClass(faClass);
        delete this.dataset.progress;
      },
      method: 'GET',
      success: (data) => {
        Swal.fire({
          icon: 'success',
          text: data.message,
          title: lang.App.success
        });

        $(fa).removeClass(faClassProgress).addClass(faClass);
        delete this.dataset.progress;

        if (typeof erp.table !== 'undefined') erp.table.draw(false);
      },
      url: url
    })
  });

  $(document).on('click', '[data-action="http-post"]', function (e) {
    e.preventDefault();

    let url = this.href;
    let fa = $(this).find('i')[0];
    let faClass = fa.className;
    let faClassProgress = 'fad fa-spinner-third fa-spin';

    if (this.dataset.progress == 'true') {
      return false;
    }

    this.dataset.progress = 'true';

    $(fa).removeClass(faClass).addClass(faClassProgress);

    $.ajax({
      data: {
        __: __
      },
      error: (xhr) => {
        Swal.fire({
          icon: 'error',
          text: xhr.responseJSON.message,
          title: lang.App.failed
        });

        $(fa).removeClass(faClassProgress).addClass(faClass);
        delete this.dataset.progress;
      },
      method: 'POST',
      success: (data) => {
        Swal.fire({
          icon: 'success',
          text: data.message,
          title: lang.App.success
        });

        $(fa).removeClass(faClassProgress).addClass(faClass);
        delete this.dataset.progress;

        if (typeof erp.table !== 'undefined') erp.table.draw(false);
      },
      url: url
    });
  });

  $(document).on('click', '[data-action="logout"]', function () {
    $.ajax({
      success: function (data) {
        if (isObject(data)) {
          if (data.code == 200) {
            toastr.success(data.message);
            setTimeout(() => location.href = '/auth/login');
            return true;
          }

          toastr.error(data.message);
        } else {
          toastr.error('Failed to logout.');
        }
      },
      url: base_url + '/auth/logout'
    });
  });

  $(document).on('click', '[data-action="export"]', function (e) {
    e.preventDefault();

    let url = this.href;
    let param = $(this.dataset.param).val();
    let fa = $(this).find('i')[0];
    let faClass = fa.className;
    let faClassProgress = 'fad fa-spinner-third fa-spin';

    if (this.dataset.progress == 'true') {
      return false;
    }

    this.dataset.progress = 'true';

    $(fa).removeClass(faClass).addClass(faClassProgress);

    let data = JSON.parse(param ? param : '{}');

    data.__ = __;

    let bank = $('#filter-bank').val();
    let biller = $('#filter-biller').val();
    let createdBy = $('#filter-createdby').val();
    let customer = $('#filter-customer').val();
    let paymentStatus = $('#filter-paymentstatus').val();
    let period = $('#filter-period').val();
    let status = $('#filter-status').val();
    let supplier = $('#filter-supplier').val();
    let warehouse = $('#filter-warehouse').val();
    let startDate = $('#filter-startdate').val();
    let endDate = $('#filter-enddate').val();

    if (bank) {
      data.bank = bank;
    }

    if (biller) {
      data.biller = biller;
    }

    if (createdBy) {
      data.created_by = createdBy;
    }

    if (customer) {
      data.customer = customer;
    }

    if (status) {
      data.status = status;
    }

    if (paymentStatus) {
      data.payment_status = paymentStatus;
    }

    if (period) {
      data.period = period;
    }

    if (supplier) {
      data.supplier = supplier;
    }

    if (warehouse) {
      data.warehouse = warehouse;
    }

    if (startDate) {
      data.start_date = startDate;
    }

    if (endDate) {
      data.end_date = endDate;
    }

    $.ajax({
      contentType: false,
      data: JSON.stringify(data),
      error: (xhr) => {
        Swal.fire({
          icon: 'error',
          text: xhr.responseJSON.message,
          title: lang.App.failed
        });

        $(fa).removeClass(faClassProgress).addClass(faClass);
        delete this.dataset.progress;
      },
      method: 'POST',
      processData: false,
      success: (data) => {
        Swal.fire({
          icon: 'success',
          text: data.message,
          title: lang.App.success
        });

        $(fa).removeClass(faClassProgress).addClass(faClass);
        delete this.dataset.progress;
      },
      url: url
    });
  });

  $(document).on('click', '[data-widget="pushmenu"]', function () {
    let collapse = 0;

    if ($('body').hasClass('sidebar-collapse')) {
      collapse = 1;
    } else {
      collapse = 0;
    }

    $.ajax({
      error: (xhr) => {
        if (xhr.status == 401) location.reload();
      },
      success: (data) => {

      },
      url: base_url + '/setting/sidebar?collapse=' + collapse
    });
  });

  $(document).on('click', '.change-locale', function (e) {
    e.preventDefault();

    $.ajax({
      success: function (data) {
        if (data.code == 200) {
          location.reload();
        } else {
          Swal.fire({ icon: 'error', text: data.message, title: data.title });
        }
      },
      url: this.href
    });
  });

  $(document).on('keyup', '.currency', function (e) {
    if (e.key != '.') $(this).val(formatCurrency($(this).val())).trigger('change');
  });

  $(document).on('click', '[data-toggle="modal"]', function (e) {
    let url = this.href ?? this.dataset.remote;

    if (url.slice(url.length - 1, 1) != '#') {
      let modalClass = (this.dataset.modalClass ?? '');
      let target = this.dataset.target;

      if (target.length) {
        document.querySelector(target).dataset.remote = url;
        $(target).find('.modal-dialog').addClass(modalClass);
      }
    }
  });

  $(document).on('click', '[href$="#"]', function (e) {
    e.preventDefault();
  });

  $(document).on('hidden.bs.modal', '.modal', function () {
    delete this.dataset.remote;
    this.querySelector('script')?.remove();
    $(this).find('.modal-dialog').prop('class', 'modal-dialog');
    $(this).find('.modal-title').html('');
    $(this).find('.modal-header').prop('class', 'modal-header');
    $(this).find('.modal-body').html(`<div class="modal-loader">
       <svg class="circular" viewBox="25 25 50 50">
         <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
       </svg></div>`);
    $(this).find('.modal-footer').html('');
    $(this).css('left', '').css('top', ''); // Reset modal position by draggable().

    if ($('.modal:visible').length) $('body').addClass('modal-open');

    erp.modal.pop();
  });

  // Fix echarts resize.
  $(window).on('resize', () => {
    let methods = Object.keys(erp.chart);

    if (methods.length) {
      for (let method of methods) {
        erp.chart[method].resize();
      }
    }
  });

  $(document).on('show.bs.modal', '.modal', function () {
    // $('body').addClass('modal-open'); // Fix body layout.

    // Make stackable modal.
    const zIndex = 3 + 10 * $('.modal:visible').length;
    $(this).css('z-index', zIndex);
    setTimeout(() => {
      $('.modal-backdrop').not('.stacked').css('z-index', zIndex - 1).addClass('stacked')
    });
  });

  $(document).on('shown.bs.modal', '.modal', function () {
    let remote = (this.dataset.remote ?? null);
    if (typeof erp.modal == 'undefined') erp.modal = []; // Stackable Modal

    erp.modal.push(this);

    $.ajax({
      error: (xhr) => {
        delete this.dataset.remote;
        if (isObject(xhr.responseJSON)) {
          Swal.fire({ icon: 'error', text: xhr.responseJSON.message, title: xhr.status }).then((result) => {
            $(this).modal('hide');
          });

          if (xhr.status == 401) {
            location.reload();
          }
        } else {
          Swal.fire({ icon: 'error', text: xhr.statusText, title: xhr.status }).then((result) => {
            $(this).modal('hide');
          });
        }
      },
      success: (data) => {
        delete this.dataset.remote;

        if (isObject(data) && data.code == 200) {

          if (typeof data.content == 'undefined') {
            console.error('Modal cannot loaded. Object "content" is not defined.');
          }
          $(this).find('.modal-content').html(data.content)
            .closest(this).draggable({ handle: '.modal-header' });
        } else if (isObject(data) && data.code != 200) {
          Swal.fire({ icon: 'error', text: data.message, title: data.title }).then((result) => {
            $(this).modal('hide');
            if (data.code == 205) location.reload();
          });
        }
      },
      url: remote
    });
  });

  $(document).on('click', '.table-row-delete', function () {
    $(this).closest('tr').remove();

    calculateSale();
  });

  // setInterval(() => {
  //   fetch(base_url + '/auth/status').then((response) => {
  //     if (response.status == 403) {
  //       location.reload();
  //     }

  //     response.json().then((response) => {

  //     });
  //   });
  // }, 60 * 1000);

  $.extend(true, $.fn.DataTable.defaults, {
    drawCallback: function (settings) {
      initControls();
    },
    language: { url: base_url + '/assets/modules/datatables/Locales/' + langId + '.json' }
  });
});
