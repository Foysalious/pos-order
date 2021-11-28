<?php

namespace App\Providers;

use App\Services\DataMigration\DataMigrationServiceProvider;
use Clockwork\Support\Laravel\ClockworkServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(DataMigrationServiceProvider::class);
        if (($this->app->environment('local') || $this->app->environment('development')) && config('clockwork.enable')) {
            $this->app->register(ClockworkServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
         if (config('l5-swagger.swagger_on_dev') == true){
            $url->forceScheme('https');
        }
        JsonResource::withoutWrapping();
    }
}
