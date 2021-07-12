<?php namespace App\Sheba\DataMigration\migrations;

use App\Models\User;
use App\Traits\ModificationFields;


class DataMigrationBase
{
    use ModificationFields;

    /** @var User */
    protected $user;

    public function __construct()
    {
        $this->setModifier(User::find(1));
    }

    protected function createdFields()
    {
        return $this->modificationFields(true, false);
    }

    protected function updatedFields()
    {
        return $this->modificationFields(false, true);
    }

    protected function commonFields()
    {
        return $this->modificationFields(true, true);
    }

    protected function withCommonFields($data)
    {
        return $this->withBothModificationFields($data);
    }

    protected function withCreatedFields($data)
    {
        return $this->withCreateModificationField($data);
    }

    protected function withUpdatedFields($data)
    {
        return $this->withUpdateModificationField($data);
    }

    protected function getDataFilesPath()
    {
        return __DIR__ . '/../files';
    }

    protected function getExcelDataFilesPath()
    {
        return $this->getDataFilesPath() . '/excel';
    }

    protected function getExcelPath($file_name)
    {
        return $this->getExcelDataFilesPath() . '/' . $file_name;
    }

    protected function loadExcel($file_name, $closure)
    {
        Excel::load($this->getExcelPath($file_name), $closure);
    }
}
