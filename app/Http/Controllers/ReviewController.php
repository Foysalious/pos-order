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
        return $this->reviewService->getProductReviews($request, $rating, $orderBy, $product_id);
    }

    public function getCustomerReviewList($customer_id,Request $request)
    {
       return $this->reviewService->getCustomerReviews($customer_id,$request);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
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
     *      @OA\Parameter(name="customer",description="Customer ID",required=true,in="path", @OA\Schema(type="BigInteger")),
     *      @OA\Parameter(name="order",description="Order ID",required=true,in="path", @OA\Schema(type="BigInteger")),
     *      @OA\RequestBody(
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="review[0]", type="JSON"),
     *                  @OA\Property(property="review_images[0][0]", type="file"),
     *                  required={"review[0]", "review_images[0][0]"}
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful"
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="অর্ডারটি পাওয়া যায় নি",
     *          @OA\JsonContent(ref="")
     *      )
     *     )
     */
    public function store(Request $request, $customer_id, $order_id)
    {
        return $this->reviewService->create($request, (int)$customer_id, (int)$order_id);
    }
}
