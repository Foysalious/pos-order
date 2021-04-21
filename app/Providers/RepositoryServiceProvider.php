<?php namespace App\Providers;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ReviewImageRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\ReviewImageRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\OrderSkusRepository;
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
        $this->app->singleton(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->singleton(ReviewImageRepositoryInterface::class, ReviewImageRepository::class);
    }

}
