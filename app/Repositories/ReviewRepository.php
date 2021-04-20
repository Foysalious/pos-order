<?php
namespace App\Repositories;

use App\Interfaces\ReviewRepositoryInterface;
use App\Models\Review;

class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    public function makeSingleReviewData($data, $singleData) :array
    {
        $singleReviewData = [];
        $singleReviewData['customer_id']    = json_decode($data['customer_id']);
        $singleReviewData['partner_id']     = json_decode($data['partner_id']);
        $singleReviewData['product_id']     = $singleData->product_id ?? null;
        $singleReviewData['order_sku_id']   = $singleData->order_sku_id;
        $singleReviewData['review_title']   = $singleData->review_title ?? null;
        $singleReviewData['review_details'] = $singleData->review_details ?? null;
        $singleReviewData['rating']         = $singleData->rating;
        $singleReviewData['category_id']    = $singleData->category_id ?? null;
        return $singleReviewData;
    }

    public function createReview($data)
    {
        $data = str_replace("'", '"', $data);
        $reviewList = json_decode($data['review']);
        $reviewCount = count($reviewList);

        for ($i = 0; $i < $reviewCount; $i++)
        {
            $singleReviewData = $this->makeSingleReviewData($data, $reviewList[$i]);
            $this->model->insert($singleReviewData);
        }
    }
}
