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
    $inv = null;

    if (isset($data['expense_id'])) {
      $inv = Expense::getRow(['id' => $data['expense_id']]);
    }

    if (isset($data['income_id'])) {
      $inv = Income::getRow(['id' => $data['income_id']]);
    }

    if (isset($data['mutation_id'])) {
      $inv = BankMutation::getRow(['id' => $data['mutation_id']]);
    }

    if (isset($data['purchase_id'])) {
      $inv = ProductPurchase::getRow(['id' => $data['purchase_id']]);
    }

    if (isset($data['sale_id'])) {
      $inv = Sale::getRow(['id' => $data['sale_id']]);
    }

    if (isset($data['transfer_id'])) {
      $inv = ProductTransfer::getRow(['id' => $data['transfer_id']]);
    }

    if (!$inv) {
      setLastError('At least one invoice must be selected.');
      return false;
    }

    $data['reference']      = $inv->reference;
    $data['reference_date'] = $inv->date;

    if (!isset($data['bank_id'])) {
      setLastError('Bank is not set.');
      return false;
    }

    if (!isset($data['biller_id'])) {
      setLastError('Biller is not set.');
      return false;
    }

    if (empty($data['amount'])) {
      setLastError('Amount is empty or zero');
      return false;
    }

    if (empty($data['type'])) {
      setLastError('Type is empty and must be received or sent.');
      return false;
    }

    $data = setCreatedBy($data);

    DB::table('payments')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Payment.
   */
  public static function delete(array $where)
  {
    $payments = self::get($where);

    DB::table('payments')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Payment collections.
   */
  public static function get($where = [])
  {
    return DB::table('payments')->get($where);
  }

  /**
   * Get Payment row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }

    return null;
  }

  /**
   * Select Payment.
   */
  public static function select(string $columns, $escape = true)
  {
    return DB::table('payments')->select($columns, $escape);
  }

  /**
   * Update Payment.
   */
  public static function update(int $id, array $data)
  {
    $inv = null;

    if (isset($data['expense_id'])) {
      $inv = Expense::getRow(['id' => $data['expense_id']]);
    }

    if (isset($data['income_id'])) {
      $inv = Income::getRow(['id' => $data['income_id']]);
    }

    if (isset($data['mutation_id'])) {
      $inv = BankMutation::getRow(['id' => $data['mutation_id']]);
    }

    if (isset($data['purchase_id'])) {
      $inv = ProductPurchase::getRow(['id' => $data['purchase_id']]);
    }

    if (isset($data['sale_id'])) {
      $inv = Sale::getRow(['id' => $data['sale_id']]);
    }

    if (isset($data['transfer_id'])) {
      $inv = ProductTransfer::getRow(['id' => $data['transfer_id']]);
    }

    if ($inv) {
      $data['reference']  = $inv->reference;
    }

    if (isset($data['amount']) && empty($data['amount'])) {
      setLastError('Amount is empty or zero');
      return false;
    }

    if (isset($data['type'])) {
      if (!in_array($data['type'], ['received', 'sent'])) {
        setLastError('Type must be received or sent.');
      }
      return false;
    }

    $data = setUpdatedBy($data);

    DB::table('payments')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
