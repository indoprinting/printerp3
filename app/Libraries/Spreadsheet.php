<?php

declare(strict_types=1);

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet as PhpOfficeSpreadsheet;
use PhpOffice\PhpSpreadsheet\{Reader, Writer};
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Fill};

/**
 * Spreadsheet
 */
class Spreadsheet
{
  /**
   * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
   */
  private $spreadsheet;
  /**
   * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
   */
  private $worksheet;

  public function __construct()
  {
    $this->spreadsheet = new PhpOfficeSpreadsheet();
    $this->worksheet = $this->spreadsheet->getActiveSheet();
    return $this->spreadsheet;
  }

  public function createSheet($index = NULL)
  {
    $this->worksheet = $this->spreadsheet->createSheet($index);
    return $this;
  }

  public function export($filename)
  {
    if (empty($filename)) return FALSE;
    $exportPath = FCPATH . 'files/exports/';
    $writer = new Writer\Xlsx($this->spreadsheet);
    $filename = (strlen($filename) < 6 ? $filename . '.xlsx' : $filename);
    $filename = (strtolower(substr($filename, -5, 5)) == '.xlsx' ? $filename : $filename . '.xlsx');
    $writer->save($exportPath . $filename);

    if (!is_file($exportPath . $filename)) {
      die('Cannot export. File doesn\'t exist.');
    }

    // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    // header('Content-Disposition: attachment; filename="' . $filename . '"');
    // header('Content-Length: ' . filesize($exportPath . $filename));
    // Just redirect it. If headers above are used. Error 520 on cloudflare.
    if (!is_cli()) {
      header('Location: ' . base_url('files/exports/' . $filename));
      exit();
    }

    return 'https://erp.indoprinting.co.id/files/exports/' . $filename . "\r\n";
  }

  /**
   * @deprecated This export is deprecated. Cannot support continue download.
   */
  public function export_($filename)
  {
    if (empty($filename)) return FALSE;
    $writer = new Writer\Xlsx($this->spreadsheet);
    $filename = (strlen($filename) < 6 ? $filename . '.xlsx' : $filename);
    $filename = (strtolower(substr($filename, -5, 5)) == '.xlsx' ? $filename : $filename . '.xlsx');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $writer->save('php://output');
    exit();
  }

  public function getProperties()
  {
    return $this->spreadsheet->getProperties();
  }

  public function getActiveSheet()
  {
    $this->worksheet = $this->spreadsheet->getActiveSheet();
    return $this;
  }

  public function getActiveSheetIndex()
  {
    return $this->spreadsheet->getActiveSheetIndex();
  }

  public function getSheet($index)
  {
    $this->worksheet = $this->spreadsheet->getSheet($index);
    return $this;
  }

  public function getSheetByName($name)
  {
    $this->worksheet = $this->spreadsheet->getSheetByName($name);
    return $this;
  }

  public function loadFile($file)
  {
    $reader = new Reader\Xlsx();
    $this->spreadsheet = $reader->load($file);
    $this->getActiveSheet();
    return $this;
  }

  /**
   * Merging cells
   * @param string $ranges Ranges to merge, Ex: 'A1:A10'
   */
  public function mergeCells($ranges)
  {
    $this->worksheet->mergeCells($ranges);
    return $this;
  }

  /**
   * Save as file.
   * @param string $filename Filename to save.
   */
  public function save($filename)
  {
    if (empty($filename)) return FALSE;
    $writer = new Writer\Xlsx($this->spreadsheet);
    $filename = (strlen($filename) < 6 ? $filename . '.xlsx' : $filename);
    $filename = (strtolower(substr($filename, -5, 5)) == '.xlsx' ? $filename : $filename . '.xlsx');
    $writer->save($filename);
    $this->spreadsheet->disconnectWorksheets();
    return TRUE;
  }

  public function setActiveSheetIndex($index)
  {
    $this->worksheet = $this->spreadsheet->setActiveSheetIndex($index);
    return $this;
  }

  public function setActiveSheetIndexByName($name)
  {
    $this->worksheet = $this->spreadsheet->setActiveSheetIndexByName($name);
    return $this;
  }

  public function setAlignment($ranges, $align)
  {
    $this->worksheet->getStyle($ranges)->getAlignment()->setHorizontal($align);
    return $this;
  }

  /**
   * range = 'A1:E1'
   */
  public function setAutoFilter($ranges)
  {
    $this->worksheet->setAutoFilter($ranges);
    return $this;
  }

  public function setBold($ranges, $bold = TRUE)
  {
    $this->worksheet->getStyle($ranges)->getFont()->setBold($bold);
    return $this;
  }

  public function setCellValue($cell, $value, $type = NULL)
  {
    if ($type) {
      $this->setCellValueExplicit($cell, $value, $type);
    } else {
      $this->worksheet->setCellValue($cell, $value);
    }
    return $this;
  }

  public function setCellValueByColumnAndRow($col, $row, $value)
  {
    $this->worksheet->setCellValue([$col, $row], $value);
    return $this;
  }

  public function setCellValueExplicit($cell, $value, $type)
  {
    $this->worksheet->setCellValueExplicit($cell, $value, $type);
    return $this;
  }

  public function setColor($ranges, $rgbColor)
  {
    $this->worksheet->getStyle($ranges)->getFont()->getColor()->setRGB($rgbColor);
    return $this;
  }

  public function setColumnAutoWidth($col)
  {
    $this->worksheet->getColumnDimension($col)->setAutoSize(TRUE);
    return $this;
  }

  public function setColumnWidth($col, $width)
  {
    $this->worksheet->getColumnDimension($col)->setWidth($width);
    return $this;
  }

  public function setComment($col, string $text)
  {
    $this->worksheet->getComment($col)->getText()->createText($text);
    return $this;
  }

  /**
   * $ranges = 'A1:C1'
   * $rgbColor [RGB] = 'FF0000' (Red)
   */
  public function setFillColor($ranges, $rgbColor)
  {
    $this->worksheet->getStyle($ranges)->getFill()
      ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rgbColor);
    return $this;
  }

  public function setHorizontalAlign($ranges, $align = Alignment::HORIZONTAL_GENERAL)
  {
    $this->worksheet->getStyle($ranges)->getAlignment()->setHorizontal($align);
  }

  public function setWorkbookFontName($fontName)
  {
    $this->spreadsheet->getDefaultStyle()->getFont()->setName($fontName);
    return $this;
  }

  public function setWorkbookFontSize($fontSize)
  {
    $this->spreadsheet->getDefaultStyle()->getFont()->setSize($fontSize);
    return $this;
  }

  public function setItalic($ranges)
  {
    $this->worksheet->getStyle($ranges)->getFont()->setItalic(TRUE);
    return $this;
  }

  public function setTabColor($rgbColor)
  {
    $this->worksheet->getTabColor()->setRGB($rgbColor);
    return $this;
  }

  public function setTitle($title)
  {
    $this->worksheet->setTitle($title);
    return $this;
  }

  public function setUnderline($ranges)
  {
    $this->worksheet->getStyle($ranges)->getFont()->setUnderline(TRUE);
    return $this;
  }

  public function setUrl($cell, $url)
  {
    $this->worksheet->getCell($cell)->getHyperlink()->setUrl($url);
    return $this;
  }

  public function setVerticalAlign($ranges, $align = Alignment::VERTICAL_TOP)
  {
    $this->worksheet->getStyle($ranges)->getAlignment()->setHorizontal($align);
  }

  public function setWrapText($ranges, $enable = TRUE)
  {
    $this->worksheet->getStyle($ranges)->getAlignment()->setWrapText($enable);
    return $this;
  }
}
