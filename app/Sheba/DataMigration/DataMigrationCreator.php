<?php namespace App\Sheba\DataMigration;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;

class DataMigrationCreator extends DataMigrationBase
{
    /** @var Filesystem $files */
    protected $files;

    /**
     * DataMigrationCreator constructor.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new migration at the given path.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     * @throws Exception
     */
    public function create($name, $path)
    {
        $this->ensureMigrationDoesNotExist($name);
        $file_path = $this->getFilePath($name, $path);
        $this->files->put($file_path, $this->getBoilerplate($name));
        return $file_path;
    }

    /**
     * Ensure that a migration with the given name does not already exist.
     *
     * @param  string  $name
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function ensureMigrationDoesNotExist($name)
    {
        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A $className migration already exists.");
        }
    }

    /**
     * Make the migration boiler plate file.
     *
     * @param  string $name
     * @return string
     * @throws FileNotFoundException
     */
    private function getBoilerplate($name)
    {
        $content = $this->files->get($this->getBoilerplatePath() . '/blank.stub');
        return $this->populateBoilerplate($name, $content);
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    private function getBoilerplatePath(): string
    {
        return __DIR__.'/boilerplate';
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $name
     * @param  string  $boilerplate
     * @return string
     */
    private function populateBoilerplate($name, $boilerplate): string
    {
        $boilerplate = str_replace('DummyNameSpace', $this->namespace, $boilerplate);
        $boilerplate = str_replace('DummyClass', $this->getClassBaseName($name), $boilerplate);
        return $boilerplate;
    }

    /**
     * Get the full path name to the migration.
     *
     * @param string $name
     * @param string $path
     * @return string
     */
    private function getFilePath(string $name, string $path):string
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }
}
