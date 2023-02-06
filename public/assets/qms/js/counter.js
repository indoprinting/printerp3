"use strict";
/**
 * counter.js run on every page. Except Display and Register.
 */

// ENTRY POINT
$(document).ready(function () {
  // 1# COUNTER TIMER
  let timerOverResting   = new QueueTimer('#timer_over_rest');
  let timerOverServing   = new QueueTimer('#timer_over_serve');
  let timerOverWaitCall  = new QueueTimer('#timer_over_waitcall');
  let timerOverWaitServe = new QueueTimer('#timer_over_waitserve');
  let timerProgress      = new QueueTimer('#timer_progress');
  let timerResting       = new QueueTimer('#timer_resting');
  let timerServing       = new QueueTimer('#timer_serving');

  // 2# CUSTOMER TIMER
  let timerCustTimeLimit = new QueueTimer('#timer_cust_time_limit');
  let timerCustWaiting   = new QueueTimer('#timer_cust_waiting');
  let timerCustServing   = new QueueTimer('#timer_cust_serving');
  let timerCustOverServe = new QueueTimer('#timer_cust_over_serve');

  // 3# TIMEOUT TIMER
  let timerWaitCallTimeout = new QueueTimer();
  let timerWaitServeTimeout = new QueueTimer();

  // REGISTER TO GLOBAL.
  // Since assign as object it will be set by reference not value.
  window.timerOverResting      = timerOverResting;
  window.timerOverServing      = timerOverServing;
  window.timerOverWaitCall     = timerOverWaitCall;
  window.timerOverWaitServe    = timerOverWaitServe;
  window.timerProgress         = timerProgress;
  window.timerResting          = timerResting;
  window.timerServing          = timerServing;
  window.timerCustTimeLimit    = timerCustTimeLimit;
  window.timerCustWaiting      = timerCustWaiting;
  window.timerCustServing      = timerCustServing;
  window.timerCustOverServe    = timerCustOverServe;
  window.timerWaitCallTimeout  = timerWaitCallTimeout 
  window.timerWaitServeTimeout = timerWaitServeTimeout

  // General.
  let QConfig    = new QueueConfig();
  let QMS        = new QueueManagementSystem();
  let QNotify    = new QueueNotify();
  let PopupTimer = null;
  let hPopupTimer = null;
  let show_timer = false;
  let hSendReport = null;
  let sendReport = false;

  let stOverResting   = 'status_over_rest';
  let stOverServing   = 'status_over_serve';
  let stOverWaitCall  = 'status_over_waitcall';
  let stOverWaitServe = 'status_over_waitserve';
  let stProgress      = 'status_progress';
  let stResting       = 'status_resting';
  let stServing       = 'status_serving';

  let tmrOverResting   = 'timer_over_rest';
  let tmrOverServing   = 'timer_over_serve';
  let tmrOverWaitCall  = 'timer_over_waitcall';
  let tmrOverWaitServe = 'timer_over_waitserve';
  let tmrProgress      = 'timer_progress';
  let tmrResting       = 'timer_resting';
  let tmrServing       = 'timer_serving';

  // TIMER LIMIT EVENT.
  timerResting.on('limit', () => {
    timerOverResting.start();
  });

  // TIMER RESET EVENT.
  timerOverResting.on('reset', (timer) => {
    QConfig.set(tmrOverResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverServing.on('reset', (timer) => {
    QConfig.set(tmrOverServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverWaitCall.on('reset', (timer) => {
    QConfig.set(tmrOverWaitCall, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverWaitServe.on('reset', (timer) => {
    QConfig.set(tmrOverWaitServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerProgress.on('reset', (timer) => {
    QConfig.set(tmrProgress, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerResting.on('reset', (timer) => {
    QConfig.set(tmrResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerServing.on('reset', (timer) => {
    QConfig.set(tmrServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // TIMER SET EVENT.
  timerOverResting.on('set', (timer) => {
    QConfig.set(tmrOverResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverServing.on('set', (timer) => {
    QConfig.set(tmrOverServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverWaitCall.on('set', (timer) => {
    QConfig.set(tmrOverWaitCall, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverWaitServe.on('set', (timer) => {
    QConfig.set(tmrOverWaitServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerProgress.on('set', (timer) => {
    QConfig.set(tmrProgress, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerResting.on('set', (timer) => {
    QConfig.set(tmrResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerServing.on('set', (timer) => {
    QConfig.set(tmrServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // TIMER START EVENT.
  timerOverResting.on('start', () => {
    QConfig.set(stOverResting, 'start');
  });
  timerOverServing.on('start', () => {
    QConfig.set(stOverServing, 'start');
  });
  timerOverWaitCall.on('start', () => {
    QConfig.set(stOverWaitCall, 'start');
  });
  timerOverWaitServe.on('start', () => {
    QConfig.set(stOverWaitServe, 'start');
  });
  timerProgress.on('start', () => {
    QConfig.set(stProgress, 'start');
  });
  timerResting.on('start', () => {
    QConfig.set(stResting, 'start');
  });
  timerServing.on('start', () => {
    QConfig.set(stServing, 'start');
  });

  // TIMER STOP EVENT.
  timerOverResting.on('stop', () => {
    QConfig.set(stOverResting, 'stop');
  });
  timerOverServing.on('stop', () => {
    QConfig.set(stOverServing, 'stop');
  });
  timerOverWaitCall.on('stop', () => {
    QConfig.set(stOverWaitCall, 'stop');
  });
  timerOverWaitServe.on('stop', () => {
    QConfig.set(stOverWaitServe, 'stop');
  });
  timerProgress.on('stop', () => {
    QConfig.set(stProgress, 'stop');
  });
  timerResting.on('stop', () => {
    QConfig.set(stResting, 'stop');
  });
  timerServing.on('stop', () => {
    QConfig.set(stServing, 'stop');
  });

  // TIMER TICKING EVENT.
  timerOverResting.on('ticking', (timer) => {
    QConfig.set(tmrOverResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverServing.on('ticking', (timer) => {
    QConfig.set(tmrOverServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverWaitCall.on('ticking', (timer) => {
    QConfig.set(tmrOverWaitCall, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerOverWaitServe.on('ticking', (timer) => {
    QConfig.set(tmrOverWaitServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerProgress.on('ticking', (timer) => {
    QConfig.set(tmrProgress, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerResting.on('ticking', (timer) => {
    QConfig.set(tmrResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerServing.on('ticking', (timer) => {
    QConfig.set(tmrServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // Restore Counter Last Time.
  timerOverResting.set(QConfig.get(tmrOverResting) ?? '00:00:00');
  timerOverServing.set(QConfig.get(tmrOverServing) ?? '00:00:00');
  timerOverWaitCall.set(QConfig.get(tmrOverWaitCall) ?? '00:00:00');
  timerOverWaitServe.set(QConfig.get(tmrOverWaitServe) ?? '00:00:00');
  timerProgress.set(QConfig.get(tmrProgress) ?? '00:00:00');
  timerResting.set(QConfig.get(tmrResting) ?? '00:00:00');
  timerServing.set(QConfig.get(tmrServing) ?? '00:00:00');

  // Start Timer from config.
  if (QConfig.get(stOverResting) == 'start') timerOverResting.start();
  if (QConfig.get(stOverServing) == 'start') timerOverServing.start();
  if (QConfig.get(stOverWaitCall) == 'start') timerOverWaitCall.start();
  if (QConfig.get(stOverWaitServe) == 'start') timerOverWaitServe.start();
  if (QConfig.get(stProgress) == 'start') timerProgress.start();
  if (QConfig.get(stResting) == 'start') timerResting.start();
  if (QConfig.get(stServing) == 'start') timerServing.start();

  let stCustServing   = 'status_cust_serving';
  let stCustOverServe = 'status_cust_over_serve';

  let tmrCustTimeLimit = 'timer_cust_time_limit';
  let tmrCustWaiting   = 'timer_cust_waiting';
  let tmrCustServing   = 'timer_cust_serving';
  let tmrCustOverServe = 'timer_cust_over_serve';

  timerCustServing.setMode(QueueTimer.COUNTERCLOCKWISE_MODE); // Countdown.

  // TIMER RESET EVENT.
  timerCustTimeLimit.on('reset', (timer) => {
    QConfig.set(tmrCustTimeLimit, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerCustWaiting.on('reset', (timer) => {
    QConfig.set(tmrCustWaiting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerCustServing.on('reset', (timer) => {
    QConfig.set(tmrCustServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerCustOverServe.on('reset', (timer) => {
    QConfig.set(tmrCustOverServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // TIMER SET EVENT.
  timerCustTimeLimit.on('set', (timer) => {
    QConfig.set(tmrCustTimeLimit, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerCustWaiting.on('set', (timer) => {
    QConfig.set(tmrCustWaiting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerCustServing.on('set', (timer) => {
    QConfig.set(tmrCustServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerCustOverServe.on('set', (timer) => {
    QConfig.set(tmrCustOverServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // TIMER START EVENT.
  timerCustServing.on('start', () => {
    QConfig.set(stCustServing, 'start');
  });
  timerCustOverServe.on('start', () => {
    QConfig.set(stCustOverServe, 'start');
  });

  // TIMER STOP EVENT.
  timerCustServing.on('stop', () => {
    QConfig.set(stCustServing, 'stop');
  });
  timerCustOverServe.on('stop', () => {
    QConfig.set(stCustOverServe, 'stop');
  });

  // TIMER TICKING EVENT.
  timerCustServing.on('ticking', (timer) => {
    QConfig.set(tmrCustServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerCustOverServe.on('ticking', (timer) => {
    QConfig.set(tmrCustOverServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // TIMER TIMEOUT EVENT.
  timerCustServing.on('timeout', () => {
    timerOverServing.start();
    timerCustOverServe.start();
  });

  // Restore Customer Last Time.
  timerCustTimeLimit.set(QConfig.get(tmrCustTimeLimit) ?? '00:00:00');
  timerCustWaiting.set(QConfig.get(tmrCustWaiting) ?? '00:00:00');
  timerCustServing.set(QConfig.get(tmrCustServing) ?? '00:00:00');
  timerCustOverServe.set(QConfig.get(tmrCustOverServe) ?? '00:00:00');

  // Start Timer from Config.
  if (QConfig.get(stCustServing) == 'start') timerCustServing.start();
  if (QConfig.get(stCustOverServe) == 'start') timerCustOverServe.start();

  let stWaitCallTimeout  = 'status_wait_call_timeout';
  let stWaitServeTimeout = 'status_wait_serve_timeout';

  let tmrWaitCallTimeout  = 'timer_wait_call_timeout';
  let tmrWaitServeTimeout = 'timer_wait_serve_timeout';

  // TIMER LIMIT REACHED EVENT.
  timerWaitCallTimeout.on('limit', () => {
    timerOverWaitCall.start();

  });
  timerWaitServeTimeout.on('limit', () => {
    timerOverWaitServe.start();
  });

  timerWaitCallTimeout.on('reset', (timer) => {
    QConfig.set(tmrWaitCallTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerWaitServeTimeout.on('reset', (timer) => {
    QConfig.set(tmrWaitServeTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // TIMER SET EVENT.
  timerWaitCallTimeout.on('set', (timer) => {
    QConfig.set(tmrWaitCallTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerWaitServeTimeout.on('set', (timer) => {
    QConfig.set(tmrWaitServeTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // TIMER START EVENT.
  timerWaitCallTimeout.on('start', () => {
    QConfig.set(stWaitCallTimeout, 'start');
  });
  timerWaitServeTimeout.on('start', () => {
    QConfig.set(stWaitServeTimeout, 'start');
  });

  // TIMER STOP EVENT.
  timerWaitCallTimeout.on('stop', () => {
    QConfig.set(stWaitCallTimeout, 'stop');
  });
  timerWaitServeTimeout.on('stop', () => {
    QConfig.set(stWaitServeTimeout, 'stop');
  });

  // TIMER TICKING EVENT.
  timerWaitCallTimeout.on('ticking', (timer) => {
    QConfig.set(tmrWaitCallTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });
  timerWaitServeTimeout.on('ticking', (timer) => {
    QConfig.set(tmrWaitServeTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
  });

  // Restore Timer Config.
  timerWaitCallTimeout.set(QConfig.get(tmrWaitCallTimeout) ?? '00:00:00');
  timerWaitServeTimeout.set(QConfig.get(tmrWaitServeTimeout) ?? '00:00:00');

  // Set Timeout
  timerWaitCallTimeout.setLimit('00:01:00');
  timerWaitServeTimeout.setLimit('00:01:00');

  // Start Timer from Config.
  if (QConfig.get(stWaitCallTimeout) == 'start') timerWaitCallTimeout.start();
  if (QConfig.get(stWaitServeTimeout) == 'start') timerWaitServeTimeout.start();

  async function CounterMessage () {
    // If counter online then...
    if (QConfig.get('counter_status') && QConfig.get('counter_status') != 'offline') {
      let display_data = await QMS.getDisplayData(warehouse_id);

      if ( ! display_data.queue_list.error) {
        if (QConfig.get('counter_status') == 'idle') {
          if ( ! timerWaitCallTimeout.isRunning() && ! timerOverWaitCall.isRunning()) {
            QNotify.warning('Ada antrian pelanggan. Silakan untuk segera memanggil. Waktu 1 menit.');
            timerWaitCallTimeout.start();
          }
        }
        QConfig.set('no_queue', false);
      } else {
        QConfig.set('no_queue', true);
      }

      if (QConfig.get('counter_status') == 'call') {
        if ( ! timerWaitServeTimeout.isRunning() && ! timerOverWaitServe.isRunning()) {
          QNotify.warning('Silakan untuk segera melayani. Waktu 1 menit.');
          timerWaitServeTimeout.start();
        }
      }

      if ( ! sendReport) { // Send counter report.
        hSendReport = window.setInterval(() => {
          QMS.sendReport({
            over_wait_call_time: QConfig.get('timer_over_waitcall'),
            over_wait_serve_time: QConfig.get('timer_over_waitserve'),
            over_serve_time: QConfig.get('timer_over_serve'),
            over_rest_time: QConfig.get('timer_over_rest')
          }).then((data) => {
            if (isObject(data) && data.error) {
              //QNotify.warning('Report tidak dapat dikirim. <b>Mohon cek koneksi internet!</b>');
            }
          });
        }, 30 * 1000); // 30 sec.

        sendReport = true;
      }

      if ( ! show_timer && QConfig.get('counter_status') != 'idle') {
        PopupTimer = alertify.warning('');
        PopupTimer.ondismiss = function() { return false; };

        if (hPopupTimer) window.clearInterval(hPopupTimer);

        hPopupTimer = window.setInterval(() => {
          PopupTimer.setContent(
            `<div class="row no-print">
              <div class="col-sm-8"><strong>Serving Time</strong></div>
              <div class="col-sm-4">${QConfig.get(tmrCustServing)}</div>
            </div>
            <div class="row no-print">
              <div class="col-sm-8"><strong>Over-Serving Time</strong></div>
              <div class="col-sm-4">${QConfig.get(tmrCustOverServe)}</div>
            </div>
            <div class="row no-print">
              <div class="col-sm-12"><a href="${site.base_url}qms/counter">BACK TO COUNTER</a></div>
            </div>`);
        }, 500);

        show_timer = true;
      }
    } // if offline

    window.setTimeout(CounterMessage, 5000);
  }

  typing('nopgplease', function () {
    Notify.success('Cheat activated', 'top-left');
    timerOverWaitCall.stop().reset();
    timerOverWaitServe.stop().reset();
    timerOverResting.stop().reset();
    timerOverServing.stop().reset();
    timerCustOverServe.stop().reset();
    timerWaitCallTimeout.stop().reset();
    timerWaitServeTimeout.stop().reset();
  });

  window.setTimeout(CounterMessage, 5000);
});
