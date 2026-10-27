<?php
 
use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
 
Route::get('/health', HealthController::class);
 
Route::post('/orders', [OrderController::class, 'store']);
 
Route::get('/orders/user/{userId}', [OrderController::class, 'indexByUser'])
    ->where('userId', '[0-9]{1,18}');
 
Route::get('/orders/{id}', [OrderController::class, 'show'])
    ->where('id', '[0-9]{1,18}');
 
Route::patch('/orders/{id}/pay', [OrderController::class, 'pay'])
    ->where('id', '[0-9]{1,18}');
