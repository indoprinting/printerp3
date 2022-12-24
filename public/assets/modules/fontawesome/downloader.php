<?php declare(strict_types=1);

if (php_sapi_name() != 'cli') die("This program cannot be run in server mode.");

define('FA_URL', 'https://pro.fontawesome.com/releases/v6.0.0-beta3/');

function parseURL(string $str): array
{
  $data = [];
  $parse = FALSE;
  $s = '';
  
  for ($x = 0; $x < strlen($str); $x++) {
    if ($parse) {
      if (substr($str, $x, 1) == ')') {
        $parse = FALSE;
        $data[] = FA_URL . substr($s, 2);
      } else {
        $s .= substr($str, $x, 1);
      }
    }
    
    if (substr($str, $x, 4) == 'url(') {
      $parse = TRUE;
      $x += 4;
    }
  }
  
  return $data;
}

$hFile = fopen(__DIR__ . '/css/all.css', 'r');

while (($line = fgets($hFile)) !== FALSE) {
  if ($pos = strpos($line, 'url(', 0)) {
    $r = parseURL($line);
    
    if ($r) {
      foreach ($r as $url) {
        $ret = 0;
        passthru("curl -O {$url}", $ret);
      }
    }
  }
}

fclose($hFile);
