<?php

declare(strict_types=1);

namespace App\Models;

class User
{
  /**
   * Add new User.
   */
  public static function add(array $data)
  {
    $data = setCreatedBy($data);

    if (!empty($data['groups']) && is_array($data['groups'])) {
      $data['groups'] = implode(',', $data['groups']);
    } else {
      setLastError('Group is not set.');
      return FALSE;
    }

    if (!empty($data['password'])) {
      if (strlen($data['password']) < 8) {
        setLastError('Password at least 8 characters');
        return FALSE;
      }

      $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    } else {
      setLastError('Password cannot be empty.');
      return FALSE;
    }

    $data = nulling($data, ['biller', 'warehouse']);

    DB::table('users')->insert($data);
    
    if ($insertID = DB::insertID()) {
      return $insertID;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Delete User.
   */
  public static function delete(array $where)
  {
    if (isset($where['id'])) {
      if ($where['id'] == 1) {
        setLastError('Owner user restricted to delete.');
        return FALSE;
      }
    }

    $users = self::get($where);

    foreach ($users as $user) {
      Attachment::delete(['id' => $user->avatar_id]);
    }

    DB::table('users')->delete($where);
    
    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }

  /**
   * Get User collections.
   */
  public static function get($where = [])
  {
    return DB::table('users')->get($where);
  }

  /**
   * Get User row.
   */
  public static function getRow($where = [])
  {
    if ($rows = self::get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Select User.
   */
  public static function select(string $columns, $escape = TRUE)
  {
    return DB::table('users')->select($columns, $escape);
  }

  /**
   * Update User.
   */
  public static function update(int $id, array $data)
  {
    if ($id == 1) {
      if (isset($data['username']) && strcasecmp($data['username'], 'owner') !== 0) {
        setLastError('User owner cannot be changed.');
        return FALSE;
      }

      if (isset($data['groups']) && is_array($data['groups'])) {
        if (!in_array('OWNER', array_map('strtoupper', $data['groups']))) {
          setLastError('User owner must has OWNER group.');
          return FALSE;
        }
      }
    }

    if (isset($data['groups'])) {
      if (is_array($data['groups'])) {
        $data['groups'] = implode(',', $data['groups']);
      } else {
        setLastError('Groups column must be an array.');
        return FALSE;
      }
    }

    if (isset($data['password'])) {
      if (is_string($data['password']) && strlen($data['password']) < 8) {
        setLastError('Password at least 8 characters');
        return FALSE;
      } else if (!is_string($data['password'])) {
        setLastError('Password must be a string.' . gettype($data['password']));
        return FALSE;
      }

      $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $data = nulling($data, ['biller', 'warehouse']);

    DB::table('users')->update($data, ['id' => $id]);
    
    if ($affectedRows = DB::affectedRows()) {
      return $affectedRows;
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
