<?php namespace App\Http\Controllers;

use App\Exceptions\ProductNotFoundException;
use App\Http\Requests\CustomerRequest;
use App\Services\Customer\CustomerCreateDto;
use App\Services\Customer\CustomerService;
use App\Services\Customer\CustomerUpdateDto;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class CustomerController extends Controller
{
    use ResponseAPI;

    /**
     * @var CustomerService
     */
    private CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Store Customer.
     *
     * @param CustomerRequest $request
     *
     * @return JsonResponse
     * @throws UnknownProperties
     * @OA\Post(
     *      path="/api/v1/customers",
     *      operationId="creatingcustomer",
     *      tags={"Customer API"},
     *      summary="To create a Customer ",
     *      description="creating customer",
     *     @OA\RequestBody(
     *     @OA\MediaType(mediaType="multipart/form-data",
     *      @OA\Schema(
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="mobile", type="string"),
     *       @OA\Property(property="pro_pic", type="string"),
     *
     *          )
     * )
     * ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful",
     *                @OA\JsonContent(
     *          type="object",
     *          example={
     *          "message": "Successful",
     *              }
     *        )
     *       ),
     *     )
     */


    public function store(CustomerRequest $request): JsonResponse
    {
        $customer = new CustomerCreateDto([
            'id' => $request->id,
            'name' => $request->name,
            'email' => $request->email,
            'partner_id' => $request->partner_id,
            'mobile' => $request->mobile,
            'pro_pic' => $request->pro_pic,
        ]);
        return $this->customerService->create($customer);
    }

    /**
     * Update Customer.
     *
     * @param Request $request
     * @param string $customer_id
     * @return JsonResponse
     * @throws UnknownProperties
     * @OA\Post(
     *      path="/api/v1/customers/{customer_id}",
     *      operationId="updatingcustomer",
     *      tags={"Customer API"},
     *      summary="To update a Customer ",
     *      description="update customer",
     *     @OA\Parameter(name="customer_id", description="customer id", required=true, in="path", @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *     @OA\MediaType(mediaType="multipart/form-data",
     *      @OA\Schema(
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="mobile", type="string"),
     *       @OA\Property(property="pro_pic", type="string"),
     *
     *          )
     * )
     * ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful",
     *                @OA\JsonContent(
     *          type="object",
     *          example={
     *          "message": "Successful",
     *              }
     *        )
     *       ),
     *     )
     */
    public function update(Request $request, string $customer_id)
    {
        $customer = new CustomerUpdateDto([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'pro_pic' => $request->pro_pic,
        ]);
        return $this->customerService->update($customer_id, $customer);
    }

    /**
     *
     * * @OA\Get(
     *      path="/api/v1/customers/{customer_id}/not-rated-order-sku-list",
     *      operationId="getNotRatedOrderSKUList",
     *      tags={"Partners Products Which is not Rated"},
     *      summary="Get Products List for POS by Partner",
     *      description="",
     *      @OA\Parameter(name="customer_id", description="customer_id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="offset", description="pagination offset", required=false, in="query", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="limit", description="pagination limit", required=false, in="query", @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(
     *          type="object",
     *          example={
     *                "message": "Successful",
     *     "not_rated_skus": {
     *           {
     *   "id": 163,
     *           "name": "Shirt",
     *          "order_id": 2000001,
     *         "details": {
     *        "id": 908,
     *         "name": "l-green-cotton",
     *        "unit": "kg",
     *         "price": 100,
     *         "quantity": 5,
     *        "channel_id": 1,
     *         "product_id": 1000328,
     *         "combination": {
     *          {
     *          "option_id": 799,
     *          "option_name": "size",
     *          "option_value_id": 1572,
     *          "option_value_name": "l",
     *          "option_value_details": {
     *          {
     *          "code": "L",
     *          "type": "size"
     *          }
     *          }
     *          },
     *          {
     *          "option_id": 800,
     *         "option_name": "color",
     *           "option_value_id": 1573,
     *           "option_value_name": "green",
     *           "option_value_details": {
     *          {
     *           "code": "#000000",
     *           "type": "color"
     *           }
     *           }
     *           }
     *           },
     *           "channel_name": "pos",
     *           "product_name": "Shirt",
     *           "warranty_unit": null,
     *           "sku_channel_id": 1062,
     *           "vat_percentage": null
     *           }
     *           }
     *           }
     *           },
     *       ),
     *      ),
     *      @OA\Response(response=404, description="message: স্টকে কোন পণ্য নেই! প্রয়োজনীয় তথ্য দিয়ে স্টকে পণ্য যোগ করুন।"),
     *      @OA\Response(response=403, description="Forbidden")
     *     )
     * @param Request $request
     * @param int $customer_id
     * @return JsonResponse
     */

    public function notRatedOrderSkuList(Request $request, int $customer_id): JsonResponse
    {
        return $this->customerService->getNotRatedOrderSkuList($customer_id, $request);
    }

    /**
     * Get customers order amount and promo used
     *
     * @param string $customer_id
     * @return JsonResponse
     *
     * @OA\GET(
     *     path="/api/v1/customers/{customer}/purchase-amount-promo-usage",
     *     tags={"Customer API"},
     *     summary="To get a Customer's total purchase amount and used promo",
     *     description="Delete customer and related orders",
     *     @OA\Parameter(name="customer", description="customer id", required=true, in="path", @OA\Schema(type="string")),
     *     @OA\Response(response="200", description="Successful"),
     *     @OA\Response(response="404", description="Customer Not Found"),
     * )
     */
    public function getPurchaseAmountAndPromoUsed(int $partner_id, string $customer_id): JsonResponse
    {
        return $this->customerService->getPuchaseAmountAndPromoUsed($partner_id,$customer_id);
    }


    public function getOrdersByDateWise(int $partner_id,string $customer_id)
    {
        return $this->customerService->getOrdersByDateWise($partner_id,$customer_id);
    }

    /**
     * Delete customer
     *
     * @param $customer_id
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/v1/customers/{customer}",
     *     tags={"Customer API"},
     *     summary="To Delete a Customer",
     *     description="Delete customer and related orders",
     *     @OA\Parameter(name="customer", description="customer id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Successful"),
     *     @OA\Response(response="403", description="Customer Not Found"),
     * )
     */
    public function destroy($customer_id)
    {
        return $this->customerService->delete($customer_id);
    }

}
