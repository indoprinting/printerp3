<?php

declare(strict_types=1);

use App\Models\{Customer, CustomerGroup, User};

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
<<<<<<< HEAD
=======
 * Convert JS time to PHP time or vice versa.
 */
function dateTimeJS(string $datetime)
{
  if (strlen($datetime) && strpos($datetime, 'T') !== FALSE) {
    return str_replace('T', ' ', $datetime);
  }

  return str_replace(' ', 'T', $datetime);
}

/**
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
 * Print debug output.
 */
function dbgprint()
{
  $args = func_get_args();

  foreach ($args as $arg) {
    $str = print_r($arg, TRUE);
    echo ('<pre>');
    echo ($str);
    echo ('</pre>');
  }
}

/**
 * Filter number string into float.
 * @param mixed $num Number string.
 */
function filterDecimal($num)
{
<<<<<<< HEAD
  return (float)preg_replace('/([^\-\.0-9Ee])/', '', $num);
=======
  return (float)preg_replace('/([^\-\.0-9Ee])/', '', strval($num));
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
}

/**
 * Convert number into formatted currency.
 */
function formatCurrency($num)
{
  return 'Rp ' . number_format(filterDecimal($num), 0, ',', '.');
}

/**
 * Convert number into formatted number.
 */
function formatNumber($num)
{
  return number_format(filterDecimal($num), 0, ',', '.');
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
 * Check if status completed. Currently 'completed', 'completed_partial' or 'delivered' as completed.
 * @param string $status Status to check.
 */
function isCompleted($status)
{
  return ($status == 'completed' || $status == 'completed_partial' ||
    $status == 'delivered' || $status == 'finished' ? TRUE : FALSE);
}

/**
 * Check if due date has happened.
 * @param string $due_date Due date
 * @example 1 isDueDate('2020-01-20 20:40:11'); // Return FALSE if current time less then due date.
 */
function isDueDate($due_date)
{
  return (strtotime($due_date) > time() ? FALSE : TRUE);
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
<<<<<<< HEAD
=======
 * Determine special customer (Privilege or TOP) by customer id.
 * @param int $customerId Customer ID.
 */
function isSpecialCustomer($customerId)
{
  $customer = Customer::getRow(['id' => $customerId]);
  $csGroup = CustomerGroup::getRow(['id' => $customer->customer_group_id]);

  if ($csGroup) {
    return (strcasecmp($csGroup->name, 'PRIVILEGE') === 0 || strcasecmp($csGroup->name, 'TOP') === 0 ? TRUE : FALSE);
  }
  return FALSE;
}

/**
 * Check if user_id is W2P or not.
 */
function isW2PUser($user_id)
{
  $user = User::getRow(['id' => $user_id]);

  if ($user) {
    return (strcasecmp($user->username, 'W2P') === 0 ? TRUE : FALSE);
  }
  return FALSE;
}

/**
 * Check if invoice from W2P or note.
 */
function isWeb2Print($sale_id)
{
  $sale = Sale::getRow(['id' => $sale_id]);

  if ($sale) {
    $saleJS = getJSON($sale->json_data);

    return (strcasecmp(($saleJS->source ?? ''), 'W2P') === 0 ? TRUE : FALSE);
  }
  return FALSE;
}

/**
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
 * Nulling empty data.
 */
function nulling(array $data, array $keys)
{
  if (empty($keys)) return $data;

  foreach ($keys as $key) {
    if (isset($data[$key]) && empty($data[$key])) {
      $data[$key] = NULL;
    }
  }

  return $data;
}

function renderAttachment(string $attachment = NULL)
{
  $res = '';

  if ($attachment) {
    $res = '
      <a href="' . base_url('filemanager/view/' . $attachment) . '"
        data-toggle="modal" data-target="#ModalDefault2" data-modal-class="modal-lg modal-dialog-centered modal-dialog-scrollable">
        <i class="fad fa-file-download"></i>
      </a>';
  }

  return $res;
}

function renderStatus(string $status)
{
  if (empty($status)) return '';

  $type = 'default';
  $st = strtolower($status);

  $danger = [
    'bad', 'decrease', 'due', 'due_partial', 'expired', 'need_approval', 'need_payment', 'off', 'over_due',
    'over_received', 'returned'
  ];
  $info = [
    'completed_partial', 'confirmed', 'delivered', 'excellent', 'finished', 'installed_partial', 'ordered',
    'partial', 'preparing', 'received', 'received_partial'
  ];
  $success = ['approved', 'completed', 'increase', 'good', 'installed', 'paid', 'sent', 'verified'];
  $warning = [
    'cancelled', 'checked', 'draft', 'packing', 'pending', 'slow', 'trouble',
    'waiting_production', 'waiting_transfer'
  ];

  if (array_search($st, $danger) !== FALSE) {
    $type = 'danger';
  } elseif (array_search($st, $info) !== FALSE) {
    $type = 'info';
  } elseif (array_search($st, $success) !== FALSE) {
    $type = 'success';
  } elseif (array_search($st, $warning) !== FALSE) {
    $type = 'warning';
  }

  $name = lang('Status.' . $status);

  return "<div class=\"badge bg-gradient-{$type} p-2\">{$name}</div>";
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
function setCreatedBy(array $data)
{
  $data['created_at'] = ($data['created_at'] ?? date('Y-m-d H:i:s'));
  $data['date'] = $data['created_at']; // Obsolete

  if (empty($data['created_by']) && isLoggedIn()) {
    $data['created_by'] = session('login')->user_id;
  } else if (empty($data['created_by'])) {
    $data['created_by'] = 119; // System.
  }

  return $data;
}

/**
 * Set expired_at as expired date. Default +1 day.
 */
function setExpired(array $data)
{
  if (empty($data['expired_at'])) {
    $data['expired_at']   = date('Y-m-d H:i:s', strtotime('+1 day', time()));
<<<<<<< HEAD
    $data['expired_date'] = date('Y-m-d H:i:s', strtotime('+1 day', time())); // Obsolete
=======
    $data['expired_date'] = date('Y-m-d H:i:s', strtotime('+1 day', time())); // Compatibility
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
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
 * Strip HTML tags for note.
 */
function stripTags(string $text)
{
  return strip_tags($text, '<a><br><em><h1><h2><h3><li><ol><p><strong><u><ul>');
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
