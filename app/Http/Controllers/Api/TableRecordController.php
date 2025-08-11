<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\DynamicTableRepository;
use App\Repositories\DynamicTableRecordRepository;
use App\Services\TableExportService;
use Dompdf\FrameDecorator\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TableRecordController extends Controller
{
    protected $tableRepository;
    protected $recordRepository;
    protected $tableExportService;

    public function __construct(
        DynamicTableRepository $tableRepository,
        DynamicTableRecordRepository $recordRepository,
        TableExportService $tableExportService
    ) {
        $this->tableRepository = $tableRepository;
        $this->recordRepository = $recordRepository;
        $this->tableExportService = $tableExportService;
    }

    /**
     * @OA\Get(
     *     path="/tables/{id}/records",
     *     summary="Listar registros de una tabla",
     *     description="Obtiene todos los registros de una tabla específica con paginación, búsqueda y filtros",
     *     operationId="getTableRecords",
     *     tags={"Table Records"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Registros por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=15)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Campo por el cual ordenar",
     *         required=false,
     *         @OA\Schema(type="string", example="id")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Dirección del ordenamiento",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="asc")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Término de búsqueda general",
     *         required=false,
     *         @OA\Schema(type="string", example="Juan")
     *     ),
     *     @OA\Parameter(
     *         name="column_filters",
     *         in="query",
     *         description="Filtros específicos por columna (formato: column_filters[nombre_columna]=valor)",
     *         required=false,
     *         style="deepObject",
     *         @OA\Schema(
     *             type="object",
     *             additionalProperties={"type": "string"},
     *             example={"nombre": "Juan", "estado": "activo"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registros obtenidos exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="table", ref="#/components/schemas/DynamicTable"),
     *             @OA\Property(
     *                 property="records",
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                     @OA\Schema(
     *                         type="object",
     *                         @OA\Property(
     *                             property="data",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/TableRecord")
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tabla no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tabla no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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
            'search' => $request->get('search') ? strtolower(trim($request->get('search'))) : null,
            'column_filters' => []
        ];

        $columnFilters = $request->get('column_filters', []);
        if (is_array($columnFilters)) {
            foreach ($columnFilters as $column => $value) {
                if (!empty($value)) {
                    $filters['column_filters'][$column] = strtolower(trim($value));
                }
            }
        }

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

    /**
     * @OA\Post(
     *     path="/tables/{id}/records",
     *     summary="Crear nuevo registro",
     *     description="Crea un nuevo registro en la tabla especificada",
     *     operationId="createTableRecord",
     *     tags={"Table Records"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del registro a crear (depende de la estructura de la tabla)",
     *         @OA\JsonContent(
     *             type="object",
     *             description="Los campos varían según la estructura de cada tabla",
     *             example={
     *                 "nombre": "Juan Pérez",
     *                 "email": "juan@example.com",
     *                 "edad": 30,
     *                 "activo": true,
     *                 "fecha_registro": "2024-01-15"
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registro creado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Registro creado exitosamente"),
     *             @OA\Property(property="record", ref="#/components/schemas/TableRecord")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tabla no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tabla no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/tables/{id}/records/{recordId}",
     *     summary="Obtener registro específico",
     *     description="Obtiene los detalles de un registro específico de una tabla",
     *     operationId="getTableRecord",
     *     tags={"Table Records"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="recordId",
     *         in="path",
     *         description="ID del registro",
     *         required=true,
     *         @OA\Schema(type="integer", example=25)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro obtenido exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="table", ref="#/components/schemas/DynamicTable"),
     *             @OA\Property(property="record", ref="#/components/schemas/TableRecord")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tabla o registro no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Registro no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/tables/{id}/records/{recordId}",
     *     summary="Actualizar registro",
     *     description="Actualiza los datos de un registro específico en una tabla",
     *     operationId="updateTableRecord",
     *     tags={"Table Records"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="recordId",
     *         in="path",
     *         description="ID del registro",
     *         required=true,
     *         @OA\Schema(type="integer", example=25)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del registro a actualizar (depende de la estructura de la tabla)",
     *         @OA\JsonContent(
     *             type="object",
     *             description="Los campos varían según la estructura de cada tabla",
     *             example={
     *                 "nombre": "Juan Carlos Pérez",
     *                 "email": "juan.carlos@example.com",
     *                 "edad": 31,
     *                 "activo": false,
     *                 "fecha_registro": "2024-01-15"
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro actualizado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Registro actualizado exitosamente"),
     *             @OA\Property(property="record", ref="#/components/schemas/TableRecord")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tabla o registro no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Registro no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/tables/{id}/records/{recordId}",
     *     summary="Eliminar registro",
     *     description="Elimina un registro específico de una tabla",
     *     operationId="deleteTableRecord",
     *     tags={"Table Records"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="recordId",
     *         in="path",
     *         description="ID del registro",
     *         required=true,
     *         @OA\Schema(type="integer", example=25)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registro eliminado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Registro eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tabla o registro no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Registro no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/tables/{id}/records/bulk-delete",
     *     summary="Eliminar múltiples registros",
     *     description="Elimina múltiples registros de una tabla en una sola operación",
     *     operationId="bulkDeleteTableRecords",
     *     tags={"Table Records"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="IDs de los registros a eliminar",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"record_ids"},
     *             @OA\Property(
     *                 property="record_ids",
     *                 type="array",
     *                 description="Array de IDs de registros a eliminar",
     *                 @OA\Items(type="integer"),
     *                 example={25, 26, 27, 28}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registros eliminados exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Se eliminaron 4 registros exitosamente"),
     *             @OA\Property(property="deleted_count", type="integer", example=4)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tabla no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tabla no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/tables/{id}/export",
     *     summary="Exportar tabla",
     *     description="Exporta todos los registros de una tabla en formato Excel o CSV",
     *     operationId="exportTable",
     *     tags={"Table Records"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Formato de exportación",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="format",
     *                 type="string",
     *                 enum={"xlsx", "csv"},
     *                 example="xlsx",
     *                 description="Formato del archivo de exportación"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Archivo descargado exitosamente",
     *         @OA\MediaType(
     *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *             @OA\Schema(type="string", format="binary")
     *         ),
     *         @OA\Header(
     *             header="Content-Disposition",
     *             description="Nombre del archivo",
     *             @OA\Schema(type="string", example="attachment; filename=tabla_datos_2024-01-15.xlsx")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tabla no encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tabla no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Formato no válido",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al exportar",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function export(Request $request, $tableId)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $tableId);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'format' => 'in:xlsx,csv'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Formato no válido',
                'errors' => $validator->errors()
            ], 422);
        }

        $format = $request->get('format', 'xlsx');

        try {
            $exportResult = $this->tableExportService->exportTable($table, $format);

            return response()->download(
                $exportResult['path'],
                $exportResult['filename'],
                $exportResult['headers']
            )->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al exportar la tabla',
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
