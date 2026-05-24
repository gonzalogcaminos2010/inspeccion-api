<?php

use App\Http\Controllers\Api\V1\AiAnalysisController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EquipmentController;
use App\Http\Controllers\Api\V1\FindingController;
use App\Http\Controllers\Api\V1\InspectionController;
use App\Http\Controllers\Api\V1\InspectionReportController;
use App\Http\Controllers\Api\V1\InspectionRequestController;
use App\Http\Controllers\Api\V1\InspectionTemplateController;
use App\Http\Controllers\Api\V1\PublicInspectionController;
use App\Http\Controllers\Api\V1\ServiceTypeController;
use App\Http\Controllers\Api\V1\TemplateCategoryController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WorkOrderController;
use App\Http\Controllers\Api\V1\WorkOrderItemController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/v1/login', [AuthController::class, 'login']);
Route::get('/v1/public/inspections/{qrToken}', [PublicInspectionController::class, 'show']);

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('role:admin');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('role:admin');
    Route::put('/users/{user}/password', [UserController::class, 'resetPassword'])->middleware('role:admin');

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Clients
    Route::apiResource('clients', ClientController::class);

    // Equipment
    Route::apiResource('equipment', EquipmentController::class);

    // Service Types
    Route::apiResource('service-types', ServiceTypeController::class);

    // Inspection Requests
    Route::apiResource('inspection-requests', InspectionRequestController::class);

    // Template Categories
    Route::get('/template-categories', [TemplateCategoryController::class, 'index']);
    Route::post('/template-categories', [TemplateCategoryController::class, 'store'])->middleware('role:admin');
    Route::patch('/template-categories/{templateCategory}', [TemplateCategoryController::class, 'update'])->middleware('role:admin');
    Route::delete('/template-categories/{templateCategory}', [TemplateCategoryController::class, 'destroy'])->middleware('role:admin');

    // Inspection Templates
    Route::post('/inspection-templates/{template}/duplicate', [InspectionTemplateController::class, 'duplicate']);
    Route::apiResource('inspection-templates', InspectionTemplateController::class);

    // Work Orders
    Route::post('/work-orders/{workOrder}/start', [WorkOrderController::class, 'start']);
    Route::post('/work-orders/{workOrder}/complete', [WorkOrderController::class, 'complete']);
    Route::get('/work-orders/{workOrder}/items', [WorkOrderController::class, 'items']);
    Route::apiResource('work-orders', WorkOrderController::class);

    // Work Order Items ("my items" + per-item reassignment)
    Route::get('/work-order-items', [WorkOrderItemController::class, 'index']);
    Route::patch('/work-order-items/{workOrderItem}', [WorkOrderItemController::class, 'update'])
        ->middleware('role:supervisor,admin');

    // Inspections
    Route::post('/inspections/{inspection}/answers', [InspectionController::class, 'saveAnswers']);
    Route::post('/inspections/{inspection}/submit', [InspectionController::class, 'submit']);
    Route::post('/inspections/{inspection}/reopen', [InspectionController::class, 'reopen']);
    Route::post('/inspections/{inspection}/photos', [InspectionController::class, 'uploadPhotos']);
    Route::post('/inspections/{inspection}/findings', [InspectionController::class, 'createFinding']);
    Route::post('/inspections/{inspection}/sign', [InspectionController::class, 'sign']);
    Route::post('/inspections/{inspection}/approve', [InspectionController::class, 'approve'])
        ->middleware('role:supervisor,admin');
    Route::post('/inspections/{inspection}/return', [InspectionController::class, 'returnInspection'])
        ->middleware('role:supervisor,admin');
    Route::get('/inspections/{inspection}/report', [InspectionReportController::class, 'show']);
    Route::get('/inspections/{inspection}/report/preview', [InspectionReportController::class, 'preview']);
    Route::get('/inspections/{inspection}/certificate', [InspectionReportController::class, 'certificate']);
    Route::apiResource('inspections', InspectionController::class)->only(['index', 'show', 'store']);

    // Findings
    Route::apiResource('findings', FindingController::class);

    // AI Photo Analysis
    Route::post('/ai/analyze-photo', [AiAnalysisController::class, 'analyzePhoto']);
    Route::patch('/ai/analyses/{aiAnalysis}/mark-used', [AiAnalysisController::class, 'markUsed']);
});
