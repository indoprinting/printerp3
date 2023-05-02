<?php

declare(strict_types=1);

namespace App\Models;

class Permission
{
  /**
   * Add new Permission.
   */
  public static function add(array $data)
  {
    if (empty($data['name'])) {
      setLastError('Name is not set.');
      return FALSE;
    }

    if (isset($data['actions'])) {
      if (is_array($data['actions'])) {
        $data['actions'] = json_encode($data['actions']);
      } else {
        setLastError('Actions must be an array.');
        return FALSE;
      }
    } else {
      setLastError('Actions are not set.');
      return FALSE;
    }

    DB::table('permission')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Permission.
   */
  public static function delete(array $where)
  {
    DB::table('permission')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Permission collections.
   */
  public static function get($where = [])
  {
    return DB::table('permission')->get($where);
  }

  /**
   * Get Permission row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Permission.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('permission')->select($columns, $escape);
  }

  /**
   * Update Permission.
   */
  public static function update(int $id, array $data)
  {
    if (isset($data['actions'])) {
      if (is_array($data['actions'])) {
        if ($id == 1 && array_search('All', $data['actions']) === FALSE) {
          setLastError('ID 1 must have All permission.');
          return FALSE;
        }

        $data['actions'] = json_encode($data['actions']);
      } else {
        setLastError('Actions must be an array.');
        return FALSE;
      }
    }

    DB::table('permission')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
