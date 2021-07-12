<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;

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
    //Route::group(['middleware' => 'ip.whitelist'], function ()
    //{
        Route::group(['prefix' => 'customers'], function () {
            Route::post('', [CustomerController::class, 'store']);
            Route::post('/{customer_id}', [CustomerController::class, 'update']);
            Route::get('/{customer_id}/not-rated-order-sku-list', [CustomerController::class, 'notRatedOrderSkuList']);
            Route::get('/{customer_id}/orders', [OrderController::class, 'getCustomerOrderList']);
            Route::get('/{customer_id}/reviews', [ReviewController::class, 'getCustomerReviewList']);
        });
        Route::group(['prefix' => 'webstore'], function () {
            Route::get('partners/{partner_id}/orders/{order_id}', [\App\Http\Controllers\Webstore\OrderController::class, 'show']);
        });
        Route::apiResource('partners.orders', OrderController::class);
        Route::group(['prefix' => 'partners'], function () {
            Route::group(['prefix' => '{partner}'], function () {
                Route::group(['prefix' => 'orders'], function () {
                    Route::group(['prefix' => '{order}'], function () {
                        Route::get('delivery-info', [OrderController::class, 'getDeliveryInfo']);
                        Route::put('update-customer', [OrderController::class, 'updateCustomer']);
                    });
                });
            });
        });
        Route::get('order-channel/{order_id}', [OrderController::class, 'getOrderWithChannel']);
        Route::group(['prefix' => 'partners/{partner}/orders/{order}'], function () {
            Route::post('update-status', [OrderController::class, 'updateStatus']);
        });
        Route::apiResource('payments', PaymentController::class);
        Route::post('customers/{customer}/orders/{order}/review', [ReviewController::class, 'store']);
        Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
   // });
});
