<?php namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


trait ModificationFields
{
    private $modifier = null;
    private $modifierModelName = null;

    public function setModifier($entity)
    {
        Session::flash('modifier', $entity);
    }


    /**
     * @param bool $created_fields
     * @param bool $updated_fields
     * @return array
     */
    private function modificationFields($created_fields = true, $updated_fields = true): array
    {
        list($name, $time) = $this->getData();

        $data = [];
        if ($created_fields) {
            $data['created_by_name'] = $name;
            $data['created_at'] = $time;
        }

        if ($updated_fields) {
            $data['updated_by_name'] = $name;
            $data['updated_at'] = $time;
        }

        return $data;
    }

    /**
     * Add the modification fields to an Object.
     *
     * @param $model
     * @param bool|true $created_fields
     * @param bool|true $updated_fields
     */
    private function addModificationFieldsToObject($model, $created_fields = true, $updated_fields = true)
    {
        list($id, $name, $time) = $this->getData();

        if ($created_fields) {
            $model->created_by_name = $name;
            $model->created_at = $time;
        }

        if ($updated_fields) {
            $model->updated_by_name = $name;
            $model->updated_at = $time;
        }
    }

    /**
     * Merge the data with both(created and updated) modification fields.
     *
     * @param $data
     * @return array
     */
    public function withBothModificationFields($data): array
    {
        if (is_array($data)) return array_merge($data, $this->modificationFields());

        $this->addModificationFieldsToObject($data);
    }

    /**
     * Merge the data with only created modification fields.
     *
     * @param $data
     * @return array
     */
    public function withCreateModificationField($data): array
    {
        if (is_array($data)) return array_merge($data, $this->modificationFields($create = true, $update = false));

        $this->addModificationFieldsToObject($data, $create = true, $update = false);
    }

    /**
     * Merge the data with only updated modification fields.
     *
     * @param $data
     * @return array
     */
    public function withUpdateModificationField($data): array
    {
        if (is_array($data)) return array_merge($data, $this->modificationFields($create = false));

        $this->addModificationFieldsToObject($data, $create = false);
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $this->modifier = Session::get('modifier');
        $time = Carbon::now();
        $name = $this->modifier;
        return [$name, $time];
    }


}
