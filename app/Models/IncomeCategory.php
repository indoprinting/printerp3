<?php

declare(strict_types=1);

namespace App\Models;

class IncomeCategory
{
  /**
   * Add new IncomeCategory.
   */
  public static function add(array $data)
  {
    DB::table('income_categories')->insert($data);
    
    if ($insertID = DB::insertID()) {
      return $insertID;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete IncomeCategory.
   */
  public static function delete(array $where)
  {
    DB::table('income_categories')->delete($where);
    
    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get IncomeCategory collections.
   */
  public static function get($where = [])
  {
    return DB::table('income_categories')->get($where);
  }

  /**
   * Get IncomeCategory row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select IncomeCategory.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('income_categories')->select($columns, $escape);
  }

  /**
   * Update IncomeCategory.
   */
  public static function update(int $id, array $data)
  {
    DB::table('income_categories')->update($data, ['id' => $id]);
    
    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
