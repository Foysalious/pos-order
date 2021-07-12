<?php namespace App\Sheba\DataMigration\Console;

use Illuminate\Support\Composer;
use App\Sheba\DataMigration\DataMigrationCreator;

class MakeDataMigrationCommand extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:data-migration {name : The name of the migration.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new data migration file';

    /**
     * The migration creator instance.
     *
     * @var DataMigrationCreator
     */
    protected $creator;

    /**
     * The Composer instance.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * Create a new migration install command instance.
     *
     * @param  DataMigrationCreator  $creator
     * @param  Composer  $composer
     */
    public function __construct(DataMigrationCreator $creator, Composer $composer)
    {
        parent::__construct();
        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $name = trim($this->input->getArgument('name'));
        $this->writeMigration($name);
        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string $name
     * @return string
     * @throws \Exception
     */
    private function writeMigration($name)
    {
        $file = pathinfo($this->creator->create($name, $this->getMigrationPath()), PATHINFO_FILENAME);
        $this->line("<info>Created Data Migration:</info> $file");
    }
}
