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

    public function create($request, $customer_id, $order_id)
    {
        $order = $this->orderRepositoryInterface->where('customer_id', $customer_id)->find($order_id);
        if(!$order) return $this->error('অর্ডারটি পাওয়া যায় নি', 404);

        $this->reviewCreator->setOrderId($order_id)
            ->setCustomerId($customer_id)
            ->setPartnerId($request->partner_id)
            ->setReview($request->review)
            ->create();

        return $this->success('Successful', null, 200, true);
    }
}
