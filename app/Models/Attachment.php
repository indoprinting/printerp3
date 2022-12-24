<?php

declare(strict_types=1);

namespace App\Models;

class Attachment
{
  /**
   * Add new attachment.
   *
   * @param array $data [ *data, *filename, *hashname, *mime, *size, created_at, created_by ]
   * @return false|int Return new attachment id or FALSE if failed.
   */
  public static function add($data)
  {
    $data = setCreatedBy($data);

    DB::table('attachment')->insert($data);

    if (DB::affectedRows()) {
      return DB::insertID();
    }
    return FALSE;
  }

  /**
   * Delete attachments.
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
    return DB::affectedRows();
  }

  /**
   * Get attachments collection.
   * @param array $where [ id, filename, mime, created_by, updated_by ]
   */
  public static function get($where = [])
  {
    return DB::table('attachment')->get($where);
  }

  /**
   * Get attachment row.
   * @param array $where [ id, filename, mime, created_by, updated_by ]
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Update attachment.
   * @param array $data [ id, filename, hashname, mime ]
   */
  public static function update(int $id, array $data)
  {
    $db = get_instance()->db;
    $db->update('attachment', $data, ['id' => $id]);
    DB::table('attachment')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
