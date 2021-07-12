<?php namespace App\Repositories;

use App\Interfaces\ReviewImageRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Models\Order;
use App\Models\OrderSku;
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

    public function makeSingleReviewData($data, $reviewData): array
    {
        $singleData = json_decode($reviewData);
        $singleReviewData = [];
        $singleReviewData['customer_id'] = $data['customer_id'];
        $singleReviewData['partner_id'] = $data['partner_id'];
        $singleReviewData['product_id'] = $singleData->product_id ?? null;
        $singleReviewData['order_sku_id'] = $singleData->order_sku_id ?? null;
        $singleReviewData['review_title'] = $singleData->review_title ?? null;
        $singleReviewData['review_details'] = $singleData->review_details ?? null;
        $singleReviewData['rating'] = $singleData->rating ?? 5;
        $singleReviewData['category_id'] = $singleData->category_id ?? null;
        $singleReviewData['images'] = $singleData->images ?? [];
        $singleReviewData['created_by_name'] = $data['created_by_name'];
        $singleReviewData['created_at'] = $data['created_at'];
        return $singleReviewData;
    }

    private function generateImageFrom64base($reviewIndex, $reviewSingleImage, $reviewIndexFromImageName): string
    {
        if ($reviewIndexFromImageName == $reviewIndex) // if review first index (review is coming as array. review_images first index indicating review index) is equal to our review index then we will save that for that review
        {
            $randomImageFile = $this->uniqueFileNameFor64base(generateRandomFileName(15)) . '_review_image' . '.png'; // 64 base has no file name. So, we have to create it.
            is_array($reviewSingleImage) ? (file_put_contents($randomImageFile, base64_decode($reviewSingleImage[0]))) : file_put_contents($randomImageFile, base64_decode($reviewSingleImage)); // put that image into local storage
            $reviewImageUrl = $this->saveFileToCDN($randomImageFile, reviewImageFolder(), $randomImageFile);
            unlink($randomImageFile); // remove local image after saving in CDN
            return $reviewImageUrl;
        }
    }

    private function getReviewIndexFromImageName($imageName): int
    {
        preg_match('#\[(.*?)\]#', $imageName, $match);
        return (int)$match[1];
    }

    private function insertReviewImages($reviewIndex, $imageFile, $reviewIndexFromSingleImage, $review_id)
    {
        $reviewImageUrl = $this->generateImageFrom64base($reviewIndex, $imageFile, $reviewIndexFromSingleImage) ?? '';
        $makeReviewImageData['review_id'] = $review_id;
        $makeReviewImageData['image_link'] = $reviewImageUrl;
        $this->reviewImageRepositoryInterface->insert($makeReviewImageData);
    }

    private function getReviewImagesFromArray($subImageList, $reviewIndex, $reviewIndexFromSingleImage, $review_id)
    {
        for ($i = 0; $i < count($subImageList); $i++) {
            if ($reviewIndex == $reviewIndexFromSingleImage)
                $this->insertReviewImages($reviewIndex, $subImageList[$i], $reviewIndexFromSingleImage, $review_id);
        }
    }

    public function getReviewImages($reviewIndex, $reviewImageList, $review_id)
    {
        foreach ($reviewImageList as $imageName => $imageFile) {
            $reviewIndexFromSingleImage = null;
            if (is_array($imageFile) && $imageFile[0]) {
                $reviewIndexFromSingleImage = $imageName;
                $this->getReviewImagesFromArray($imageFile, $reviewIndex, $reviewIndexFromSingleImage, $review_id);
            }
        }
    }

    public function createReview($data)
    {
        $reviewList = $data['review'];
        $reviewImageList = $data['review_images'] ?? [];

        for ($i = 0; $i < count($reviewList); $i++) {
            $singleReviewData = $this->makeSingleReviewData($data, $reviewList[$i]);
            $review = $this->create($singleReviewData);
            if (count($reviewImageList) > 0) $this->getReviewImages($i, $reviewImageList, $review->id);
        }
    }

    public function getReviews($offset, $limit, $product_id, $rating, $orderBy)
    {
        if (!$orderBy)
            $orderBy = 'desc';
        $query = $this->model->where('product_id', $product_id);
        if (!empty($rating)) {
            $query = $query->where('rating', $rating);
        }
        return $query->orderBy('created_at', $orderBy)->offset($offset)->limit($limit)->get();

    }

    public function getCustomerReviews(string $customer_id, int $offset, int $limit, $order)
    {
        $query = $this->model->where('customer_id', $customer_id);
        if ($order) $query = $query->orderBy('id', $order);
        return $query->offset($offset)->limit($limit)->get();
    }

    public function getProductIdsByRating($partnerId, $ratings)
    {
        return $this->model->where('partner_id',$partnerId)->whereIn('rating',$ratings)->pluck('product_id');
    }


}
