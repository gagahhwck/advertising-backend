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
    // Event Category (Super Admin Only)
    Route::get('event_categories', [EventCategoryController::class, 'index'])->middleware('permission:ads.view-event-categories');
    Route::get('event_categories/{event_category}', [EventCategoryController::class, 'show'])->middleware('permission:ads.view-event-categories');
    Route::post('event_categories', [EventCategoryController::class, 'store'])->middleware('permission:ads.create-event-categories');
    Route::put('event_categories/{event_category}', [EventCategoryController::class, 'update'])->middleware('permission:ads.edit-event-categories');
    // Event (Super Admin, Admin Only)
    Route::delete('event_categories/{event_category}', [EventCategoryController::class, 'destroy'])->middleware('permission:ads.delete-event-categories');
    Route::post('events', [EventController::class, 'store'])->middleware('permission:ads.create-event');
    Route::put('events/{event}', [EventController::class, 'update'])->middleware('permission:ads.edit-event');
    Route::get('events/{event}', [EventController::class, 'show'])->middleware('permission:ads.view-event');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->middleware('permission:ads.delete-event');
    // Template (Super Admin, Media)
    Route::get('templates', [TemplateController::class, 'index'])->middleware('permission:ads.view-template');
    Route::get('templates/{template}', [TemplateController::class, 'show'])->middleware('permission:ads.view-template');
    Route::post('templates', [TemplateController::class, 'store'])->middleware('permission:ads.create-template');
    Route::put('templates/{template}', [TemplateController::class, 'update'])->middleware('permission:ads.update-template');
    Route::delete('templates/{template}', [TemplateController::class, 'destroy'])->middleware('permission:ads.delete-template');
    // Content (Super Admin, Admin)
    Route::post('contents', [ContentController::class, 'store'])->middleware('permission:ads.create-content');
    Route::get('contents/{content}', [ContentController::class, 'show'])->middleware('permission:ads.view-content');
    Route::put('contents/{content}', [ContentController::class, 'update'])->middleware('permission:ads.update-content');
    Route::delete('contents/{content}', [ContentController::class, 'destroy'])->middleware('permission:ads.delete-content');
    // Content Media (Global)
    Route::apiResource('content_location',ContentLocationController::class)->except('index','show');
    // Media File (Global)
    Route::apiResource('media_files', MediaFileController::class)->except('index','show');
    // Route::apiResource('content_receipts', ContentReceiptController::class)->except('index','show','update');
  });
});
