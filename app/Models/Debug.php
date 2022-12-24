<?php

declare(strict_types=1);

namespace App\Models;

class Debug
{
  public static function add($name)
  {
    $r = DB::table('user')->update(['dark_mode' => 2], ['id' => 1]);

    echo '<pre>';
    print_r($r);
    echo '</pre>';

    $a = DB::table('user')->getRow();

    echo '<pre>';
    print_r($a);
    echo '</pre>';
  }
}
