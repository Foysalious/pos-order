<?php

use App\Http\Controllers\DataMigrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Webstore\ReviewController;
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
    Route::group(['middleware' => 'ip.whitelist'], function ()
    {
        Route::group(['prefix' => 'partners/{partner_id}/customers'], function () {
            Route::post('', [CustomerController::class, 'store']);
            Route::post('/{customer_id}', [CustomerController::class, 'update']);
            Route::get('/{customer_id}/not-rated-order-sku-list', [CustomerController::class, 'notRatedOrderSkuList']);
            Route::get('/{customer_id}/reviews', [ReviewController::class, 'getCustomerReviewList']);
            Route::delete('/{customer_id}', [CustomerController::class, 'destroy']);
        });
        Route::group(['prefix' => 'webstore'], function () {
            Route::get('orders/{order_id}/generate-invoice', [OrderController::class, 'getOrderinvoice']);
            Route::get('partners/{partner_id}/orders/{order_id}/customers/{customer_id}/order-details', [\App\Http\Controllers\Webstore\OrderController::class, 'show']);
            Route::get('partners/{partner_id}/products-by-ratings', [ReviewController::class, 'getProductIdsByRating']);
            Route::get('/{customer_id}/orders', [OrderController::class, 'getCustomerOrderList']);
        });
        Route::group(['middleware' => 'apiRequestLog'], function() {
            Route::apiResource('partners.orders', OrderController::class);
        });
        Route::apiResource('partners.migrate', DataMigrationController::class)->only('store');
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
        Route::post('payment/delete', [PaymentController::class,'deletePayment']);
        Route::post('customers/{customer}/orders/{order}/review', [ReviewController::class, 'store']);
        Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
        Route::put('partners/{partner_id}',[DataMigrationController::class, 'updatePartnersTable']);
        Route::get('/partners/{partner_id}/customers/{customer_id}/purchase-amount-promo-usage', [CustomerController::class, 'getPurchaseAmountAndPromoUsed']);
        Route::get('/partners/{partner_id}/customers/{customer_id}/orders', [CustomerController::class, 'getOrdersByDateWise']);
        Route::get('partners/{partner_id}/reports/product-wise', [ReportController::class, 'getProductWise']);
        Route::get('partners/{partner_id}/reports/customer-wise', [ReportController::class, 'getCustomerWise']);
    });
});
