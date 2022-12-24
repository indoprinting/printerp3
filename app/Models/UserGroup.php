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
    DB::table('groups')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete UserGroup.
   * @param array $clause [ id, name, code ]
   */
  public static function delete(array $clause)
  {
    DB::table('groups')->delete($clause);
    return DB::affectedRows();
  }

  /**
   * Get UserGroup collections.
   * @param array $clause [ id, name, code ]
   */
  public static function get($clause = [])
  {
    return DB::table('groups')->get($clause);
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
    return DB::table('groups')->select($columns, $escape);
  }

  /**
   * Update UserGroup.
   * @param int $id UserGroup ID.
   * @param array $data [ name, code ]
   */
  public static function update(int $id, array $data)
  {
    DB::table('groups')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
