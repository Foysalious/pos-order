<?php namespace App\Services\DataMigration;

use App\Interfaces\DataMigrationRepositoryInterface;
use Illuminate\Filesystem\Filesystem;

class DataMigrator extends DataMigrationBase
{
    /**
     * The migration repository implementation.
     *
     */
    protected $repo;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The notes for the current operation.
     *
     * @var array
     */
    protected $notes = [];

    /**
     * Create a new migrator instance.
     *
     * @param  DataMigrationRepositoryInterface $repo
     * @param  Filesystem $files
     */
    public function __construct(DataMigrationRepositoryInterface $repo, Filesystem $files)
    {
        $this->files = $files;
        $this->repo = $repo;
    }

    public function run($path)
    {
        $files = $this->getMigrationFiles($path);
        $ran = $this->repo->getAllRan()->toArray();
        $migrations = array_diff($files, $ran);
        $this->runMigrationFiles($migrations);
    }


    /**
     * Run "up" a migration instance.
     *
     * @param  array  $migrations
     * @return void
     */
    protected function runMigrationFiles($migrations)
    {
        if (count($migrations) == 0) {
            $this->note('<info>Nothing to migrate.</info>');
            return;
        }

        $batch = $this->repo->getNextBatchNumber();
        foreach ($migrations as $file) {
            $this->handleMigration($file, $batch);
        }
    }

    /**
     * Run "handle" a migration instance.
     *
     * @param  string  $file
     * @param  int     $batch
     * @return void
     */
    protected function handleMigration($file, $batch)
    {
        $migration = $this->resolve($file);
        $result = $migration->handle();
        $this->repo->save($file, $batch);
        $this->note("<info>Migrated Data:</info> $file");
        if($result) $this->note("<info>Returned:</info> $result");
    }

    /**
     * Get all of the migration files in a given path.
     *
     * @param  string  $path
     * @return array
     */
    public function getMigrationFiles($path)
    {
        $files = $this->files->glob($path.'/*_*.php');
        if ($files === false) return [];
        $files = $this->formatMigrationFiles($files);
        return $this->removeDefaultFiles($files);
    }

    private function formatMigrationFiles($files)
    {
        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));
        }, $files);
        sort($files);
        return $files;
    }

    private function removeDefaultFiles($files)
    {
        return array_filter($files, function($file) {
            return !in_array($file, ['DataMigrationBase', 'DataMigrationInterface']);
        });
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @param  string  $file
     * @return DataMigrationInterface
     */
    public function resolve($file)
    {
        $base_name = implode('_', array_slice(explode('_', $file), 4));
        $class = $this->getClassName($base_name);
        return app($class);
    }

    /**
     * Raise a note event for the migrator.
     *
     * @param  string  $message
     * @return void
     */
    protected function note($message)
    {
        $this->notes[] = $message;
    }

    /**
     * Get the notes for the last operation.
     *
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
