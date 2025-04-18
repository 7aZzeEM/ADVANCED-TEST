<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

Route::get('/server/api/verify-token', [AuthController::class, 'verifyToken']);
Route::get('/server/api/get-payload', [AuthController::class, 'getPayload']);
Route::post('/server/api/login', [AuthController::class, 'generate']);
