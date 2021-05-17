<?php


namespace App\Interfaces;


interface ReviewRepositoryInterface extends BaseRepositoryInterface
{
    public function createReview($data);
    public function getReviews($offset, $limit, $product_id,$rating,$orderBy);

}
