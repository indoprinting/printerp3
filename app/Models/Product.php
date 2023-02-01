<?php

declare(strict_types=1);

namespace App\Models;

class Product
{
  /**
   * Add new Product.
   */
  public static function add(array $data)
  {
    DB::table('products')->insert($data);
    
    if ($insertID = DB::insertID()) {
      return $insertID;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete Product.
   */
  public static function delete(array $where)
  {
    DB::table('products')->delete($where);
    
    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get Product collections.
   */
  public static function get($where = [])
  {
    return DB::table('products')->get($where);
  }

  /**
   * Get Product row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select Product.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('products')->select($columns, $escape);
  }

  /**
   * Update Product.
   */
  public static function update(int $id, array $data)
  {
    DB::table('products')->update($data, ['id' => $id]);
    
    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
