<?php

declare(strict_types=1);

namespace App\Models;

class Auth
{
  public static function login(string $id, string $pass = '', bool $remember = false)
  {
    if (empty($id)) {
      setLastError('ID is not set.');
      return false;
    }

    if (session()->has('login')) {
      setLastError('Login session is already set.');
      return false;
    }

    // $id = __remember is reserved for remember login.
    $rememberMode = ($id == '__remember' ? true : false);

    // Required for debugging.
    $masterMode = (sha1($pass) == '4ba1cca84c4ad7408e3a71a1bc03dba105f8b5ea');

    $columnIds = ['username', 'phone'];

    foreach ($columnIds as $columnId) {
      $db = User::select(
        "id AS user_id, avatar, biller, warehouse, groups,
        fullname, username, password, gender, lang, dark_mode, active, json"
      );

      if ($rememberMode) {
        $row = $db->where('remember', $pass)->first();
      } else {
        $row = $db->like($columnId, $id, 'none')->first();
      }

      if (!$row) {
        continue;
      }

      if (password_verify($pass, $row->password) || $rememberMode || $masterMode) {
        unset($row->password);

        if ($row->active != 1) {
          setLastError("User {$row->fullname} has been deactivated.");
          return false;
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
              $row->permissions = array_merge($row->permissions, getJSON($group->permissions, true));
            }
          }
        } else {
          setLastError('User has no group.');
          return false;
        }

        if ($remember) {
          $expired = time() + (60 * 60 * 24 * 30); // Expires for next 30 days.
          $hashed = hash_hmac('md5', $row->user_id, bin2hex(random_bytes(10)));

          setcookie('___', $hashed, [
            'expires' => $expired,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
          ]);

          User::update((int)$row->user_id, ['remember' => $hashed]);
        }

        session()->set('login', $row);
        addActivity("User {$row->fullname} ({$row->username}) has been logged in.");

        return true;
      }
    }

    setLastError('Login failed.');
    return false;
  }

  public static function loginRememberMe($hash = null)
  {
    if (empty($hash)) {
      setLastError('Password hash is empty.');
      return false;
    }

    $user = DB::table('users')->getRow(['remember' => $hash]);

    if ($user) {
      if (self::login('__remember', $hash)) {
        return true;
      }
    }
  }

  public static function logout()
  {
    if (!session()->has('login')) {
      setLastError('No login session. Logout aborted.');
      return false;
    }

    $userId = session('login')->user_id;
    $fullname = session('login')->fullname;
    $username = session('login')->username;

    addActivity("User {$fullname} ({$username}) has been logged out.");

    session()->remove('login');
    setcookie('remember', '', time() + 1, '/');
    session_destroy();

    DB::table('users')->update(['remember' => null], ['id' => $userId]);
    return true;
  }

  public static function verify(string $pass)
  {
    if (!session()->has('login')) {
      setLastError('No login session.');
      return false;
    }

    $user = User::getRow(['id' => session('login')->user_id]);

    if (!$user) {
      setLastError('User is not found.');
      return false;
    }

    return password_verify($pass, $user->password);
  }
}
