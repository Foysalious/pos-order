<?php namespace App\Http\Controllers\Webstore;


use App\Http\Controllers\Controller;
use App\Services\Webstore\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{

    private ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }
    public function index(Request $request)
    {
        $rating = $request->rating;
        $orderBy = $request->order_by;
        $product_ids = json_decode($request->products,true);
        return $this->reviewService->getProductReviews($request, $rating, $orderBy, $product_ids);
    }

    public function getCustomerReviewList(int $partner_id,string $customer_id, Request $request)
    {
        $request->validate([
            'order' => 'sometimes|in:asc,desc',
            'limit' => 'sometimes|digits_between:1,4',
            'offset' => 'sometimes|digits_between:1,4,'
        ]);
        return $this->reviewService->getCustomerReviews($customer_id, $request);
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
     *      @OA\Parameter(name="customer",description="Customer ID",required=true,in="path", @OA\Schema(type="string")),
     *      @OA\Parameter(name="order",description="Order ID",required=true,in="path", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          @OA\MediaType(mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="review[0]", type="JSON"),
     *                  @OA\Property(property="review_images[0][0]", type="file"),
     *                  required={"review[0]"}
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
        return $this->reviewService->create($request, $customer_id, (int)$order_id);
    }

    public function getProductIdsByRating($partner_id,Request $request)
    {
        return $this->reviewService->setPartnerId($partner_id)->setRatings($request->ratings)->getProductIdsByRating();
    }
}
