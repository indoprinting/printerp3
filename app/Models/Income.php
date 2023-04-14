<?php

declare(strict_types=1);

namespace App\Models;

class Income
{
  /**
   * Add new Income.
   */
  public static function add(array $data)
  {
    if (isset($data['bank'])) { // Compatibility.
      $bank = Bank::getRow(['code' => $data['bank']]);
      $data['bank_id'] = $bank->id;
    }

    if (isset($data['biller'])) { // Compatibility.
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id;
    }

    if (isset($data['category'])) { // Compatibility.
      $category = IncomeCategory::getRow(['code' => $data['category']]);
      $data['category_id'] = $category->id;
    }

    $data = setCreatedBy($data);
    $data['reference'] = OrderRef::getReference('income');

    DB::table('incomes')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      OrderRef::updateReference('income');

      return $insertId;
    }

    setLastError(DB::error()['message']);

    return FALSE;
  }

  /**
   * Delete Income.
   */
  public static function delete(array $where)
  {
    DB::table('incomes')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Income collections.
   */
  public static function get($where = [])
  {
    return DB::table('incomes')->get($where);
  }

  /**
   * Get Income row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Select Income.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('incomes')->select($columns, $escape);
  }

  /**
   * Update Income.
   */
  public static function update(int $id, array $data)
  {
    if (isset($data['bank'])) {
      $bank = Bank::getRow(['code' => $data['bank']]);
      $data['bank_id'] = $bank->id;
    }

    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id;
    }

    if (isset($data['category'])) {
      $category = IncomeCategory::getRow(['code' => $data['category']]);
      $data['category_id'] = $category->id;
    }

    $data = setUpdatedBy($data);

    DB::table('incomes')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return true;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
