<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\Attachment;

/**
 * File Upload class.
 */
class FileUpload
{
  protected $file = NULL;
  /**
   * @var array
   */
  protected $files = [];
  /**
   * @var bool
   */
  protected $isMoved;

  public function __construct()
  {
    if (is_cli()) die("FileUpload() class cannot be run in CLI mode.");

    $this->files = $_FILES;
  }

  public function files()
  {
    return $this->files;
  }

  /**
   * Check if file has been uploaded and has size more than zero.
   * @param string $filename Filename.
   */
  public function has($filename)
  {
    $this->isMoved = FALSE;

    if (isset($this->files[$filename]) && $this->files[$filename]['size'] > 0) {
      $this->file = $this->files[$filename];
      return TRUE;
    }
    return FALSE;
  }

  public function getExtension()
  {
    if ($this->file) {
      if (strpos($this->getName(), '.') !== FALSE) {
        $s = explode('.', $this->getName());
        $len = count($s);

        return '.' . $s[$len - 1];
      }
    }
    return NULL;
  }

  public function getRandomName()
  {
    if ($this->file) {
      return bin2hex(random_bytes(16)) . $this->getExtension();
    }
    return NULL;
  }

  public function getName()
  {
    if ($this->file) {
      return $this->file['name'];
    }
    return NULL;
  }

  /**
   * Get file size.
   * @param string unit Unit to check. byte, kb, mb, gb
   */
  public function getSize($unit = 'byte')
  {
    if ($this->file) {
      switch ($unit) {
        case 'kb':
          $acc = 1024;
          break;
        case 'mb':
          $acc = (1024 * 1024);
          break;
        case 'gb':
          $acc = (1024 * 1024 * 1024);
          break;
        case 'byte':
        default:
          $acc = 1;
      }

      return ceil($this->file['size'] / $acc);
    }
    return NULL;
  }

  public function getTempName()
  {
    if ($this->file) {
      return $this->file['tmp_name'];
    }
    return NULL;
  }

  public function getType()
  {
    if ($this->file) {
      return $this->file['type'];
    }
    return NULL;
  }

  /**
   * Check if file has been moved or not.
   * @return bool
   */
  public function isMoved()
  {
    return $this->isMoved;
  }

  public function move($path, $newName = NULL)
  {
    if ($this->file) {
      $path = rtrim($path, '/') . '/';
      checkPath($path);
      $newName = ($newName ?? $this->getName());

      if (move_uploaded_file($this->getTempName(), $path . $newName)) {
        $this->isMoved = TRUE;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Store file to attachment table as BLOB.
   * @param string $filename Filename to store. Use default filename if omitted.
   * @param string $hashname Update record if present. Use random hashname if omitted.
   */
  public function store($filename = NULL, $hashname = NULL)
  {
    if ($hashname) {
      $attachment = Attachment::getRow(['hashname' => $hashname]);

      if ($attachment) {
        Attachment::update((int)$attachment->id, [
          'filename'  => ($filename ?? $this->getName()),
          'hashname'  => $attachment->hashname,
          'mime'      => $this->getType(),
          'data'      => ($this->getTempName()),
          'size'      => $this->getSize()
        ]);

        return (int)$attachment->id;
      }
    }

    return Attachment::add([
      'filename'  => ($filename ?? $this->getName()),
      'hashname'  => ($hashname ?? uuid()),
      'mime'      => $this->getType(),
      'data'      => file_get_contents($this->getTempName()),
      'size'      => $this->getSize()
    ]);
  }

  /**
   * Store file with random name to attachment table as BLOB.
   */
  public function storeRandom()
  {
    return $this->store($this->getRandomName());
  }
}
