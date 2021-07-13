<?php namespace App\Services\DataMigration;

use App\Interfaces\DataMigrationRepositoryInterface;
use App\Repositories\DataMigrationRepository;
use Illuminate\Support\ServiceProvider;
use App\Services\DataMigration\Console\DataMigrateCommand;
use App\Services\DataMigration\Console\MakeDataMigrationCommand;

class DataMigrationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepository();
        $this->registerCommands();
    }

    /**
     * Register the migration repository service.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton(DataMigrationRepositoryInterface::class, DataMigrationRepository::class);
    }

    /**
     * Register the data migration commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->commands(
            MakeDataMigrationCommand::class,
            DataMigrateCommand::class
        );
    }
}
