<?php namespace App\Services\Order;

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

    public function getProductReviews($request, $rating, $orderBy, $product_id): object
    {
        list($offset, $limit) = calculatePagination($request);
        $reviews = $this->reviewRepositoryInterface->getReviews($offset, $limit, $product_id, $rating, $orderBy);
        if (count($reviews) == 0) return $this->error('এই প্রোডাক্ট এর জন্য কোন রিভিউ পাওয়া যায় নি', 404);
        $reviews = ReviewResource::collection($reviews);


        return $this->success('Successful', ['reviews' => $reviews, 'statistics' => $this->reviewStatistics()], 200);
    }

    public function reviewStatistics()
    {
        return json_decode(json_encode([
            "5" => 110,
            "4" => 82,
            "3" => 0,
            "2" => 0,
            "1" => 0,
        ]));
    }

    public function create($request, $customer_id, $order_id)
    {
        $order = $this->orderRepositoryInterface->where('customer_id', $customer_id)->find($order_id);
        if (!$order) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        $this->reviewCreator->setOrderId($order_id)
            ->setCustomerId($customer_id)
            ->setPartnerId($request->partner_id)
            ->setReview($request->review)
            ->setReviewImages($request->review_images)
            ->create();

        return $this->success('Successful', null, 201, true);
    }
}
