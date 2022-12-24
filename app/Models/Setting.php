<?php
declare(strict_types=1);

namespace App\Models;

class Setting
{
  protected $db;

  public function __construct()
  {
    $this->db = db_connect();
  }

  /**
   * Add new user group.
   * @param array $userGroupData [ *name, *permissions, type[employee|student] ]
   */
  public function addUserGroup($userGroupData)
  {
    if (empty(trim($userGroupData['name']))) return FALSE;

    $userGroupData = setCreatedBy($userGroupData);

    $this->db->table('usergroup')->insert($userGroupData);

    if ($this->db->affectedRows()) {
      return $this->db->insertID();
    }
    return FALSE;
  }

  public function deleteUserGroups($clause = [])
  {
    if ($clause['id'] == 1) return FALSE; // Prevent delete owner group.

    $this->db->table('usergroup')->delete($clause);

    if ($this->db->affectedRows()) {
      return TRUE;
    }
    return FALSE;
  }

  public function getUserGroup($clause = [])
  {
    return $this->db->table('usergroup')->getWhere($clause)->getRowObject();
  }

  public function getUserGroups($clause = [])
  {
    $rows = [];

    foreach ($this->db->table('usergroup')->getWhere($clause)->getResult() as $row) {
      $rows[] = $row;
    }
    return $rows;
  }

  public function updateUserGroup($userGroupId, $userGroupData)
  {
    if ($userGroupId == 1) {
      // Prevent change of Owner group.
      if (strcasecmp($userGroupData['name'], 'Owner') !== 0) {
        return FALSE;
      }

      $permissions = getJSON($userGroupData['permissions'], TRUE);

      if (!in_array('All', $permissions)) { // Prevent owner group missing of All permissions.
        return FALSE;
      }
    }

    $userGroupData = setUpdatedBy($userGroupData);

    $this->db->table('usergroup')->update($userGroupData, ['id' => $userGroupId]);

    if ($this->db->affectedRows()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Add new user.
   *
   * @param array $userData [ address, avatar_id, birth_date, birth_place, city_id, classroom_id,
   *  dark_mode, district_id, educationleve_id, father_name, *fullname, gender, *group, lang,
   *  mother_name, *password, parent_phone, phone, province_id, remember_be, room_id, status,
   *  *type(general|employee|student), *username ]
   */
  public function addUser($userData)
  {
    if (empty($userData['username'])) {
      setLastError('Username is required.');
      return FALSE;
    }

    if (empty($userData['fullname'])) {
      setLastError('Fullname is required.');
      return FALSE;
    }

    if (!empty($userData['password'])) {
      if (strlen($userData['password']) < 8) {
        setLastError('Password at least 8 characters.');
        return FALSE;
      }

      $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
    } else {
      setLastError('Password is not set.');
      return FALSE;
    }

    if (!empty($userData['groups'])) {
      if (is_array($userData['groups'])) {
        $userData['groups'] = implode(',', $userData['groups']);
      }
    } else {
      setLastError('Group is not set.');
      return FALSE;
    }

    // Set JSON
    $userData = setJSONColumn($userData, ['address', 'birth_date', 'birth_place', 'city_id',
    'classroom_id', 'district_id', 'educationlevel_id', 'father_name', 'mother_name', 'parent_phone',
    'province_id', 'room_id']);

    $userData = setCreatedBy($userData);

    $this->db->table('user')->insert($userData);

    if ($this->db->affectedRows()) {
      return $this->db->insertID();
    }

    setLastError($this->db->error()['message']);
    return FALSE;
  }

  public function deleteUsers($clause = [])
  {
    if ($clause['id'] == 1) { // Prevent delete owner user.
      setLastError('Reserved user cannot be deleted.');
      return FALSE;
    }

    $user = $this->getUser($clause);

    $this->db->table('user')->delete($clause);

    if ($this->db->affectedRows()) {
      $attachment = new Attachment();
      $attachment->deleteAttachments(['id' => $user->avatar_id]);
      return TRUE;
    }
    return FALSE;
  }

  public function getCities($clause = [])
  {
    $rows = [];

    $qb = $this->db->table('city');

    if (!empty($clause['name'])) {
      $qb->like('name', $clause['name'], 'none');
      unset($clause['name']);
    }

    foreach ($qb->getWhere($clause)->getResult() as $row) {
      $rows[] = $row;
    }
    return $rows;
  }

  public function getDistricts($clause = [])
  {
    $rows = [];

    $qb = $this->db->table('district');

    if (!empty($clause['name'])) {
      $qb->like('name', $clause['name'], 'none');
      unset($clause['name']);
    }

    foreach ($qb->getWhere($clause)->getResult() as $row) {
      $rows[] = $row;
    }
    return $rows;
  }

  public function getProvinces($clause = [])
  {
    $rows = [];

    $qb = $this->db->table('province');

    if (!empty($clause['name'])) {
      $qb->like('name', $clause['name'], 'none');
      unset($clause['name']);
    }

    foreach ($qb->getWhere($clause)->getResult() as $row) {
      $rows[] = $row;
    }

    return $rows;
  }

  public function getUser($clause = [])
  {
    return $this->db->table('user')->getWhere($clause)->getRowObject();
  }

  public function getUsers($clause = [])
  {
    $rows = [];

    $qb = $this->db->table('user');

    if (!empty($clause['username'])) {
      $qb->like('username', $clause['username'], 'none');
      unset($clause['username']);
    }

    foreach ($qb->getWhere($clause)->getResult() as $row) {
      $rows[] = $row;
    }

    return $rows;
  }

  public function updateUser($userId, $userData)
  {
    $user = $this->getUser(['id' => $userId]);
    $json = getJSON($user->json, TRUE);

    if (!$user) {
      setLastError('User is not valid.');
      return FALSE;
    }

    if (!empty($userData['password'])) {
      if (strlen($userData['password']) < 8) {
        setLastError('Password at least 8 characters');
        return FALSE;
      }

      $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
    } else if (isset($userData['password'])) {
      unset($userData['password']);
    }

    if (!empty($userData['groups'])) {
      if (is_array($userData['groups'])) {
        if ($userId == 1) {
          if (isset($userData['username']) && $userData['username'] !== 'owner') {
            setLastError('Username owner cannot be change.');
            return FALSE;
          }

          $isValid = FALSE;

          foreach ($userData['groups'] as $groupName) {
            if (strcasecmp('OWNER', $groupName) === 0) $isValid = TRUE;
          }

          if (!$isValid) {
            setLastError(lang('App.accessDenied'));
            return FALSE;
          }
        }

        $userData['groups'] = implode(',', $userData['groups']);
      }
    } else if (isset($userData['groups'])) {
      unset($userData['groups']);
    }

    // Profile JSON
    $userData = setJSONColumn($userData, ['address', 'birth_date', 'birth_place', 'city_id',
    'classroom_id', 'district_id', 'educationlevel_id', 'father_name', 'mother_name', 'parent_phone',
    'province_id', 'room_id'], $json);

    $userData = setUpdatedBy($userData);

    $this->db->table('user')->update($userData, ['id' => $userId]);

    if ($this->db->affectedRows()) {
      return TRUE;
    }

    setLastError($this->db->error()['message']);
    return FALSE;
  }
}