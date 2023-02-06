<?php if ($recallTicketId = getGet('recall')) : ?>
  <script>
    $(document).ready(function() {
      window.recallTicketid = <?= $recallTicketId ?>;
      history.pushState(null, null, base_url + '/qms/counter');
    });
  </script>
<?php endif; ?>
<link href="<?= base_url('assets/qms/css/ridintek.css?v=') . $resver ?>" rel="stylesheet" />
<div class="container-fluid">
  <div class="row">
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
<script src="<?= base_url('assets/qms/js/counter.js?v=') . $resver ?>"></script>
<script>
  $(document).ready(function() {
    'use strict';

    window._x = '<?= csrf_token() ?>';
    window._vx = '<?= csrf_hash() ?>';
    window.warehouseCode = '<?= session('login')->warehouse ?? 'LUC' ?>';
    let QConfig = new QueueConfig();
    let QHttp = new QueueHttp();
    let QNotify = new QueueNotify();
    let Counter = {
      number: 0,
      setNumber: function(number) {
        if (number === null) return false;
        if (number == 0) this.setStatus('offline');
        this.number = number;
        counter_number = number;
        $('#counter_number').html(number > 0 ? number : ' (Offline)');
        QConfig.set('counter_number', number);
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
        QConfig.set('counter_status', status);
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

      if (typeof window.recallTicketid !== 'undefined' && window.recallTicketid) {
        res = await QHttp.send('GET', base_url + '/qms/recallQueue/' + window.recallTicketid);
        delete(window.recallTicketid);
      } else {
        res = await QHttp.send('GET', base_url + '/qms/callQueue/<?= session('login')->warehouse ?? 'LUC' ?>');
      }

      let old_ticket = QConfig.getObject('ticket_data');

      if (old_ticket) { // END OLD TICKET
        let data = {};
        data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';
        data['serve_time'] = timerCustServing.getTime();
        data['ticket'] = old_ticket.id;

        if (Counter.status == 'serve' || Counter.status == 'paused') {
          QHttp.send('POST', base_url + '/qms/endQueue', data).then((r) => {
            if (!r.error) {
              console.log('Current ticket has been ended.');
            } else {
              console.log(r.msg);
            }
          });
        } else if (Counter.status == 'call') {
          QHttp.send('POST', base_url + '/qms/skipQueue', data).then((r) => {
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
        QConfig.set('ticket_data', ticket);
        QConfig.delete('extend_time');

        if (!timerProgress.isRunning()) timerProgress.start();

        if (ticket.prefix == 'D') {
          QConfig.set('edit_design', true);
        } else {
          QConfig.set('edit_design', false);
        }

        CustTicket.html(ticket.token);
        CustCategory.html(ticket.queue_category_name);
        CustName.html(ticket.customer_name);
        CustPhone.html(ticket.customer_phone);
        timerCustTimeLimit.set(ticket.duration);
        timerCustWaiting.set(get_time_difference(ticket.date.split(' ')[1], ticket.call_date.split(' ')[1]));

        timerCustServing.stop().set(ticket.duration);
        timerCustOverServe.stop().reset();
      } else {
        timerProgress.stop();
        timerOverServing.stop();
        timerCustOverServe.stop();

        Counter.setStatus('idle');
        QNotify.error('<strong>Tidak ada antrian. Silakan coba lagi beberapa menit.</strong>', 2);

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

      let ticket = QConfig.getObject('ticket_data');

      let res = await QHttp.send('GET', base_url + '/qms/recallQueue/' + ticket.id);

      if (!res.error) {
        QNotify.success('<strong>Recall success.</strong>');
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
      let ticket = QConfig.getObject('ticket_data');

      data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';
      data['ticket'] = ticket.id;

      $(this).prop('disabled', true);

      let res = await QHttp.send('POST', base_url + '/qms/serveQueue', data);

      if (!res.error) {
        timerServing.start();
        timerCustServing.start();
        Counter.setStatus('serve');
        initializeControls();
        QNotify.success('Serving started.');
      } else {
        QNotify.error(res.msg);
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
      let old_ticket = QConfig.getObject('ticket_data');

      data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';

      $(this).prop('disabled', true);

      if (old_ticket) { // END OLD TICKET
        let data = {};
        data[<?= csrf_token() ?>] = '<?= csrf_hash() ?>';
        data['serve_time'] = timerCustServing.getTime();
        data['ticket'] = old_ticket.id;

        if (Counter.status == 'serve' || Counter.status == 'paused') {
          QHttp.send('POST', base_url + '/qms/endQueue', data).then((r) => {
            if (!r.error) {
              console.log('Current ticket has been ended.');
            } else {
              console.log(r.msg);
            }
          });
        } else if (Counter.status == 'call') {
          QHttp.send('POST', base_url + '/qms/skipQueue', data).then((r) => {
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
      QNotify.success('Sekarang anda dapat beristirahat.');
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
      let extend_qty = (QConfig.get('extend_qty') ?? 0);

      timerCustServing.increment('00:10:00');
      extend_qty++;
      QConfig.set('extend_qty', extend_qty);
      QNotify.success(`Perpanjangan waktu 10 menit berhasil ditambahkan sebanyak ${extend_qty}x.`);

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
      QHttp.send('POST', base_url + '/qms/setCounter', data).then((res) => {
        if (!res.error) {
          Counter.setNumber(this.value);
          if (!QConfig.get('counter_status') || QConfig.get('counter_status') == 'offline') {
            Counter.setStatus(this.value > 0 ? 'idle' : 'offline');
          } else {
            Counter.setStatus(QConfig.get('counter_status'));
          }

          initializeControls();
        } else {
          QNotify.error('Failed to change counter number.');
        }
      }).catch((res) => {
        QNotify.error('Failed to change counter number. Please check your network status!');
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
      let no_queue = (!QConfig.get('no_queue') ? true : (QConfig.getObject('no_queue') == true ? true : false));

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
      let ticket = QConfig.getObject('ticket_data');

      cbCounter.val(counter_number).trigger('change');

      if (ticket) {
        CustTicket.html(ticket.token);
        CustCategory.html(ticket.queue_category_name);
        CustName.html(ticket.customer_name);
        CustPhone.html(ticket.customer_phone);
        timerCustTimeLimit.set(ticket.duration);
        timerCustWaiting.set(get_time_difference(ticket.date.split(' ')[1], ticket.call_date.split(' ')[1]));
      }
    }

    window.setInterval(() => {
      let no_queue = (!QConfig.get('no_queue') ? true : (QConfig.getObject('no_queue') == true ? true : false));

      if (QConfig.get('counter_status') == 'serve' && !no_queue && !btnExtend.is(':disabled')) {
        btnExtend.prop('disabled', true);
      }
    }, 2500);

    restore_state(); // Restore counter state.
  });
</script>