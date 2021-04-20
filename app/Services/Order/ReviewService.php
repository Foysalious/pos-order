<?php


namespace App\Services\Order;


use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Services\BaseService;

class ReviewService extends BaseService
{
    protected $reviewRepositoryInterface;
    protected $orderRepositoryInterface;
    protected $reviewCreator;

    public function __construct(ReviewRepositoryInterface $reviewRepositoryInterface, OrderRepositoryInterface $orderRepositoryInterface, ReviewCreator $reviewCreator)
    {
        $this->reviewRepositoryInterface = $reviewRepositoryInterface;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->reviewCreator = $reviewCreator;
    }

    public function create($request, $order_id)
    {
        $order = $this->orderRepositoryInterface->find($order_id);
        if(!$order) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        $review = $this->reviewCreator->setOrderId($order_id)
            ->setCustomerId($request->customer_id)
            ->setPartnerId($request->partner_id)
            ->setReview($request->review)
            ->create();

        return $this->success('Successful', ['review' => $review], 200, true);
    }
}
