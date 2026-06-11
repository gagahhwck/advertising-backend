<?php

use App\Http\Controllers\DocsApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('index');
});

Route::get('/docs/api/login', [DocsApiController::class, 'index']);
Route::get('/docs/api/logout', [DocsApiController::class, 'logout']);
Route::post('/docs/api/login', [DocsApiController::class, 'login']);
