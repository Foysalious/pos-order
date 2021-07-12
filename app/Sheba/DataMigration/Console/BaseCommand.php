<?php namespace App\Sheba\DataMigration\Console;

use App\Console\Commands\Command;

class BaseCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get migration path.
     *
     */
    protected function getMigrationPath():string
    {
        return __DIR__ . '/../migrations';
    }
}
