<?php

declare(strict_types=1);

namespace App\Models;

class OrderRef
{
  private static $prefix = [
    'adjustment'  => 'QA-',
    'expense'     => 'EXP-',
    'income'      => 'INC-',
    'iuse'        => 'IUS-',
    'mutation'    => 'MUT-',
    'opname'      => 'SO-',
    'purchase'    => 'PO-',
    'sale'        => 'INV-',
    'transfer'    => 'TRF-'
  ];

  /**
   * Get OrderRef collections.
   */
  protected static function get($where = [])
  {
    return DB::table('order_ref')->get($where);
  }

  /**
   * Get order reference
   * @param string $name Order name.
   */
  public static function getReference(string $name)
  {
    $order = self::getRow(['ref_id' => 1]);

    if (property_exists($order, $name) && isset(self::$prefix[$name])) {
      return self::$prefix[$name] . date('Y/m/', strtotime($order->date)) . sprintf('%04s', $order->{$name});
    }
  }

  /**
   * Get OrderRef row.
   */
  protected static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return null;
  }

  /**
   * Reset all order reference.
   */
  public static function resetReference()
  {
    $order = self::getRow(['ref_id' => 1]);

    if (strcmp($order->date, date('Y-m-') . '01') === false) {
      DB::table('order_ref')->update([
        'date'        => date('Y-m-') . '01',
        'adjustment'  => 1,
        'expense'     => 1,
        'income'      => 1,
        'iuse'        => 1,
        'mutation'    => 1,
        'opname'      => 1,
        'purchase'    => 1,
        'sale'        => 1,
        'transfer'    => 1
      ], ['ref_id' => $order->ref_id]);
      return true;
    }

    return false;
  }

  /**
   * Update order reference
   * @param string $name Order name.
   */
  public static function updateReference(string $name)
  {
    $order = self::getRow(['ref_id' => 1]);

    if (property_exists($order, $name) && isset(self::$prefix[$name])) {
      DB::table('order_ref')->update([$name => [$order->{$name} + 1]], ['ref_id' => $order->ref_id]);

      if ($affectedRows = DB::affectedRows()) {
        return $affectedRows;
      }

      setLastError(DB::error()['message']);

      return false;
    }

    setLastError("Property doesn't exist {$name}.");

    return false;
  }
}
