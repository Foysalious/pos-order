<?php namespace App\Sheba\DataMigration\Console;

use App\Sheba\DataMigration\DataMigrator;

class DataMigrateCommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'data-migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run new migration files.';

    private $migrator;

    /**
     * Create a new migration install command instance.
     *
     * @param DataMigrator $migrator
     *
     */
    public function __construct(DataMigrator $migrator)
    {
        parent::__construct();
        $this->migrator = $migrator;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->migrator->run($this->getMigrationPath());
        foreach ($this->migrator->getNotes() as $note) {
            $this->output->writeln($note);
        }
        $this->info('Data Migrated Successfully');
    }
}
