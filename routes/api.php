<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api_key')->group(function () {
  Route::middleware('auth')->group(function () {
    // API routes that require authentication go here
  });
});
