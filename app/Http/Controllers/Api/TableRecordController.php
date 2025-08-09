<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\DynamicTableRepository;
use App\Repositories\DynamicTableRecordRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TableRecordController extends Controller
{
    protected $tableRepository;
    protected $recordRepository;

    public function __construct(
        DynamicTableRepository $tableRepository,
        DynamicTableRecordRepository $recordRepository
    ) {
        $this->tableRepository = $tableRepository;
        $this->recordRepository = $recordRepository;
    }

    public function index(Request $request, $tableId)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $tableId);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        $perPage = $request->get('per_page', 15);
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'asc');

        $filters = [
            'search' => $request->get('search'),
            'column_filters' => $request->get('column_filters', [])
        ];

        $records = $this->recordRepository->getByTablePaginated(
            $tableId,
            $perPage,
            $filters,
            $sortBy,
            $sortDirection
        );

        return response()->json([
            'table' => $table,
            'records' => $records
        ]);
    }

    public function store(Request $request, $tableId)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $tableId);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        // Validar datos según las columnas de la tabla
        $rules = $this->buildValidationRules($table);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtener el siguiente número de fila
            $nextRowNumber = $this->recordRepository->countByTable($tableId) + 1;

            $record = $this->recordRepository->createForTable(
                $tableId,
                $request->all(),
                $nextRowNumber
            );

            // Actualizar contador de registros en la tabla
            $totalRecords = $this->recordRepository->countByTable($tableId);
            $table->update(['total_records' => $totalRecords]);

            return response()->json([
                'message' => 'Registro creado exitosamente',
                'record' => $record
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($tableId, $recordId)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $tableId);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        $record = $this->recordRepository->get($recordId);

        if (!$record || $record->dynamic_table_id != $tableId) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        return response()->json([
            'table' => $table,
            'record' => $record
        ]);
    }

    public function update(Request $request, $tableId, $recordId)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $tableId);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        $record = $this->recordRepository->get($recordId);

        if (!$record || $record->dynamic_table_id != $tableId) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        // Validar datos
        $rules = $this->buildValidationRules($table);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->recordRepository->updateData($record, $request->all());

            return response()->json([
                'message' => 'Registro actualizado exitosamente',
                'record' => $record->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($tableId, $recordId)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $tableId);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        $record = $this->recordRepository->get($recordId);

        if (!$record || $record->dynamic_table_id != $tableId) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        try {
            $this->recordRepository->delete($record);

            // Actualizar contador de registros
            $totalRecords = $this->recordRepository->countByTable($tableId);
            $table->update(['total_records' => $totalRecords]);

            return response()->json([
                'message' => 'Registro eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkDelete(Request $request, $tableId)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $tableId);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'record_ids' => 'required|array|min:1',
            'record_ids.*' => 'integer|exists:dynamic_table_records,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deletedCount = 0;
            foreach ($request->record_ids as $recordId) {
                $record = $this->recordRepository->get($recordId);
                if ($record && $record->dynamic_table_id == $tableId) {
                    $this->recordRepository->delete($record);
                    $deletedCount++;
                }
            }

            // Actualizar contador de registros
            $totalRecords = $this->recordRepository->countByTable($tableId);
            $table->update(['total_records' => $totalRecords]);

            return response()->json([
                'message' => "Se eliminaron {$deletedCount} registros exitosamente",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar los registros',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function export($tableId)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $tableId);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        try {
            // TODO: Implementar exportación con Maatwebsite\Excel
            // Por ahora retornamos los datos para que el frontend los procese
            $records = $this->recordRepository->getForExport($tableId);

            return response()->json([
                'table' => $table,
                'records' => $records,
                'export_url' => route('tables.export', $tableId) // Para descarga directa
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al exportar los datos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function buildValidationRules($table)
    {
        $rules = [];

        foreach ($table->columns as $column) {
            $columnRules = [];

            // Requerido si no es nullable
            if (!$column['nullable']) {
                $columnRules[] = 'required';
            } else {
                $columnRules[] = 'nullable';
            }

            // Tipo de dato
            switch ($column['type']) {
                case 'integer':
                    $columnRules[] = 'integer';
                    break;
                case 'decimal':
                    $columnRules[] = 'numeric';
                    break;
                case 'date':
                    $columnRules[] = 'date';
                    break;
                case 'boolean':
                    $columnRules[] = 'boolean';
                    break;
                default:
                    $columnRules[] = 'string';
                    $columnRules[] = 'max:255';
            }

            $rules[$column['name']] = implode('|', $columnRules);
        }

        return $rules;
    }
}
