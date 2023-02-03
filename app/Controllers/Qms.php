<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\DataTables;
use App\Models\DB;
use App\Models\QueueTicket;

class Qms extends BaseController
{
  public function getQueueTickets()
  {
    checkPermission('QueueTicket.View');

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

    checkPermission('QueueTicket.View');

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

  public function delete($id = null)
  {
    checkPermission('QueueTicket.Delete');

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
}
