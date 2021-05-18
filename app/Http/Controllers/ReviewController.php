<?php namespace App\Http\Controllers;

use App\Services\Order\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(Request $request, $product_id)
    {
        $rating = $request->rating;
        $orderBy = $request->order_by;
        return $this->reviewService->getProductReviews($request,$rating,$orderBy, $product_id);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *      path="/api/v1/customers/{customer}/orders/{order}/review",
     *      operationId="creatingreview",
     *      tags={"REVIEW CREATE API"},
     *      summary="To create a review of an order",
     *      description="creating review of an order",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\Schema (ref="{}")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function store(Request $request, $customer_id, $order_id)
    {
        return $this->reviewService->create($request, $customer_id, $order_id);
    }
}