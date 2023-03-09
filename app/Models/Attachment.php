<?php

declare(strict_types=1);

namespace App\Models;

class Attachment
{
  /**
   * Add new Attachment.
   *
   * @param array $data [ *data, *filename, *hashname, *mime, *size, created_at, created_by ]
   * @return false|int Return new attachment id or FALSE if failed.
   */
  public static function add($data)
  {
    $data = setCreatedBy($data);

    DB::table('attachment')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return FALSE;
  }

  /**
   * Delete Attachment.
   *
   * @param array $clause
   */
  public static function delete(array $where)
  {
    if (isset($where['id'])) {
      if ($where['id'] == 1 || $where['id'] == 2) {
        setLastError('Reserved attachment cannot be deleted.');
        return FALSE;
      }
    }

    DB::table('attachment')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Attachment collection.
   * @param array $where [ id, filename, mime, created_by, updated_by ]
   */
  public static function get($where = [])
  {
    return DB::table('attachment')->get($where);
  }

  /**
   * Get Attachment row.
   * @param array $where [ id, filename, mime, created_by, updated_by ]
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Attachment.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('attachment')->select($columns, $escape);
  }

  /**
   * Update Attachment.
   * @param array $data [ id, filename, hashname, mime ]
   */
  public static function update(int $id, array $data)
  {
    DB::table('attachment')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
