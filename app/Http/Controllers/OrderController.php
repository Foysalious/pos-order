<?php namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Http\Requests\OrderFilterRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\Order\Creator;
use App\Services\Order\StatusChanger;
use App\Traits\ResponseAPI;



class OrderController extends Controller
{
    use ResponseAPI;
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @OA\Get(
     *      path="/api/v1/partners/{partner}/orders",
     *      operationId="getOrders",
     *      tags={"ORDER LIST API"},
     *      summary="Api to get all orders",
     *      description="Return all orders with searching and filtering parameters",
     *      @OA\Parameter(name="payment_status",description="Payment Status",required=false,in="path", @OA\Schema(type="String")),
     *      @OA\Parameter(name="order_status",description="Order Status",required=false,in="path", @OA\Schema(type="String")),
     *      @OA\Parameter(name="customer_name",description="Customer Name",required=false,in="path", @OA\Schema(type="String")),
     *      @OA\Parameter(name="order_id",description="Order ID",required=false,in="path", @OA\Schema(type="Integer")),
     *      @OA\Parameter(name="sales_channel_id",description="Sales Channel ID",required=false,in="path", @OA\Schema(type="IntegerInteger")),
     *      @OA\Parameter(name="type",description="Type",required=false,in="path", @OA\Schema(type="String", example={"new": "running", "pending": "Processing Shipped", "completed": "Completed Cancelled Declined"})),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object", example={
     *              "message": "Successful",
     *              "orderList": {{
     *                  "id": 2000038,
     *                  "previous_order_id": null,
     *                  "partner_wise_order_id": 21,
     *                  "customer_id": 1,
     *                  "status": "Completed",
     *                  "sales_channel_id": 1,
     *                  "emi_month": null,
     *                  "interest": null,
     *                  "delivery_charge": "0.00",
     *                  "bank_transaction_charge": null,
     *                  "delivery_name": "",
     *                  "delivery_mobile": "",
     *                  "delivery_address": "",
     *                  "note": null,
     *                  "voucher_id": null,
     *                  "payment_status": null
     *               },
     *               {
     *                  "id": 2000037,
     *                  "previous_order_id": null,
     *                  "partner_wise_order_id": 20,
     *                  "customer_id": 1,
     *                  "status": "Completed",
     *                  "sales_channel_id": 1,
     *                  "emi_month": null,
     *                  "interest": null,
     *                  "delivery_charge": "0.00",
     *                  "bank_transaction_charge": null,
     *                  "delivery_name": "",
     *                  "delivery_mobile": "",
     *                  "delivery_address": "",
     *                  "note": null,
     *                  "voucher_id": null,
     *                  "payment_status": null
     *               }
     *               }
     *              }
     *          )),
     *      @OA\Response(
     *          response=404,
     *          description="Message: অর্ডারটি পাওয়া যায় নি ",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index($partner_id, OrderFilterRequest $request)
    {
        return $this->orderService->getOrderList($partner_id, $request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($partner,Request $request, Creator $creator)
    {
        $creator->setPartner($partner)->setData($request->all());
        return $creator->create();
    }

    public function updateStatus($partner,Request $request,StatusChanger $statusChanger)
    {
        $order = Order::find($request->order);
        return $statusChanger->setOrder($order)->setStatus($request->status)->changeStatus();

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($partner_id, $order_id)
    {
        return $this->orderService->getOrderDetails($partner_id, $order_id);
    }

    /**
     * Update the specified resource in storage.
     * @param $partner_id
     * @param OrderUpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $partner_id, $id)
    {
        return $this->orderService->update($request, $partner_id, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $partner_id
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($partner_id, $id)
    {
        return $this->orderService->delete($partner_id, $id);
    }

    public function getOrderWithChannel($order_id)
    {
        return $this->orderService->getOrderWithChannel($order_id);
    }
}
