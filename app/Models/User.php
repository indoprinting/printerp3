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

      // Begin Backward Compatibilty
      $userGroup = UserGroup::getRow(['name' => $data['groups'][0]]);

      if ($userGroup) {
        $data['group_id'] = $userGroup->id;
      }
      // End Backward Compatibilty
    } else {
      setLastError('Group is not set.');
      return false;
    }

    if (isset($data['biller'])) {
      $biller = Biller::getRow(['code' => $data['biller']]);

      if ($biller) {
        $data['biller_id'] = $biller->id;
      }
    }

    if (isset($data['warehouse'])) {
      $warehouse = Warehouse::getRow(['code' => $data['warehouse']]);

      if ($warehouse) {
        $data['warehouse_id'] = $warehouse->id;
      }
    }

    if (!empty($data['password'])) {
      if (strlen($data['password']) < 8) {
        setLastError('Password at least 8 characters');
        return false;
      }

      $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    } else {
      setLastError('Password cannot be empty.');
      return false;
    }

    $data = nulling($data, ['biller', 'warehouse']);

    DB::table('users')->insert($data);

    if (DB::error()['code'] == 0) {
      return DB::insertID();
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
        return false;
      }
    }

    $users = self::get($where);

    foreach ($users as $user) {
      Attachment::delete(['id' => $user->avatar_id]);
    }

    DB::table('users')->delete($where);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
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
  public static function select(string $columns, $escape = true)
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
        return false;
      }

      if (isset($data['groups']) && is_array($data['groups'])) {
        if (!in_array('OWNER', array_map('strtoupper', $data['groups']))) {
          setLastError('User owner must has OWNER group.');
          return false;
        }
      }
    }

    if (isset($data['groups'])) {
      if (is_array($data['groups'])) {
        $data['groups'] = implode(',', $data['groups']);

        // Begin Backward Compatibilty
        $userGroup = UserGroup::getRow(['name' => $data['groups'][0]]);

        if ($userGroup) {
          $data['group_id'] = $userGroup->id;
        }
        // End Backward Compatibilty
      } else {
        setLastError('Groups column must be an array.');
        return false;
      }
    }

    if (isset($data['biller'])) { // Backward Compatibility
      $biller = Biller::getRow(['code' => $data['biller']]);

      if ($biller) {
        $data['biller_id'] = $biller->id;
      } else {
        $data['biller_id'] = null;
      }
    }

    if (isset($data['warehouse'])) { // Backward Compatibility
      $warehouse = Warehouse::getRow(['code' => $data['warehouse']]);

      if ($warehouse) {
        $data['warehouse_id'] = $warehouse->id;
      } else {
        $data['warehouse_id'] = null;
      }
    }

    if (isset($data['password'])) {
      if (is_string($data['password']) && strlen($data['password']) < 8) {
        setLastError('Password at least 8 characters');
        return false;
      } else if (!is_string($data['password'])) {
        setLastError('Password must be a string.' . gettype($data['password']));
        return false;
      }

      $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $data = nulling($data, ['biller', 'warehouse']);

    DB::table('users')->update($data, ['id' => $id]);

    if (DB::error()['code'] == 0) {
      return DB::affectedRows();
    }

    setLastError(DB::error()['message']);

    return false;
  }
}
