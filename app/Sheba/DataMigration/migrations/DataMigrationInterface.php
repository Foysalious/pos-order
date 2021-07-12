<?php namespace App\Sheba\DataMigration\migrations;

interface DataMigrationInterface
{
    /**
     * handle the migrations.
     *
     * @return void | string
     */
    public function handle();
}
