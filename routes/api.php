<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'v1'], function(){
    Route::apiResource('partners.orders', OrderController::class);
    Route::get('order-channel/{order_id}', [OrderController::class, 'getOrderWithChannel']);
    Route::group(['prefix' => 'partners/{partner}/orders/{order}'], function () {
        Route::post('update-status', [OrderController::class, 'updateStatus']);
    });
    Route::apiResource('payments', PaymentController::class);
    Route::post('customers/{customer}/orders/{order}/review', [ReviewController::class, 'store']);
    Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
});
