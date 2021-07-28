<?php namespace App\Http\Controllers\Webstore;

use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use App\Traits\ResponseAPI;
use App\Http\Controllers\Controller;

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
     * * @OA\Get(
     *      path="/api/v1/webstore/partners/{partner}/orders/{order}",
     *      operationId="getOrderDetail",
     *      tags={"ORDER API"},
     *      summary="Get an order details",
     *      description="Return all orders with searching parameters",
     *      @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="order", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(
     *          type="object",
     *          example={"message":"Successful","order":{"id":2000017,"previous_order_id":null,"partner_wise_order_id":8,"customer_id":1,"status":"Completed","sales_channel_id":1,"emi_month":null,"interest":null,"bank_transaction_charge":null,"delivery_name":"","delivery_mobile":"","delivery_address":"","note":null,"voucher_id":null,"items":{{"id":148,"name":"","sku_id":521,"details":null,"quantity":"1.00","unit_price":"100.00","unit":null,"vat_percentage":null,"warranty":0,"warranty_unit":"day","note":null},{"id":149,"name":"","sku_id":819,"details":null,"quantity":"1.00","unit_price":"80.00","unit":null,"vat_percentage":null,"warranty":0,"warranty_unit":"day","note":null},{"id":150,"name":"","sku_id":null,"details":null,"quantity":"2.00","unit_price":"50.00","unit":null,"vat_percentage":null,"warranty":0,"warranty_unit":"day","note":null}},"price_info":{"delivery_charge":"30.00","promo":"50.00","total_price":"280.00","total_bill":"280.00","discount_amount":null,"due_amount":"160.00","paid_amount":100,"total_item_discount":"0.00","total_vat":"0.00"},"customer_info":{"name":"Foysal","mobile":"01855570816","pro_pic":"https:\/\/s3.ap-south-1.amazonaws.com\/cdn-shebadev\/images\/pos\/categories\/thumbs\/1621499030_phpvv7lc4_category_thumb.png"},"payment_info":null}}
     *          ),
     *     ),
     *      @OA\Response(response=404, description="message: অর্ডারটি পাওয়া যায় নি"),
     *      @OA\Response(response=403, description="You're not authorized to access this order")
     *  )
     *
     * @param $partner_id
     * @param $order_id
     * @return JsonResponse
     */
    public function show($partner_id, $order_id,$customer_id)
    {
        return $this->orderService->getWebStoreOrderDetails($partner_id, $order_id,$customer_id);
    }


}
