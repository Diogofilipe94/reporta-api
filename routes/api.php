<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportDetailController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

// Rotas de acesso públicas
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


// Rotas protegidas por JWT
Route::middleware(JwtMiddleware::class)->group(function () {
    // Reports - CRUD
    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports', [ReportController::class, 'store']);
    Route::get('reports/{report}', [ReportController::class, 'show']);
    Route::put('reports/{report}', [ReportController::class, 'update']);
    Route::delete('reports/{report}', [ReportController::class, 'destroy']);

    Route::get('categories', [CategoryController::class, 'index']);

    Route::post('reports/{report}/details', [ReportDetailController::class, 'store']);
    Route::get('reports/{report}/details', [ReportDetailController::class, 'show']);
    Route::put('reports/{report}/details', [ReportDetailController::class, 'update']);

    Route::put('reports/{report}/status', [ReportController::class, 'updateStatus']);
    Route::get('user/reports', [ReportController::class, 'getUserOwnReports']);
});
