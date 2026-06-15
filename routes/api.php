<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\ContentLocationController;
use App\Http\Controllers\EventCategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware('api_key')->group(function () {
  Route::middleware('auth')->group(function () {
    Route::apiResource('event_categories', EventCategoryController::class);
    Route::apiResource('events', EventController::class);
    Route::apiResource('templates', TemplateController::class);
    Route::apiResource('contents', ContentController::class);
    Route::apiResource('content_location',ContentLocationController::class)->except('index','show');
    Route::apiResource('media_files', MediaFileController::class)->except('index','show');
    // Route::apiResource('content_receipts', ContentReceiptController::class)->except('index','show','update');
  });
});
