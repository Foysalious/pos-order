<?php


namespace App\Repositories;


use App\Interfaces\ReviewImageRepositoryInterface;
use App\Models\ReviewImage;

class ReviewImageRepository extends BaseRepository implements ReviewImageRepositoryInterface
{
    public function __construct(ReviewImage $model)
    {
        parent::__construct($model);
    }
}
