<?php

declare(strict_types=1);

namespace App\Models;

class QueueTicket
{
  const STATUS_WAITING = 1;
  const STATUS_CALLING = 2;
  const STATUS_CALLED  = 3;
  const STATUS_SERVING = 4;
  const STATUS_SERVED  = 5;
  const STATUS_SKIPPED = 6;

  /**
   * Add new QueueTicket.
   */
  public static function add(array $data)
  {
    if (empty($data['phone'])) {
      setLastError('Phone number is empty.');
      return false;
    }

    if (empty($data['name'])) {
      setLastError('Customer name is empty.');
      return false;
    }

    if (empty($data['queue_category_id'])) {
      setLastError('Queue Category is empty.');
      return false;
    }

    if (empty($data['warehouse_id'])) {
      setLastError('Warehouse is empty.');
      return false;
    }

    $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']); // Filter phone number.

    $category   = QueueCategory::getRow(['id' => $data['queue_category_id']]);

    if (!$category) {
      setLastError('Queue Category is not found.');
      return false;
    }

    $warehouse  = Warehouse::getRow(['id' => $data['warehouse_id']]);

    if (!$warehouse) {
      setLastError('Warehouse is not found.');
      return false;
    }

    $customer = Customer::getRow(['phone' => $data['phone']]);

    if (!$customer) {
      $insertID = Customer::add([
        'company'             => '',
        'customer_group_id'   => 1,
        'customer_group_name' => 'Reguler',
        'name'                => $data['name'],
        'phone'               => $data['phone']
      ]);

      if (!$insertID) {
        return false;
      }

      $customer = Customer::getRow(['id' => $insertID]);
    }

    unset($data['name'], $data['phone']);

    // Begin Prevent Duplicate entries.
    $lastTicket = self::getTodayLastQueueTicket([
      'queue_category_id' => $data['queue_category_id'],
      'warehouse_id'      => $data['warehouse_id']
    ]);

    if ($lastTicket && $lastTicket->customer_id == $customer->id) {
      setLastError('Anda sudah mengambil tiket sebelumnya.');
      return false;
    }
    // End Prevent Duplicate entries.

    // Begin get estimated call date.
    $servingQueues = self::select('*')->like('date', date('Y-m-d'), 'right')->get([
      'status' => self::STATUS_SERVING,
      'warehouse_id' => $data['warehouse_id']
    ]);

    $waitingQueues = self::select('*')->like('date', date('Y-m-d'), 'right')->get([
      'status' => self::STATUS_WAITING,
      'warehouse_id' => $data['warehouse_id'],
    ]);

    $waitTime = 0;

    if ($servingQueues && $servingQueues[0]->queue_category_name == 'Siap Cetak') {
      $waitTime += 10;
    } else if ($servingQueues && $servingQueues[0]->queue_category_name == 'Edit Design') {
      $waitTime += 20;
    }

    foreach ($waitingQueues as $waitQueue) {
      if ($waitQueue->queue_category_name == 'Siap Cetak') {
        $waitTime += 10;
      } else if ($waitQueue->queue_category_name == 'Edit Design') {
        $waitTime += 20;
      }
    };

    $di = new \DateInterval("PT{$waitTime}M");

    $estCallDate = new \DateTime('now', new \DateTimeZone('Asia/Jakarta')); // Current datetime.
    $estCallDate->add($di);

    $est_call_date = $estCallDate->format('Y-m-d H:i:s');
    // End get estimated call date.

    $data['customer_id']          = $customer->id;
    $data['est_call_date']        = getQueueDateTime($est_call_date);
    $data['warehouse_name']       = $warehouse->name;
    $data['queue_category_name']  = $category->name;
    $data['status']               = self::STATUS_WAITING;
    $data['status2']              = self::toStatus(self::STATUS_WAITING);
    $data['token']                = self::generateNewQueueTicketToken($data);

    $data['date'] = date('Y-m-d H:i:s');

    DB::table('queue_tickets')->insert($data);

    if ($insertID = DB::insertID()) {
      return $insertID;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Call a queue ticket.
   * @param array $data [user_id*, warehouse_id*]
   */
  public function callQueue($data)
  {
    $warehouse = Warehouse::getRow(['id' => $data['warehouse_id']]);

    $queue_lists = self::getTodayQueueTicketList($warehouse->code);

    if ($queue_lists) {
      $user = User::getRow(['id' => $data['user_id']]);

      foreach ($queue_lists as $list) {
        $ticket = $list;
        break;
      }

      $call_date   = date('Y-m-d H:i:s');
      $create_date = $ticket->date;

      $callDate = new \DateTime($call_date);
      $createDate = new \DateTime($create_date);
      $wait_time = $createDate->diff($callDate)->format('%H:%I:%S');

      $ticket_data = [
        'call_date' => $call_date,
        'wait_time' => $wait_time, // OK.
        'counter' => $user->counter,
        'status'  => self::STATUS_CALLING, // 2 = To be call by Display.
        'status2' => $this->toStatus(self::STATUS_CALLING),
        'user_id' => $user->id,
      ];

      if (self::update((int)$ticket->id, $ticket_data)) {
        User::update((int)$user->id, ['token' => $ticket->token, 'queue_category_id' => $ticket->queue_category_id]);
        return self::getRow(['id' => $ticket->id]);
      }
      return NULL;
    }
    return NULL;
  }

  /**
   * Delete QueueTicket.
   */
  public static function delete(array $where)
  {
    DB::table('queue_tickets')->delete($where);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  public static function formatTicket($number)
  {
    return ($number < 10 ? '00' . $number : ($number < 100 ? '0' . $number : $number));
  }

  /**
   * Generate new queue ticket token.
   * @param array $data [ *queue_category_id, *warehouse_id ]
   */
  public static function generateNewQueueTicketToken($data)
  {
    $queueCategory = QueueCategory::getRow(['id' => $data['queue_category_id']]);
    $lastTicket = self::getTodayLastQueueTicket($data);

    if ($lastTicket) {
      $ticket_number = intval(str_replace($queueCategory->prefix, '', $lastTicket->token));
      $ticket_number++;

      return $queueCategory->prefix . self::formatTicket($ticket_number);
    }

    // If not ticket present.
    return $queueCategory->prefix . '001'; // For first ticket.
  }

  /**
   * Get QueueTicket collections.
   */
  public static function get($where = [])
  {
    return DB::table('queue_tickets')->get($where);
  }

  /**
   * Get QueueTicket row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  public static function getTodayCallableQueueTicket(string $warehouseCode)
  {
    $warehouse = Warehouse::getRow(['code' => $warehouseCode]);

    return self::select('*')
      ->like('date', date('Y-m-d'), 'right')
      ->where('warehouse_id', $warehouse->id)
      ->where('status', self::STATUS_CALLING)
      ->getRow();
  }

  /**
   * Get today last queue ticket.
   * @param array $data [ *queue_category_id, *warehouse_id ]
   */
  public static function getTodayLastQueueTicket(array $data)
  {
    if (empty($data['queue_category_id'])) {
      setLastError('No queue category id');
      return null;
    }

    if (empty($data['warehouse_id'])) {
      setLastError('No warehouse id');
      return null;
    }

    return self::select('*')
      ->like('date', date('Y-m-d'), 'right')
      ->where('warehouse_id', $data['warehouse_id'])
      ->where('queue_category_id', $data['queue_category_id'])
      ->where('status', self::STATUS_WAITING)
      ->orderBy('date', 'desc')
      ->getRow();
  }

  public static function getTodayOnlineCounters(string $warehouseCode)
  {
    $warehouse = Warehouse::getRow(['code' => $warehouseCode]);

    return User::select('*')
      ->where('warehouse_id', $warehouse->id)
      ->where('counter > 0')
      ->orderBy('counter', 'ASC')
      ->get();
  }

  public static function getTodayQueueTicketList(string $warehouseCode)
  {
    $warehouse = Warehouse::getRow(['code' => $warehouseCode]);

    return self::select('*')
      ->like('date', date('Y-m-d'), 'right')
      ->where('warehouse_id', $warehouse->id)
      ->where('status', self::STATUS_WAITING)
      ->orderBy('date', 'ASC')
      ->get();
  }

  public static function getTodaySkippedQueueList(string $warehouseCode)
  {
    $warehouse = Warehouse::getRow(['code' => $warehouseCode]);

    $expMinute = 20; // Hardcoded.
    $date = date('Y-m-d H:i:s', strtotime("-{$expMinute} minute"));

    return self::select('*')
      ->like('date', date('Y-m-d'), 'right')
      ->where("est_call_date > '{$date}'")
      ->where('warehouse_id', $warehouse->id)
      ->where('status', self::STATUS_SKIPPED)
      ->orderBy('date', 'ASC')
      ->get();
  }

  /**
   * Select QueueTicket.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('queue_tickets')->select($columns, $escape);
  }

  /**
   * Convert status (int) to status (string).
   */
  public static function toStatus(int $status)
  {
    switch ($status) {
      case self::STATUS_WAITING;
        return 'waiting';
      case self::STATUS_CALLING;
        return 'calling';
      case self::STATUS_CALLED;
        return 'called';
      case self::STATUS_SERVING;
        return 'serving';
      case self::STATUS_SERVED;
        return 'served';
      case self::STATUS_SKIPPED;
        return 'skipped';
      default:
        return null;
    }
  }

  /**
   * Update QueueTicket.
   */
  public static function update(int $id, array $data)
  {
    DB::table('queue_tickets')->update($data, ['id' => $id]);

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
