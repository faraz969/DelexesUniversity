<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User Management API Routes
Route::prefix('users')->group(function () {
    Route::post('/', [App\Http\Controllers\ApiController::class, 'createUser']);
    Route::get('/', [App\Http\Controllers\ApiController::class, 'listUsers']);
    Route::get('/{id}', [App\Http\Controllers\ApiController::class, 'getUser']);
});

// Form Types API Routes
Route::prefix('form-types')->group(function () {
    Route::get('/', [App\Http\Controllers\ApiController::class, 'getFormTypes']);
    Route::get('/{id}', [App\Http\Controllers\ApiController::class, 'getFormType']);
});
