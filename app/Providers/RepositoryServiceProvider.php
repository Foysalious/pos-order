<?php namespace App\Providers;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Repositories\OrderRepository;
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
        $this->app->singleton(PartnerRepositoryInterface::class,PartnerRepository::class);
        $this->app->singleton(OrderRepositoryInterface::class,OrderRepository::class);
    }

}
