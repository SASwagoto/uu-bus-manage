<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TripController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::prefix('driver')->group(function () {
        Route::post('/trip/start', [TripController::class, 'startTrip']);
        Route::post('/trip/{id}/update-location', [TripController::class, 'updateLocation']);
        Route::post('/trip/{id}/end', [TripController::class, 'endTrip']);
    });

    Route::prefix('passenger')->group(function () {
        Route::get('/active-trips', [TripController::class, 'getActiveTrips']);
        Route::get('/trip/{id}/track', [TripController::class, 'trackBus']);
        Route::post('/trip/{id}/check-in', [TripController::class, 'passengerCheckIn']);
    });

    Route::get('/schedules', [TripController::class, 'getSchedules']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});
