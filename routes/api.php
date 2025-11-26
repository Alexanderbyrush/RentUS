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
use App\Models\User;
use App\Models\Property;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

/*
|--------------------------------------------------------------------------
| Public Endpoints
|--------------------------------------------------------------------------
*/
Route::get('users', [UserController::class, 'index']);
Route::get('properties', [PropertyController::class, 'index']);
// Primero ruta específica
Route::get('properties/count', [PropertyController::class, 'count']);

Route::get('properties', [PropertyController::class, 'index']);
Route::get('properties/{property}', [PropertyController::class, 'show']);

Route::get('contracts', [ContractController::class, 'index']);
Route::get('payments', [PaymentController::class, 'index']);
Route::get('ratings', [RatingController::class, 'index']);
Route::get('maintenances', [MaintenanceController::class, 'index']);
Route::get('reports', [ReportController::class, 'index']);
Route::get('rental-requests', [RentalRequestController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Dashboard Metrics (Public or Private depending on your policy)
| Actualmente los dejamos públicos porque tu frontend los llama sin token.
|--------------------------------------------------------------------------
*/

// Total de propiedades
Route::get('properties/count', function () {
    return response()->json([
        'count' => Property::count()
    ]);
});

// Total de clientes activos (ajusta al criterio real)
Route::get('users/active/count', function () {
    return response()->json([
        'count' => User::where('status', 'active')->count()
    ]);
});

/*
|--------------------------------------------------------------------------
| Private Endpoints (require token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    // Properties
    Route::post('properties', [PropertyController::class, 'store']);
    Route::put('properties/{property}', [PropertyController::class, 'update']);
    Route::delete('properties/{property}', [PropertyController::class, 'destroy']);
    Route::post('properties/{id}/point', [PropertyController::class, 'savePoint']);

    // Other protected features could be added here...
});
