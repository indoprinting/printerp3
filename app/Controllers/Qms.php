<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\{Customer, DB, QueueCategory, QueueTicket, Warehouse};

class Qms extends BaseController
{
  public function getQueueTickets()
  {
    checkPermission('QMS.View');

    $dt = new DataTables('queue_tickets');
    $dt
      ->select('queue_tickets.id, queue_tickets.date, queue_tickets.call_date, queue_tickets.serve_date,
        queue_tickets.end_date,  customers.name AS customer_name, queue_tickets.token,
        queue_tickets.queue_category_name, queue_tickets.warehouse_name, queue_tickets.status2,
        queue_tickets.counter, caller.fullname')
      ->join('customers', 'customers.id = queue_tickets.customer_id', 'left')
      ->join('users caller', 'caller.id = queue_tickets.user_id', 'left')
      ->editColumn('id', function ($data) {
        return '
        <div class="btn-group btn-action">
          <a class="btn btn-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
            <i class="fad fa-gear"></i>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="' . base_url('qms/counter?recall=' . $data['id']) . '">
              <i class="fad fa-fw fa-megaphone"></i> ' . lang('App.recall') . '
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="' . base_url('qms/delete/' . $data['id']) . '"
              data-action="confirm">
              <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
            </a>
          </div>
        </div>';
      })
      ->editColumn('status2', function ($data) {
        return renderStatus($data['status2']);
      });


    $dt->generate();
  }

  public function index()
  {
    if ($args = func_get_args()) {
      $method = __FUNCTION__ . '_' . $args[0];

      if (method_exists($this, $method)) {
        array_shift($args);
        return call_user_func_array([$this, $method], $args);
      }
    }

    checkPermission('QMS.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.qms'), 'slug' => 'qms', 'url' => '#'],
        ['name' => lang('App.queue'), 'slug' => 'queue', 'url' => '#']
      ],
      'content' => 'QMS/index',
      'title' => lang('App.queue')
    ];

    return $this->buildPage($this->data);
  }

  public function addQueueTicket()
  {
    checkPermission('QMS.Add');

    $name         = getPost('name');
    $phone        = getPost('phone');
    $categoryId   = getPost('category');
    $warehouseId  = getPost('warehouse');

    $data = [
      'name'              => $name,
      'phone'             => $phone,
      'queue_category_id' => $categoryId,
      'warehouse_id'      => $warehouseId
    ];

    DB::transStart();

    $insertID = QueueTicket::add($data);

    if (!$insertID) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $ticket = QueueTicket::getRow(['id' => $insertID]);

      $this->response(200, ['data' => $ticket]);
    }

    $this->response(400, ['message' => 'Cannot create ticket.']);
  }

  public function counter()
  {
    checkPermission('QMS.Counter');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.qms'), 'slug' => 'qms', 'url' => '#'],
        ['name' => lang('App.counter'), 'slug' => 'counter', 'url' => '#']
      ],
      'content' => 'QMS/counter',
      'title' => lang('App.counter')
    ];

    return $this->buildPage($this->data);
  }

  public function delete($id = null)
  {
    checkPermission('QMS.Delete');

    $ticket = QueueTicket::getRow(['id' => $id]);

    if (!$ticket) {
      $this->response(404, ['message' => 'Queue Ticket is not found.']);
    }

    if (requestMethod() == 'POST' && isAJAX()) {
      DB::transStart();

      $res = QueueTicket::delete(['id' => $id]);

      if (!$res) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(200, ['message' => 'Queue Ticket has been deleted.']);
      }

      $this->response(400,  ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->response(400, ['message' => 'Failed to delete Queue Ticket.']);
  }

  public function display($code = null)
  {
    checkPermission('QMS.Display');

    $code = ($code ?? session('login')->warehouse);

    $warehouse = Warehouse::getRow(['code' => $code]);

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    $this->data['active']     = (getGet('active') == 1);
    $this->data['warehouse']  = $warehouse;

    return view('QMS/display', $this->data);
  }

  public function getCustomers()
  {
    $phone = getGet('term');

    $customers = Customer::select('name, phone')->where('phone', $phone)->get();
    $data = [];

    if ($customers) {
      foreach ($customers as $customer) {
        $data[] = ['id' => $customer->phone, 'text' => $customer->phone, 'name' => $customer->name];
      }
    }

    $this->response(200, ['results' => $data]);
  }

  /**
   * Display will call this function intervally.
   */
  public function getDisplayData($warehouseCode = NULL)
  {
    if ($warehouseCode) {
      $display_data = [
        'call'       => [],
        'counter'    => [],
        'queue_list' => [],
        'skip_list'  => []
      ];

      $call = QueueTicket::getTodayCallableQueueTicket($warehouseCode);

      if ($call) {
        $display_data['call'] = ['error' => 0, 'data' => $call];
      } else {
        $display_data['call'] = ['error' => 1, 'data' => NULL, 'msg' => 'No queue ticket to call.'];
      }

      $counters = QueueTicket::getTodayOnlineCounters($warehouseCode);

      if ($counters) {
        foreach ($counters as $counter) {
          $queue_category = QueueCategory::getRow(['id' => $counter->queue_category_id]);

          $counter_list[] = [
            'counter' => $counter->counter,
            'name' => explode(' ', $counter->fullname)[0],
            'token' => $counter->token,
            'category_name' => (!empty($queue_category) ? $queue_category->name : NULL)
          ];
        }

        $display_data['counter'] = ['error' => 0, 'data' => $counter_list];
      } else {
        $display_data['counter'] = ['error' => 1, 'data' => [], 'msg' => 'No counter online.'];
      }

      $queue_lists = QueueTicket::getTodayQueueTicketList($warehouseCode);

      if ($queue_lists) {
        foreach ($queue_lists as $ticket) {
          $customer = Customer::getRow(['id' => $ticket->customer_id]);

          $queue_list[] = [
            'customer_id' => intval($customer->id),
            'customer_name' => $customer->name,
            'est_call_date' => $ticket->est_call_date,
            'queue_category_id' => intval($ticket->queue_category_id),
            'queue_category_name' => $ticket->queue_category_name,
            'token' => $ticket->token,
            'user_id' => ($ticket->user_id ? intval($ticket->user_id) : $ticket->user_id),
            'warehouse_id' => intval($ticket->warehouse_id)
          ];
        }

        $display_data['queue_list'] = ['error' => 0, 'data' => $queue_list];
      } else {
        $display_data['queue_list'] = ['error' => 1, 'data' => [], 'msg' => 'No queue ticket available.'];
      }

      $skip_lists = QueueTicket::getTodaySkippedQueueList($warehouseCode);

      if ($skip_lists) {
        foreach ($skip_lists as $ticket) {
          $customer = Customer::getRow(['id' => $ticket->customer_id]);

          $skip_list[] = [
            'customer_id' => intval($customer->id),
            'customer_name' => $customer->name,
            'est_call_date' => $ticket->est_call_date,
            'queue_category_id' => intval($ticket->queue_category_id),
            'queue_category_name' => $ticket->queue_category_name,
            'token' => $ticket->token,
            'user_id' => ($ticket->user_id ? intval($ticket->user_id) : $ticket->user_id),
            'warehouse_id' => intval($ticket->warehouse_id)
          ];
        }

        $display_data['skip_list'] = ['error' => 0, 'data' => $skip_list];
      } else {
        $display_data['skip_list'] = ['error' => 1, 'data' => [], 'msg' => 'No skipped ticket available.'];
      }

      $this->response(200, ['data' => $display_data]);
    }

    $this->response(400, ['message' => 'Warehouse is not found.']);
  }

  public function registration($code = null)
  {
    checkPermission('QMS.Registration');

    $code = ($code ?? session('login')->warehouse);

    $warehouse = Warehouse::getRow(['code' => $code]);

    if (!$warehouse) {
      $this->response(404, ['message' => 'Warehouse is not found.']);
    }

    $this->data['warehouse'] = $warehouse;

    return view('QMS/registration', $this->data);
  }
}
