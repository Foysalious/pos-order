<?php namespace App\Services\Webstore;

use App\Constants\ResponseMessages;
use App\Http\Resources\CustomerReviewResource;
use App\Http\Resources\Webstore\AverageRatingAndRatingCountResource;
use App\Http\Resources\Webstore\ReviewResource;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Services\BaseService;
use App\Services\FileManagers\CdnFileManager;
use App\Services\FileManagers\FileManager;
use Illuminate\Http\JsonResponse;

class ReviewService extends BaseService
{
    private $partnerId;
    private $ratings;
    use FileManager, CdnFileManager;

    public function __construct(private ReviewRepositoryInterface $reviewRepositoryInterface, private OrderRepositoryInterface $orderRepositoryInterface, private ReviewCreator $reviewCreator)
    {
    }

    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }


    public function getProductIdsByRating(): JsonResponse
    {

        $product_ids_by_ratings = $this->reviewRepositoryInterface->getProductIdsByRating($this->partnerId, $this->ratings);
        return $this->success(ResponseMessages::SUCCESS, ['product_ids_by_ratings' => $product_ids_by_ratings]);
    }

    /**
     * @param mixed $ratings
     * @return ReviewService
     */
    public function setRatings($ratings)
    {
        $this->ratings = !is_array($ratings) ? json_decode($ratings, 1) : $ratings;
        return $this;
    }

    /**
     * @param $request
     * @param int $rating
     * @param string $orderBy
     * @param array $product_id
     * @return object
     */
    public function getProductReviews($request, $rating, $orderBy,  $product_id): object
    {
        list($offset, $limit) = calculatePagination($request);
        $reviews = $this->reviewRepositoryInterface->getReviews($offset, $limit, $product_id, $rating, $orderBy);
        if (count($reviews) == 0) return $this->error('এই প্রোডাক্ট এর জন্য কোন রিভিউ পাওয়া যায় নি', 404);
        $reviews = ReviewResource::collection($reviews);
        $review_statistics = $this->reviewStatistics($product_id);
        return $this->success(ResponseMessages::SUCCESS, ['reviews' => $reviews, 'review_statistics'=> $review_statistics]);
    }

    public function getReviewsByProductIds($partner_id,array $productIds): object
    {
        $reviews = $this->reviewRepositoryInterface->getReviewsByProductIds($partner_id,$productIds);
        $reviews = AverageRatingAndRatingCountResource::collection($reviews);
        return $this->success(ResponseMessages::SUCCESS, ['reviews' => $reviews]);
    }

    public function reviewStatistics($productId): array
    {
        $statistics = $this->reviewRepositoryInterface->getRatingStatistics($productId);
        $sorted_statistics = [];
        for ($i = 1; $i <= 5; $i++)
            if (!isset($statistics[$i]))
                $sorted_statistics[$i] = 0;
            else
                $sorted_statistics[$i] = $statistics[$i];
        return $sorted_statistics;
    }

    /**
     * @param string $customer_id
     * @param $request
     * @return JsonResponse
     */
    public function getCustomerReviews(string $customer_id, $request): JsonResponse
    {
        $order = $request->order;
        list($offset, $limit) = calculatePagination($request);
        $reviewCount = count($this->reviewRepositoryInterface->getCustomerCount($customer_id));
        $reviews = $this->reviewRepositoryInterface->getCustomerReviews($customer_id, $offset, $limit, $order);
        if (count($reviews) == 0) return $this->error('You have not placed any reviews yet', 404);
        $reviews = CustomerReviewResource::collection($reviews);
        return $this->success(ResponseMessages::SUCCESS, ['total_count' => $reviewCount, 'reviews' => $reviews]);
    }


    public function create($request, $customer_id, $order_id): JsonResponse
    {
        $order = $this->orderRepositoryInterface->where('customer_id', $customer_id)->find($order_id);
        if (is_null($order)) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        $this->reviewCreator->setOrderId($order_id)
            ->setCustomerId($customer_id)
            ->setPartnerId($order->partner_id)
            ->setReview($request->review)
            ->setReviewImages($request->review_images)
            ->create();

        return $this->success(ResponseMessages::SUCCESS);
    }
}
