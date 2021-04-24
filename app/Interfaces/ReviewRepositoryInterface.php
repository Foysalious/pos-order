<?php


namespace App\Interfaces;


interface ReviewRepositoryInterface extends BaseRepositoryInterface
{
    public function createReview($data);
}
