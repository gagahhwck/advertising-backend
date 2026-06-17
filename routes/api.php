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
    Route::apiResource('event_categories', EventCategoryController::class)
      ->middleware('permission:ads.view-event-categories')->only(['index', 'show'])
      ->middleware('permission:ads.create-event-categories')->only(['store'])
      ->middleware('permission:ads.edit-event-categories')->only(['update'])
      ->middleware('permission:ads.delete-event-categories')->only(['destroy']);
    Route::apiResource('events', EventController::class)
      ->middleware('permission:ads.create-event')->only(['store'])
      ->middleware('permission:ads.edit-event')->only(['update'])
      ->middleware('permission:ads.view-event')->only(['show'])
      ->middleware('permission:ads.delete-event')->only(['destroy']);
    Route::apiResource('templates', TemplateController::class)
      ->middleware('permission:ads.view-template')->only(['index','show'])
      ->middleware('permission:ads.create-template')->only(['store'])
      ->middleware('permission:ads.update-template')->only(['update'])
      ->middleware('permission:ads.delete-template')->only(['destroy']);
    Route::apiResource('contents', ContentController::class)
    ->middleware('permission:ads.create-content')->only(['create'])
    ->middleware('permission:ads.view-content')->only(['show'])
    ->middleware('permission:ads.update-content')->only(['update'])
    ->middleware('permission:ads.delete-content')->only(['destroy']);
    Route::apiResource('content_location',ContentLocationController::class)->except('index','show');
    Route::apiResource('media_files', MediaFileController::class)->except('index','show');
    // Route::apiResource('content_receipts', ContentReceiptController::class)->except('index','show','update');
  });
});
