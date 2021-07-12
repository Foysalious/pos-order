<?php namespace App\Interfaces;


interface DataMigrationRepositoryInterface
{
    /**
     * Save that a migration was run.
     *
     * @param string $file
     * @param int $batch
     */
    public function save(string $file, int $batch);

    /**
     * Get all the ran migrations.
     *
     */
    public function getAllRan();

    /**
     * Get the last migration batch.
     *
     */
    public function getLastBatch();

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber();

    /**
     * Remove a migration from the log.
     *
     * @param  object  $migration
     * @return void
     */
    public function delete($migration);

    /**
     * Get the next migration batch number.
     * @return int
     */
    public function getNextBatchNumber();
}
