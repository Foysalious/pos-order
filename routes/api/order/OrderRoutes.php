<?php

use App\Http\Controllers\Order\OrderPaymentController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'orders'], function () {
    Route::group(['prefix' => '{order}'], function () {
        Route::get('delivery-info', [OrderController::class, 'getDeliveryInfo']);
        Route::put('update-customer', [OrderController::class, 'updateCustomer']);
        Route::get('logs', [OrderController::class, 'logs']);
        Route::post('payments/create', [OrderPaymentController::class, 'create']);
    });
});
