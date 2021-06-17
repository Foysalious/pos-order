<?php namespace App\Http\Controllers;

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
     * @param \Illuminate\Http\Request $request
     * @param Request $request
     *
     * @return JsonResponse
     * @return JsonResponse
     *
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
     * @throws UnknownProperties
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
     * @param \Illuminate\Http\Request $request
     * @param Request $request
     *
     * @return JsonResponse
     * @return JsonResponse
     *
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
     * @throws UnknownProperties
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

    public function notRatedOrderSkuList(Request $request, $customer_id)
    {
       return $this->customerService->getNotRatedOrderSkuList($customer_id);
    }

}
