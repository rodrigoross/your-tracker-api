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
Route::post('/auth', [\App\Http\Controllers\Api\AuthenticateController::class, 'login'])->name('api.login');
Route::post('/register', [\App\Http\Controllers\Api\AuthenticateController::class, 'register'])->name('api.register');

Route::prefix('v1')
    ->name('v1.')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('/me', function () {
            return auth()->user();
        });
    });

