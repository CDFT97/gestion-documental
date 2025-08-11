# Document Management Full Stack

Es aplicación Full Stack  de gestión documental que permita autenticación, manejo de archivos Excel, CRUD de datos y visualización de documentos PDF.Se ha puesto especial énfasis en la implementación de las mejores prácticas y principios de desarrollo en Laravel, abarcando conceptos clave como:

-   **Form Requests**: Para validación y autorización de peticiones.
-   **Middleware**: Para la lógica de pre-procesamiento de peticiones.
-   **Services**: Para encapsular la lógica de negocio.
-   **Repositories**: Para la abstracción de la capa de datos.
-   **Repository Pattern**: Para desacoplar la lógica de acceso a datos.
-   **Traits**: Para reutilización de código.
-   **Swagger**: Para la documentación interactiva de la API.
-   **Vue**: Para la interfaz de usuario.
-   **Store**: Para la gestión de estado.
-   **Composables**: Para la reutilización de código.
-   **Utils**: Para la reutilización de funciones.
---

## ⚙️ Requisitos

Antes de comenzar, asegúrate de tener instalados los siguientes componentes:

-   **PHP**: Versión 8.1 o superior.
-   **Laravel**: Versión 10 o superior .
-   **Composer**: Gestor de dependencias de PHP.
-   **MySQL**: Base de datos relacional.
-   **Node 22**: Versión de Node.js.

---

## 🚀 Instalación

Sigue los pasos a continuación para configurar y ejecutar el proyecto en tu entorno local:

1.  **Clonar el repositorio**:
    ```bash
    git clone <URL_DEL_REPOSITORIO>
    cd gestion-documental # O el nombre de tu carpeta
    ```
2.  **Crear el archivo `.env`**:
    Copia el archivo de ejemplo y genera tu propio archivo de configuración:
    ```bash
    cp .env.example .env
    ```
3.  **Instalar dependencias de Composer**:
    ```bash
    composer install
    ```
3.  **Generar la clave de la aplicación**:
    ```bash
    php artisan key:generate
    ```
5.  **Ejecutar migraciones**:
    Esto creará las tablas necesarias en tu base de datos:
    ```bash
    php artisan migrate
    ```
6.  **Instalar dependencias de Node.js**:
    Esto poblará la base de datos con datos de prueba:
    ```bash
    npm install
    ```
8.  **Iniciar el servidor de desarrollo**:
    ```bash
    php artisan serve
    ```
    El servidor estará disponible usualmente en `http://127.0.0.1:8000`.
9  **Iniciar el servidor de vite**:
    ```bash
    npm run dev
    ```
---

## 📄 Documentación (Swagger)

El proyecto incluye documentación de la API generada con Swagger para facilitar la exploración y el uso de los endpoints.

1.  Asegúrate de tener el plugin [L5-Swagger](https://github.com/DarkaOnline/L5-Swagger) configurado.
2.  Genera la documentación ejecutando:
    ```bash
    php artisan l5-swagger:generate
    ```
    La documentación generada se encontrará en el directorio `resources/docs/swagger.json`.
3.  Para acceder a la documentación interactiva en tu navegador, visita:
    ```
    /api/documentation
    ```
    (Por ejemplo, `http://127.0.0.1:8000/api/documentation`)

---

## 📝 Licencia
Este proyecto está licenciado bajo la licencia MIT.
