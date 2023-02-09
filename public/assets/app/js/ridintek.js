
export default class Ridintek {
  tbody = null;

  constructor(table) {
    this.tbody = $(table).find('tbody');
  }

  addItem(item) {
    console.log(item);
  }
}

/**
 * QueueConfig
 */
export class QueueConfig {
  static clear() {
    localStorage.clear();
  }

  static delete(name) {
    return localStorage.removeItem(name);
  }

  static get(name) {
    return localStorage.getItem(name);
  }

  static getObject(name) {
    return JSON.parse(localStorage.getItem(name));
  }

  static set(name, value) {
    let val = value;
    if (typeof value === 'object') val = JSON.stringify(value);
    localStorage.setItem(name, val);
  }

  static setObject(name, value) {
    this.set(name, value);
  }
}

/**
* QueueHttp
*/
export class QueueHttp {
  constructor() {
    this._headers = {};
  }

  static setHeaders(headers = {}) {
    this._headers = headers;
    return this;
  }

  static async send(method, url, data = null) {
    return new Promise((resolve, reject) => {
      $.ajax({
        data: data,
        error: (xhr) => {
          reject(xhr.response);
        },
        headers: this._headers,
        method: method,
        success: (data) => {
          resolve(data);
        },
        url: url
      });
    });
  }
}

/**
 * QueueManagementSystem
 */
export class QMS {
  static async getDisplayData(warehouseCode) {
    return new Promise((resolve, reject) => {
      resolve(QueueHttp.send('GET', base_url + '/qms/getDisplayData/' + warehouseCode));
    });
  }

  static async sendDisplayResponse(ticket_id) {
    return new Promise((resolve, reject) => {
      let data = {};
      data[_x] = _vx;
      resolve(QueueHttp.send('POST', base_url + '/qms/displayResponse/' + ticket_id, data));
    });
  }

  static async sendReport(data = {}) {
    return new Promise((resolve, reject) => {
      data[_x] = _vx;
      console.log('%cSent', 'color:lime');
      console.log(data);
      resolve(QueueHttp.send('POST', base_url + '/qms/sendReport', data));
    });
  }
}

export class QueueNotify {
  static audio;

  static {
    this.audio = {
      error: new Audio(`${base_url}/assets/qms/audio/nasty-error-short.mp3`),
      success: new Audio(`${base_url}/assets/qms/audio/when.mp3`),
      warning: new Audio(`${base_url}/assets/qms/audio/system-fault.mp3`)
    };
  }

  static error(msg, delay = 5) {
    toastr.options.timeOut = delay * 1000;
    toastr.error(msg);
    this.audio.error.play();
  }

  static success(msg, delay = 5) {
    toastr.options.timeOut = delay * 1000;
    toastr.success(msg);
    this.audio.success.play();
  }

  static warning(msg, delay = 5) {
    toastr.options.timeOut = delay * 1000;
    toastr.warning(msg);
    this.audio.warning.play();
  }
}

/**
 * QueueTimer
 */
export class QueueTimer {
  static CLOCKWISE_MODE = 0;
  static COUNTERCLOCKWISE_MODE = 1;

  constructor(selector = null) {
    this._mode = 0;
    this._cb = []; // array of [event: '', callback: null]
    this._hElm = null;
    this._hTimer = null;
    this._limit = {
      hours: '', minutes: '', seconds: ''
    };
    this._sec = 0;

    if (selector) {
      this._hElm = document.querySelector(selector);

      if (!this._hElm) {
        console.warn('QueueTimer::constructor(): Element is not defined.');
      }
    }
  }

  decrement(time_str) {
    if (!time_str) return false;
    let hours = parseInt(time_str.split(':')[0]);
    let minutes = parseInt(time_str.split(':')[1]);
    let seconds = parseInt(time_str.split(':')[2]);

    this._sec -= (hours * 3600) + (minutes * 60) + seconds;

    for (let a in this._cb) {
      if (this._cb[a].event == 'decrement' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    return this;
  }

  increment(time_str) {
    if (!time_str) return false;
    let hours = parseInt(time_str.split(':')[0]);
    let minutes = parseInt(time_str.split(':')[1]);
    let seconds = parseInt(time_str.split(':')[2]);

    this._sec += (hours * 3600) + (minutes * 60) + seconds;

    for (let a in this._cb) {
      if (this._cb[a].event == 'increment' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    return this;
  }

  getHours() {
    let hour = Math.floor(this._sec / 3600);
    hour = (hour < 10 ? '0' + hour : hour);
    return hour;
  }

  getMinutes() {
    let min = Math.floor((this._sec % 3600) / 60);
    min = (min < 10 ? '0' + min : min);
    return min;
  }

  getSeconds() {
    let sec = Math.floor((this._sec % 3600) % 60);
    sec = (sec < 10 ? '0' + sec : sec);
    return sec;
  }

  getTime() {
    return `${this.getHours()}:${this.getMinutes()}:${this.getSeconds()}`;
  }

  isRunning() {
    return (this._hTimer ? true : false);
  }

  /**
   * Event callback for `limit`, `reset`, `start`, `set`, `stop`, `ticking`, `timeout`.
   *
   * - `limit` Reached after timer equal as `setLimit` time.
   * - `reset` Reached after reset event occurred.
   * - `start` Reached after start event occurred.
   * - `set` Reached after set event occurred.
   * - `stop` Reached after stop event occurred.
   * - `ticking` Reached every second if timer has been started.
   * - `timeout` Reached after timer become `00:00:00`.
   *
   * @param {string} event
   * @param {function} callback
   * @returns `QueueTimer`
   */
  on(event, callback) {
    this._cb.push({
      event: event,
      callback: callback
    });

    return this;
  }

  /**
   * Reset timer into `00:00:00`.
   * @returns `QueueTimer`
   */
  reset() {
    this._sec = 0;

    for (let a in this._cb) {
      if (this._cb[a].event == 'reset' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    if (this._hElm) {
      this._hElm.innerHTML = this.getTime();
    }

    return this;
  }

  seconds() {
    return this._sec;
  }

  set(time_str) {
    if (!time_str) return false;
    let hours = parseInt(time_str.split(':')[0]);
    let minutes = parseInt(time_str.split(':')[1]);
    let seconds = parseInt(time_str.split(':')[2]);

    this._sec = (hours * 3600) + (minutes * 60) + seconds;

    if (this._hElm) {
      this._hElm.innerHTML = time_str;
    }

    for (let a in this._cb) {
      if (this._cb[a].event == 'set' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    return this;
  }

  /**
   * Set timer limit if time is reached. Suitable for increment or decrement.
   * @param {string} time_str Time string 'hh:mm:ss'
   * @returns Return QueueTimer object.
   */
  setLimit(time_str) {
    this._limit.hours = time_str.split(':')[0];
    this._limit.minutes = time_str.split(':')[1];
    this._limit.seconds = time_str.split(':')[2];

    return this;
  }

  /**
   * Set Timer mode.
   *
   * Available mode:
   * - `QueueTimer.CLOCKWISE_MODE` Timer will count up.
   * - `QueueTimer.COUNTERCLOCKWISE_MODE` Timer will count down.
   *
   * @param {Number} mode
   * @returns
   */
  setMode(mode) {
    this._mode = mode;

    return this;
  }

  setMiliseconds(miliseconds) {
    this._sec = Math.floor(miliseconds / 1000);

    return this;
  }

  setSeconds(seconds) {
    this._sec = seconds;

    return this;
  }

  start() {
    if (this._hTimer) {
      console.warn('hTimer instance is invalid.');
      return false; // If instance present, then ignore it.
    }

    // Callback Handler.
    for (let a in this._cb) {
      if (this._cb[a].event == 'start' && typeof this._cb[a].callback == 'function') {
        this._cb[a].callback.call(this, this);
      }
    }

    this._hTimer = setInterval(() => {
      // Increment timer.
      if (this._mode == QueueTimer.CLOCKWISE_MODE) {
        this._sec++;
      }

      // Decrement timer.
      if (this._mode == QueueTimer.COUNTERCLOCKWISE_MODE) {
        // Prevent minus decrement.
        if (!(this.getHours() == '00' && this.getMinutes() == '00' && this.getSeconds() == '00')) {
          this._sec--;
        }
      }

      // Callback Handler.
      for (let a in this._cb) {
        if (this._cb[a].event == 'limit' && typeof this._cb[a].callback == 'function') {
          if (this.getHours() == this._limit.hours && this.getMinutes() == this._limit.minutes && this.getSeconds() == this._limit.seconds) {
            this._cb[a].callback.call(this, this);
            this.stop(); // Stop after limit reached.
          }
        }

        if (this._cb[a].event == 'ticking' && typeof this._cb[a].callback == 'function') {
          this._cb[a].callback.call(this, this);
        }

        if (this._cb[a].event == 'timeout' && typeof this._cb[a].callback == 'function') {
          if (this.getHours() == '00' && this.getMinutes() == '00' && this.getSeconds() == '00') {
            if (this._mode == QueueTimer.COUNTERCLOCKWISE_MODE) {
              this._cb[a].callback.call(this, this);
            }
            this.stop(); // Stop after timeout reached.
          }
        }
      }

      // WRONG POSITION.
      // if (this._mode == QueueTimer.CLOCKWISE_MODE) this._sec++;
      // if (this._mode == QueueTimer.COUNTERCLOCKWISE_MODE) this._sec--;

      if (this._hElm) {
        this._hElm.innerHTML = `${this.getHours()}:${this.getMinutes()}:${this.getSeconds()}`;
      }
    }, 1000);
    return true;
  }

  stop() {
    if (this._hTimer) {
      window.clearInterval(this._hTimer);

      // Callback Handler.
      for (let a in this._cb) {
        if (this._cb[a].event == 'stop' && typeof this._cb[a].callback == 'function') {
          this._cb[a].callback.call(this, this);
        }
      }

      this._hTimer = null;
    }

    return this;
  }
}

export class Sale {
  tbody = null;

  constructor(table) {
    this.tbody = $(table).find('tbody');
  }

  addItem(item, allowDuplicate = false) {
    if (!allowDuplicate) {
      let items = this.tbody.find('.item_name');

      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    item.area = item.width * item.length;
    item.price = getSalePrice(item.area * item.quantity, item.ranges, item.prices);
    item.subtotal = item.area * item.price;

    let readOnly = (item.category != 'DPI' ? ' readonly' : '');

    this.tbody.append(`
      <tr>
        <td class="col-md-3">
          <input type="hidden" name="item[ranges][]" value="${JSON.stringify(item.ranges)}">
          <input type="hidden" name="item[prices][]" value="${JSON.stringify(item.prices)}">
          <input type="hidden" name="item[code][]" class="item_name" value="${item.code}">
          (${item.code}) ${item.name}
        </td>
        <td>
          <div class="card card-dark card-tabs">
            <div class="card-header bg-gradient-indigo p-0 pt-1">
              <ul class="nav nav-tabs">
                <li class="nav-item">
                  <a href="#tab-size-${item.code}" class="nav-link active" data-toggle="pill">${lang.App.size}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-spec-${item.code}" class="nav-link" data-toggle="pill">${lang.App.spec}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-opr-${item.code}" class="nav-link" data-toggle="pill">${lang.App.operator}</a>
                </li>
                <li class="nav-item">
                  <a href="#tab-price-${item.code}" class="nav-link" data-toggle="pill">${lang.App.price}</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane fade active show" id="tab-size-${item.code}">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>${lang.App.width}</label>
                        <input name="item[width][]" type="number" class="form-control form-control-border form-control-sm saleitem" min="0" value="${item.width}" style="max-width:60px" ${readOnly}>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>${lang.App.length}</label>
                        <input name="item[length][]" type="number" class="form-control form-control-border form-control-sm saleitem" min="0" value="${item.length}" style="max-width:60px" ${readOnly}>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>${lang.App.area}</label>
                        <input name="item[area][]" type="number" class="form-control form-control-border form-control-sm" min="0" value="${item.area}" style="max-width:60px" readonly>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>${lang.App.quantity}</label>
                        <input name="item[quantity][]" type="number" class="form-control form-control-border form-control-sm saleitem" min="0" value="${item.quantity}" style="max-width:60px">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-spec-${item.code}">
                  <div class="form-group">
                    <label>${lang.App.spec}</label>
                    <input name="item[spec][]" class="form-control form-control-border form-control-sm" placeholder="${lang.App.spec}" value="${item.spec}">
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-opr-${item.code}">
                  <div class="form-group">
                    <label>${lang.App.operator}</label>
                    <select name="item[operator][]" class="select-user" data-placeholder="${lang.App.operator}" style="width:100%">
                    </select>
                  </div>
                </div>
                <div class="tab-pane fade" id="tab-price-${item.code}">
                  <div class="form-group">
                    <label>${lang.App.price}</label>
                    <input name="item[price][]" class="form-control form-control-border form-control-sm currency saleitem" value="${item.price}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </td>
        <td class="saleitem-subtotal">${item.subtotal}</td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);

    calculateSale(this.tbody.closest('table'));
  }
}

export class StockAdjustment {
  tbody = null;

  constructor(table) {
    this.tbody = $(table).find('tbody');
  }

  addItem(item, allowDuplicate = false) {

    if (!allowDuplicate) {
      let items = this.tbody.find('.item_name');

      for (let i of items) {
        if (item.code == i.value) {
          toastr.error('Item has been added before.');
          return false;
        }
      }
    }

    this.tbody.append(`
      <tr>
        <input type="hidden" name="item[code][]" class="item_name" value="${item.code}">
        <td>(${item.code}) ${item.name}</td>
        <td><input type="number" name="item[quantity][]" class="form-control form-control-border form-control-sm" min="0" value="${filterDecimal(item.quantity)}"></td>
        <td>${filterDecimal(item.current_qty)}</td>
        <td><a href="#" class="table-row-delete"><i class="fad fa-fw fa-times"></i></a></td>
      </tr>
    `);
  }
}

export class TableFilter {
  constructor() {
    this._cb = [];
  }

  bind(action, selector) {
    if (action == 'apply') {
      $(document).on('click', selector, (ev) => {
        for (let a in this._cb) {
          if (this._cb[a].ev == 'apply' && typeof this._cb[a].cb == 'function') {
            this._cb[a].cb.call(this, this);
          }
        }

        if (typeof Table != 'undefined') {
          Table.draw(false);
        }

        controlSidebar('collapse');
      });
    }

    if (action == 'clear') {
      $(document).on('click', selector, (ev) => {
        for (let a in this._cb) {
          if (this._cb[a].ev == 'clear' && typeof this._cb[a].cb == 'function') {
            this._cb[a].cb.call(this, this);
          }
        }

        if (typeof Table != 'undefined') {
          Table.draw(false);
        }

        controlSidebar('collapse');
      });
    }
  }

  on(event, callback) {
    this._cb.push({
      ev: event,
      cb: callback
    });
  }
}