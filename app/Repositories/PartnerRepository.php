<?php namespace App\Repositories;


use App\Interfaces\PartnerRepositoryInterface;
use App\Models\Partner;
use App\Repositories\BaseRepository;

class PartnerRepository extends BaseRepository implements PartnerRepositoryInterface
{
    public function __construct(Partner $model)
    {
        parent::__construct($model);
    }

}
