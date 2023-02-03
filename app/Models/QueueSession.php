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
    if (empty($data['name'])) {
      setLastError('Customer name is empty.');
      return false;
    }

    if (empty($data['phone'])) {
      setLastError('Customer phone is empty.');
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

    $customer = Customer::getRow(['phone' => $data['phone']]);

    if (!$customer) {
      $res = Customer::add([
        'company'             => '',
        'customer_group_id'   => 1,
        'customer_group_name' => 'Reguler',
        'name'                => $data['name'],
        'phone'               => $data['phone']
      ]);

      if (!$res) {
        return false;
      }
    }

    DB::table('queue_sessions')->insert($data);

    if ($insertID = DB::insertID()) {
      return $insertID;
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

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
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
    return NULL;
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

    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
