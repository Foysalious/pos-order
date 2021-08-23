<?php namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use App\Events\RewardOnOrderCreate as RewardOnOrderCreateEvent;
use App\Listeners\AccountingEntryOnOrderCreation;
use App\Listeners\AccountingEntryOnOrderUpdating;
use App\Listeners\RewardOnOrderCreate as RewardOnOrderCreateListener;
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
        OrderCreated::class => [
            AccountingEntryOnOrderCreation::class,
        ],
        OrderUpdated::class => [
            AccountingEntryOnOrderUpdating::class,
        ],
        RewardOnOrderCreateEvent::class => [
            RewardOnOrderCreateListener::class
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
