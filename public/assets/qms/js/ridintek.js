"use strict";

function append_zero(number) { // Return as string, you can convert to number with parseInt().
  if (number < 10) {
    return '0' + number;
  }
  return number;
}

/**
 * Get time difference.
 * @param {string} timestr1 Time string at first time. Ex. 00:20:43
 * @param {string} timestr2 Time string at last time. Ex. 00:31:22
 * @return {string} Return Time string difference. Ex. 01:20:30
 */
function get_time_difference(timestr1, timestr2) {
  let time1 = timestr1.split(':');
  let time2 = timestr2.split(':');
  let time1sec = (parseInt(time1[0]) * 3600) + (parseInt(time1[1]) * 60) + parseInt(time1[2]);
  let time2sec = (parseInt(time2[0]) * 3600) + (parseInt(time2[1]) * 60) + parseInt(time2[2]);
  let diff = time2sec - time1sec;
  let hours = Math.floor(diff / 3600);
  let minutes = Math.floor((diff % 3600) / 60);
  let seconds = Math.floor(diff % 60);

  return `${append_zero(hours)}:${append_zero(minutes)}:${append_zero(seconds)}`;
}

function is_object_different(obj1, obj2) {
  return (JSON.stringify(obj1) === JSON.stringify(obj2) ? false : true);
}

/**
 * Convert string to unix time miliseconds.
 * @param {string} time Time string.
 */
function strtotime(time) {
  return Date.parse(time);
}

function separate_char(char) {
  let buff = '';
  if (char.length > 0) {
    for (let a = 0; a < char.length; a++) {
      buff = buff + char[a] + ', ';
    }
  }
  return buff;
}

/**
 * QueueConfig
 */
class QueueConfig {
  constructor() {

  }

  clear() {
    localStorage.clear();
  }

  delete(name) {
    return localStorage.removeItem(name);
  }

  get(name) {
    return localStorage.getItem(name);
  }

  getObject(name) {
    return JSON.parse(localStorage.getItem(name));
  }

  set(name, value) {
    let val = value;
    if (typeof value === 'object') val = JSON.stringify(value);
    localStorage.setItem(name, val);
  }

  setObject(name, value) {
    this.set(name, value);
  }
}

/**
 * QueueHttp
 */
class QueueHttp {
  constructor() {
    this._headers = [];
  }

  header(name, value) {
    this._headers.push({
      name: name,
      value: value
    });
  }

  async send(method, url, data = null) {
    return new Promise((resolve, reject) => {
      let parsed = '';
      let response = null;
      let xmlhttp = new XMLHttpRequest();

      xmlhttp.addEventListener('error', () => {
        if (xmlhttp.status == 0) {
          console.error(`Request failed: ${url}`);
          reject(url);
        } else {
          console.error(`Status (${xmlhttp.status}): Error on request(${xmlhttp.readyState}): ${url}`);
          reject(url);
        }
      });

      xmlhttp.addEventListener('load', () => {
        if (xmlhttp.readyState == 4) {
          if (xmlhttp.status == 200) {
            if (xmlhttp.getResponseHeader('Content-Type') == 'application/json') {
              response = JSON.parse(xmlhttp.response);
            } else {
              response = xmlhttp.response;
            }
            resolve(response);
          } else {
            reject(xmlhttp.response);
          }
        }
      });

      method = (method ?? 'GET');
      xmlhttp.open(method, url);
      xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); // Default type form.

      if (data !== null && typeof data === 'object') {
        for (let a in data) {
          parsed += a + '=' + data[a] + '&';
        }
        // parsed = parsed.substr(0, parsed.length - 1); // substr is deprecated.
        parsed = parsed.slice(0, parsed.length - 1);
      } else {
        parsed = data;
      }

      if (this._headers) {
        for (let a in this._headers) {
          xmlhttp.setRequestHeader(this._headers[a].name, this._headers[a].value);
        }
      }

      xmlhttp.send(parsed);
    });
  }
}

/**
 * QueueManagementSystem
 */
class QueueManagementSystem {
  constructor() {
    this.qhttp = new QueueHttp();
  }

  async getDisplayData(warehouse_id) {
    return new Promise((resolve, reject) => {
      resolve(this.qhttp.send('GET', base_url + '/qms/getDisplayData/' + warehouse_id));
    });
  }

  async sendDisplayResponse(ticket_id) {
    return new Promise((resolve, reject) => {
      let data = {};
      data[_x] = _vx;
      resolve(this.qhttp.send('POST', base_url + '/qms/displayResponse/' + ticket_id, data));
    });
  }

  async sendReport(data = {}) {
    return new Promise((resolve, reject) => {
      data[_x] = _vx;
      console.log('%cSent', 'color:lime');
      console.log(data);
      resolve(this.qhttp.send('POST', base_url + '/qms/sendReport', data));
    });
  }
}

class QueueNotify {
  constructor() {
    this.audio = {
      error: new Audio(`${base_url}/assets/qms/audio/nasty-error-short.mp3`),
      success: new Audio(`${base_url}/assets/qms/audio/when.mp3`),
      warning: new Audio(`${base_url}/assets/qms/audio/system-fault.mp3`)
    };
  }

  error(msg, delay = 5) {
    alertify.set('notifier', 'position', 'top-center');
    alertify.error(msg, delay);
    this.audio.error.play();
  }

  success(msg, delay = 5) {
    alertify.set('notifier', 'position', 'top-center');
    alertify.success(msg, delay);
    this.audio.success.play();
  }

  warning(msg, delay = 5) {
    alertify.set('notifier', 'position', 'top-center');
    alertify.warning(msg, delay);
    this.audio.warning.play();
  }
}

/**
 * QueueTimer
 */
class QueueTimer {
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
    }
  }

  decrement(time_str) {
    if (!time_str) return false;
    let hours = parseInt(time_str.split(':')[0]);
    let minutes = parseInt(time_str.split(':')[1]);
    let seconds = parseInt(time_str.split(':')[2]);

    this._sec -= (hours * 3600) + (minutes * 60) + seconds;

    for (let a in this._cb) {
      if (this._cb[a].event == 'decrement') {
        this._cb[a].callback(this);
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
      if (this._cb[a].event == 'increment') {
        this._cb[a].callback(this);
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
      if (this._cb[a].event == 'reset') {
        this._cb[a].callback(this);
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
      if (this._cb[a].event == 'set') {
        this._cb[a].callback(this);
      }
    }

    return this;
  }

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
   * @param {*} mode
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
    if (this._hTimer) return false; // If instance present, then ignore it.

    // Callback Handler.
    for (let a in this._cb) {
      if (this._cb[a].event == 'start') {
        this._cb[a].callback(this);
      }
    }

    this._hTimer = window.setInterval(() => {
      if (this._mode == QueueTimer.CLOCKWISE_MODE) this._sec++;

      // Prevent minus decrement.
      if (!(this.getHours() == '00' && this.getMinutes() == '00' && this.getSeconds() == '00')) {
        if (this._mode == QueueTimer.COUNTERCLOCKWISE_MODE) this._sec--;
      }

      // Callback Handler.
      for (let a in this._cb) {
        if (this._cb[a].event == 'limit') {
          if (this.getHours() == this._limit.hours && this.getMinutes() == this._limit.minutes && this.getSeconds() == this._limit.seconds) {
            this._cb[a].callback(this);
            this.stop(); // Stop after limit reached.
          }
        }

        if (this._cb[a].event == 'ticking') {
          this._cb[a].callback(this);
        }

        if (this._cb[a].event == 'timeout') {
          if (this.getHours() == '00' && this.getMinutes() == '00' && this.getSeconds() == '00') {
            if (this._mode == QueueTimer.COUNTERCLOCKWISE_MODE) {
              this._cb[a].callback(this);
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
        if (this._cb[a].event == 'stop') {
          this._cb[a].callback(this);
        }
      }

      this._hTimer = null;
    }

    return this;
  }
}
