<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/registration', [UserController::class, 'singIn']);
Route::post('/authorization', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/logout', [UserController::class, 'logout']);

    Route::post('/files', [FileController::class, 'store']);

    Route::patch('/files/{file_id}', [FileController::class, 'edit']);
    Route::delete('/files/{file_id}', [FileController::class, 'destroy']);
    Route::delete('/files/{file_id}', [FileController::class, 'download']);
});
