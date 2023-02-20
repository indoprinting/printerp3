<?php

declare(strict_types=1);

namespace App\Models;

class UserGroup
{
  /**
   * Add new UserGroup.
   * @param array $data [ name, code ]
   */
  public static function add(array $data)
  {
    if (empty($data['name'])) {
      setLastError('Group name is required.');
      return FALSE;
    }

    DB::table('usergroup')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete UserGroup.
   * @param array $clause [ id, name, code ]
   */
  public static function delete(array $clause)
  {
    if (isset($clause['id'])) {
      if ($clause['id'] == 1) {
        setLastError('OWNER usergroup cannot be deleted.');
        return FALSE;
      }
    }

    DB::table('usergroup')->delete($clause);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get UserGroup collections.
   * @param array $clause [ id, name, code ]
   */
  public static function get($clause = [])
  {
    return DB::table('usergroup')->get($clause);
  }

  /**
   * Get UserGroup row.
   * @param array $clause [ id, name, code ]
   */
  public static function getRow($clause = [])
  {
    if ($rows = self::get($clause)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select UserGroup.
   * @param string $columns Select columns.
   * @param bool $escape Escape string (Default: TRUE).
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('usergroup')->select($columns, $escape);
  }

  /**
   * Update UserGroup.
   * @param int $id UserGroup ID.
   * @param array $data [ name, code ]
   */
  public static function update(int $id, array $data)
  {
    if ($id == 1) {
      if (!in_array('All', json_decode($data['permissions'], TRUE))) {
        setLastError('Owner group must has all permissions.');
        return FALSE;
      }

      if (isset($data['name']) && $data['name'] != 'OWNER') {
        setLastError('Owner group name must be OWNER.');
        return FALSE;
      }
    }

    if (isset($data['name'])) {
      if (empty($data['name'])) {
        setLastError('Group name must has name.');
        return FALSE;
      }
    }

    DB::table('usergroup')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
