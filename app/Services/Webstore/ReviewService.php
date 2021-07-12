<?php namespace App\Services\Webstore;

use App\Interfaces\ReviewRepositoryInterface;
use App\Services\BaseService;


class ReviewService extends BaseService
{

    private $partnerId;
    private array $ratings;

    public function __construct(private ReviewRepositoryInterface $reviewRepositoryInterface)
    {
    }

    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }


    public function getProductIdsByRating()
    {
        $product_ids_by_ratings = $this->reviewRepositoryInterface->getProductIdsByRating($this->partnerId, $this->ratings);
        return $this->success('Successful', ['product_ids_by_ratings' => $product_ids_by_ratings], 200);
    }

    /**
     * @param mixed $ratings
     * @return ReviewService
     */
    public function setRatings($ratings)
    {
        $this->ratings = !is_array($ratings) ? json_decode($ratings,1) : $ratings;
        return $this;
    }


}
