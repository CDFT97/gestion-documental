<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Gestión Documental API",
 *     description="API para gestionar documentos y tablas a partir de excels y PDFs",
 *     version="1.0.0",
 *     @OA\Contact(
 *         email="febrescesar7@gmail.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor Local"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Servidor Local (Base)"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum",
 *     description="Ingresa tu token de Sanctum en el formato: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="Usuario",
 *     description="Modelo de usuario básico",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Juan Pérez"),
 *     @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     title="Recurso de Usuario Completo",
 *     description="Información completa del usuario con estadísticas",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Juan Pérez"),
 *     @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(
 *         property="tables_stats",
 *         type="object",
 *         @OA\Property(property="total_tables", type="integer", example=5),
 *         @OA\Property(property="total_records", type="integer", example=1500),
 *         @OA\Property(property="total_size_bytes", type="integer", example=2048576),
 *         @OA\Property(property="total_size_formatted", type="string", example="2.05 MB"),
 *         @OA\Property(property="completed_tables", type="integer", example=4),
 *         @OA\Property(property="processing_tables", type="integer", example=1),
 *         @OA\Property(property="failed_tables", type="integer", example=0)
 *     ),
 *     @OA\Property(
 *         property="documents_stats",
 *         type="object",
 *         @OA\Property(property="total_documents", type="integer", example=10),
 *         @OA\Property(property="total_size_bytes", type="integer", example=5242880),
 *         @OA\Property(property="total_size_formatted", type="string", example="5.24 MB"),
 *         @OA\Property(property="active_documents", type="integer", example=8),
 *         @OA\Property(property="categories_count", type="integer", example=3),
 *         @OA\Property(property="categories", type="array", @OA\Items(type="string"), example={"reports", "contracts", "invoices"}),
 *         @OA\Property(
 *             property="documents_by_category",
 *             type="object",
 *             @OA\Property(property="reports", type="integer", example=4),
 *             @OA\Property(property="contracts", type="integer", example=3),
 *             @OA\Property(property="invoices", type="integer", example=3)
 *         ),
 *         @OA\Property(property="largest_document_size", type="integer", example=1048576),
 *         @OA\Property(property="largest_document_size_formatted", type="string", example="1.05 MB"),
 *         @OA\Property(property="most_recent_upload", type="string", format="date-time", example="2024-01-15T10:30:00Z")
 *     ),
 *     @OA\Property(
 *         property="storage_stats",
 *         type="object",
 *         @OA\Property(property="total_storage_used_bytes", type="integer", example=7291456),
 *         @OA\Property(property="total_storage_used_formatted", type="string", example="7.29 MB"),
 *         @OA\Property(property="tables_storage_percentage", type="number", format="float", example=28.09),
 *         @OA\Property(property="documents_storage_percentage", type="number", format="float", example=71.91)
 *     ),
 *     @OA\Property(
 *         property="activity",
 *         type="object",
 *         @OA\Property(property="last_table_created", type="string", format="date-time", example="2024-01-10T14:20:00Z"),
 *         @OA\Property(property="last_document_uploaded", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *         @OA\Property(property="last_activity", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *         @OA\Property(property="account_age_days", type="integer", example=45)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Error de Validación",
 *     @OA\Property(property="message", type="string", example="Error de validación"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="field_name",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"Este campo es obligatorio."}
 *         )
 *     ),
 *     @OA\Property(property="status", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     title="Respuesta de Autenticación",
 *     @OA\Property(property="message", type="string", example="Login exitoso"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz123456789")
 * )
 *
 * @OA\Schema(
 *     schema="MessageResponse",
 *     type="object",
 *     title="Respuesta con Mensaje",
 *     @OA\Property(property="message", type="string", example="Operación exitosa")
 * )
 *
 * @OA\Schema(
 *     schema="Document",
 *     type="object",
 *     title="Documento",
 *     description="Modelo de documento PDF",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Contrato de servicios 2024"),
 *     @OA\Property(property="original_filename", type="string", example="contrato_servicios.pdf"),
 *     @OA\Property(property="file_size", type="integer", example=2048576, description="Tamaño en bytes"),
 *     @OA\Property(property="file_size_formatted", type="string", example="2.05 MB"),
 *     @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *     @OA\Property(property="category", type="string", example="contratos"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Contrato anual de servicios de consultoría"),
 *     @OA\Property(property="status", type="string", enum={"active", "archived", "deleted"}, example="active"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(
 *         property="metadata",
 *         type="object",
 *         @OA\Property(
 *             property="pdf_info",
 *             type="object",
 *             @OA\Property(property="pages", type="integer", example=15),
 *             @OA\Property(property="title", type="string", nullable=true, example="Título del PDF"),
 *             @OA\Property(property="author", type="string", nullable=true, example="Autor del PDF")
 *         ),
 *         @OA\Property(property="upload_info", type="object"),
 *         @OA\Property(property="processing_info", type="object")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     type="object",
 *     title="Respuesta Paginada",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/endpoint?page=1"),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=10),
 *     @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/endpoint?page=10"),
 *     @OA\Property(property="links", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="next_page_url", type="string", nullable=true, example="http://localhost:8000/api/endpoint?page=2"),
 *     @OA\Property(property="path", type="string", example="http://localhost:8000/api/endpoint"),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
 *     @OA\Property(property="to", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=150)
 * )
 */
class SwaggerBaseController extends Controller
{
    // Esta clase solo sirve para contener las definiciones base de Swagger
    // No necesita métodos
}
