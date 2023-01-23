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

    if (isset($data['expense'])) { // Compatibility
      $inv = Expense::getRow(['code' => $data['expense']]);
      $data['expense_id'] = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['income'])) { // Compatibility
      $inv = Income::getRow(['code' => $data['income']]);
      $data['income_id'] = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['mutation'])) { // Compatibility
      $inv = BankMutation::getRow(['code' => $data['mutation']]);
      $data['mutation_id'] = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['purchase'])) { // Compatibility
      $inv = ProductPurchase::getRow(['code' => $data['purchase']]);
      $data['purchase_id'] = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['sale'])) { // Compatibility
      $inv = Sale::getRow(['code' => $data['sale']]);
      $data['sale_id'] = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['transfer'])) { // Compatibility
      $inv = ProductTransfer::getRow(['code' => $data['transfer']]);
      $data['transfer_id'] = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['bank'])) { // Compatibility
      $bank = Bank::getRow(['code' => $data['bank']]);
      $data['bank_id'] = $bank->id;
    }

    if (isset($data['biller'])) { // Compatibility
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id;
    }

    if (empty($data['amount'])) {
      setLastError('Amount is empty or zero');
      return FALSE;
    }

    if (empty($data['type'])) {
      setLastError('Type is empty and must be received or sent.');
      return FALSE;
    }

    DB::table('payments')->insert($data);

    if (DB::affectedRows()) {
      $insertID = DB::insertID();

      if ($data['type'] == 'received') {
        Bank::amountIncrease((int)$bank->id, floatval($data['amount']));
      } else if ($data['type'] == 'sent') {
        Bank::amountDecrease((int)$bank->id, floatval($data['amount']));
      } else {
        setLastError('Type is unknown.');
      }

      return $insertID;
    }

    return FALSE;
  }

  /**
   * Delete Payment.
   */
  public static function delete(array $where)
  {
    DB::table('payments')->delete($where);
    return DB::affectedRows();
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

    return NULL;
  }

  /**
   * Select Payment.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('payments')->select($columns, $escape);
  }

  /**
   * Update Payment.
   */
  public static function update(int $id, array $data)
  {
    DB::table('payments')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
