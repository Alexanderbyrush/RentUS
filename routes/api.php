<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RentalRequestController;
use App\Http\Controllers\ReportController;

// ============================================
// RUTAS DE AUTENTICACIÓN PÚBLICAS
// ============================================
Route::prefix('auth')->group(function () {
    // Registro y verificación
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('resend-code', [AuthController::class, 'resendVerificationCode']);

    // Login
    Route::post('login', [AuthController::class, 'login']);

    // Recuperación de contraseña
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// ============================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================
Route::get('properties', [PropertyController::class, 'index']);
Route::get('properties/count', [PropertyController::class, 'count']);
Route::get('properties/{property}', [PropertyController::class, 'show']);
Route::post('/properties/{property}/view', [PropertyController::class, 'incrementViews']);
Route::get('users', [UserController::class, 'index']);
Route::get('users/{id}', [UserController::class, 'show']);


// ============================================
// RUTAS PROTEGIDAS (requieren autenticación)
// ============================================
Route::middleware('auth:api')->group(function () {

    // ============================================
    // AUTH - Gestión de sesión y perfil
    // ============================================
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::put('password', [AuthController::class, 'updatePassword']);
    });

    // ============================================
    // USERS - CRUD de usuarios
    // ============================================
    Route::prefix('users')->group(function () {
        Route::post('/', [UserController::class, 'store']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });

    // ============================================
    // PROPERTIES - Gestión de propiedades
    // ============================================
    Route::prefix('properties')->group(function () {
        Route::post('/', [PropertyController::class, 'store']);
        Route::put('{property}', [PropertyController::class, 'update']);
        Route::delete('{property}', [PropertyController::class, 'destroy']);
        Route::post('{property}/point', [PropertyController::class,'update']);
    });

    // ============================================
    // CONTRACTS - Contratos (CORREGIDO)
    // ============================================
    Route::prefix('contracts')->group(function () {
        Route::get('/', [ContractController::class, 'index']);
        Route::get('stats', [ContractController::class, 'stats']);
        Route::put('{id}/accept', [ContractController::class, 'accept']); // ✅ CORREGIDO
        Route::put('{id}/reject', [ContractController::class, 'reject']); // ✅ CORREGIDO
    });

    // ============================================
    // PAYMENTS - Gestión de pagos
    // ============================================
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('{id}', [PaymentController::class, 'show']);
        Route::put('{id}', [PaymentController::class, 'update']);
        Route::delete('{id}', [PaymentController::class, 'destroy']);
    });

    // ============================================
    // RATINGS - Calificaciones
    // ============================================
    Route::prefix('ratings')->group(function () {
        Route::get('/', [RatingController::class, 'index']);
        Route::post('/', [RatingController::class, 'store']);
        Route::get('{id}', [RatingController::class, 'show']);
        Route::put('{id}', [RatingController::class, 'update']);
        Route::delete('{id}', [RatingController::class, 'destroy']);
    });

    // ============================================
    // MAINTENANCES - Mantenimientos
    // ============================================
    Route::prefix('maintenances')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index']);
        Route::post('/', [MaintenanceController::class, 'store']);
        Route::get('{id}', [MaintenanceController::class, 'show']);
        Route::put('{id}', [MaintenanceController::class, 'update']);
        Route::delete('{id}', [MaintenanceController::class, 'destroy']);
    });

    // ============================================
    // REPORTS - Reportes
    // ============================================
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('{id}', [ReportController::class, 'show']);
        Route::put('{id}', [ReportController::class, 'update']);
        Route::delete('{id}', [ReportController::class, 'destroy']);
    });

    // ============================================
    // RENTAL REQUESTS - Solicitudes de alquiler
    // ============================================

    // Inquilino
    Route::post('/rental-requests', [RentalRequestController::class, 'create']);
    Route::get('/rental-requests/my-requests', [RentalRequestController::class, 'getMyRequests']);
    Route::put('/rental-requests/{id}/accept-counter', [RentalRequestController::class, 'acceptCounterProposal']);
    Route::put('/rental-requests/{id}/reject-counter', [RentalRequestController::class, 'rejectCounterProposal']);

    // Dueño
    Route::get('/rental-requests/owner', [RentalRequestController::class, 'getOwnerRequests']);
    Route::put('/rental-requests/{id}/accept', [RentalRequestController::class, 'acceptRequest']);
    Route::put('/rental-requests/{id}/reject', [RentalRequestController::class, 'rejectRequest']);
    Route::put('/rental-requests/{id}/counter-propose', [RentalRequestController::class, 'counterPropose']);
    Route::get('/rental-requests/{id}/visit-status', [RentalRequestController::class, 'checkVisitStatus']);
    Route::post('/rental-requests/send-contract', [RentalRequestController::class, 'sendContractTerms']);

    // General
    Route::get('/rental-requests/{id}', [RentalRequestController::class, 'getDetails']);
    Route::delete('/rental-requests/{id}', [RentalRequestController::class, 'cancel']);

    // ============================================
    // NOTIFICATIONS - Notificaciones
    // ============================================
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete']);
});
