<?php

namespace App\Repositories;

use App\Interfaces\ApiRequestRepositoryInterface;
use App\Models\ApiRequest;

class ApiRequestRepository extends BaseRepository implements ApiRequestRepositoryInterface
{
    public function __construct(ApiRequest $model)
    {
        parent::__construct($model);
    }

    public function create($data)
    {
        return $this->model->create($data);
    }
}
