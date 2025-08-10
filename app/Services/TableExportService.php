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
    $spreadsheet = new Spreadsheet();
    $worksheet = $spreadsheet->getActiveSheet();

    $worksheet->setTitle(substr($table->original_filename, 0, 31)); //limite de 31 chars pera el titulo

    // Obtener todos los registros
    $records = $this->recordRepository->getForExport($table->id);

    // Headers
    $headers = [];
    foreach ($table->columns as $column) {
      $headers[] = $column['original_name'];
    }

    // Escribir headers con estilo
    $worksheet->fromArray($headers, null, 'A1');
    $this->styleHeaders($worksheet, count($headers));

    // Escribir datos
    $row = 2;
    foreach ($records as $record) {
      $rowData = [];
      foreach ($table->columns as $column) {
        $value = $record->data[$column['name']] ?? '';
        $rowData[] = $this->formatCellValue($value, $column['type']);
      }
      $worksheet->fromArray($rowData, null, 'A' . $row);
      $row++;
    }

    // Auto-ajustar columnas
    foreach (range('A', $worksheet->getHighestColumn()) as $col) {
      $worksheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Generar archivo temporal
    $filename = $this->generateFilename($table, 'xlsx');
    $tempPath = storage_path('app/temp/' . $filename);

    // Asegurar que el directorio temp existe
    if (!file_exists(dirname($tempPath))) {
      mkdir(dirname($tempPath), 0755, true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($tempPath);

    return [
      'path' => $tempPath,
      'filename' => $filename,
      'headers' => [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"'
      ]
    ];
  }

  private function exportToCsv($table)
  {
    $spreadsheet = new Spreadsheet();
    $worksheet = $spreadsheet->getActiveSheet();

    // Obtener todos los registros
    $records = $this->recordRepository->getForExport($table->id);

    // Headers
    $headers = [];
    foreach ($table->columns as $column) {
      $headers[] = $column['original_name'];
    }

    // Escribir headers
    $worksheet->fromArray($headers, null, 'A1');

    // Escribir datos
    $row = 2;
    foreach ($records as $record) {
      $rowData = [];
      foreach ($table->columns as $column) {
        $value = $record->data[$column['name']] ?? '';
        $rowData[] = $this->formatCellValue($value, $column['type']);
      }
      $worksheet->fromArray($rowData, null, 'A' . $row);
      $row++;
    }

    // Generar archivo temporal
    $filename = $this->generateFilename($table, 'csv');
    $tempPath = storage_path('app/temp/' . $filename);

    // Asegurar que el directorio temp existe
    if (!file_exists(dirname($tempPath))) {
      mkdir(dirname($tempPath), 0755, true);
    }

    $writer = new Csv($spreadsheet);
    $writer->setDelimiter(',');
    $writer->setEnclosure('"');
    $writer->setSheetIndex(0);
    $writer->save($tempPath);

    return [
      'path' => $tempPath,
      'filename' => $filename,
      'headers' => [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"'
      ]
    ];
  }

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

  private function formatCellValue($value, $type)
  {
    if ($value === null || $value === '') {
      return '';
    }

    switch ($type) {
      case 'boolean':
        // Manejar diferentes representaciones de boolean
        if ($value === 1 || $value === '1' || $value === true || strtolower($value) === 'true') {
          return 'Sí';
        } else {
          return 'No';
        }
      case 'date':
        try {
          return date('d/m/Y', strtotime($value));
        } catch (\Exception $e) {
          return $value;
        }
      case 'decimal':
        return is_numeric($value) ? number_format($value, 2, ',', '.') : $value;
      case 'integer':
        return is_numeric($value) ? number_format($value, 0, ',', '.') : $value;
      default:
        return $value;
    }
  }

  private function generateFilename($table, $extension)
  {
    $baseName = pathinfo($table->original_filename, PATHINFO_FILENAME);
    $timestamp = date('Y-m-d_H-i-s');
    return "{$baseName}_export_{$timestamp}.{$extension}";
  }
}
