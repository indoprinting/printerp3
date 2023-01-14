<?php

declare(strict_types=1);

namespace App\Models;

class Payment
{
  /**
   * Add new Payment.
   */
  public static function add(array $data)
  {
    $data = setCreatedBy($data);

<<<<<<< HEAD
    DB::table('payment')->insert($data);
=======
    DB::table('payments')->insert($data);
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
    return DB::insertID();
  }

  /**
   * Delete Payment.
   */
  public static function delete(array $where)
  {
<<<<<<< HEAD
    DB::table('payment')->delete($where);
=======
    DB::table('payments')->delete($where);
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
    return DB::affectedRows();
  }

  /**
   * Get Payment collections.
   */
  public static function get($where = [])
  {
<<<<<<< HEAD
    return DB::table('payment')->get($where);
=======
    return DB::table('payments')->get($where);
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
  }

  /**
   * Get Payment row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Payment.
   */
  public static function select(string $columns, $escape = TRUE)
  {
<<<<<<< HEAD
    return DB::table('payment')->select($columns, $escape);
=======
    return DB::table('payments')->select($columns, $escape);
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
  }

  /**
   * Update Payment.
   */
  public static function update(int $id, array $data)
  {
<<<<<<< HEAD
    DB::table('payment')->update($data, ['id' => $id]);
=======
    DB::table('payments')->update($data, ['id' => $id]);
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
    return DB::affectedRows();
  }
}
