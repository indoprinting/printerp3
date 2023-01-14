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
    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);
      $data['biller_id'] = $biller->id;
    }

<<<<<<< HEAD
    if (empty($data['bankfrom'])) {
=======
    if (isset($data['bankfrom'])) {
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
      $bankFrom = Bank::getRow(['code' => $data['bankfrom']]);
      $data['from_bank_id']   = $bankFrom->id;
      $data['from_bank_name'] = $bankFrom->name;
    }

<<<<<<< HEAD
    if (empty($data['bankto'])) {
      $bankTo = Bank::getRow(['code' => $data['bankfrom']]);
=======
    if (isset($data['bankto'])) {
      $bankTo = Bank::getRow(['code' => $data['bankto']]);
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
      $data['to_bank_id']   = $bankTo->id;
      $data['to_bank_name'] = $bankTo->name;
    }

    $data = setCreatedBy($data);
<<<<<<< HEAD
    $data['reference'] = OrderRef::getReference('mutation');
    OrderRef::updateReference('mutation');
=======
    $data['date'] = $data['created_at']; // Compatibility.
    $data['reference'] = OrderRef::getReference('mutation');

    DB::transStart();
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170

    DB::table('bank_mutations')->insert($data);
    $insertID = DB::insertID();

    if ($insertID) {
<<<<<<< HEAD
=======
      OrderRef::updateReference('mutation');

>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
      PaymentValidation::add([
        'mutation'    => $data['reference'],
        'amount'      => $data['amount'],
        'biller'      => $data['biller'],
        'attachment'  => ($data['attachment'] ?? NULL)
      ]);

      self::update((int)$insertID, ['status' => 'waiting_transfer']);
    }

<<<<<<< HEAD
=======
    DB::transComplete();

>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
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
