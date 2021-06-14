<?php namespace App\Interfaces;

interface ReviewRepositoryInterface extends BaseRepositoryInterface
{
    public function createReview($data);
    public function getReviews(int $offset, int $limit, int $product_id,int $rating, string $orderBy);
    public function getRatingStatistics(int $productId);

}
