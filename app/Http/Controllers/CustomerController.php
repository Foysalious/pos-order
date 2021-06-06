<?php namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Services\Customer\CustomerService;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\Customer\Creator;



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
     *      path="/api/v1/customer",
     *      operationId="creatingcustomer",
     *      tags={"Customer API"},
     *      summary="To create a Customer ",
     *      description="creating customer",
     *      @OA\Parameter(name="name",description="Customer Name",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="email",description="Customer email",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="phone",description="Customer phone",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="picture",description="Customer picture",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Response(
     *          response=201,
     *          description="Successful",
     *                @OA\JsonContent(
     *          type="object",
     *          example={
     *          "message": "Successful",
     *                    "order": {
     *                   "name": "string",
     *                   "email": "email",
     *                   "phone": 01888888888,
     *                   "pro_pic": "image.png",
     *                   "updated_at": "Date",
     *                   "created_at": "Date",
     *                   "id": 2,
     *              }
     *          }
     *        )
     *       ),
     *     )
     */


    public function store(Creator $creator, CustomerRequest $request)
    {
        $creator->setPartner($request->name)->setEmail($request->email)->setPhone($request->phone)->setProfilePicture($request->picture);
        return $creator->create();
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
     *      path="/api/v1/customer/{customer_id}",
     *      operationId="updatingcustomer",
     *      tags={"Customer API"},
     *      summary="To update a Customer ",
     *      description="updating customer",
     *      @OA\Parameter(name="customer_id",description="Customer id",required=false,in="path", @OA\Schema(type="Integer")),
     *      @OA\Parameter(name="name",description="Customer Name",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="email",description="Customer email",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="phone",description="Customer phone",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="picture",description="Customer picture",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Response(
     *          response=201,
     *          description="Successful",
     *                @OA\JsonContent(
     *          type="object",
     *          example={
     *          "message": "Successful",
     *          }
     *        )
     *       ),
     *     )
     */
    public function update(Request $request,int $customer_id )
    {
        $this->customerService->update($request, $customer_id);
        return $this->success('Successful', null, 201, true);
    }

}
