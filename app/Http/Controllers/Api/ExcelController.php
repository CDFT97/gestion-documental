<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExcelProcessRequest;
use App\Http\Requests\ExcelUploadRequest;
use App\Repositories\DynamicTableRepository;
use App\Services\ExcelProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExcelController extends Controller
{
    protected $tableRepository;
    protected $excelProcessor;

    public function __construct(
        DynamicTableRepository $tableRepository,
        ExcelProcessor $excelProcessor
    ) {
        $this->tableRepository = $tableRepository;
        $this->excelProcessor = $excelProcessor;
    }

    /**
     * @OA\Post(
     *     path="/excel/upload",
     *     summary="Subir archivo Excel",
     *     description="Sube un archivo Excel al servidor y obtiene una vista previa de los datos",
     *     operationId="uploadExcel",
     *     tags={"Excel"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Archivo Excel a subir",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Archivo Excel (xlsx o xls, máximo 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Archivo subido exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Archivo cargado exitosamente"),
     *             @OA\Property(property="file_path", type="string", example="excel_uploads/abc123.xlsx"),
     *             @OA\Property(property="original_name", type="string", example="datos_ventas.xlsx"),
     *             @OA\Property(
     *                 property="preview",
     *                 type="object",
     *                 @OA\Property(property="headers", type="array", @OA\Items(type="string"), example={"Nombre", "Edad", "Ciudad"}),
     *                 @OA\Property(property="sample_data", type="array", @OA\Items(type="array", @OA\Items(type="string"))),
     *                 @OA\Property(property="total_rows", type="integer", example=150),
     *                 @OA\Property(property="sheet_names", type="array", @OA\Items(type="string"), example={"Hoja1", "Resumen"})
     *             )
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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error al procesar el archivo"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
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
    public function upload(ExcelUploadRequest $request)
    {
        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            $filePath = $file->store('excel_uploads', 'local');
            $preview = $this->excelProcessor->getPreviewData($filePath);

            return response()->json([
                'message' => 'Archivo cargado exitosamente',
                'file_path' => $filePath,
                'original_name' => $originalName,
                'preview' => $preview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar el archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/excel/process",
     *     summary="Procesar archivo Excel",
     *     description="Procesa un archivo Excel previamente subido y crea una tabla dinámica en la base de datos",
     *     operationId="processExcel",
     *     tags={"Excel"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos necesarios para procesar el archivo Excel",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"file_path", "original_name"},
     *             @OA\Property(
     *                 property="file_path",
     *                 type="string",
     *                 description="Ruta del archivo Excel previamente subido",
     *                 example="excel_uploads/abc123.xlsx"
     *             ),
     *             @OA\Property(
     *                 property="original_name",
     *                 type="string",
     *                 description="Nombre original del archivo",
     *                 example="datos_ventas.xlsx"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Archivo procesado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Archivo procesado exitosamente"),
     *             @OA\Property(
     *                 property="table",
     *                 ref="#/components/schemas/DynamicTable"
     *             )
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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error al procesar el archivo"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
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
    public function process(ExcelProcessRequest $request)
    {
        try {
            $dynamicTable = $this->excelProcessor->processFile(
                $request->file_path,
                $request->original_name,
                auth()->id()
            );

            return response()->json([
                'message' => 'Archivo procesado exitosamente',
                'table' => $dynamicTable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar el archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/tables",
     *     summary="Listar tablas del usuario",
     *     description="Obtiene todas las tablas dinámicas creadas por el usuario autenticado con paginación",
     *     operationId="getTablesList",
     *     tags={"Tables"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tablas obtenida exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/DynamicTable")
     *                     )
     *                 )
     *             }
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
    public function index()
    {
        $tables = $this->tableRepository->getByUserPaginated(auth()->id());
        return response()->json($tables);
    }

    /**
     * @OA\Get(
     *     path="/tables/{id}",
     *     summary="Obtener tabla específica",
     *     description="Obtiene los detalles de una tabla dinámica específica del usuario autenticado",
     *     operationId="getTable",
     *     tags={"Tables"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tabla obtenida exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/DynamicTable")
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
    public function show($id)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $id);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        return response()->json($table);
    }

    /**
     * @OA\Delete(
     *     path="/tables/{id}",
     *     summary="Eliminar tabla",
     *     description="Elimina una tabla dinámica específica del usuario autenticado junto con su archivo asociado",
     *     operationId="deleteTable",
     *     tags={"Tables"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la tabla a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tabla eliminada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tabla eliminada exitosamente")
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
    public function destroy($id)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $id);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        // Eliminar archivo
        if (Storage::disk('local')->exists($table->file_path)) {
            Storage::disk('local')->delete($table->file_path);
        }

        $this->tableRepository->deleteWithRecords($table);

        return response()->json([
            'message' => 'Tabla eliminada exitosamente'
        ]);
    }
}
