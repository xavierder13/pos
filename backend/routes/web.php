<?php

use Illuminate\Support\Facades\Route;

// API-only backend: the SPA lives in ../frontend. GET /up is the health check.
Route::get('/', fn () => response()->json(['name' => config('app.name'), 'api' => url('/api')]));
