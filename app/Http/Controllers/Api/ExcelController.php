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

    public function index()
    {
        $tables = $this->tableRepository->getByUserPaginated(auth()->id());
        return response()->json($tables);
    }

    public function show($id)
    {
        $table = $this->tableRepository->findByUserAndId(auth()->id(), $id);

        if (!$table) {
            return response()->json(['message' => 'Tabla no encontrada'], 404);
        }

        return response()->json($table);
    }

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
