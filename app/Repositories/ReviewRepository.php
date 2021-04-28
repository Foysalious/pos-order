<?php
namespace App\Repositories;

use App\Interfaces\ReviewImageRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Models\Review;
use App\Services\FileManagers\CdnFileManager;
use App\Services\FileManagers\FileManager;

class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    use CdnFileManager, FileManager;
    protected $reviewImageRepositoryInterface;

    public function __construct(Review $model, ReviewImageRepositoryInterface $reviewImageRepositoryInterface)
    {
        $this->reviewImageRepositoryInterface = $reviewImageRepositoryInterface;
        parent::__construct($model);
    }

    public function makeSingleReviewData($data, $singleData) :array
    {
        $singleReviewData = [];
        $singleReviewData['customer_id']    = json_decode($data['customer_id']);
        $singleReviewData['partner_id']     = json_decode($data['partner_id']);
        $singleReviewData['product_id']     = $singleData->product_id ?? null;
        $singleReviewData['order_sku_id']   = $singleData->order_sku_id ?? null;
        $singleReviewData['review_title']   = $singleData->review_title ?? null;
        $singleReviewData['review_details'] = $singleData->review_details ?? null;
        $singleReviewData['rating']         = $singleData->rating ?? 5;
        $singleReviewData['category_id']    = $singleData->category_id ?? null;
        return $singleReviewData;
    }

    public function saveReviewImages($imageList, $review_id)
    {
        for($i = 0; $i < count($imageList); $i++)
        {
            list($file, $fileName) = [$imageList[$i], $this->uniqueFileName($imageList[$i], '_' . getFileName($imageList[$i]) . '_review_image')];
            $reviewImageUrl = $this->saveFileToCDN($file, reviewImageFolder(), $fileName);
            $makeReviewImageData['review_id'] = $review_id;
            $makeReviewImageData['image_link'] = $reviewImageUrl;
            return $this->reviewImageRepositoryInterface->insert($makeReviewImageData);
        }
    }

    public function createReview($data)
    {
        $reviewList = $data['review'];
        $reviewList = json_decode(str_replace("'", '"', $reviewList));
        $reviewCount = count($reviewList);
        $reviewImageList = $data['review_images'] ?? [];

        for ($i = 0; $i < $reviewCount; $i++)
        {
            $singleReviewData = $this->makeSingleReviewData($data, $reviewList[$i]);
            $review = $this->model->create($singleReviewData);
            if(count($reviewImageList) > 0 && count($reviewImageList[$i]) >0 ) $this->saveReviewImages($reviewImageList[$i], $review->id);
        }
    }
}
