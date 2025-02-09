<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Middleware\Guest;
use App\Http\Middleware\OnlyAdminCanManageCustomers;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(Guest::class)->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::delete('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customer-cv-download/{customer}', [CustomerController::class, 'downloadCV']);
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers/create', [CustomerController::class, 'store']);
});

Route::middleware(['auth:sanctum', OnlyAdminCanManageCustomers::class])->group(function () {
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::post('/customers/{customer}/update', [CustomerController::class, 'update']); //should be patch but patch does not allow for file update
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
});
