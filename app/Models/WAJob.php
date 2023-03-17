<?php

declare(strict_types=1);

namespace App\Models;

class WAJob
{
  /**
   * Add new WAJob.
   */
  public static function add(array $data)
  {
    if (empty($data['phone'])) {
      setLastError('Phone number is required.');
      return FALSE;
    }

    if (empty($data['message'])) {
      setLastError('Message is required.');
      return FALSE;
    }

    if (empty($data['send_date'])) $data['send_date'] = date('Y-m-d H:i:s');
    if (empty($data['status']))    $data['status']    = 'pending';

    $data = setCreatedBy($data);

    DB::table('wa_job')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete WAJob.
   */
  public static function delete(array $where)
  {
    DB::table('wa_job')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get WAJob collections.
   */
  public static function get($where = [])
  {
    return DB::table('wa_job')->get($where);
  }

  /**
   * Get WAJob row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select WAJob.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('wa_job')->select($columns, $escape);
  }

  /**
   * Update WAJob.
   */
  public static function update(int $id, array $data)
  {
    DB::table('wa_job')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
