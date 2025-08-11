<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('app');
});

// ✅ Excluir SOLO la ruta específica de Swagger
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api\/documentation).*$');