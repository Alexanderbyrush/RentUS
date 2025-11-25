<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RentalRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('contracts', [ContractController::class, 'index']);
        Route::get('/contracts/stats', [ContractController::class, 'stats']);
    
    });
});


// Rutas de la API
Route::get('users', [UserController::class, 'index'])->name('api.user.index');
Route::get('properties', [PropertyController::class, 'index'])->name('api.property.index');
Route::get('payments', [PaymentController::class, 'index'])->name('api.payment.index');
Route::get('ratings', [RatingController::class, 'index'])->name('api.rating.index');
Route::get('maintenances', [MaintenanceController::class, 'index'])->name('api.maintenance.index');
Route::get('reports', [ReportController::class, 'index'])->name('api.report.index');
Route::get('rental-requests', [RentalRequestController::class, 'index'])->name('api.rentalRequest.index');
