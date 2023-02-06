<?php if ($recallTicketId = getGet('recall')) : ?>
  <script>
    $(document).ready(function() {
      window.recallTicketId = <?= $recallTicketId ?>;
      history.pushState(null, null, base_url + '/qms/counter');
    });
  </script>
<?php endif; ?>
<link href="<?= base_url('assets/qms/css/ridintek.css?v=') . $resver ?>" rel="stylesheet" />
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card shadow">
        <div class="card-header bg-gradient-dark">
          <?= lang('App.counter'); ?> <span id="counter_number"></span>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header bg-gradient-primary">ACTION</div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-9">
                      <button class="btn btn-lg btn-primary" id="btn-call"><i class="fa fa-megaphone"></i> <span data-field="label">CALL</span></button>
                      <button class="btn btn-lg btn-danger" id="btn-recall"><i class="fa fa-phone"></i> <span data-field="label">RECALL</span></button>
                      <button class="btn btn-lg btn-success" id="btn-serve"><i class="fa fa-play"></i> <span data-field="label">SERVE</span></button>
                      <button class="btn btn-lg btn-warning" id="btn-rest"><i class="fa fa-mug-hot"></i> <span data-field="label">REST</span></button>
                      <button class="btn btn-lg btn-default" id="btn-extend"><i class="fa fa-clock"></i> <span data-field="label">EXTEND TIME</span></button>
                      <button class="btn btn-lg btn-primary" id="btn-addsale"><i class="fa fa-plus"></i> <span data-field="label">ADD SALE</span></button>
                    </div>
                    <div class="col-md-3">
                      <?php $counterOpts = [
                        '0' => 'Counter Offline',
                        '1' => 'Counter 1',
                        '2' => 'Counter 2',
                        '3' => 'Counter 3',
                        '4' => 'Counter 4',
                        '5' => 'Counter 5'
                      ];
                      ?>
                      <select id="cb-counter" name="counter" class="select" style="width:100%">
                        <?php foreach ($counterOpts as $key => $value) : ?>
                          <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header bg-gradient-success">COUNTER INFORMATION</div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-sm-3 bold">Counter Status</div>
                    <div class="col-sm-3" id="counter_status">-</div>
                    <div class="col-sm-3 bold">Over-Waiting Call Time</div>
                    <div class="col-sm-3" id="timer_over_waitcall"></div>
                  </div>
                  <div class="row">
                    <div class="col-sm-3 bold">Progress Time</div>
                    <div class="col-sm-3" id="timer_progress"></div>
                    <div class="col-sm-3 bold">Over-Waiting Serve Time</div>
                    <div class="col-sm-3" id="timer_over_waitserve"></div>
                  </div>
                  <div class="row">
                    <div class="col-sm-3 bold">Resting Time</div>
                    <div class="col-sm-3" id="timer_resting"></div>
                    <div class="col-sm-3 bold">Over-Resting Time</div>
                    <div class="col-sm-3" id="timer_over_rest"></div>
                  </div>
                  <div class="row">
                    <div class="col-sm-3 bold">Serving Time</div>
                    <div class="col-sm-3" id="timer_serving"></div>
                    <div class="col-sm-3 bold">Over-Serving Time</div>
                    <div class="col-sm-3" id="timer_over_serve"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header bg-gradient-warning">CUSTOMER INFORMATION</div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-sm-3 bold">Ticket</div>
                    <div class="col-sm-3" id="cust_ticket">-</div>
                    <div class="col-sm-3 bold">Time Limit</div>
                    <div class="col-sm-3" id="timer_cust_time_limit"></div>
                  </div>
                  <div class="row">
                    <div class="col-sm-3 bold">Category</div>
                    <div class="col-sm-3" id="cust_category">-</div>
                    <div class="col-sm-3 bold">Waiting Time</div>
                    <div class="col-sm-3" id="timer_cust_waiting"></div>
                  </div>
                  <div class="row">
                    <div class="col-sm-3 bold">Name</div>
                    <div class="col-sm-3" id="cust_name">-</div>
                    <div class="col-sm-3 bold">Serving Time</div>
                    <div class="col-sm-3" id="timer_cust_serving"></div>
                  </div>
                  <div class="row">
                    <div class="col-sm-3 bold">Phone</div>
                    <div class="col-sm-3" id="cust_phone">-</div>
                    <div class="col-sm-3 bold">Over-Serving Time</div>
                    <div class="col-sm-3" id="timer_cust_over_serve"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="module">
  import {
    QMS,
    QueueConfig,
    QueueHttp,
    QueueNotify,
    QueueTimer
  } from '<?= base_url('assets/app/js/ridintek.js?v=') . $resver ?>';

  $(document).ready(function() {
    // 1# COUNTER TIMER
    let timerOverResting = new QueueTimer('#timer_over_rest');
    let timerOverServing = new QueueTimer('#timer_over_serve');
    let timerOverWaitCall = new QueueTimer('#timer_over_waitcall');
    let timerOverWaitServe = new QueueTimer('#timer_over_waitserve');
    let timerProgress = new QueueTimer('#timer_progress');
    let timerResting = new QueueTimer('#timer_resting');
    let timerServing = new QueueTimer('#timer_serving');

    // 2# CUSTOMER TIMER
    let timerCustTimeLimit = new QueueTimer('#timer_cust_time_limit');
    let timerCustWaiting = new QueueTimer('#timer_cust_waiting');
    let timerCustServing = new QueueTimer('#timer_cust_serving');
    let timerCustOverServe = new QueueTimer('#timer_cust_over_serve');

    // 3# TIMEOUT TIMER
    let timerWaitCallTimeout = new QueueTimer();
    let timerWaitServeTimeout = new QueueTimer();

    // REGISTER TO GLOBAL.
    // Since assign as object it will be set by reference not value.
    window.timerOverResting = timerOverResting;
    window.timerOverServing = timerOverServing;
    window.timerOverWaitCall = timerOverWaitCall;
    window.timerOverWaitServe = timerOverWaitServe;
    window.timerProgress = timerProgress;
    window.timerResting = timerResting;
    window.timerServing = timerServing;
    window.timerCustTimeLimit = timerCustTimeLimit;
    window.timerCustWaiting = timerCustWaiting;
    window.timerCustServing = timerCustServing;
    window.timerCustOverServe = timerCustOverServe;
    window.timerWaitCallTimeout = timerWaitCallTimeout
    window.timerWaitServeTimeout = timerWaitServeTimeout

    // General.
    let PopupTimer = null;
    let hPopupTimer = null;

    let stOverResting = 'status_over_rest';
    let stOverServing = 'status_over_serve';
    let stOverWaitCall = 'status_over_waitcall';
    let stOverWaitServe = 'status_over_waitserve';
    let stProgress = 'status_progress';
    let stResting = 'status_resting';
    let stServing = 'status_serving';

    let tmrOverResting = 'timer_over_rest';
    let tmrOverServing = 'timer_over_serve';
    let tmrOverWaitCall = 'timer_over_waitcall';
    let tmrOverWaitServe = 'timer_over_waitserve';
    let tmrProgress = 'timer_progress';
    let tmrResting = 'timer_resting';
    let tmrServing = 'timer_serving';

    // TIMER LIMIT EVENT.
    timerResting.on('limit', () => {
      timerOverResting.start();
    });

    // TIMER RESET EVENT.
    timerOverResting.on('reset', (timer) => {
      QueueConfig.set(tmrOverResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverServing.on('reset', (timer) => {
      QueueConfig.set(tmrOverServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverWaitCall.on('reset', (timer) => {
      QueueConfig.set(tmrOverWaitCall, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverWaitServe.on('reset', (timer) => {
      QueueConfig.set(tmrOverWaitServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerProgress.on('reset', (timer) => {
      QueueConfig.set(tmrProgress, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerResting.on('reset', (timer) => {
      QueueConfig.set(tmrResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerServing.on('reset', (timer) => {
      QueueConfig.set(tmrServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // TIMER SET EVENT.
    timerOverResting.on('set', (timer) => {
      QueueConfig.set(tmrOverResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverServing.on('set', (timer) => {
      QueueConfig.set(tmrOverServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverWaitCall.on('set', (timer) => {
      QueueConfig.set(tmrOverWaitCall, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverWaitServe.on('set', (timer) => {
      QueueConfig.set(tmrOverWaitServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerProgress.on('set', (timer) => {
      QueueConfig.set(tmrProgress, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerResting.on('set', (timer) => {
      QueueConfig.set(tmrResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerServing.on('set', (timer) => {
      QueueConfig.set(tmrServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // TIMER START EVENT.
    timerOverResting.on('start', () => {
      QueueConfig.set(stOverResting, 'start');
    });
    timerOverServing.on('start', () => {
      QueueConfig.set(stOverServing, 'start');
    });
    timerOverWaitCall.on('start', () => {
      QueueConfig.set(stOverWaitCall, 'start');
    });
    timerOverWaitServe.on('start', () => {
      QueueConfig.set(stOverWaitServe, 'start');
    });
    timerProgress.on('start', () => {
      QueueConfig.set(stProgress, 'start');
    });
    timerResting.on('start', () => {
      QueueConfig.set(stResting, 'start');
    });
    timerServing.on('start', () => {
      QueueConfig.set(stServing, 'start');
    });

    // TIMER STOP EVENT.
    timerOverResting.on('stop', () => {
      QueueConfig.set(stOverResting, 'stop');
    });
    timerOverServing.on('stop', () => {
      QueueConfig.set(stOverServing, 'stop');
    });
    timerOverWaitCall.on('stop', () => {
      QueueConfig.set(stOverWaitCall, 'stop');
    });
    timerOverWaitServe.on('stop', () => {
      QueueConfig.set(stOverWaitServe, 'stop');
    });
    timerProgress.on('stop', () => {
      QueueConfig.set(stProgress, 'stop');
    });
    timerResting.on('stop', () => {
      QueueConfig.set(stResting, 'stop');
    });
    timerServing.on('stop', () => {
      QueueConfig.set(stServing, 'stop');
    });

    // TIMER TICKING EVENT.
    timerOverResting.on('ticking', (timer) => {
      QueueConfig.set(tmrOverResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverServing.on('ticking', (timer) => {
      QueueConfig.set(tmrOverServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverWaitCall.on('ticking', (timer) => {
      QueueConfig.set(tmrOverWaitCall, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerOverWaitServe.on('ticking', (timer) => {
      QueueConfig.set(tmrOverWaitServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerProgress.on('ticking', (timer) => {
      QueueConfig.set(tmrProgress, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerResting.on('ticking', (timer) => {
      QueueConfig.set(tmrResting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerServing.on('ticking', (timer) => {
      QueueConfig.set(tmrServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // Restore Counter Last Time.
    timerOverResting.set(QueueConfig.get(tmrOverResting) ?? '00:00:00');
    timerOverServing.set(QueueConfig.get(tmrOverServing) ?? '00:00:00');
    timerOverWaitCall.set(QueueConfig.get(tmrOverWaitCall) ?? '00:00:00');
    timerOverWaitServe.set(QueueConfig.get(tmrOverWaitServe) ?? '00:00:00');
    timerProgress.set(QueueConfig.get(tmrProgress) ?? '00:00:00');
    timerResting.set(QueueConfig.get(tmrResting) ?? '00:00:00');
    timerServing.set(QueueConfig.get(tmrServing) ?? '00:00:00');

    // Start Timer from config.
    if (QueueConfig.get(stOverResting) == 'start') timerOverResting.start();
    if (QueueConfig.get(stOverServing) == 'start') timerOverServing.start();
    if (QueueConfig.get(stOverWaitCall) == 'start') timerOverWaitCall.start();
    if (QueueConfig.get(stOverWaitServe) == 'start') timerOverWaitServe.start();
    if (QueueConfig.get(stProgress) == 'start') timerProgress.start();
    if (QueueConfig.get(stResting) == 'start') timerResting.start();
    if (QueueConfig.get(stServing) == 'start') timerServing.start();

    let stCustServing = 'status_cust_serving';
    let stCustOverServe = 'status_cust_over_serve';

    let tmrCustTimeLimit = 'timer_cust_time_limit';
    let tmrCustWaiting = 'timer_cust_waiting';
    let tmrCustServing = 'timer_cust_serving';
    let tmrCustOverServe = 'timer_cust_over_serve';

    timerCustServing.setMode(QueueTimer.COUNTERCLOCKWISE_MODE); // Countdown.

    // TIMER RESET EVENT.
    timerCustTimeLimit.on('reset', (timer) => {
      QueueConfig.set(tmrCustTimeLimit, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerCustWaiting.on('reset', (timer) => {
      QueueConfig.set(tmrCustWaiting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerCustServing.on('reset', (timer) => {
      QueueConfig.set(tmrCustServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerCustOverServe.on('reset', (timer) => {
      QueueConfig.set(tmrCustOverServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // TIMER SET EVENT.
    timerCustTimeLimit.on('set', (timer) => {
      QueueConfig.set(tmrCustTimeLimit, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerCustWaiting.on('set', (timer) => {
      QueueConfig.set(tmrCustWaiting, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerCustServing.on('set', (timer) => {
      QueueConfig.set(tmrCustServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerCustOverServe.on('set', (timer) => {
      QueueConfig.set(tmrCustOverServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // TIMER START EVENT.
    timerCustServing.on('start', () => {
      QueueConfig.set(stCustServing, 'start');
    });
    timerCustOverServe.on('start', () => {
      QueueConfig.set(stCustOverServe, 'start');
    });

    // TIMER STOP EVENT.
    timerCustServing.on('stop', () => {
      QueueConfig.set(stCustServing, 'stop');
    });
    timerCustOverServe.on('stop', () => {
      QueueConfig.set(stCustOverServe, 'stop');
    });

    // TIMER TICKING EVENT.
    timerCustServing.on('ticking', (timer) => {
      QueueConfig.set(tmrCustServing, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerCustOverServe.on('ticking', (timer) => {
      QueueConfig.set(tmrCustOverServe, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // TIMER TIMEOUT EVENT.
    timerCustServing.on('timeout', () => {
      timerOverServing.start();
      timerCustOverServe.start();
    });

    // Restore Customer Last Time.
    timerCustTimeLimit.set(QueueConfig.get(tmrCustTimeLimit) ?? '00:00:00');
    timerCustWaiting.set(QueueConfig.get(tmrCustWaiting) ?? '00:00:00');
    timerCustServing.set(QueueConfig.get(tmrCustServing) ?? '00:00:00');
    timerCustOverServe.set(QueueConfig.get(tmrCustOverServe) ?? '00:00:00');

    // Start Timer from Config.
    if (QueueConfig.get(stCustServing) == 'start') timerCustServing.start();
    if (QueueConfig.get(stCustOverServe) == 'start') timerCustOverServe.start();

    let stWaitCallTimeout = 'status_wait_call_timeout';
    let stWaitServeTimeout = 'status_wait_serve_timeout';

    let tmrWaitCallTimeout = 'timer_wait_call_timeout';
    let tmrWaitServeTimeout = 'timer_wait_serve_timeout';

    // TIMER LIMIT REACHED EVENT.
    timerWaitCallTimeout.on('limit', () => {
      timerOverWaitCall.start();

    });
    timerWaitServeTimeout.on('limit', () => {
      timerOverWaitServe.start();
    });

    timerWaitCallTimeout.on('reset', (timer) => {
      QueueConfig.set(tmrWaitCallTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerWaitServeTimeout.on('reset', (timer) => {
      QueueConfig.set(tmrWaitServeTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // TIMER SET EVENT.
    timerWaitCallTimeout.on('set', (timer) => {
      QueueConfig.set(tmrWaitCallTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerWaitServeTimeout.on('set', (timer) => {
      QueueConfig.set(tmrWaitServeTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // TIMER START EVENT.
    timerWaitCallTimeout.on('start', () => {
      QueueConfig.set(stWaitCallTimeout, 'start');
    });
    timerWaitServeTimeout.on('start', () => {
      QueueConfig.set(stWaitServeTimeout, 'start');
    });

    // TIMER STOP EVENT.
    timerWaitCallTimeout.on('stop', () => {
      QueueConfig.set(stWaitCallTimeout, 'stop');
    });
    timerWaitServeTimeout.on('stop', () => {
      QueueConfig.set(stWaitServeTimeout, 'stop');
    });

    // TIMER TICKING EVENT.
    timerWaitCallTimeout.on('ticking', (timer) => {
      QueueConfig.set(tmrWaitCallTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });
    timerWaitServeTimeout.on('ticking', (timer) => {
      QueueConfig.set(tmrWaitServeTimeout, `${timer.getHours()}:${timer.getMinutes()}:${timer.getSeconds()}`);
    });

    // Restore Timer Config.
    timerWaitCallTimeout.set(QueueConfig.get(tmrWaitCallTimeout) ?? '00:00:00');
    timerWaitServeTimeout.set(QueueConfig.get(tmrWaitServeTimeout) ?? '00:00:00');

    // Set Timeout
    timerWaitCallTimeout.setLimit('00:01:00');
    timerWaitServeTimeout.setLimit('00:01:00');

    // Start Timer from Config.
    if (QueueConfig.get(stWaitCallTimeout) == 'start') timerWaitCallTimeout.start();
    if (QueueConfig.get(stWaitServeTimeout) == 'start') timerWaitServeTimeout.start();

    async function CounterMessage() {
      // If counter online then...
      if (QueueConfig.get('counter_status') && QueueConfig.get('counter_status') != 'offline') {
        let displayData = await QMS.getDisplayData(window.warehouseCode);

        if (displayData.data.queue_list.data.length) {
          if (QueueConfig.get('counter_status') == 'idle') {
            if (!timerWaitCallTimeout.isRunning() && !timerOverWaitCall.isRunning()) {
              QueueNotify.warning('Ada antrian pelanggan. Silakan untuk segera memanggil. Waktu 1 menit.');
              timerWaitCallTimeout.start();
            }
          }
          QueueConfig.set('no_queue', false);
        } else {
          QueueConfig.set('no_queue', true);
        }

        if (QueueConfig.get('counter_status') == 'call') {
          if (!timerWaitServeTimeout.isRunning() && !timerOverWaitServe.isRunning()) {
            QueueNotify.warning('Silakan untuk segera melayani. Waktu 1 menit.');
            timerWaitServeTimeout.start();
          }
        }

        if (window.show_timer && QueueConfig.get('counter_status') != 'idle') {
          PopupTimer = alertify.warning('');
          PopupTimer.ondismiss = function() {
            return false;
          };

          if (hPopupTimer) window.clearInterval(hPopupTimer);

          hPopupTimer = window.setInterval(() => {
            PopupTimer.setContent(
              `<div class="row no-print">
              <div class="col-sm-8" style="color:#000000"><strong>Serving Time</strong></div>
              <div class="col-sm-4" style="color:#000000">${QueueConfig.get(tmrCustServing)}</div>
            </div>
            <div class="row no-print">
              <div class="col-sm-8" style="color:#000000"><strong>Over-Serving Time</strong></div>
              <div class="col-sm-4" style="color:#000000">${QueueConfig.get(tmrCustOverServe)}</div>
            </div>
            <div class="row no-print">
              <div class="col-sm-12"><a href="${base_url}/qms/counter" data-action="link">BACK TO COUNTER</a></div>
            </div>`);
          }, 500);

          window.show_timer = false;
        }
      } // if offline

      window.setTimeout(CounterMessage, 5000);
    }

    window.setTimeout(CounterMessage, 5000);

    window._x = '<?= csrf_token() ?>';
    window._vx = '<?= csrf_hash() ?>';
    window.warehouseCode = '<?= session('login')->warehouse ?? 'LUC' ?>';
    let Counter = {
      number: 0,
      setNumber: function(number) {
        if (number === null) return false;
        if (number == 0) this.setStatus('offline');
        this.number = number;
        counter_number = number;
        $('#counter_number').html(number > 0 ? number : ' (Offline)');
        QueueConfig.set('counter_number', number);
      },
      setStatus: function(status) {
        if (!status) return false;
        this.status = status;
        let st = '';
        switch (status) {
          case 'call': {
            st = 'CALLING';
            break;
          }
          case 'idle': {
            st = 'IDLE';
            break;
          }
          case 'offline': {
            st = 'OFFLINE';
            break;
          }
          case 'paused': {
            st = 'PAUSED';
            break;
          }
          case 'rest': {
            st = 'RESTING';
            break;
          }
          case 'serve': {
            st = 'SERVING';
            break;
          }
          default:
            st = status.toUpperCase();
        }

        $('#counter_status').html(st);
        QueueConfig.set('counter_status', status);
      },
      status: null
    };

    let counter_number = <?= (session('login')?->counter ?? 0); ?>; // Updated by get_counter_number() function.

    // ACTION CONTROL
    let btnCall = $('#btn-call');
    let btnRecall = $('#btn-recall');
    let btnServe = $('#btn-serve');
    let btnRest = $('#btn-rest');
    let btnExtend = $('#btn-extend');
    let btnAddSale = $('#btn-addsale');
    let cbCounter = $('#cb-counter');

    // CUSTOMER INFO CONTROL
    let CustTicket = $('#cust_ticket');
    let CustCategory = $('#cust_category');
    let CustName = $('#cust_name');
    let CustPhone = $('#cust_phone');

    btnCall.click(async function() { // CALL
      $(this).prop('disabled', true);

      let res = {};

      if (typeof window.recallTicketId !== 'undefined' && window.recallTicketId) {
        res = await QueueHttp.send('GET', base_url + '/qms/recallQueue/' + window.recallTicketId);
        delete(window.recallTicketId);
      } else {
        res = await QueueHttp.send('GET', base_url + '/qms/callQueue/<?= session('login')->warehouse ?? 'LUC' ?>');
      }

      let old_ticket = QueueConfig.getObject('ticket_data');

      if (old_ticket) { // END OLD TICKET
        let data = {};
        data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';
        data['serve_time'] = timerCustServing.getTime();
        data['ticket'] = old_ticket.id;

        if (Counter.status == 'serve' || Counter.status == 'paused') {
          QueueHttp.send('POST', base_url + '/qms/endQueue', data).then((r) => {
            if (!r.error) {
              console.log('Current ticket has been ended.');
            } else {
              console.log(r.msg);
            }
          });
        } else if (Counter.status == 'call') {
          QueueHttp.send('POST', base_url + '/qms/skipQueue', data).then((r) => {
            if (!r.error) {
              console.log('Current ticket has been skipped.');
            } else {
              console.log(r.msg);
            }
          });
        }
      }

      if (!res.error) {
        let ticket = res.data;

        //console.log(ticket);

        Counter.setStatus('call');
        initializeControls();
        QueueConfig.set('ticket_data', JSON.stringify(ticket));
        QueueConfig.delete('extend_time');

        if (!timerProgress.isRunning()) timerProgress.start();

        if (ticket.prefix == 'D') {
          QueueConfig.set('edit_design', true);
        } else {
          QueueConfig.set('edit_design', false);
        }

        CustTicket.html(ticket.token);
        CustCategory.html(ticket.queue_category_name);
        CustName.html(ticket.customer_name);
        CustPhone.html(ticket.customer_phone);
        timerCustTimeLimit.set(ticket.duration);
        timerCustWaiting.set(getTimeDifference(ticket.date.split(' ')[1], ticket.call_date.split(' ')[1]));

        timerCustServing.stop().set(ticket.duration);
        timerCustOverServe.stop().reset();
      } else {
        timerProgress.stop();
        timerOverServing.stop();
        timerCustOverServe.stop();

        Counter.setStatus('idle');
        QueueNotify.error('<strong>Tidak ada antrian. Silakan coba lagi beberapa menit.</strong>', 2);

        initializeControls();
      }

      $(this).prop('disabled', false);

      timerOverResting.stop();
      timerOverServing.stop();
      timerOverWaitCall.stop();
      timerOverWaitServe.stop();
      timerResting.stop();
      timerServing.stop();

      timerCustServing.stop();
      timerCustOverServe.stop();

      timerWaitCallTimeout.stop().reset();
      timerWaitServeTimeout.stop().reset();
    });

    btnRecall.click(async function() { // RECALL
      $(this).prop('disabled', true);

      let ticket = QueueConfig.getObject('ticket_data');

      let res = await QueueHttp.send('GET', base_url + '/qms/recallQueue/' + ticket.id);

      if (!res.error) {
        QueueNotify.success('<strong>Recall success.</strong>');
        timerWaitServeTimeout.reset();
      }

      $(this).prop('disabled', false);

      timerOverWaitCall.stop();
      timerOverWaitServe.stop();
      timerResting.stop();
      timerServing.stop();

      timerCustServing.stop();
      timerCustOverServe.stop();

      timerWaitCallTimeout.stop().reset();
      timerWaitServeTimeout.reset();
    });

    btnServe.click(async function() { // START SERVING
      let data = {};
      let ticket = QueueConfig.getObject('ticket_data');

      data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';
      data['ticket'] = ticket.id;

      $(this).prop('disabled', true);

      let res = await QueueHttp.send('POST', base_url + '/qms/serveQueue', data);

      if (!res.error) {
        timerServing.start();
        timerCustServing.start();
        Counter.setStatus('serve');
        initializeControls();
        QueueNotify.success('Serving started.');
      } else {
        QueueNotify.error(res.msg);
        $(this).prop('disabled', false);
      }

      timerOverWaitCall.stop();
      timerOverWaitServe.stop();
      timerResting.stop();

      timerCustOverServe.stop();

      timerWaitCallTimeout.stop().reset();
      timerWaitServeTimeout.stop().reset();
    });

    btnRest.click(async function() { // START RESTING
      let data = {};
      let old_ticket = QueueConfig.getObject('ticket_data');

      data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';

      $(this).prop('disabled', true);

      if (old_ticket) { // END OLD TICKET
        let data = {};
        data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';
        data['serve_time'] = timerCustServing.getTime();
        data['ticket'] = old_ticket.id;

        if (Counter.status == 'serve' || Counter.status == 'paused') {
          QueueHttp.send('POST', base_url + '/qms/endQueue', data).then((r) => {
            if (!r.error) {
              console.log('Current ticket has been ended.');
            } else {
              console.log(r.msg);
            }
          });
        } else if (Counter.status == 'call') {
          QueueHttp.send('POST', base_url + '/qms/skipQueue', data).then((r) => {
            if (!r.error) {
              console.log('Current ticket has been skipped.');
            } else {
              console.log(r.msg);
            }
          });
        }
      }

      timerResting.setLimit('00:15:00');
      timerResting.start();
      QueueNotify.success('Sekarang anda dapat beristirahat.');
      Counter.setStatus('rest');

      initializeControls();

      timerOverResting.stop();
      timerOverServing.stop();
      timerOverWaitCall.stop();
      timerOverWaitServe.stop();
      timerProgress.stop();
      timerServing.stop();
      timerCustServing.stop();
      timerCustOverServe.stop();
      timerWaitCallTimeout.stop().reset();
      timerWaitServeTimeout.stop().reset();
    });

    btnExtend.click(function() {
      let extend_qty = (QueueConfig.get('extend_qty') ?? 0);

      timerCustServing.increment('00:10:00');
      extend_qty++;
      QueueConfig.set('extend_qty', extend_qty);
      QueueNotify.success(`Perpanjangan waktu 10 menit berhasil ditambahkan sebanyak ${extend_qty}x.`);

      $(this).prop('disabled', true);

      window.setTimeout(() => {
        $(this).prop('disabled', false);
      }, 10 * 1000); // Time out for 10 seconds.
    });

    btnAddSale.click(function() {
      location.href = base_url + 'sales/add?opt=noattachment';

      $(this).prop('disabled', true);
    });

    cbCounter.change(function() { // Counter number change.
      let data = {};
      data.counter = this.value;
      data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';
      QueueHttp.send('POST', base_url + '/qms/setCounter', data).then((res) => {
        if (!res.error) {
          Counter.setNumber(this.value);
          if (!QueueConfig.get('counter_status') || QueueConfig.get('counter_status') == 'offline') {
            Counter.setStatus(this.value > 0 ? 'idle' : 'offline');
          } else {
            Counter.setStatus(QueueConfig.get('counter_status'));
          }

          initializeControls();
        } else {
          QueueNotify.error('Failed to change counter number.');
        }
      }).catch((res) => {
        QueueNotify.error('Failed to change counter number. Please check your network status!');
        console.groupCollapsed('cbCounter.onChange', 'color: yellow');
        console.warn(res);
        console.groupEnd();
      });
    });

    $('#holdMe').click(function() {
      if (Counter.status == 'serve') {
        Counter.setStatus('paused');
        timerCustServing.stop();
        timerCustOverServe.stop();
        timerProgress.stop();
        timerServing.stop();
        timerOverServing.stop();
      } else if (Counter.status == 'paused') {
        Counter.setStatus('serve');
        timerCustServing.start();
        timerProgress.start();
        timerServing.start();
      }
    });

    function initializeControls() {
      let status = '';
      let no_queue = (!QueueConfig.get('no_queue') ? true : (QueueConfig.getObject('no_queue') == true ? true : false));

      if (Counter.number > 0) { // COUNTER ONLINE
        cbCounter.prop('disabled', true);

        if (Counter.status == 'idle') { // IDLE
          btnCall.prop('disabled', false);
          btnRecall.prop('disabled', true);
          btnServe.prop('disabled', true);
          btnRest.prop('disabled', true);
          btnExtend.prop('disabled', true);
          btnAddSale.prop('disabled', true);

          if (no_queue) {
            cbCounter.prop('disabled', false);
          }
        } else if (Counter.status == 'call') { // CALLING
          btnCall.prop('disabled', false);
          btnRecall.prop('disabled', false);
          btnServe.prop('disabled', false);
          btnRest.prop('disabled', false);
          btnExtend.prop('disabled', true);
          btnAddSale.prop('disabled', true);
        } else if (Counter.status == 'serve') { // SERVING
          btnCall.prop('disabled', false);
          btnRecall.prop('disabled', true);
          btnServe.prop('disabled', true);
          btnRest.prop('disabled', false);

          if (!no_queue) {
            btnExtend.prop('disabled', true);
          } else if (no_queue) {
            btnExtend.prop('disabled', false);
          }

          btnAddSale.prop('disabled', false);
        } else if (Counter.status == 'rest') { // RESTING
          btnCall.prop('disabled', false);
          btnRecall.prop('disabled', true);
          btnServe.prop('disabled', true);
          btnRest.prop('disabled', true);
          btnExtend.prop('disabled', true);
          btnAddSale.prop('disabled', true);
        }
      } else if (Counter.number == 0) { // COUNTER OFFLINE
        btnCall.prop('disabled', true);
        btnRecall.prop('disabled', true);
        btnServe.prop('disabled', true);
        btnRest.prop('disabled', true);
        btnExtend.prop('disabled', true);
        btnAddSale.prop('disabled', true);

        // TIMERS
        timerProgress.stop().reset();
        timerResting.stop().reset();
        timerServing.stop().reset();
        timerOverWaitCall.stop().reset();
        timerOverWaitServe.stop().reset();
        timerOverResting.stop().reset();
        timerOverServing.stop().reset();

        timerCustServing.stop().reset();
        timerCustOverServe.stop().reset();

        timerWaitCallTimeout.stop().reset();
        timerWaitServeTimeout.stop().reset();
      }
    }

    function restore_state() {
      let ticket = QueueConfig.getObject('ticket_data');

      cbCounter.val(counter_number).trigger('change');

      if (ticket) {
        CustTicket.html(ticket.token);
        CustCategory.html(ticket.queue_category_name);
        CustName.html(ticket.customer_name);
        CustPhone.html(ticket.customer_phone);
        timerCustTimeLimit.set(ticket.duration);
        timerCustWaiting.set(getTimeDifference(ticket.date.split(' ')[1], ticket.call_date.split(' ')[1]));
      }
    }

    window.setInterval(() => {
      let no_queue = (!QueueConfig.get('no_queue') ? true : (JSON.parse(QueueConfig.getObject('no_queue')) == true ? true : false));

      if (QueueConfig.get('counter_status') == 'serve' && !no_queue && !btnExtend.is(':disabled')) {
        btnExtend.prop('disabled', true);
      }
    }, 2500);

    restore_state(); // Restore counter state.
  });
</script>