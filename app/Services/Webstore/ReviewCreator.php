<?php namespace App\Services\Webstore;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Traits\ModificationFields;

class ReviewCreator
{
    use ModificationFields;

    protected $order_id, $customer_id, $partner_id, $rating, $review, $review_images;
    protected $reviewRepositoryInterface;
    protected $orderRepositoryInterface;

    public function __construct(ReviewRepositoryInterface $reviewRepositoryInterface, OrderRepositoryInterface $orderRepositoryInterface)
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->reviewRepositoryInterface = $reviewRepositoryInterface;
    }

    /**
     * @param mixed $review_images
     * @return ReviewCreator
     */
    public function setReviewImages($review_images)
    {
        $this->review_images = $review_images;
        return $this;
    }

    /**
     * @param mixed $review
     * @return ReviewCreator
     */
    public function setReview($review)
    {
        $this->review = $review;
        return $this;
    }

    /**
     * @param mixed $partner_id
     * @return ReviewCreator
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param mixed $customer_id
     * @return ReviewCreator
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param mixed $order_id
     * @return ReviewCreator
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    public function create()
    {
        return $this->reviewRepositoryInterface->createReview($this->makeReviewData());
    }

    private function makeReviewData() : array
    {
        $data = array();
        if(isset($this->customer_id)) $data['customer_id'] = $this->customer_id;
        if(isset($this->partner_id)) $data['partner_id'] = $this->partner_id;
        if(isset($this->review)) $data['review'] = $this->review;
        if(isset($this->review_images)) $data['review_images'] = $this->review_images;
        return $data + $this->modificationFields(true, false);
    }

}
