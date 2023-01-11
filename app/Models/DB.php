<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Convenient class to access database.
 */
class DB
{
  /**
   * @var \CodeIgniter\Database\BaseConnection
   */
  protected static $db;
  /**
   * @var \CodeIgniter\Database\BaseBuilder
   */
  protected static $qb;
  /**
   * @var DB
   */
  protected static $instance;
  /**
   * @var string
   */
  protected static $table;

  public static function affectedRows()
  {
    return self::$db->affectedRows();
  }

  public static function connect($groupName)
  {
    self::$db = db_connect($groupName);
    self::$qb = self::$db->table(self::$table);
    self::$instance = new self;
    return self::$instance;
  }

  /**
   * Compiles a delete string and runs the query
   *
   * @param mixed $where
   *
   * @return int Return affected of deleted rows.
   */
  public function delete($where = '', ?int $limit = null, bool $resetData = true)
  {
    self::$qb->delete($where, $limit, $resetData);
    return self::$db->affectedRows();
  }

  /**
   * Returns the last error code and message.
   */
  public static function error()
  {
    return self::$db->error();
  }

  public function from($table)
  {
    self::$table = $table;
    return self::$instance;
  }

  /**
   * Retrieve the results of the query. Typically an array of
   * individual data rows, which can be either an 'array', an
   * 'object', or a custom class name.
   */
  public function get($where = NULL, ?int $limit = NULL, ?int $offset = 0, bool $reset = true)
  {
    return self::$qb->getWhere($where, $limit, $offset, $reset)->getResult();
  }

  /**
   * Get compiled select.
   * @param bool $reset Reset last query.
   */
  public function getCompiledSelect(bool $reset = TRUE)
  {
    return self::$qb->getCompiledSelect($reset);
  }

  /**
   * Retrieve the results of the query. Typically an array of
   * individual data rows, which can be either an 'array', an
   * 'object', or a custom class name.
   */
  public function getRow($where = NULL)
  {
    if ($rows = self::$instance->get($where)) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Group by.
   */
  public function groupBy($by, bool $escape = NULL)
  {
    self::$qb->groupBy($by, $escape);
    return self::$instance;
  }

  /**
   * Starts a query group.
   */
  public function groupStart()
  {
    self::$qb->groupStart();
    return self::$instance;
  }

  /**
   * Ends a query group
   */
  public function groupEnd()
  {
    self::$qb->groupEnd();
    return self::$instance;
  }

  /**
   * Separates multiple calls with 'AND'.
   *
   * @param array|RawSql|string $key
   * @param mixed               $value
   */
  public function having($key, $value = NULL, bool $escape = NULL)
  {
    self::$qb->having($key, $value, $escape);
    return self::$instance;
  }

  /**
   * Compiles an insert string and runs the query
   */
  public function insert($set = NULL, ?bool $escape = NULL)
  {
    self::$qb->insert($set, $escape);
    return self::$db->insertID();
  }

  /**
   * Compiles batch insert strings and runs the queries
   */
  public function insertBatch(array $set = NULL, bool $escape = NULL, int $batchSize = 100)
  {
    return self::$qb->insertBatch($set, $escape, $batchSize);
  }

  /**
   * Return insert id.
   */
  public static function insertID()
  {
    return self::$db->insertID();
  }

  /**
   * Is not NULL
   */
  public function isNotNull($field)
  {
    self::$qb->where("{$field} IS NOT NULL");
    return self::$instance;
  }

  /**
   * Is NULL
   */
  public function isNull($field)
  {
    self::$qb->where("{$field} IS NULL");
    return self::$instance;
  }

  /**
   * Generates the JOIN portion of the query
   *
   * @param string $table Table name to join.
   * @param RawSql|string $cond Clause condition.
   * @param string $type Type of join: left, right, outer, inner, left outer, right outer.
   */
  public function join($table, $cond, $type = '', bool $escape = NULL)
  {
    self::$qb->join($table, $cond, $type, $escape);
    return self::$instance;
  }

  /**
   * Generates a %LIKE% portion of the query.
   * Separates multiple calls with 'AND'.
   *
   * @param array|RawSql|string $field
   */
  public function like($field, string $match = '', string $side = 'both', bool $escape = NULL, bool $insensitiveSearch = FALSE)
  {
    self::$qb->like($field, $match, $side, $escape, $insensitiveSearch);
    return self::$instance;
  }

  /**
   * Limit results of the query.
   * @param int $value Size of rows.
   * @param int $offset Offset of rows index.
   */
  public function limit(?int $value = NULL, ?int $offset = 0)
  {
    self::$qb->limit($value, $offset);
    return self::$instance;
  }

  /**
   * Generates a NOT LIKE portion of the query.
   * Separates multiple calls with 'AND'.
   *
   * @param array|RawSql|string $field
   */
  public function notHavingLike($field, $match = '', $side = 'both', bool $escape = NULL, bool $insensitiveSearch = false)
  {
    self::$qb->notHavingLike($field, $match, $side, $escape, $insensitiveSearch);
    return self::$instance;
  }

  /**
   * Generates a NOT LIKE portion of the query.
   * Separates multiple calls with 'AND'.
   *
   * @param array|RawSql|string $field
   */
  public function notLike($field, string $match = '', string $side = 'both', bool $escape = NULL, bool $insensitiveSearch = FALSE)
  {
    self::$qb->notLike($field, $match, $side, $escape, $insensitiveSearch);
    return self::$instance;
  }

  /**
   * Order rows by column.
   * 
   * @param string $orderBy Column name.
   * @param string $direction ASC, DESC or RANDOM
   */
  public function orderBy(string $orderBy, string $direction = '', ?bool $escape = NULL)
  {
    self::$qb->orderBy($orderBy, $direction, $escape);
    return self::$instance;
  }

  /**
   * Separates multiple calls with 'OR'.
   *
   * @param array|RawSql|string $key
   * @param mixed               $value
   */
  public function orHaving($key, $value = NULL, bool $escape = NULL)
  {
    self::$qb->orHaving($key, $value, $escape);
    return self::$instance;
  }

  /**
   * Generates a NOT LIKE portion of the query.
   * Separates multiple calls with 'OR'.
   *
   * @param array|RawSql|string $field
   *
   */
  public function orNotLike($field, string $match = '', string $side = 'both', bool $escape = NULL, bool $insensitiveSearch = FALSE)
  {
    self::$qb->orNotLike($field, $match, $side, $escape, $insensitiveSearch);
    return self::$instance;
  }

  /**
   * Generates a %LIKE% portion of the query.
   * Separates multiple calls with 'OR'.
   *
   * @param array|RawSql|string $field
   */
  public function orLike($field, string $match = '', string $side = 'both', bool $escape = NULL, bool $insensitiveSearch = FALSE)
  {
    self::$qb->orLike($field, $match, $side, $escape, $insensitiveSearch);
    return self::$instance;
  }

  /**
   * Generates the WHERE portion of the query.
   * Separates multiple calls with 'OR'.
   *
   * @param array|RawSql|string $key
   * @param mixed               $value
   * @param bool                $escape
   */
  public function orWhere($key, $value = NULL, bool $escape = NULL)
  {
    self::$qb->orWhere($key, $value, $escape);
    return self::$instance;
  }

  /**
   * Retrieve the results of the query. Typically an array of
   * individual data rows, which can be either an 'array', an
   * 'object', or a custom class name.
   */
  public function rows()
  {
    return self::$qb->get()->getResult();
  }

  /**
   * Retrieve the first results of the query.
   */
  public function first()
  {
    if ($rows = self::$instance->rows()) {
      return $rows[0];
    }
    return NULL;
  }

  /**
   * Retrieve the last results of the query.
   */
  public function last()
  {
    if ($rows = self::$instance->rows()) {
      return $rows[count($rows) - 1];
    }
    return NULL;
  }

  /**
   * Generates the SELECT portion of the query
   * @param array|RawSql|string $select
   * @param NULL|bool $escape
   */
  public function select($select = '*', bool $escape = NULL)
  {
    self::$qb->select($select, $escape);
    return self::$instance;
  }

  /**
   * Generates a SELECT AVG(field) portion of a query
   */
  public function selectAvg($select = '', $alias = '')
  {
    self::$qb->selectAvg($select, $alias);
    return self::$instance;
  }

  /**
   * Generates a SELECT COUNT(field) portion of a query
   */
  public function selectCount($select = '', $alias = '')
  {
    self::$qb->selectCount($select, $alias);
    return self::$instance;
  }

  /**
   * Sets a flag which tells the query string compiler to add DISTINCT
   */
  public function distinct($val = TRUE)
  {
    self::$qb->distinct($val);
    return self::$instance;
  }

  /**
   * Generates a SELECT MAX(field) portion of a query
   */
  public function selectMax($select = '', $alias = '')
  {
    self::$qb->selectMax($select, $alias);
    return self::$instance;
  }

  /**
   * Generates a SELECT MIN(field) portion of a query
   */
  public function selectMin($select = '', $alias = '')
  {
    self::$qb->selectMin($select, $alias);
    return self::$instance;
  }

  /**
   * Generates a SELECT SUM(field) portion of a query
   */
  public function selectSum($select = '', $alias = '')
  {
    self::$qb->selectSum($select, $alias);
    return self::$instance;
  }

  /**
   * Select a table.
   * @param string $name Table name.
   */
  public static function table(string $name)
  {
    self::$db = db_connect();
    self::$qb = self::$db->table($name);
    self::$instance = new self;
    self::$table = $name;
    return self::$instance;
  }

  /**
   * Begin Transaction
   */
  public static function transBegin(bool $testMode = FALSE)
  {
    self::$db = db_connect();
    return self::$db->transBegin($testMode);
  }

  /**
   * Commit Transaction
   */
  public static function transCommit()
  {
    return self::$db->transCommit();
  }

  /**
   * Complete Transaction
   */
  public static function transComplete()
  {
    return self::$db->transComplete();
  }

  /**
   * Disable Transactions
   */
  public static function transOff()
  {
    self::$db = db_connect();
    return self::$db->transOff();
  }

  /**
   * Rollback Transaction
   */
  public static function transRollback()
  {
    return self::$db->transRollback();
  }

  /**
   * Start Transaction
   */
  public static function transStart(bool $testMode = FALSE)
  {
    self::$db = db_connect();
    return self::$db->transStart($testMode);
  }

  /**
   * Lets you retrieve the transaction flag to determine if it has failed
   */
  public static function transStatus()
  {
    return self::$db->transStatus();
  }

  /**
   * Enable/disable Transaction Strict Mode
   */
  public static function transStrict()
  {
    self::$db = db_connect();
    return self::$db->transStrict();
  }

  /**
   * Generates the WHERE portion of the query.
   * Separates multiple calls with 'AND'.
   * 
   * @param array|RawSql|string $key
   * @param mixed $value
   * @param NULL|bool $escape
   */
  public function where($key, $value = NULL, bool $escape = NULL)
  {
    self::$qb->where($key, $value, $escape);
    return self::$instance;
  }

  /**
   * Compiles an update string and runs the query.
   *
   * @param array|object|null        $set
   * @param array|RawSql|string|null $where
   * @return int Return affected rows of updates.
   */
  public function update($set = NULL, $where = NULL, ?int $limit = NULL)
  {
    self::$qb->update($set, $where, $limit);
    return self::$instance->affectedRows();
  }

  /**
   * Generates a WHERE field IN('item', 'item') SQL query,
   * joined with 'AND' if appropriate.
   *
   * @param array|BaseBuilder|Closure|string $values The values searched on, or anonymous function with subquery
   */
  public function whereIn($key, $value = NULL, bool $escape = NULL)
  {
    self::$qb->whereIn($key, $value, $escape);
    return self::$instance;
  }
}
