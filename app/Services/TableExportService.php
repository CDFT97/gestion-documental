<?php

namespace App\Services;

use App\Repositories\DynamicTableRecordRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;

class TableExportService
{
  protected $recordRepository;

  public function __construct(DynamicTableRecordRepository $recordRepository)
  {
    $this->recordRepository = $recordRepository;
  }

  public function exportTable($table, $format = 'xlsx')
  {
    switch ($format) {
      case 'xlsx':
        return $this->exportToExcel($table);
      case 'csv':
        return $this->exportToCsv($table);
      default:
        throw new \InvalidArgumentException('Formato no soportado: ' . $format);
    }
  }

  private function exportToExcel($table)
  {
    $spreadsheet = $this->createSpreadsheetWithData($table);

    // Aplicar estilos específicos de Excel
    $worksheet = $spreadsheet->getActiveSheet();
    $this->styleHeaders($worksheet, count($table->columns));
    $this->autoSizeColumns($worksheet);

    return $this->saveSpreadsheet($spreadsheet, $table, 'xlsx', new Xlsx($spreadsheet));
  }

  private function exportToCsv($table)
  {
    $spreadsheet = $this->createSpreadsheetWithData($table);

    $writer = new Csv($spreadsheet);
    $writer->setDelimiter(',');
    $writer->setEnclosure('"');
    $writer->setSheetIndex(0);

    return $this->saveSpreadsheet($spreadsheet, $table, 'csv', $writer);
  }
  /**
   * Crear spreadsheet con datos comunes para Excel y CSV
   */
  private function createSpreadsheetWithData($table)
  {
    $spreadsheet = new Spreadsheet();
    $worksheet = $spreadsheet->getActiveSheet();

    // Configurar título de la hoja
    $worksheet->setTitle(substr($table->original_filename, 0, 31));

    // Obtener datos
    $records = $this->recordRepository->getForExport($table->id);
    $headers = $this->extractHeaders($table);

    // Escribir headers
    $worksheet->fromArray($headers, null, 'A1');

    // Escribir datos
    $this->writeDataRows($worksheet, $records, $table);

    return $spreadsheet;
  }
  /**
   * Extraer headers de las columnas de la tabla
   */
  private function extractHeaders($table)
  {
    $headers = [];
    foreach ($table->columns as $column) {
      $headers[] = $column['original_name'];
    }
    return $headers;
  }
  /**
   * Escribir filas de datos en el worksheet
   */
  private function writeDataRows($worksheet, $records, $table)
  {
    $row = 2; // Empezar en la fila 2 (después de headers)

    foreach ($records as $record) {
      $rowData = $this->formatRecordData($record, $table);
      $worksheet->fromArray($rowData, null, 'A' . $row);
      $row++;
    }
  }
  /**
   * Formatear datos de un registro según las columnas de la tabla
   */
  private function formatRecordData($record, $table)
  {
    $rowData = [];
    foreach ($table->columns as $column) {
      $value = $record->data[$column['name']] ?? '';
      $rowData[] = $this->formatCellValue($value, $column['type']);
    }
    return $rowData;
  }
  /**
   * Guardar spreadsheet y retornar información para descarga
   */
  private function saveSpreadsheet($spreadsheet, $table, $format, $writer)
  {
    $filename = $this->generateFilename($table, $format);
    $tempPath = $this->ensureTempPath($filename);

    $writer->save($tempPath);

    return [
      'path' => $tempPath,
      'filename' => $filename,
      'headers' => $this->getResponseHeaders($format, $filename)
    ];
  }
  /**
   * Asegurar que existe el directorio temporal y retornar path completo
   */
  private function ensureTempPath($filename)
  {
    $tempPath = storage_path('app/temp/' . $filename);

    if (!file_exists(dirname($tempPath))) {
      mkdir(dirname($tempPath), 0755, true);
    }

    return $tempPath;
  }

  /**
   * Obtener headers HTTP según el formato
   */
  private function getResponseHeaders($format, $filename)
  {
    $contentTypes = [
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'csv' => 'text/csv'
    ];

    return [
      'Content-Type' => $contentTypes[$format],
      'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    ];
  }

  /**
   * Aplicar estilos a los headers (solo para Excel)
   */
  private function styleHeaders($worksheet, $columnCount)
  {
    $headerRange = 'A1:' . chr(64 + $columnCount) . '1';

    $worksheet->getStyle($headerRange)->applyFromArray([
      'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFF']
      ],
      'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => '366092']
      ],
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
      ]
    ]);
  }
  /**
   * Auto-ajustar tamaño de columnas (solo para Excel)
   */
  private function autoSizeColumns($worksheet)
  {
    foreach (range('A', $worksheet->getHighestColumn()) as $col) {
      $worksheet->getColumnDimension($col)->setAutoSize(true);
    }
  }
  /**
   * Formatear valor de celda según su tipo
   */
  private function formatCellValue($value, $type)
  {
    if ($value === null || $value === '') {
      return '';
    }

    switch ($type) {
      case 'boolean':
        return $this->formatBooleanValue($value);
      case 'date':
        return $this->formatDateValue($value);
      case 'decimal':
        return $this->formatDecimalValue($value);
      case 'integer':
        return $this->formatIntegerValue($value);
      default:
        return $value;
    }
  }
  /**
   * Formatear valor booleano
   */
  private function formatBooleanValue($value)
  {
    if ($value === 1 || $value === '1' || $value === true || strtolower($value) === 'true') {
      return 'Sí';
    } else {
      return 'No';
    }
  }
  /**
   * Formatear valor de fecha
   */
  private function formatDateValue($value)
  {
    try {
      return date('d/m/Y', strtotime($value));
    } catch (\Exception $e) {
      return $value;
    }
  }
  /**
   * Formatear valor decimal
   */
  private function formatDecimalValue($value)
  {
    return is_numeric($value) ? number_format($value, 2, ',', '.') : $value;
  }
  /**
   * Formatear valor entero
   */
  private function formatIntegerValue($value)
  {
    return is_numeric($value) ? number_format($value, 0, ',', '.') : $value;
  }
  /**
   * Generar nombre de archivo con timestamp
   */
  private function generateFilename($table, $extension)
  {
    $baseName = pathinfo($table->original_filename, PATHINFO_FILENAME);
    $timestamp = date('Y-m-d_H-i-s');
    return "{$baseName}_export_{$timestamp}.{$extension}";
  }
}
