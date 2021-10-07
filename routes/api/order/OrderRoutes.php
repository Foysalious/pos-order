<?php

use App\Http\Controllers\Order\OrderPaymentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'orders'], function () {
    Route::group(['prefix' => '{order}'], function () {
        Route::post('payments/create', [OrderPaymentController::class, 'create']);
    });
});
