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
    return DB::insertID();
  }

  /**
   * Delete ExpenseCategory.
   */
  public static function delete(array $where)
  {
    DB::table('expense_categories')->delete($where);
    return DB::affectedRows();
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
    return NULL;
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
    return DB::affectedRows();
  }
}
