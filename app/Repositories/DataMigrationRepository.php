<?php namespace App\Repositories;

use App\Interfaces\DataMigrationRepositoryInterface;
use App\Models\DataMigration;

class DataMigrationRepository implements DataMigrationRepositoryInterface
{
    private DataMigration $model;

    public function __construct(DataMigration $model)
    {
        $this->model = $model;
    }

    /**
     * Save that a migration was run.
     *
     * @param  string $file
     * @param  int    $batch
     * @return void
     */
    public function save($file, $batch)
    {
        $this->model->create(['migration' => $file, 'batch' => $batch]);
    }

    /**
     * Get all ran migrations.
     *
     */
    public function getAllRan()
    {
        return $this->model->orderBy('batch', 'asc')->orderBy('migration', 'asc')->pluck('migration');
    }

    /**
     * Get the last migration batch.
     *
     */
    public function getLastBatch()
    {
        return $this->model->where('batch', $this->getLastBatchNumber())->orderBy('migration', 'desc')->get();
    }

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber()
    {
        return $this->model->max('batch');
    }

    /**
     * Remove a migration from the log.
     *
     * @param  object $migration
     * @return void
     */
    public function delete($migration)
    {
        $this->model->where('migration', $migration->migration)->delete();
    }

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }
}
