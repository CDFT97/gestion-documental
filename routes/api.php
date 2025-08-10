<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ExcelController;
use App\Http\Controllers\Api\TableRecordController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // User Profile Routes
    Route::prefix('user')->group(function () {
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/password', [UserController::class, 'updatePassword']);
    });

    // Excel Upload 
    Route::prefix('excel')->group(function () {
        Route::post('/upload', [ExcelController::class, 'upload']);
        Route::post('/process', [ExcelController::class, 'process']);
    });

    // Tables management 
    Route::prefix('tables')->group(function () {
        Route::get('/', [ExcelController::class, 'index']);
        Route::get('/{id}', [ExcelController::class, 'show']);
        Route::delete('/{id}', [ExcelController::class, 'destroy']);

        // CRUD routes for table records
        Route::get('/{id}/records', [TableRecordController::class, 'index']);
        Route::post('/{id}/records', [TableRecordController::class, 'store']);
        Route::get('/{id}/records/{recordId}', [TableRecordController::class, 'show']);
        Route::put('/{id}/records/{recordId}', [TableRecordController::class, 'update']);
        Route::delete('/{id}/records/{recordId}', [TableRecordController::class, 'destroy']);

        // Bulk operations
        Route::post('/{id}/records/bulk-delete', [TableRecordController::class, 'bulkDelete']);
        Route::post('/{id}/export', [TableRecordController::class, 'export']);
    });

    // Rutas de documentos
    Route::prefix('documents')->group(function () {
        Route::post('upload', [DocumentController::class, 'upload']);
        Route::get('categories', [DocumentController::class, 'categories']);
        Route::get('stats', [DocumentController::class, 'stats']);
        Route::get('{id}/preview', [DocumentController::class, 'preview']);
        Route::apiResource('', DocumentController::class)->parameters(['' => 'id']);
    });
});
