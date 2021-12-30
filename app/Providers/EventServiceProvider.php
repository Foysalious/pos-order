<?php namespace App\Providers;

use App\Events\OrderCustomerUpdated;
use App\Events\OrderDeleted;
use App\Events\OrderPlaceTransactionCompleted;
use App\Events\OrderDueCleared;
use App\Events\OrderUpdated;
use App\Listeners\Accounting\EntryOnOrderCreation;
use App\Listeners\Accounting\EntryOnOrderDelete;
use App\Listeners\Accounting\EntryOnOrderDueCleared;
use App\Listeners\Accounting\EntryOnOrderUpdating;
use App\Listeners\Accounting\EntryOnOrderCustomerUpdate;
use App\Listeners\GenerateInvoiceOnOrderCreate;
use App\Listeners\InventoryStockUpdateOnOrderDelete;
use App\Listeners\InventoryStockUpdateOnOrderUpdate;
use App\Listeners\InventoryStockUpdateOnOrderPlace;
use App\Listeners\PushNotificationForOrder;
use App\Listeners\RewardOnOrderCreate as RewardOnOrderCreateListener;
use App\Listeners\UsageOnOrderCreate;
use App\Listeners\WebstoreSettingsSyncOnOrderCreate;
use App\Listeners\WebstoreSmsSendForOrder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\WebstoreSettingsSyncOnOrderUpdate;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderPlaceTransactionCompleted::class => [
            EntryOnOrderCreation::class,
            RewardOnOrderCreateListener::class,
            UsageOnOrderCreate::class,
            GenerateInvoiceOnOrderCreate::class,
            WebstoreSettingsSyncOnOrderCreate::class,
            InventoryStockUpdateOnOrderPlace::class,
            PushNotificationForOrder::class,
            WebstoreSmsSendForOrder::class
        ],
        OrderUpdated::class => [
            EntryOnOrderUpdating::class,
            EntryOnOrderDueCleared::class,
            GenerateInvoiceOnOrderCreate::class,
            WebstoreSettingsSyncOnOrderUpdate::class,
            InventoryStockUpdateOnOrderUpdate::class,
        ],
        OrderDueCleared::class => [
            EntryOnOrderDueCleared::class,
        ],
        OrderDeleted::class => [
            EntryOnOrderDelete::class,
            InventoryStockUpdateOnOrderDelete::class,
        ],
        OrderCustomerUpdated::class => [
            EntryOnOrderCustomerUpdate::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
