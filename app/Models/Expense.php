<?php

declare(strict_types=1);

namespace App\Models;

class Expense
{
  /**
   * Add new Expense.
   */
  public static function add(array $data)
  {
    if (!isset($data['bank_id'])) {
      setLastError('Bank is empty.');
      return false;
    }

    if (!isset($data['biller_id'])) {
      setLastError('Biller is empty.');
      return false;
    }

    if (!isset($data['category_id'])) {
      setLastError('Category is empty.');
      return false;
    }

    $data = setCreatedBy($data);
    $data['reference'] = OrderRef::getReference('expense');
    $data['status'] = 'need_approval';
    $data['payment_status'] = 'pending';

    DB::table('expenses')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      OrderRef::updateReference('expense');

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Expense.
   */
  public static function delete(array $where)
  {
    DB::table('expenses')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Expense collections.
   */
  public static function get($where = [])
  {
    return DB::table('expenses')->get($where);
  }

  /**
   * Get Expense row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Expense.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('expenses')->select($columns, $escape);
  }

  /**
   * Update Expense.
   */
  public static function update(int $id, array $data)
  {
    $data = setUpdatedBy($data);

    DB::table('expenses')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
