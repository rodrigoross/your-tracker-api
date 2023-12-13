<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/auth', [\App\Http\Controllers\Api\AuthenticateController::class, 'login'])->name('login');
Route::post('/register', [\App\Http\Controllers\Api\AuthenticateController::class, 'register'])->name('register');

Route::prefix('v1')
    ->name('v1.')
    ->group(function () {
       Route::get('/track', \App\Http\Controllers\TrackController::class)->name('track');
    });

Route::prefix('v1')
    ->name('v1.')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('/me', function () {
            return auth()->user();
        });

        Route::apiResource('packages', \App\Http\Controllers\Api\PackageController::class);
    });

