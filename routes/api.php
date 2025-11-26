<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RentalRequestController;
use App\Http\Controllers\ReportController;

// RUTAS DE AUTENTICACIÓN PÚBLICAS
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// RUTAS PÚBLICAS (solo lectura)
Route::get('users', [UserController::class, 'index']);
Route::get('properties', [PropertyController::class, 'index']);
Route::get('properties/{property}', [PropertyController::class, 'show']);
Route::get('contracts', [ContractController::class, 'index']);
Route::get('payments', [PaymentController::class, 'index']);
Route::get('ratings', [RatingController::class, 'index']);
Route::get('maintenances', [MaintenanceController::class, 'index']);
Route::get('reports', [ReportController::class, 'index']);

// RUTAS PROTEGIDAS (requieren login)
// RUTAS PROTEGIDAS
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Users (CRUD)
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });

    // Properties (CRUD)
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::post('/', [PropertyController::class, 'store']);
        Route::get('{id}', [PropertyController::class, 'show']);
        Route::put('{id}', [PropertyController::class, 'update']);
        Route::delete('{id}', [PropertyController::class, 'destroy']);
        Route::post('{id}/point', [PropertyController::class, 'savePoint']); // acción especial del módulo
    });

    // Contracts (CRUD + rutas especiales)
    Route::prefix('contracts')->group(function () {
        Route::get('stats', [ContractController::class, 'stats']);
        Route::get('/', [ContractController::class, 'index']);
        Route::post('/', [ContractController::class, 'store']);
        Route::get('{id}', [ContractController::class, 'show']);
        Route::put('{id}', [ContractController::class, 'update']);
        Route::delete('{id}', [ContractController::class, 'destroy']);
    });

    // Payments (CRUD)
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('{id}', [PaymentController::class, 'show']);
        Route::put('{id}', [PaymentController::class, 'update']);
        Route::delete('{id}', [PaymentController::class, 'destroy']);
    });

    // Ratings (CRUD)
    Route::prefix('ratings')->group(function () {
        Route::get('/', [RatingController::class, 'index']);
        Route::post('/', [RatingController::class, 'store']);
        Route::get('{id}', [RatingController::class, 'show']);
        Route::put('{id}', [RatingController::class, 'update']);
        Route::delete('{id}', [RatingController::class, 'destroy']);
    });

    // Maintenances (CRUD)
    Route::prefix('maintenances')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index']);
        Route::post('/', [MaintenanceController::class, 'store']);
        Route::get('{id}', [MaintenanceController::class, 'show']);
        Route::put('{id}', [MaintenanceController::class, 'update']);
        Route::delete('{id}', [MaintenanceController::class, 'destroy']);
    });

    // Reports (CRUD si aplica)
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('{id}', [ReportController::class, 'show']);
        Route::put('{id}', [ReportController::class, 'update']);
        Route::delete('{id}', [ReportController::class, 'destroy']);
    });

});

