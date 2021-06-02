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

    public function getProductReviews($request,$rating,$orderBy, $product_id) :object
    {
        list($offset, $limit) = calculatePagination($request);
        $reviews = ReviewResource::collection($this->reviewRepositoryInterface->getReviews($offset, $limit, $product_id,$rating,$orderBy));
        if(count($reviews) == 0) return $this->error('No Review Found For This Product', 404);
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

        return $this->success('Successful', null, 201, true);
    }
}
