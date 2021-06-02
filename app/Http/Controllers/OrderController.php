<?php namespace App\Http\Controllers;

use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderFilterRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\Order\Creator;
use App\Services\Order\StatusChanger;
use App\Traits\ResponseAPI;
use Illuminate\Validation\ValidationException;


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
     * @return JsonResponse
     */

    /**
     * @OA\Get(
     *      path="/api/v1/partners/{partner}/orders",
     *      operationId="getOrders",
     *      tags={"ORDER API"},
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
     * @OA\Post(
     *     path="/api/v1/partners/{partner}/orders",
     *     summary="Place an order",
     *     tags={"ORDER API"},
     *     @OA\Parameter(name="partner_id",description="Partner Id",required=true,in="path", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  example={
     *                      "customer_id":1,
     *                      "paid_amount":100,
     *                      "sales_channel_id":1,
     *                      "skus": "[{'id':523,'product_name':'Shirt','product_id':1000328,'warranty':null,;warranty_unit':null,'vat_percentage':null,'sku_channel_id':1062,'channel_id':1,'channel_name':'pos','price':100,'unit':'kg','quantity':5,'discount':5,'is_discount_percentage':0,'cap':null,'combination':[{'option_id':799,'option_name':'size','option_value_id':1572,'option_value_name':'l','option_value_details':[{'code':'L','type':'size'}]},{'option_id':800,'option_name':'color','option_value_id':1573,'option_value_name':'green','option_value_details':[{'code':'#000000','type':'color'}]}]}]",
     *                      "discount":5,
     *                      "is_discount_percentage":0,
     *                      "payment_method":"payment_link",
     *                      "payment_link_amount":10,
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful",
     *          @OA\JsonContent(
     *          type="object",
     *          example={
     *              "message": "Successful",
     *              "order": {
     *                  "id": 2000175
     *              },
     *              "payment": {
     *                  "link": "https://pl.dev-sheba.xyz/@PartnerDevox0c8oey"
     *              }
     *          }
     *       ),
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Message: Customer Not Found ",
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     *
     * @param $partner
     * @param OrderCreateRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store($partner, OrderCreateRequest $request)
    {
        return $this->orderService->store($partner, $request);
    }

    public function updateStatus($partner, Request $request, StatusChanger $statusChanger)
    {
        $order = Order::find($request->order);
        return $statusChanger->setOrder($order)->setStatus($request->status)->changeStatus();

    }

    /**
     * * @OA\Get(
     *      path="/api/v1/partners/{partner}/orders/{order}",
     *      operationId="getOrderDetail",
     *      tags={"ORDER API"},
     *      summary="Get an order details",
     *      description="Return all orders with searching parameters",
     *      @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="order", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="")),
     *      @OA\Response(response=404, description="message: কঅর্ডারটি পাওয়া যায় নি"),
     *      @OA\Response(response=403, description="Forbidden")
     *     )
     *
     * @param int $id
     * @return JsonResponse
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
     * @return JsonResponse
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
     * @return JsonResponse
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
