<?php

declare(strict_types=1);

namespace App\Models;

class BankMutation
{
  /**
   * Add new BankMutation.
   */
  public static function add(array $data)
  {

    $data = setCreatedBy($data);
    $data['reference'] = OrderRef::getReference('mutation');
    OrderRef::updateReference('mutation');

    if (!empty($data['bank_from'])) {
      $bankFrom = Bank::getRow(['code' => $data['bank_from']]);
      $data['from_bank_id'] = $bankFrom->id; // Obsolete
      $data['to_bank_name'] = $bankFrom->name; // Obsolete
    }

    if (!empty($data['bank_to'])) {
      $bankTo = Bank::getRow(['code' => $data['bank_to']]);
      $data['to_bank_id']   = $bankTo->id; // Obsolete
      $data['to_bank_name'] = $bankTo->name; // Obsolete
    }

    if (!empty($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id; // Obsolete
    }

    DB::table('bank_mutations')->insert($data);
    $insertID = DB::insertID();

    if ($insertID) {
      PaymentValidation::add([
        'mutation'    => $data['reference'],
        'amount'      => $data['amount'],
        'biller'      => $data['biller'],
        'attachment'  => ($data['attachment'] ?? NULL)
      ]);

      self::update((int)$insertID, ['status' => 'waiting_transfer']);
    }

    return $insertID;
  }

  /**
   * Delete BankMutation.
   */
  public static function delete(array $where)
  {
    DB::table('bank_mutations')->delete($where);
    return DB::affectedRows();
  }

  /**
   * Get BankMutation collections.
   */
  public static function get($where = [])
  {
    return DB::table('bank_mutations')->get($where);
  }

  /**
   * Get BankMutation row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select BankMutation.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('bank_mutations')->select($columns, $escape);
  }

  /**
   * Update BankMutation.
   */
  public static function update(int $id, array $data)
  {
    DB::table('bank_mutations')->update($data, ['id' => $id]);
    return DB::affectedRows();
  }
}
