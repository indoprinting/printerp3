<?php

declare(strict_types=1);

namespace App\Models;

class QueueTicket
{
  /**
   * Add new QueueTicket.
   */
  public static function add(array $data)
  {
    DB::table('queue_tickets')->insert($data);

    if ($insertID = DB::insertID()) {
      $data['phone'] = preg_replace('/[^0-9]/', '', $data['phone']); // Filter phone number.

      return $insertID;
    }

    setLastError(DB::error()['message']);

    return false;
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
      ->orderBy('date', 'desc')
      ->getRow();
  }

  /**
   * Select QueueTicket.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('queue_tickets')->select($columns, $escape);
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
