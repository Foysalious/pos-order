<?php namespace App\Sheba\DataMigration;

use App\Interfaces\DataMigrationRepositoryInterface;
use App\Repositories\DataMigrationRepository;
use Illuminate\Support\ServiceProvider;
use App\Sheba\DataMigration\Console\DataMigrateCommand;
use App\Sheba\DataMigration\Console\MakeDataMigrationCommand;

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
