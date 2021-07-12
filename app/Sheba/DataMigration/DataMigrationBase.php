<?php namespace App\Sheba\DataMigration;

use Illuminate\Support\Str;

class DataMigrationBase
{
    protected $namespace = "App\\Sheba\\DataMigration\\migrations";

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the class name of a migration name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getClassName($name)
    {
        return $this->namespace . "\\" . $this->getClassBaseName($name);
    }

    /**
     * Get the class base name of a migration name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getClassBaseName($name)
    {
        return Str::studly($name);
    }
}
