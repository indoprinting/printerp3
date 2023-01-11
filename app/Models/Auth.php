<?php

declare(strict_types=1);

namespace App\Models;

class Auth
{
  public static function login(string $id, string $pass = '', bool $remember = FALSE)
  {
    if (empty($id)) {
      setLastError('ID is not set.');
      return FALSE;
    }

    if (session()->has('login')) {
      setLastError('Login session is already set.');
      return FALSE;
    }

    // $id = __remember is reserved for remember login.
    $rememberMode = ($id == '__remember' ? TRUE : FALSE);

    $columnIds = ['username', 'phone'];

    foreach ($columnIds as $columnId) {
      $db = User::select(
        "id AS user_id, avatar, biller, warehouse, groups,
        fullname, username, password, gender, lang, dark_mode, active"
      );

      if ($rememberMode) {
        $row = $db->where('remember', $pass)->first();
      } else {
        $row = $db->like($columnId, $id, 'none')->first();
      }

      if ($row) {
        if (password_verify($pass, $row->password) || $rememberMode) {
          unset($row->password);

          if ($row->active != 1) {
            setLastError("User {$row->fullname} has been deactivated.");
            return FALSE;
          }

          if (!$row->avatar) {
            $row->avatar = ($row->gender == 'male' ? 'avatarmale' : 'avatarfemale');
          }

          unset($attachment);

          if (!empty($row->groups)) {
            $groupNames = explode(',', $row->groups);
            $row->permissions = [];

            $row->groups = $groupNames; // Make user groups as array.

            foreach ($groupNames as $groupName) {
              $group = UserGroup::getRow(['name' => $groupName]);

              if ($group) {
                $row->permissions = array_merge($row->permissions, getJSON($group->permissions, TRUE));
              }
            }
          } else {
            setLastError('User has no group.');
            return FALSE;
          }

          if ($remember) {
            $expired = time() + (60 * 60 * 24 * 30); // Expires for next 30 days.
            $hashed = hash_hmac('md5', $row->user_id, bin2hex(random_bytes(10)));

            setcookie('___', $hashed, [
              'expires' => $expired,
              'path' => '/',
              'httponly' => TRUE,
              'samesite' => 'Lax'
            ]);

            User::update((int)$row->user_id, ['remember' => $hashed]);
          }

          session()->set('login', $row);
          return TRUE;
        }
      }
    }

    setLastError('Login failed.');
    return FALSE;
  }

  public static function loginRememberMe($hash = NULL)
  {
    if (!empty($hash)) {
      $user = DB::table('users')->getRow(['remember' => $hash]);

      if ($user) {
        if (self::login('__remember', $hash)) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  public static function logout()
  {
    if (session()->has('login')) {
      $userId = session('login')->user_id;
      session()->remove('login');
      setcookie('remember', '', time() + 1, '/');
      session_destroy();

      DB::table('users')->update(['remember' => NULL], ['id' => $userId]);
      return TRUE;
    } else {
      setLastError('No login session. Logout aborted.');
    }
    return FALSE;
  }
}
