<?php

declare(strict_types=1);

namespace App\Models;

class ExpenseCategory
{
  /**
   * Add new ExpenseCategory.
   */
  public static function add(array $data)
  {
    DB::table('expense_categories')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete ExpenseCategory.
   */
  public static function delete(array $where)
  {
    DB::table('expense_categories')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get ExpenseCategory collections.
   */
  public static function get($where = [])
  {
    return DB::table('expense_categories')->get($where);
  }

  /**
   * Get ExpenseCategory row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select ExpenseCategory.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('expense_categories')->select($columns, $escape);
  }

  /**
   * Update ExpenseCategory.
   */
  public static function update(int $id, array $data)
  {
    DB::table('expense_categories')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
