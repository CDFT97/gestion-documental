<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{DocumentUploadRequest, DocumentIndexRequest, DocumentUpdateRequest};
use App\Repositories\DocumentRepository;
use App\Services\DocumentService;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Documents",
 *     description="Gestión de documentos PDF"
 * )
 */
class DocumentController extends Controller
{
    protected $documentRepository;
    protected $documentService;

    public function __construct(
        DocumentRepository $documentRepository,
        DocumentService $documentService
    ) {
        $this->documentRepository = $documentRepository;
        $this->documentService = $documentService;
    }

    /**
     * @OA\Post(
     *     path="/documents/upload",
     *     summary="Subir un nuevo documento PDF",
     *     description="Permite subir un archivo PDF al sistema con metadatos opcionales",
     *     operationId="uploadDocument",
     *     tags={"Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Archivo PDF y metadatos",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Archivo PDF (máximo 50MB)"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     maxLength=255,
     *                     description="Nombre personalizado del documento",
     *                     example="Contrato de servicios 2024"
     *                 ),
     *                 @OA\Property(
     *                     property="category",
     *                     type="string",
     *                     maxLength=100,
     *                     description="Categoría del documento",
     *                     example="contratos"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     maxLength=1000,
     *                     description="Descripción del documento",
     *                     example="Contrato anual de servicios de consultoría"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Documento subido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documento subido exitosamente"),
     *             @OA\Property(property="document", ref="#/components/schemas/Document")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="file", type="array", @OA\Items(type="string", example="Solo se permiten archivos PDF."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al subir el documento"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
     *         )
     *     )
     * )
     */
    public function upload(DocumentUploadRequest $request)
    {
        try {
            $result = $this->documentService->uploadDocument(
                $request->file('file'),
                auth()->id(),
                [
                    'name' => $request->input('name'),
                    'category' => $request->input('category', 'general'),
                    'description' => $request->input('description')
                ]
            );

            return response()->json([
                'message' => $result['message'],
                'document' => $result['document']
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al subir el documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/documents",
     *     summary="Obtener lista paginada de documentos",
     *     description="Retorna una lista paginada de documentos del usuario con filtros opcionales",
     *     operationId="getDocuments",
     *     tags={"Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Número de elementos por página (1-100)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Término de búsqueda en nombre y descripción",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoría",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=100)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filtrar documentos desde esta fecha",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filtrar documentos hasta esta fecha",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de documentos",
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener los documentos"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
     *         )
     *     )
     * )
     */
    public function index(DocumentIndexRequest $request)
    {
        try {
            $documents = $this->documentRepository->getByUserPaginated(
                auth()->id(),
                $request->getPerPage(),
                $request->getFilters()
            );

            return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los documentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/documents/{id}",
     *     summary="Obtener un documento específico",
     *     description="Retorna los detalles de un documento específico del usuario",
     *     operationId="getDocument",
     *     tags={"Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del documento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del documento",
     *         @OA\JsonContent(
     *             @OA\Property(property="document", ref="#/components/schemas/Document")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Documento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documento no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener el documento"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $document = $this->documentRepository->findByUserAndId(auth()->id(), $id);

            if (!$document) {
                return response()->json([
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            return response()->json([
                'document' => $document
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/documents/{id}/preview",
     *     summary="Vista previa del documento PDF",
     *     description="Obtiene el contenido del PDF para mostrar en el navegador. Puede usar autenticación Bearer o token en query parameter",
     *     operationId="previewDocument",
     *     tags={"Documents"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del documento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="Token de autenticación alternativo",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contenido del PDF",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autorizado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Documento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documento no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener la vista previa"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}},
     *         {}
     *     }
     * )
     */
    public function preview($id, Request $request)
    {
        try {

            // Si no hay usuario autenticado, intentar con token de URL
            if (!auth()->check() && $request->has('token')) {
                $token = $request->get('token');

                // Buscar el token en la base de datos
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

                if ($personalAccessToken) {
                    // Verificar que el token no haya expirado
                    if (!$personalAccessToken->expires_at || $personalAccessToken->expires_at->isFuture()) {
                        auth()->setUser($personalAccessToken->tokenable);
                    } else {
                        \Log::info('Token expired');
                    }
                }
            }

            // Verificar que el usuario esté autenticado
            if (!auth()->check()) {
                \Log::info('Final auth check failed');
                return response()->json(['message' => 'No autorizado'], 401);
            }

            $result = $this->documentService->getDocumentContent($id, auth()->id());

            return response($result['content'])
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="document.pdf"')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            \Log::error('Preview error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la vista previa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/documents/{id}/preview/download",
     *     summary="Descargar documento PDF",
     *     description="Descarga el archivo PDF del documento",
     *     operationId="downloadDocument",
     *     tags={"Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del documento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Archivo PDF para descarga",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Documento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documento no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener el documento"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
     *         )
     *     )
     * )
     */
    public function download($id, Request $request)
    {
        try {
            $result = $this->documentService->getDocumentContent($id, auth()->id());

            return response($result['content'])
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="document.pdf"')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            \Log::error('Preview error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener el documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/documents/{id}",
     *     summary="Actualizar información del documento",
     *     description="Actualiza los metadatos de un documento (nombre, categoría, descripción)",
     *     operationId="updateDocument",
     *     tags={"Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del documento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos a actualizar",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255, example="Nuevo nombre del documento"),
     *             @OA\Property(property="category", type="string", maxLength=100, example="nueva-categoria"),
     *             @OA\Property(property="description", type="string", maxLength=1000, example="Nueva descripción del documento")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documento actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documento actualizado exitosamente"),
     *             @OA\Property(property="document", ref="#/components/schemas/Document")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Documento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documento no encontrado")
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
     *             @OA\Property(property="message", type="string", example="Error al actualizar el documento"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
     *         )
     *     )
     * )
     */
    public function update(DocumentUpdateRequest $request, $id)
    {
        try {
            $document = $this->documentRepository->findByUserAndId(auth()->id(), $id);

            if (!$document) {
                return response()->json([
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            $this->documentRepository->updateDocument(
                $document,
                $request->getUpdateData()
            );

            return response()->json([
                'message' => 'Documento actualizado exitosamente',
                'document' => $document->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/documents/{id}",
     *     summary="Eliminar documento",
     *     description="Elimina un documento del sistema incluyendo el archivo físico",
     *     operationId="deleteDocument",
     *     tags={"Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del documento",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documento eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documento eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Documento no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Documento no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al eliminar el documento"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->documentService->deleteDocument($id, auth()->id());

            return response()->json([
                'message' => 'Documento eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/documents/categories",
     *     summary="Obtener categorías disponibles",
     *     description="Retorna la lista de categorías únicas de documentos del usuario",
     *     operationId="getDocumentCategories",
     *     tags={"Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorías",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"contratos", "reportes", "facturas", "general"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener las categorías"),
     *             @OA\Property(property="error", type="string", example="Detalle del error")
     *         )
     *     )
     * )
     */
    public function categories()
    {
        try {
            $categories = $this->documentRepository->getCategories(auth()->id());

            return response()->json([
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
