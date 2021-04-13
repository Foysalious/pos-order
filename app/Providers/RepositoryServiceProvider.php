<?php namespace App\Providers;

use App\Interfaces\DiscountRepositoryInterface;
use App\Interfaces\LogRepositoryInterface;
use App\Interfaces\OrderPaymentsRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Repositories\DiscountRepository;
use App\Repositories\LogRepository;
use App\Repositories\OrderPaymentsRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderSkusRepository;
use App\Repositories\PartnerRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->singleton(OrderSkusRepositoryInterface::class, OrderSkusRepository::class);
        $this->app->singleton(PartnerRepositoryInterface::class, PartnerRepository::class);
        $this->app->singleton(DiscountRepositoryInterface::class, DiscountRepository::class);
        $this->app->singleton(OrderPaymentsRepositoryInterface::class, OrderPaymentsRepository::class);
        $this->app->singleton(LogRepositoryInterface::class, LogRepository::class);
    }

}
