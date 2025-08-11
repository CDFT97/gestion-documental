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
 * @OA\Schema(
 *     schema="DynamicTable",
 *     type="object",
 *     title="Tabla Dinámica",
 *     description="Modelo de tabla dinámica creada a partir de archivos Excel",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Datos de Ventas Q1 2024"),
 *     @OA\Property(property="original_filename", type="string", example="ventas_q1_2024.xlsx"),
 *     @OA\Property(property="file_path", type="string", example="excel_uploads/abc123.xlsx"),
 *     @OA\Property(property="file_size", type="integer", example=1024576, description="Tamaño en bytes"),
 *     @OA\Property(property="file_size_formatted", type="string", example="1.02 MB"),
 *     @OA\Property(
 *         property="structure",
 *         type="object",
 *         description="Estructura de columnas de la tabla",
 *         @OA\Property(
 *             property="columns",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="name", type="string", example="nombre"),
 *                 @OA\Property(property="type", type="string", example="string"),
 *                 @OA\Property(property="nullable", type="boolean", example=true),
 *                 @OA\Property(property="original_header", type="string", example="Nombre")
 *             )
 *         ),
 *         @OA\Property(property="total_columns", type="integer", example=5),
 *         @OA\Property(property="sheet_name", type="string", example="Hoja1")
 *     ),
 *     @OA\Property(
 *         property="stats",
 *         type="object",
 *         description="Estadísticas de la tabla",
 *         @OA\Property(property="total_records", type="integer", example=150),
 *         @OA\Property(property="processed_records", type="integer", example=148),
 *         @OA\Property(property="failed_records", type="integer", example=2),
 *         @OA\Property(property="processing_time_seconds", type="number", format="float", example=2.45)
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"processing", "completed", "failed"},
 *         example="completed",
 *         description="Estado del procesamiento"
 *     ),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="processed_at", type="string", format="date-time", nullable=true, example="2024-01-15T10:32:45Z")
 * )
 * @OA\Schema(
 *     schema="ExcelPreview",
 *     type="object",
 *     title="Vista Previa de Excel",
 *     description="Vista previa de los datos del archivo Excel",
 *     @OA\Property(
 *         property="headers",
 *         type="array",
 *         description="Encabezados de las columnas",
 *         @OA\Items(type="string"),
 *         example={"ID", "Nombre", "Email", "Fecha de Registro", "Estado"}
 *     ),
 *     @OA\Property(
 *         property="sample_data",
 *         type="array",
 *         description="Muestra de las primeras filas de datos",
 *         @OA\Items(
 *             type="array",
 *             @OA\Items(type="string")
 *         ),
 *         example={
 *             {"1", "Juan Pérez", "juan@example.com", "2024-01-01", "Activo"},
 *             {"2", "María García", "maria@example.com", "2024-01-02", "Inactivo"}
 *         }
 *     ),
 *     @OA\Property(property="total_rows", type="integer", example=150, description="Total de filas en el archivo"),
 *     @OA\Property(property="total_columns", type="integer", example=5, description="Total de columnas en el archivo"),
 *     @OA\Property(
 *         property="sheet_names",
 *         type="array",
 *         description="Nombres de las hojas en el archivo Excel",
 *         @OA\Items(type="string"),
 *         example={"Datos", "Resumen", "Configuración"}
 *     ),
 *     @OA\Property(property="file_info", type="object",
 *         @OA\Property(property="size_bytes", type="integer", example=1024576),
 *         @OA\Property(property="size_formatted", type="string", example="1.02 MB"),
 *         @OA\Property(property="extension", type="string", example="xlsx"),
 *         @OA\Property(property="detected_encoding", type="string", example="UTF-8")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TableColumn",
 *     type="object",
 *     title="Columna de Tabla",
 *     description="Definición de una columna en una tabla dinámica",
 *     @OA\Property(property="name", type="string", example="nombre", description="Nombre de la columna en la base de datos"),
 *     @OA\Property(property="type", type="string", enum={"string", "integer", "float", "boolean", "date", "datetime", "text"}, example="string"),
 *     @OA\Property(property="nullable", type="boolean", example=true, description="Si la columna puede ser nula"),
 *     @OA\Property(property="original_header", type="string", example="Nombre Completo", description="Encabezado original del Excel"),
 *     @OA\Property(property="max_length", type="integer", nullable=true, example=255, description="Longitud máxima para campos de texto"),
 *     @OA\Property(property="default_value", type="string", nullable=true, example=null, description="Valor por defecto")
 * )
 *
 * @OA\Schema(
 *     schema="TableStats",
 *     type="object",
 *     title="Estadísticas de Tabla",
 *     description="Estadísticas de procesamiento de una tabla",
 *     @OA\Property(property="total_records", type="integer", example=150, description="Total de registros en el archivo"),
 *     @OA\Property(property="processed_records", type="integer", example=148, description="Registros procesados exitosamente"),
 *     @OA\Property(property="failed_records", type="integer", example=2, description="Registros que fallaron al procesar"),
 *     @OA\Property(property="processing_time_seconds", type="number", format="float", example=2.45, description="Tiempo de procesamiento en segundos"),
 *     @OA\Property(property="success_rate", type="number", format="float", example=98.67, description="Porcentaje de éxito"),
 *     @OA\Property(
 *         property="errors",
 *         type="array",
 *         description="Errores encontrados durante el procesamiento",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="row", type="integer", example=45),
 *             @OA\Property(property="column", type="string", example="email"),
 *             @OA\Property(property="error", type="string", example="Formato de email inválido"),
 *             @OA\Property(property="value", type="string", example="email_invalido")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Respuesta de Error",
 *     description="Respuesta estándar para errores del servidor",
 *     @OA\Property(property="message", type="string", example="Error al procesar la solicitud"),
 *     @OA\Property(property="error", type="string", example="Detalle específico del error"),
 *     @OA\Property(property="status", type="boolean", example=false)
 * )
 * @OA\Schema(
 *     schema="TableRecord",
 *     type="object",
 *     title="Registro de Tabla",
 *     description="Registro individual de una tabla dinámica",
 *     @OA\Property(property="id", type="integer", example=25),
 *     @OA\Property(property="dynamic_table_id", type="integer", example=1, description="ID de la tabla a la que pertenece"),
 *     @OA\Property(property="row_number", type="integer", example=15, description="Número de fila original en el Excel"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         description="Datos del registro (estructura dinámica según las columnas de la tabla)",
 *         example={
 *             "nombre": "Juan Pérez",
 *             "email": "juan@example.com",
 *             "edad": 30,
 *             "activo": true,
 *             "fecha_registro": "2024-01-15",
 *             "salario": 45000.50
 *         }
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="TableRecordsResponse",
 *     type="object",
 *     title="Table Records Response",
 *     description="Respuesta que incluye la tabla y sus registros paginados",
 *     @OA\Property(property="table", ref="#/components/schemas/DynamicTable"),
 *     @OA\Property(
 *         property="records",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="data",
 *                     type="array",
 *                     @OA\Items(ref="#/components/schemas/TableRecord")
 *                 )
 *             )
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="BulkDeleteRequest",
 *     type="object",
 *     title="Solicitud de Eliminación Masiva",
 *     description="Datos para eliminar múltiples registros",
 *     required={"record_ids"},
 *     @OA\Property(
 *         property="record_ids",
 *         type="array",
 *         description="Array de IDs de registros a eliminar",
 *         @OA\Items(type="integer"),
 *         example={25, 26, 27, 28, 29}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="BulkDeleteResponse",
 *     type="object",
 *     title="Respuesta de Eliminación Masiva",
 *     description="Resultado de la operación de eliminación masiva",
 *     @OA\Property(property="message", type="string", example="Se eliminaron 5 registros exitosamente"),
 *     @OA\Property(property="deleted_count", type="integer", example=5, description="Cantidad de registros eliminados")
 * )
 *
 * @OA\Schema(
 *     schema="ExportRequest",
 *     type="object",
 *     title="Solicitud de Exportación",
 *     description="Parámetros para exportar una tabla",
 *     @OA\Property(
 *         property="format",
 *         type="string",
 *         enum={"xlsx", "csv"},
 *         example="xlsx",
 *         description="Formato del archivo de exportación"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="RecordValidationRules",
 *     type="object",
 *     title="Reglas de Validación de Registro",
 *     description="Ejemplo de cómo se construyen las reglas de validación dinámicamente",
 *     @OA\Property(
 *         property="validation_example",
 *         type="object",
 *         description="Ejemplo de validación para diferentes tipos de columnas",
 *         @OA\Property(property="nombre", type="string", example="required|string|max:255"),
 *         @OA\Property(property="email", type="string", example="required|string|max:255"),
 *         @OA\Property(property="edad", type="string", example="nullable|integer"),
 *         @OA\Property(property="activo", type="string", example="nullable|boolean"),
 *         @OA\Property(property="fecha_registro", type="string", example="required|date"),
 *         @OA\Property(property="salario", type="string", example="nullable|numeric")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TableRecordFilters",
 *     type="object",
 *     title="Filtros de Registros",
 *     description="Filtros disponibles para buscar y filtrar registros",
 *     @OA\Property(
 *         property="search",
 *         type="string",
 *         nullable=true,
 *         example="Juan",
 *         description="Búsqueda general en todos los campos"
 *     ),
 *     @OA\Property(
 *         property="column_filters",
 *         type="object",
 *         description="Filtros específicos por columna",
 *         additionalProperties={"type": "string"},
 *         example={
 *             "nombre": "Juan",
 *             "estado": "activo",
 *             "ciudad": "Madrid"
 *         }
 *     ),
 *     @OA\Property(
 *         property="sort_by",
 *         type="string",
 *         example="nombre",
 *         description="Campo por el cual ordenar"
 *     ),
 *     @OA\Property(
 *         property="sort_direction",
 *         type="string",
 *         enum={"asc", "desc"},
 *         example="asc",
 *         description="Dirección del ordenamiento"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="DynamicRecordData",
 *     type="object",
 *     title="Datos Dinámicos de Registro",
 *     description="Ejemplo de estructura de datos para diferentes tipos de columnas",
 *     @OA\Property(
 *         property="string_column",
 *         type="string",
 *         example="Valor de texto",
 *         description="Columna de tipo string"
 *     ),
 *     @OA\Property(
 *         property="integer_column",
 *         type="integer",
 *         example=42,
 *         description="Columna de tipo integer"
 *     ),
 *     @OA\Property(
 *         property="decimal_column",
 *         type="number",
 *         format="float",
 *         example=123.45,
 *         description="Columna de tipo decimal"
 *     ),
 *     @OA\Property(
 *         property="boolean_column",
 *         type="boolean",
 *         example=true,
 *         description="Columna de tipo boolean"
 *     ),
 *     @OA\Property(
 *         property="date_column",
 *         type="string",
 *         format="date",
 *         example="2024-01-15",
 *         description="Columna de tipo date"
 *     ),
 *     @OA\Property(
 *         property="nullable_column",
 *         type="string",
 *         nullable=true,
 *         example=null,
 *         description="Columna que permite valores nulos"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="RecordOperationResponse",
 *     type="object",
 *     title="Respuesta de Operación de Registro",
 *     description="Respuesta estándar para operaciones CRUD de registros",
 *     @OA\Property(property="message", type="string", example="Operación completada exitosamente"),
 *     @OA\Property(property="record", ref="#/components/schemas/TableRecord")
 * )
 *
 * @OA\Schema(
 *     schema="TableStatsSummary",
 *     type="object",
 *     title="Resumen de Estadísticas de Tabla",
 *     description="Estadísticas resumidas de una tabla con sus registros",
 *     @OA\Property(property="total_records", type="integer", example=1250),
 *     @OA\Property(property="records_this_month", type="integer", example=85),
 *     @OA\Property(property="last_record_created", type="string", format="date-time", example="2024-01-15T14:30:00Z"),
 *     @OA\Property(property="avg_records_per_day", type="number", format="float", example=12.5),
 *     @OA\Property(
 *         property="column_stats",
 *         type="object",
 *         description="Estadísticas por columna",
 *         @OA\Property(
 *             property="nombre",
 *             type="object",
 *             @OA\Property(property="unique_values", type="integer", example=1180),
 *             @OA\Property(property="null_count", type="integer", example=5)
 *         ),
 *         @OA\Property(
 *             property="edad",
 *             type="object",
 *             @OA\Property(property="min", type="integer", example=18),
 *             @OA\Property(property="max", type="integer", example=65),
 *             @OA\Property(property="avg", type="number", format="float", example=32.5)
 *         )
 *     )
 * )
 */
class SwaggerBaseController extends Controller
{
    // Esta clase solo sirve para contener las definiciones base de Swagger
    // No necesita métodos
}
