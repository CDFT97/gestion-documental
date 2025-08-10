<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{DocumentUploadRequest, DocumentIndexRequest, DocumentUpdateRequest};
use App\Repositories\DocumentRepository;
use App\Services\DocumentService;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;

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
     * Subir un nuevo documento PDF
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
     * Obtener lista paginada de documentos del usuario
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
     * Obtener un documento específico
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
     * Obtener vista previa del documento PDF
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
     * Actualizar información del documento
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
     * Eliminar documento
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
     * Obtener categorías disponibles del usuario
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
