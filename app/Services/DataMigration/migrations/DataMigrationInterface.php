<?php namespace App\Services\DataMigration\migrations;

interface DataMigrationInterface
{
    /**
     * handle the migrations.
     *
     * @return void | string
     */
    public function handle();
}
