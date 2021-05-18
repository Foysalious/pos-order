<?php namespace App\Repositories;

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

    public function makeSingleReviewData($data, $singleData): array
    {
        $singleReviewData = [];
        $singleReviewData['customer_id']        = json_decode($data['customer_id']);
        $singleReviewData['partner_id']         = json_decode($data['partner_id']);
        $singleReviewData['product_id']         = $singleData->product_id ?? null;
        $singleReviewData['order_sku_id']       = $singleData->order_sku_id ?? null;
        $singleReviewData['review_title']       = $singleData->review_title ?? null;
        $singleReviewData['review_details']     = $singleData->review_details ?? null;
        $singleReviewData['rating']             = $singleData->rating ?? 5;
        $singleReviewData['category_id']        = $singleData->category_id ?? null;
        $singleReviewData['images']             = $singleData->images ?? [];
        $singleReviewData['created_by_name']    = $data['created_by_name'];
        $singleReviewData['created_at']         = $data['created_at'];
        return $singleReviewData;
    }

    private function generateImageFrom64base($index, $reviewSingleImage) : string
    {
        $reviewImageIndex = $reviewSingleImage['review_index']; // receiving filename like review_images[0][0]. So, we need array first index to identify which SKU review is processing now
        if($reviewImageIndex == $index) // if review first index (review is coming as array. review_images first index indicating review index) is equal to our review index then we will save that for that review
        {
            $randomImageFile = $this->uniqueFileNameFor64base(generateRandomFileName(15)) . '_review_image' . '.png'; // 64 base has no file name. So, we have to create it.
            file_put_contents($randomImageFile, base64_decode($reviewSingleImage['file'])); // put that image into local storage
            $reviewImageUrl = $this->saveFileToCDN($randomImageFile, reviewImageFolder(), $randomImageFile);
            unlink($randomImageFile); // remove local image after saving in CDN
            return $reviewImageUrl;
        }
        else
        {
            return '';
        }
    }

    public function saveReviewImages($reviewIndex, $imageList, $review_id)
    {
        for($i = 0; $i < count($imageList); $i++)
        {
            $reviewIndexFromSingleImage = $imageList[$i]['review_index'];
            if($reviewIndexFromSingleImage == $reviewIndex) {
                $reviewImageUrl = $this->generateImageFrom64base($reviewIndex, $imageList[$i]);
                if($reviewImageUrl != '') {
                    $makeReviewImageData['review_id'] = $review_id;
                    $makeReviewImageData['image_link'] = $reviewImageUrl;
                    $this->reviewImageRepositoryInterface->insert($makeReviewImageData);
                }
            }
        }
    }

    public function createReview($data)
    {
        $reviewList = $data['review'];
        $reviewList = json_decode(str_replace("'", '"', $reviewList));
        $reviewImageList = $data['review_images'] ?? [];

        for ($i = 0; $i < count($reviewList); $i++)
        {
            $singleReviewData = $this->makeSingleReviewData($data, $reviewList[$i]);
            $review = $this->create($singleReviewData);
            if(count($reviewImageList) > 0) $this->saveReviewImages($i, $reviewImageList, $review->id);
        }
    }

       public function getReviews($offset, $limit, $product_id, $rating, $orderBy)
    {
        if(!$orderBy)
            $orderBy = 'desc';
        $query=$this->model->where('product_id', $product_id);
        if ($rating!=NULL) {
            $query= $query->where('rating', $rating);
        }
        return $query->orderBy('created_at', $orderBy)->offset($offset)->limit($limit)->get();

    }
}
