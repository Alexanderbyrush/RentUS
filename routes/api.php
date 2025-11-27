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
use App\Models\User;
use App\Models\Property;

// RUTAS DE AUTENTICACIÓN PÚBLICAS
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// RUTAS PÚBLICAS (solo lectura)
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

    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::post('/', [PropertyController::class, 'store']);
        Route::get('{property}', [PropertyController::class, 'show']);
        Route::put('{property}', [PropertyController::class, 'update']);
        Route::delete('{property}', [PropertyController::class, 'destroy']);
        Route::post('{property}/point', [PropertyController::class, 'savePoint']);
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

    Route::post('/rental-requests', [RentalRequestController::class, 'create']); // Crear solicitud
    Route::get('/rental-requests/my-requests', [RentalRequestController::class, 'getMyRequests']); // Mis solicitudes
    Route::put('/rental-requests/{id}/accept-counter', [RentalRequestController::class, 'acceptCounterProposal']); // Aceptar contra-propuesta
    Route::put('/rental-requests/{id}/reject-counter', [RentalRequestController::class, 'rejectCounterProposal']); // Rechazar contra-propuesta

    // DUEÑO
    Route::get('/rental-requests/owner', [RentalRequestController::class, 'getOwnerRequests']); // Solicitudes recibidas
    Route::put('/rental-requests/{id}/accept', [RentalRequestController::class, 'acceptRequest']); // Aceptar solicitud
    Route::put('/rental-requests/{id}/reject', [RentalRequestController::class, 'rejectRequest']); // Rechazar solicitud
    Route::put('/rental-requests/{id}/counter-propose', [RentalRequestController::class, 'counterPropose']); // Proponer otra fecha
    Route::get('/rental-requests/{id}/visit-status', [RentalRequestController::class, 'checkVisitStatus']); // Verificar si puede continuar
    Route::post('/rental-requests/send-contract', [RentalRequestController::class, 'sendContractTerms']); // Enviar términos del contrato

    // GENERAL
    Route::get('/rental-requests/{id}', [RentalRequestController::class, 'getDetails']); // Detalles
    Route::delete('/rental-requests/{id}', [RentalRequestController::class, 'cancel']); // Cancelar

    Route::get('/notifications', [NotificationController::class, 'index']); // Todas
    Route::get('/notifications/unread', [NotificationController::class, 'unread']); // No leídas
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']); // Contador
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']); // Marcar como leída
    Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']); // Marcar todas
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete']); // Eliminar

    Route::get('/contracts', [ContractController::class, 'index']); // Todos mis contratos
    Route::get('/contracts/stats', [ContractController::class, 'stats']); // Estadísticas
    Route::put('/contracts/{id}/accept', [ContractController::class, 'acceptContract']); // Inquilino acepta contrato
    Route::put('/contracts/{id}/reject', [ContractController::class, 'rejectContract']);
});
