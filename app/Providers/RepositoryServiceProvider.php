<?php namespace App\Providers;

use App\Interfaces\OrderDiscountRepositoryInterface;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderLogRepositoryInterface;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\PaymentLinkRepositoryInterface;
use App\Interfaces\PaymentRepositoryInterface;
use App\Interfaces\ReviewImageRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Repositories\OrderDiscountRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderLogRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderPaymentRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentLinkRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ReviewImageRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\OrderSkusRepository;
use App\Repositories\OrderSkuRepository;
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
        $this->app->singleton(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->singleton(ReviewImageRepositoryInterface::class, ReviewImageRepository::class);
        $this->app->singleton(PartnerRepositoryInterface::class,PartnerRepository::class);
        $this->app->singleton(OrderRepositoryInterface::class,OrderRepository::class);
        $this->app->singleton(OrderSkuRepositoryInterface::class,OrderSkuRepository::class);
        $this->app->singleton(OrderPaymentRepositoryInterface::class,OrderPaymentRepository::class);
        $this->app->singleton(PaymentRepositoryInterface::class,PaymentRepository::class);
        $this->app->singleton(PaymentLinkRepositoryInterface::class,PaymentLinkRepository::class);
        $this->app->singleton(OrderLogRepositoryInterface::class, OrderLogRepository::class);
        $this->app->singleton(CustomerRepositoryInterface::class,CustomerRepository::class);
        $this->app->singleton(OrderDiscountRepositoryInterface::class,OrderDiscountRepository::class);
        $this->app->singleton(CustomerRepositoryInterface::class, CustomerRepository::class);
    }

}
