<?php namespace App\Providers;

use App\Events\OrderCustomerUpdated;
use App\Events\OrderDeleted;
use App\Events\OrderPlaceTransactionCompleted;
use App\Events\OrderDueCleared;
use App\Events\OrderUpdated;
use App\Listeners\AccountingEntryOnOrderCreation;
use App\Listeners\AccountingEntryOnOrderDelete;
use App\Listeners\AccountingEntryOnOrderDueCleared;
use App\Listeners\AccountingEntryOnOrderUpdating;
use App\Listeners\AccountingEntryOrderCustomerUpdate;
use App\Listeners\GenerateInvoiceOnOrderCreate;
use App\Listeners\RewardOnOrderCreate as RewardOnOrderCreateListener;
use App\Listeners\UsageOnOrderCreate;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
            AccountingEntryOnOrderCreation::class,
            RewardOnOrderCreateListener::class,
            UsageOnOrderCreate::class,
            GenerateInvoiceOnOrderCreate::class
        ],
        OrderUpdated::class => [
            AccountingEntryOnOrderUpdating::class,
            GenerateInvoiceOnOrderCreate::class
        ],
        OrderDueCleared::class => [
            AccountingEntryOnOrderDueCleared::class,
        ],
        OrderDeleted::class => [
            AccountingEntryOnOrderDelete::class,
        ],
        OrderCustomerUpdated::class => [
            AccountingEntryOrderCustomerUpdate::class
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
