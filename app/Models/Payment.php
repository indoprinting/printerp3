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
    if (isset($data['expense'])) {
      $inv = Expense::getRow(['reference' => $data['expense']]);
      $data['expense_id']     = $inv->id;
      $data['reference']      = $inv->reference;
      $data['reference_date'] = $inv->date;
    }

    if (isset($data['income'])) {
      $inv = Income::getRow(['reference' => $data['income']]);
      $data['income_id']  = $inv->id;
      $data['reference']  = $inv->reference;
      $data['reference_date'] = $inv->date;
    }

    if (isset($data['mutation'])) {
      $inv = BankMutation::getRow(['reference' => $data['mutation']]);
      $data['mutation_id']  = $inv->id;
      $data['reference']    = $inv->reference;
      $data['reference_date'] = $inv->date;
    }

    if (isset($data['purchase'])) {
      $inv = ProductPurchase::getRow(['reference' => $data['purchase']]);
      $data['purchase_id']    = $inv->id;
      $data['reference']      = $inv->reference;
      $data['reference_date'] = $inv->date;
    }

    if (isset($data['sale'])) {
      $inv = Sale::getRow(['reference' => $data['sale']]);
      $data['sale_id']        = $inv->id;
      $data['reference']      = $inv->reference;
      $data['reference_date'] = $inv->date;
    }

    if (isset($data['transfer'])) {
      $inv = ProductTransfer::getRow(['reference' => $data['transfer']]);
      $data['transfer_id']    = $inv->id;
      $data['reference']      = $inv->reference;
      $data['reference_date'] = $inv->date;
    }

    if (isset($data['bank'])) {
      $bank = Bank::getRow(['code' => $data['bank']]);

      if (!$bank) {
        setLastError('Bank is not found.');
        return false;
      }

      $data['bank_id']  = $bank->id;
    } else {
      setLastError('Bank is not set.');
      return false;
    }

    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id']  = $biller->id;
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

    $data = setCreatedBy($data);

    DB::table('payments')->insert($data);

    if (DB::error()['code'] == 0) {
      $insertId = DB::insertID();

      if ($data['type'] == 'received') {
        Bank::amountIncrease((int)$bank->id, floatval($data['amount']));
      } else if ($data['type'] == 'sent') {
        Bank::amountDecrease((int)$bank->id, floatval($data['amount']));
      } else {
        setLastError('Type is unknown.');
      }

      return $insertId;
    }

    return false;
  }

  /**
   * Delete Payment.
   */
  public static function delete(array $where)
  {
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
    if (isset($data['expense'])) {
      $inv = Expense::getRow(['reference' => $data['expense']]);
      $data['expense_id'] = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['income'])) {
      $inv = Income::getRow(['reference' => $data['income']]);
      $data['income_id']  = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['mutation'])) {
      $inv = BankMutation::getRow(['reference' => $data['mutation']]);
      $data['mutation_id']  = $inv->id;
      $data['reference']    = $inv->reference;
    }

    if (isset($data['purchase'])) {
      $inv = ProductPurchase::getRow(['reference' => $data['purchase']]);
      $data['purchase_id']  = $inv->id;
      $data['reference']    = $inv->reference;
    }

    if (isset($data['sale'])) {
      $inv = Sale::getRow(['reference' => $data['sale']]);
      $data['sale_id']    = $inv->id;
      $data['reference']  = $inv->reference;
    }

    if (isset($data['transfer'])) {
      $inv = ProductTransfer::getRow(['reference' => $data['transfer']]);
      $data['transfer_id']  = $inv->id;
      $data['reference']    = $inv->reference;
    }

    if (isset($data['bank'])) {
      $bank = Bank::getRow(['code' => $data['bank']]);
      $data['bank_id']  = $bank->id;
    }

    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id']  = $biller->id;
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
