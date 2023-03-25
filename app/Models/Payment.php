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
      $data['expense'] = $inv->reference;
    }

    if (isset($data['income_id'])) {
      $inv = Income::getRow(['id' => $data['income_id']]);
      $data['income'] = $inv->reference;
    }

    if (isset($data['mutation_id'])) {
      $inv = BankMutation::getRow(['id' => $data['mutation_id']]);
      $data['mutation'] = $inv->reference;
    }

    if (isset($data['purchase_id'])) {
      $inv = ProductPurchase::getRow(['id' => $data['purchase_id']]);
      $data['purchase'] = $inv->reference;
    }

    if (isset($data['sale_id'])) {
      $inv = Sale::getRow(['id' => $data['sale_id']]);
      $data['sale'] = $inv->reference;
    }

    if (isset($data['transfer_id'])) {
      $inv = ProductTransfer::getRow(['id' => $data['transfer_id']]);
      $data['transfer'] = $inv->reference;
    }

    if (!$inv) {
      setLastError('At least one invoice must be selected.');
      return false;
    }

    if (isset($data['bank_id'])) {
      $bank = Bank::getRow(['id' => $data['bank_id']]);

      if (!$bank) {
        setLastError('Bank is not found.');
        return false;
      }

      $data['bank'] = $bank->code;
    } else {
      setLastError('Bank is not set.');
      return false;
    }

    if (isset($data['biller_id'])) {
      $biller = Biller::getRow(['id' => $data['biller_id']]);

      if (!$biller) {
        setLastError('Biller is not found.');
        return false;
      }

      $data['biller'] = $biller->code;
    } else {
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

    $data['reference']      = $inv->reference;
    $data['reference_date'] = $inv->date;

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

    if (isset($data['bank_id'])) {
      $bank = Bank::getRow(['id' => $data['bank_id']]);

      if (!$bank) {
        setLastError('Bank is not found.');
        return false;
      }

      $data['bank'] = $bank->code;
    }

    if (isset($data['biller_id'])) {
      $biller = Biller::getRow(['id' => $data['biller_id']]);

      if (!$biller) {
        setLastError('Biller is not found.');
        return false;
      }

      $data['biller'] = $biller->code;
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
