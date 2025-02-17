<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportDetailController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Rotas de acesso públicas
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('addresses', [AddressController::class, 'store']);
Route::get('addresses/{id}', [AddressController::class, 'show']);
Route::post('addresses/check', [AddressController::class, 'check']);

// Rotas protegidas por JWT
Route::middleware(JwtMiddleware::class)->group(function () {
    // Reports - CRUD
    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports', [ReportController::class, 'store']);
    Route::get('reports/{id}', [ReportController::class, 'show']);
    Route::put('reports/{id}', [ReportController::class, 'update']);
    Route::delete('reports/{id}', [ReportController::class, 'destroy']);

    Route::get('categories', [CategoryController::class, 'index']);

    Route::post('reports/{id}/details', [ReportDetailController::class, 'store']);
    Route::get('reports/{id}/details', [ReportDetailController::class, 'show']);
    Route::patch('reports/{id}/details', [ReportDetailController::class, 'update']);

    Route::patch('reports/{id}/status', [ReportController::class, 'updateStatus']);

    Route::get('user/reports', [ReportController::class, 'getUserOwnReports']);
    Route::get('user', [AuthController::class, 'user']);

    Route::get('userdata/{id}', [UserController::class, 'index']);
});
