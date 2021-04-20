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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $order_id)
    {
        return $this->reviewService->create($request, $order_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $orderReview
     * @return \Illuminate\Http\Response
     */
    public function show(Review $orderReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $orderReview
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Review $orderReview)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $orderReview
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $orderReview)
    {
        //
    }
}
