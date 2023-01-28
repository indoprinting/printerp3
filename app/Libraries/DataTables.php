<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * DataTables Server Side Processing Library.
 *
 * Copyright (C) 2021 Ridintek Industri.
 */
class DataTables
{
  const ERR_QUERY_BUILDER = 'Query builder is not defined. Please use table() or class constructor first.';

  /**
   * @var array
   */
  private $addColumns;

  /**
   * @var \CodeIgniter\Database\BaseBuilder
   */
  private static $qb;

  /**
   * @var array
   */
  private $columns;

  /**
   * @var \CodeIgniter\Database\BaseConnection
   */
  private static $db;

  /**
   * @var array
   */
  private $editColumns;

  /**
   * @var array
   */
  private $removeColumns;

  /**
   * @var \CodeIgniter\HTTP\IncomingRequest
   */
  private $request;

  /**
   * @var bool
   */
  private $returnObject;

  /**
   * @var array
   */
  private $filterData;

  /**
   * @var int
   */
  private $length;

  /**
   * @var array
   */
  private $order;

  /**
   * @var string
   */
  private $search;

  /**
   * @var int
   */
  private $start;

  public function __construct($tableName = NULL)
  {
    $this->returnObject  = FALSE;
    self::$db            = db_connect();
    $this->columns       = [];
    $this->filterData    = [];
    $this->addColumns    = [];
    $this->removeColumns = [];
    $this->request       = \Config\Services::request();

    // Used by DataTables.
    $this->length  = (int)$this->request->getPostGet('length'); // LIMIT value.
    $this->order   = $this->request->getPostGet('order');
    $this->search  = $this->request->getPostGet('search[value]');
    $this->start   = (int)$this->request->getPostGet('start'); // LIMIT offset.

    if ($tableName) {
      self::$qb = self::$db->table($tableName);
    }
  }

  /**
   * Add new column or overwrite existing column.
   * @param string $name New column name or overwrite existing column.
   * @param \Closure $callback Callback to manipulate existing column.
   * @param string $columnAfter Insert after column name. Use `first`, `last` or column name.
   */
  public function addColumn(string $name, \Closure $callback, string $columnAfter = 'last')
  {
    $this->addColumns[] = [
      'name'     => $name,
      'callback' => $callback,
      'after'    => $columnAfter
    ];
    return $this;
  }

  /**
   * Generate will return as object.
   * @param bool $returnObject Return as object.
   */
  public function asObject(bool $returnObject = TRUE)
  {
    $this->returnObject = $returnObject;
    return $this;
  }

  /**
   * Edit existing column.
   * @param string $name Existing column name.
   * @param \Closure $callback Callback to manipulate existing column.
   */
  public function editColumn(string $name, \Closure $callback)
  {
    $this->editColumns[] = [
      'name'     => $name,
      'callback' => $callback
    ];
    return $this;
  }

  public function filter($filterData)
  {
    $this->filterData = $filterData;
    return $this;
  }

  public function from($table, bool $overwrite = FALSE)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->from($table, $overwrite);
    return $this;
  }

  /**
   * Generate output to stdout by default.
   * @param bool $useReturn Function will return array or object if TRUE.
   */
  public function generate(bool $useReturn = FALSE)
  {
    $results = [];

    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    // Get total records. 'where', 'orWhere', 'like' and 'orLike' is not filtering.
    $recordsTotal = self::$qb->countAllResults(FALSE);

    // Internal filter data.
    if ($this->filterData) {
      self::$qb->groupStart();

      foreach ($this->filterData as $col => $value) {
        self::$qb->orLike($col, strval($value));
      }

      self::$qb->groupEnd();
    }

    // Global search by front-end.
    if ($this->search) {
      self::$qb->groupStart();

      foreach ($this->columns as $col) {
        if (!empty($col)) {
          if (stripos($col, 'GROUP_CONCAT') !== FALSE) continue;

          self::$qb->orLike($col, strval($this->search), 'both');
        }
      }

      self::$qb->groupEnd();

      // die(print_r($this->columns, TRUE));
      // die(self::$qb->getCompiledSelect()); // DEBUG_ONLY
      self::$qb->groupBy('id'); // Temporary test. Required.
    }

    // Return number of filtered rows.
    $recordsFiltered = self::$qb->countAllResults(FALSE);

    if ($this->order && is_array($this->order)) {
      foreach ($this->order as $order) {
        // Since first column is 0 from client, MySQL need column min. 1 for orderding.
        // So we add this by one.
        self::$qb->orderBy(strval($order['column'] + 1), $order['dir']);
      }
    }

    if ($this->length > 1) {
      // Length return -1 for no limit.
      self::$qb->limit($this->length, $this->start);
    }

    // print_r(self::$qb->getCompiledSelect()); die(); // DEBUG_ONLY

    $rows = self::$qb->get()->getResultArray();

    // Modifying row data.
    for ($x = 0; $x < count($rows); $x++) {
      // Add new column.
      if ($this->addColumns) {
        foreach ($this->addColumns as $addColumn) {
          if (is_callable($addColumn['callback']) && !empty($addColumn['after'])) {
            $callback = $addColumn['callback']($rows[$x]);

            $pos = array_search($addColumn['after'], array_keys($rows[$x]));

            if (strcasecmp($addColumn['after'], $addColumn['name']) !== 0) {
              unset($rows[$x][$addColumn['name']]);

              // Re-Positioning again after unset.
              $pos = array_search($addColumn['after'], array_keys($rows[$x]));
            }

            if (strcasecmp($addColumn['after'], 'first') === 0) {
              $pos = 0;
            } else if (strcasecmp($addColumn['after'], 'last') === 0 || $pos === FALSE) {
              $pos = count($rows[$x]);
            } else {
              $pos++;
            }

            // Merging data.
            $firstData  = array_slice($rows[$x], 0, $pos);
            $middleData = [$addColumn['name'] => $callback];
            $lastData   = array_slice($rows[$x], $pos);

            $rows[$x] = array_merge($firstData, $middleData, $lastData);
          }
        }
      }

      // Currently use this instead of addColumns.
      if ($this->editColumns) {
        foreach ($this->editColumns as $editColumn) {
          if (array_key_exists($editColumn['name'], $rows[$x])) {
            if (is_callable($editColumn['callback'])) {
              $rows[$x][$editColumn['name']] = $editColumn['callback']($rows[$x]);
            }
          }
        }
      }
    }

    // Removing column data.
    if ($this->removeColumns) {
      for ($x = 0; $x < count($rows); $x++) {
        foreach ($this->removeColumns as $rcol) {
          if (isset($rows[$x][$rcol])) unset($rows[$x][$rcol]);
        }
      }
    }

    // Finalizing results as object or array (default).
    foreach ($rows as $row) {
      $results[] = ($this->returnObject ? $row : array_values($row));
    }

    if ($useReturn) {
      return $results;
    }

    header('Content-Type: application/json; charset=UTF-8');

    die(json_encode([
      'data' => $results,
      'draw' => (int)$this->request->getPostGet('draw'),
      'recordsFiltered' => $recordsFiltered, // Total rows after filtered.
      'recordsTotal' => $recordsTotal // Total rows without filtered.
    ]));
  }

  /**
   * Return query
   */
  public function get(int $limit = NULL, int $offset = 0, bool $reset = TRUE)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    return self::$qb->get($limit, $offset, $reset);
  }

  /**
   * Convert select column string into array. Used by filter like Where or Like.
   * @param string $columns Select column string.
   */
  protected function getColumns($columns)
  {
    $cols = [];

    if (!empty($columns) && is_string($columns)) {
      $len = strlen($columns);
      $a = 0;
      $brackets = 0;
      $col = '';

      while ($a < $len) {
        $ch = substr($columns, $a, 1);

        if ($ch == '(') $brackets++;
        if ($ch == ')') $brackets--;

        if ($ch == ',' && !$brackets) {
          $cols[] = trim($col);
          $col = '';
        } else {
          $col .= $ch;
        }

        $a++;
      }

      $cols[] = trim($col);

      $a = 0;

      while ($a < count($cols)) {
        // Check if col has ' as '.
        if (stripos($cols[$a], ' as ') !== FALSE) {
          $cols[$a] = preg_split('/\ as\ /i', $cols[$a])[0];
        } else {
          // Check if col has space character.
          $operators = 0;
          $spacer = 0;

          for ($b = strlen($cols[$a]) - 1; $b > 0; $b--) {
            if ($cols[$a][$b] == ')') break;

            if ($cols[$a][$b] == '*') $operators++;
            if ($cols[$a][$b] == '/') $operators++;
            if ($cols[$a][$b] == '+') $operators++;
            if ($cols[$a][$b] == '-') $operators++;

            if ($cols[$a][$b] == ' ') $spacer++;
          }

          if ($spacer > 0 && !$operators) {
            $cl = explode(' ', $cols[$a]);
            array_pop($cl); // Remove last array.
            $cols[$a] = implode(' ', $cl); // Join array with space.
          }
        }

        $a++;
      }
    }

    return $cols;
  }

  public function groupBy($by, $escape = NULL)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->groupBy($by, $escape);
    return $this;
  }

  public function join(string $table, string $cond, string $type = '', bool $escape = NULL)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->join($table, $cond, $type, $escape);
    return $this;
  }

  public function like($field, string $match = '', string $side = 'both', bool $escape = NULL, bool $insensitiveSearch = FALSE)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->like($field, $match, $side, $escape, $insensitiveSearch);
    return $this;
  }

  protected function limit(int $value = NULL, int $offset = 0)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->limit($value, $offset);
    return $this;
  }

  protected function orderBy(string $orderBy, string $direction = '', bool $escape = NULL)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->orderBy($orderBy, $direction, $escape);
    return $this;
  }

  public function orLike($field, string $match = '', $side = 'both', bool $escape = NULL, bool $insensitiveSearch = FALSE)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->orLike($field, $match, $side, $escape, $insensitiveSearch);
    return $this;
  }

  public function orWhere($key, $value = NULL, bool $escape = NULL)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->orWhere($key, $value, $escape);
    return $this;
  }

  public function removeColumn(string $columns)
  {
    $cols = explode(',', $columns);

    $this->removeColumns = $this->trimArray($cols);
    return $this;
  }

  public function select($select, bool $escape = NULL)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    $this->columns = $this->getColumns($select);
    self::$qb->select($select, $escape);
    return $this;
  }

  public static function table($table)
  {
    self::$db = db_connect();
    self::$qb = self::$db->table($table);
    return new self;
  }

  private function trimArray(array $data)
  {
    if (is_array($data) && count($data)) {
      for ($x = 0; $x < count($data); $x++) {
        $data[$x] = trim($data[$x]);
      }
    }
    return $data;
  }

  public function where($key, $value = NULL, bool $escape = NULL)
  {
    if (!self::$qb) {
      throw new \Exception(self::ERR_QUERY_BUILDER);
    }

    self::$qb->where($key, $value, $escape);
    return $this;
  }
}
