<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\Order\ReviewService;
use Illuminate\Http\Request;

class OrderReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(Request $request, $product_id)
    {
        return $this->reviewService->setRating($request->rating)->setOrder($request->order)->getProductReviews($request, $product_id);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $customer_id, $order_id)
    {
        return $this->reviewService->create($request, $customer_id, $order_id);
    }
}
