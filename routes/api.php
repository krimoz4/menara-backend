<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/dashboard/uptime-history', [DashboardController::class, 'getUptimeHistory']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard/kpi-indicators', [DashboardController::class, 'getKpiIndicators']);
    Route::get('/dashboard/security', [DashboardController::class, 'getSecurityDomains']);
    Route::get('/dashboard/glpi-projects', [DashboardController::class, 'getGlpiProjects']);
    Route::post('/dashboard/glpi-projects', [DashboardController::class, 'storeGlpiProject']);
    Route::put('/dashboard/glpi-projects/{id}', [DashboardController::class, 'updateGlpiProject']);
    Route::delete('/dashboard/glpi-projects/{id}', [DashboardController::class, 'destroyGlpiProject']);
    Route::put('/dashboard/security/{id}', [DashboardController::class, 'updateSecurityDomain']);
    Route::post('/dashboard/users', [DashboardController::class, 'storeUser']);
    Route::get('/dashboard/users', [DashboardController::class, 'getUsers']);
    Route::put('/dashboard/users/{id}/role', [DashboardController::class, 'updateUserRole']);
    Route::delete('/dashboard/users/{id}', [DashboardController::class, 'destroyUser']);
    Route::put('/dashboard/users/{id}', [DashboardController::class, 'updateUser']);
    Route::get('/dashboard/audit-logs', [DashboardController::class, 'getAuditLogs']);
    Route::post('/dashboard/health-check', [DashboardController::class, 'runHealthCheck']);
    Route::post('/dashboard/trigger-alert', [DashboardController::class, 'triggerAlertNotification']);

    Route::apiResource('departments', \App\Http\Controllers\Api\DepartmentController::class);
    Route::apiResource('kpi-categories', \App\Http\Controllers\Api\KpiCategoryController::class);
    Route::apiResource('kpis', \App\Http\Controllers\Api\KpiController::class);
    Route::apiResource('kpi-records', \App\Http\Controllers\Api\KpiRecordController::class);
});
