<?php

declare(strict_types=1);
/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

/**
 * Check for permission and login status.
 * @param string $permission Permission to check. Ex. "User.View". If NULL it will check for login session.
 */
function checkPermission(string $permission = NULL)
{
  $request = \Config\Services::request();
  $ajax   = $request->isAJAX();

  if (isLoggedIn()) {
    if ($permission) {
      if ($ajax) {
        if (!hasAccess($permission)) {
          sendJSON(['err' => 1, 'text' => lang('Msg.notAuthorized'), 'title' => lang('Msg.accessDenied')]);
        }
      }

      if (!hasAccess($permission)) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url()));
        die;
      }
    }
  } else {
    if ($ajax) {
      sendJSON(['err' => 2, 'text' => lang('Msg.notLoggedIn'), 'title' => lang('Msg.accessDenied')]);
    } else {
      $data = [
        'resver' => '1.0'
      ];

      if (!isLoggedIn() && getCookie('___')) {
        if (\App\Models\Auth::loginRememberMe(getCookie('___'))) {
          header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        }
      }

      echo view('Auth/login', $data);
      die;
    }
  }
}

/**
 * Fetch an item from GET data.
 */
function getCookie($name)
{
  return \Config\Services::request()->getCookie($name);
}

/**
 * Fetch an item from GET data.
 */
function getGet($name)
{
  return \Config\Services::request()->getGet($name);
}

/**
 * Fetch an item from POST.
 */
function getPost($name)
{
  return \Config\Services::request()->getPost($name);
}

/**
 * A convenience method that grabs the raw input stream(send method in PUT, PATCH, DELETE) and
 * decodes the String into an array.
 */
function getRawInput()
{
  return \Config\Services::request()->getRawInput();
}

/**
 * Decode JSON string into object.
 *
 * @param mixed $json JSON string to decode into object or array.
 * @param bool $assoc Return as associative array if TRUE. Default FALSE.
 */
function getJSON($json, bool $assoc = FALSE)
{
  if ($json) {
    return (json_decode($json, $assoc) ?? ($assoc ? [] : (object)[]));
  }
  return ($assoc ? [] : (object)[]);
}

/**
 * Get last error message.
 * @return string|null Return error message. NULL or empty string if no error.
 */
function getLastError()
{
  return (session()->has('lastErrMsg') ? session('lastErrMsg') : NULL);
}

/**
 * Check if current login session has permission access.
 * If session has permission 'All' then it's always return TRUE.
 *
 * @param array|string $permission Permission to check. Ex. 'User.Add'
 */
function hasAccess($permission)
{
  if (isLoggedIn()) {
    $perms = session('login')->permissions;

    if (is_array($permission)) {
      $roles = $permission;
    } else {
      $roles[] = $permission;
    }

    foreach ($roles as $role) {
      if (in_array('All', $perms) || in_array($role, $perms)) {
        return TRUE;
      }
    }
  }
  return FALSE;
}

/**
 * Check if request from AJAX.
 */
function isAJAX()
{
  return \Config\Services::request()->isAJAX();
}

/**
 * Check if request from command line.
 */
function isCLI()
{
  return (PHP_SAPI === 'cli');
}

/**
 * Check if current environment is same as value.
 */
function isEnv($environment)
{
  return (ENVIRONMENT == $environment);
}

/**
 * Check current session if has login data.
 */
function isLoggedIn()
{
  return (session()->has('login') ? TRUE : FALSE);
}

/**
 * Get request method.
 */
function requestMethod()
{
  return (!isCLI() ? $_SERVER['REQUEST_METHOD'] : NULL);
}

/**
 * Send JSON response.
 * @param mixed $data Data to send.
 * @param array $options Options [ string origin ].
 */
function sendJSON($data, $options = [])
{
  $origin = base_url();

  if (!empty($options['origin'])) $origin = $options['origin'];

  header("Access-Control-Allow-Origin: {$origin}");
  header('Content-Type: application/json');
  die(json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Set created_by based on user id and created_at. Used for Model data.
 * @param array $data
 */
function setCreatedBy($data = [])
{
  $data['created_at'] = ($data['created_at'] ?? date('Y-m-d H:i:s'));

  if (empty($data['created_by']) && isLoggedIn()) {
    $data['created_by'] = session('login')->user_id;
  }
  return $data;
}

/**
 * Set or update json column. Used for Model data.
 * @param array $data Column data.
 * @param array $columns JSON column to set.
 * @param array $jsonData Existing json data to be update.
 */
function setJSONColumn($data = [], $columns = [], $jsonData = [])
{
  $json = $jsonData;

  foreach ($columns as $col) {
    if (array_key_exists($col, $data)) {
      $json[$col] = $data[$col];
      unset($data[$col]);
    }
  }

  $data['json'] = json_encode($json);

  return $data;
}

/**
 * Set last error message.
 * @param string $message Error message.
 */
function setLastError(string $message = NULL)
{
  if ($message) {
    session()->set('lastErrMsg', $message);
  } else {
    session()->remove('lastErrMsg');
  }
}

/**
 * Set updated by based on user id. Used for Model data.
 * @param array $data
 */
function setUpdatedBy($data = [])
{
  $data['updated_at'] = ($data['updated_at'] ?? date('Y-m-d H:i:s'));

  if (empty($data['updated_by']) && isLoggedIn()) {
    $data['updated_by'] = session('login')->user_id;
  }
  return $data;
}

/**
 * Generate UUID (Universally Unique Identifier)/GUID (Globally Unique Identifier)
 */
function uuid()
{
  $timeLow          = bin2hex(random_bytes(4));
  $timeHigh         = bin2hex(random_bytes(2));
  $timeHiAndVersion = bin2hex(random_bytes(2));
  $clockSeqLow      = bin2hex(random_bytes(2));
  $node             = bin2hex(random_bytes(6));

  return "{$timeLow}-{$timeHigh}-{$timeHiAndVersion}-{$clockSeqLow}-{$node}";
}

class FileLogger
{
  protected $hFile;

  public function __construct($filename = 'logger.log')
  {
    $this->hFile = fopen($filename, 'ab');

    return $this;
  }

  public function close()
  {
    return fclose($this->hFile);
  }

  public function write($data, $length = NULL)
  {
    return fputs($this->hFile, '[' . date('Y-m-d H:i:s') . '] ' . print_r($data, TRUE) . "\r\n", $length);
  }
}
