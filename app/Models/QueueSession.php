<?php

declare(strict_types=1);

namespace App\Models;

class QueueSession
{
  /**
   * Add new QueueSession.
   */
  public static function add(array $data)
  {
    if (empty($data['user_id'])) {
      setLastError('User id is empty.');
      return false;
    }
    if (empty($data['warehouse_id'])) {
      setLastError('Warehouse id is empty.');
      return false;
    }

    $data['date']             = date('Y-m-d H:i:s');
    $data['over_wcall_time']  = ($data['over_wcall_time'] ?? '00:00:00');
    $data['over_wserve_time'] = ($data['over_wserve_time'] ?? '00:00:00');
    $data['over_rest_time']   = ($data['over_rest_time'] ?? '00:00:00');
    $data['updated_at']       = date('Y-m-d H:i:s');

    DB::table('queue_sessions')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete QueueSession.
   */
  public static function delete(array $where)
  {
    DB::table('queue_sessions')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get QueueSession collections.
   */
  public static function get($where = [])
  {
    return DB::table('queue_sessions')->get($where);
  }

  /**
   * Get QueueSession row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  public static function getTodayQueueSession(int $userId)
  {
    return self::select('*')
      ->like('date', date('Y-m-d'), 'right')
      ->where('user_id', $userId)
      ->getRow();
  }

  /**
   * Select QueueSession.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('queue_sessions')->select($columns, $escape);
  }

  /**
   * Update QueueSession.
   */
  public static function update(int $id, array $data)
  {
    DB::table('queue_sessions')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
