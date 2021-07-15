<?php namespace App\Services\Order;

use App\Http\Resources\CustomerReviewResource;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Services\BaseService;
use App\Services\FileManagers\CdnFileManager;
use App\Services\FileManagers\FileManager;
use App\Http\Resources\ReviewResource;

class ReviewService extends BaseService
{
    use FileManager, CdnFileManager;

    protected $reviewRepositoryInterface;
    protected $orderRepositoryInterface;
    protected $reviewCreator;

    public function __construct(ReviewRepositoryInterface $reviewRepositoryInterface, OrderRepositoryInterface $orderRepositoryInterface, ReviewCreator $reviewCreator)
    {
        $this->reviewRepositoryInterface = $reviewRepositoryInterface;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->reviewCreator = $reviewCreator;
    }

    /**
     * @param $request
     * @param int $rating
     * @param string $orderBy
     * @param int $product_id
     * @return object
     */
    public function getProductReviews($request,  $rating,  $orderBy, int $product_id): object
    {
        list($offset, $limit) = calculatePagination($request);
        $reviews = $this->reviewRepositoryInterface->getReviews($offset, $limit, $product_id, $rating, $orderBy);
        if (count($reviews) == 0) return $this->error('এই প্রোডাক্ট এর জন্য কোন রিভিউ পাওয়া যায় নি', 404);
        $reviews = ReviewResource::collection($reviews);
        $review_statistics = $this->reviewStatistics($product_id);
        return $this->success('Successful', ['reviews' => $reviews, 'rating_statistics' => $review_statistics], 200);
    }

    public function reviewStatistics($productId): array
    {
        $statistics = $this->reviewRepositoryInterface->getRatingStatistics($productId);
        $sorted_statistics = [];
        for($i=1 ; $i<=5 ; $i++)
            if(!isset($statistics[$i]))
                $sorted_statistics[$i] = 0;
            else
                $sorted_statistics[$i] = $statistics[$i];
        return $sorted_statistics;
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function getCustomerReviews(string $customer_id, $request)
    {
        $order = $request->order;
        list($offset, $limit) = calculatePagination($request);
        $reviews = $this->reviewRepositoryInterface->getCustomerReviews($customer_id, $offset, $limit, $order);
        if (count($reviews) == 0) return $this->error('You have not placed any reviews yet', 404);
        $reviews = CustomerReviewResource::collection($reviews);
        return $this->success('Successful', ['reviews' => $reviews], 200);
    }



    public function create($request, $customer_id, $order_id)
    {
        $order = $this->orderRepositoryInterface->where('customer_id', $customer_id)->find($order_id);
        if(is_null($order)) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        $this->reviewCreator->setOrderId($order_id)
            ->setCustomerId($customer_id)
            ->setPartnerId($order->partner_id)
            ->setReview($request->review)
            ->setReviewImages($request->review_images)
            ->create();

        return $this->success('Successful', null, 201);
    }
}
