<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();
        $this->afterApplicationCreated(function () {
            $this->artisan('config:clear');
        });
        (new LoadConfiguration())->bootstrap($app);
        $this->baseUrl = env('APP_URL');
        return $app;
    }
}
