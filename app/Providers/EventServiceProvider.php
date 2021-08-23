<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderDueCleared;
use App\Events\OrderUpdated;
use App\Listeners\AccountingEntryOnOrderCreation;
use App\Listeners\AccountingEntryOnOrderDueCleared;
use App\Listeners\AccountingEntryOnOrderUpdating;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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
        OrderCreated::class => [
            AccountingEntryOnOrderCreation::class,
        ],
        OrderUpdated::class => [
            AccountingEntryOnOrderUpdating::class,
        ],
        OrderDueCleared::class => [
            AccountingEntryOnOrderDueCleared::class,
        ],
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
