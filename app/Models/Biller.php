<?php

declare(strict_types=1);

namespace App\Models;

class Biller
{
  /**
   * Add new billers.
   * @param array $data [ name, code ]
   */
  public static function add(array $data)
  {
    DB::table('billers')->insert($data);
    return DB::insertID();
  }

  /**
   * Delete billers.
   * @param array $clause [ id, name, code ]
   */
  public static function delete(array $clause)
  {
    DB::table('billers')->delete($clause);
    return DB::affectedRows();
  }

  /**
   * Get billers collections.
   * @param array $clause [ id, name, code ]
   */
  public static function get($clause = [])
  {
    return DB::table('billers')->get($clause);
  }

  /**
   * Get billers row.
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
   * Update billers.
   * @param int $id billers ID.
   * @param array $data [ name, code ]
   */
  public static function update(int $id, array $data)
  {
    DB::table('billers')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
