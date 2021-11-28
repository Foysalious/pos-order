<?php namespace App\Http\Controllers;

use App\Exceptions\OrderException;
use App\Http\Reports\InvoiceService;
use App\Http\Requests\DeliveryStatusUpdateIpnRequest;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderCustomerRequest;
use App\Http\Requests\OrderFilterRequest;
use App\Http\Requests\OrderStatusUpdateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use Illuminate\Validation\ValidationException;


class OrderController extends Controller
{
    /**
     * @OA\Info(title="POS-ORDER PROJECTS API", version="1")
     * It's required to generate swagger doc
     */
    use ResponseAPI;

    protected $orderService;
    private InvoiceService $invoiceService;

    public function __construct(OrderService $orderService, InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
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
     *      @OA\Parameter(name="partner",description="partner ID",required=false,in="path", @OA\Schema(type="Integer")),
     *      @OA\Parameter(name="payment_status",description="Payment Status query string either 'paid' or 'due'",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="order_status",description="Order Status query strings are (Pending | Processing | Declined | Shipped | Completed | Cancelled)",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="type",description="type query strings are (completed | new | running)",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="sales_channel_id",description="Sales Channel ID 1 or 2",required=false,in="query", @OA\Schema(type="Integer")),
     *      @OA\Parameter(name="type",description="Type",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="q",description="search query customer_name or mobile or partner-wise order-id",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="limit",description="limit range",required=false,in="query", @OA\Schema(type="Integer")),
     *      @OA\Parameter(name="offset",description="offset value",required=false,in="query", @OA\Schema(type="Integer")),
     *      @OA\Parameter(name="sort_by",description="sort by variable either created_at or customer_name",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="sort_by_order",description="order of the sort by 'asc' or 'desc'",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              example={"message": "Successful", "orderList": {{ "id": 2000038, "previous_order_id": null, "partner_wise_order_id": 21, "customer_id": 1, "status": "Completed", "sales_channel_id": 1, "emi_month": null, "interest": null, "delivery_charge": "0.00", "bank_transaction_charge": null, "delivery_name": "", "delivery_mobile": "", "delivery_address": "", "note": null, "voucher_id": null, "payment_status": null }, { "id": 2000037, "previous_order_id": null, "partner_wise_order_id": 20, "customer_id": 1, "status": "Completed", "sales_channel_id": 1, "emi_month": null, "interest": null, "delivery_charge": "0.00", "bank_transaction_charge": null, "delivery_name": "", "delivery_mobile": "", "delivery_address": "", "note": null, "voucher_id": null, "payment_status": null }} }
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
    public function index(int $partner_id, OrderFilterRequest $request)
    {
        return $this->orderService->getOrderList($partner_id, $request);
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */

    /**
     * @OA\Get(
     *      path="/api/v1/customers/{customer_id}/orders",
     *      operationId="getReviewsofCustomer",
     *      tags={"Customer API"},
     *      summary="Api to get all customer specific orders",
     *      description="Return all Customer specific orders",
     *      @OA\Parameter(name="customer_id",description="Customer Id",required=false,in="path", @OA\Schema(type="String")),
     *      @OA\Parameter(name="filter",description="created_at",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="order",description="asc or desc",required=false,in="query", @OA\Schema(type="String")),
     *      @OA\Parameter(name="limit",description="limit",required=false,in="query", @OA\Schema(type="Integer")),
     *      @OA\Parameter(name="offset",description="offset",required=false,in="query", @OA\Schema(type="Integer")),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *             example={"message": "Successful","orders": {{"id": 2000001,"status": "Cancelled","date": "20,Apr,2021","price": "0.00"}}}
     *          )),
     *      @OA\Response(
     *          response=404,
     *          description="Message: অর্ডারটি পাওয়া যায় নি ",
     *      )
     *     )
     */
    public function getCustomerOrderList(string $customer_id, Request $request)
    {

        $request->validate([
            'filter' => 'sometimes|in:created_at',
            'order' => 'sometimes|in:asc,desc',
            'limit' => 'sometimes|digits_between:1,4',
            'offset' => 'sometimes|digits_between:1,4,'
        ]);
        return $this->orderService->getCustomerOrderList($customer_id, $request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/partners/{partner}/orders",
     *     summary="Place an order",
     *     tags={"ORDER API"},
     *     @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  example={
     *                      "customer_id":1,
     *                      "paid_amount":100,
     *                      "sales_channel_id":1,
     *                      "skus": "[{'id':523,'product_name':'Shirt','product_id':1000328,'warranty':null,;warranty_unit':null,'vat_percentage':null,'sku_channel_id':1062,'channel_id':1,'channel_name':'pos','price':100,'unit':'kg','quantity':5,'discount':5,'is_discount_percentage':0,'cap':null,'combination':[{'option_id':799,'option_name':'size','option_value_id':1572,'option_value_name':'l','option_value_details':[{'code':'L','type':'size'}]},{'option_id':800,'option_name':'color','option_value_id':1573,'option_value_name':'green','option_value_details':[{'code':'#000000','type':'color'}]}]}]",
     *                      "discount": "{'original_amount':10,'is_percentage':1,'cap':70,'discount_details':'Order discount details','discount_id':null,'item_id':null'}",
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
     * @throws ValidationException|OrderException
     */
    public function store($partner, OrderCreateRequest $request): JsonResponse
    {
        return $this->orderService->store($partner, $request);
    }

    public function updateStatus($partner_id, $order_id, OrderStatusUpdateRequest $request)
    {
        return $this->orderService->updateOrderStatus($partner_id, $order_id, $request);
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
    public function show($partner_id, $order_id): JsonResponse
    {
        return $this->orderService->getOrderDetails($partner_id, $order_id);
    }

    /**
     *
     * @param OrderUpdateRequest $request
     * @param int $partner_id
     * @param int $order_id
     * @return JsonResponse
     */

    /**
     * @OA\Put(
     *     path="/api/v1/partners/{partner}/orders/{order}",
     *     tags={"ORDER API"},
     *     summary="Order update request",
     *     description="Order update under a specific partner",
     *     @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="order", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *          @OA\MediaType(mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="sales_channel_id", type="integer", example=1),
     *                  @OA\Property(property="skus", type="string"),
     *                  @OA\Property(property="emi_month", type="string"),
     *                  @OA\Property(property="interest", type="integer"),
     *                  @OA\Property(property="delivery_charge", type="integer"),
     *                  @OA\Property(property="bank_transaction_charge", type="integer"),
     *                  @OA\Property(property="delivery_name", type="string"),
     *                  @OA\Property(property="delivery_mobile", type="number"),
     *                  @OA\Property(property="delivery_address", type="string"),
     *                  @OA\Property(property="note", type="string"),
     *                  @OA\Property(property="voucher_id", type="integer"),
     *                  @OA\Property(property="discount", type="json", example={"type":"order","original_amount":10,"is_percentage":1,"cap":70,"discount_details":"Order discount details","discount_id":null,"item_id":null})
     *             )
     *         )
     *      ),
     *     @OA\Response(response="200", description="Successful"),
     *     @OA\Response(response="403", description="You're not authorized to access this order"),
     * )
     */

    public function update(OrderUpdateRequest $request, $partner_id, $order_id)
    {
        return $this->orderService->update($request, $partner_id, $order_id);
    }

    public function getOrderInfoForPaymentLink($order_id): JsonResponse
    {
        return $this->orderService->getOrderInfoForPaymentLink($order_id);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/partners/{partner}/orders/{order}/update-customer",
     *     tags={"ORDER API"},
     *     summary="Order customer update request",
     *     description="Order customer update under a specific partner",
     *     @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="order", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *          @OA\MediaType(mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="customer_id", type="integer")
     *             )
     *         )
     *      ),
     *     @OA\Response(response="200", description="Successful"),
     *     @OA\Response(response="403", description="কাস্টমার পরিবর্তন করতে পারবেন না"),
     * )
     */
    public function updateCustomer(OrderCustomerRequest $request, $partner_id, $order_id)
    {
        return $this->orderService->updateCustomer($request->validated()['customer_id'], $partner_id, $order_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $partner_id
     * @param int $id
     * @return JsonResponse
     */

    /**
     * @OA\Delete(
     *     path="/api/v1/partners/{partner}/orders/{order}",
     *     tags={"ORDER API"},
     *     summary="Order delete request",
     *     description="Order delete under a specific partner",
     *     @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="order", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Successful"),
     *     @OA\Response(response="403", description="অর্ডারটি পাওয়া যায় নি"),
     * )
     */
    public function destroy($partner_id, $id)
    {
        return $this->orderService->delete($partner_id, $id);
    }

    /**
     * * @OA\Get(
     *      path="/api/v1/partners/{partner}/orders/{order}/delivery-info",
     *      operationId="getOrderDeliveryDetail",
     *      tags={"ORDER API"},
     *      summary="Get an order delivery details",
     *      description="Return orders delivery details",
     *      @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="order", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(
     *          type="object",
     *          example={"message":"Successful","order":{"id":2000001,"delivery_name":"Arnab Rahman","delivery_address":"Mohakhali","delivery_mobile":"01818181818","delivery_vendor_name":"paperfly","delivery_request_id":"ORD-1620719460-0027","delivery_thana":"Hajiganj","delivery_district":"Chandpur","payment_method":"cod","due":0}}
     *          ),
     *     ),
     *      @OA\Response(response=404, description="message: অর্ডারটি পাওয়া যায় নি"),
     *      @OA\Response(response=403, description="You're not authorized to access this order")
     *  )
     *
     * @param int $partner_id
     * @param int $order_id
     * @return JsonResponse
     */
    public function getDeliveryInfo(int $partner_id, int $order_id): JsonResponse
    {
        return $this->orderService->getDeliveryInfo($partner_id, $order_id);
    }

    /**
     * * @OA\Get(
     *      path="/api/v1/webstore/orders/{order_id}/generate-invoice",
     *      operationId="getWebstoreOrderInvoice",
     *      tags={"ORDER API"},
     *      summary="Get an order invoice",
     *      description="Return invoice",
     *      @OA\Parameter(name="order_id", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(
     *          type="object",
     *          example={"message":"Successful",  "invoice": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/invoices/pdf/20210810_pos_order_invoice_2001022_report_1628597035.pdf"}
     *          ),
     *     ),
     *      @OA\Response(response=404, description="message: Order Not Found")
     *  )
     *
     * @param int $order_id
     * @return JsonResponse
     */
    public function getWebstoreOrderinvoice(int $order_id)
    {
        return $this->orderService->getWebsotreOrderInvoice($order_id);
    }

    /**
     * * @OA\Get(
     *      path="/api/v1/partners/{partner}/orders/{order_id}/generate-invoice",
     *      operationId="getOrderInvoice",
     *      tags={"ORDER API"},
     *      summary="Get an order invoice",
     *      description="Return invoice",
     *      @OA\Parameter(name="order_id", description="order id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation",
     *          @OA\JsonContent(
     *          type="object",
     *          example={"message":"Successful",  "invoice": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/invoices/pdf/20210810_pos_order_invoice_2001022_report_1628597035.pdf"}
     *          ),
     *     ),
     *      @OA\Response(response=404, description="message: Order Not Found")
     *  )
     *
     * @param int $order_id
     * @return JsonResponse
     */
    public function getOrderinvoice(int $partner_id, int $order_id)
    {
        return $this->orderService->getOrderInvoice($partner_id, $order_id);
    }


    /**
     * @OA\Put(
     *     path="/api/v1/partners/{partner}/delivery_req_id/{delivery_req_id}/update-status",
     *     tags={"ORDER API"},
     *     summary="Order status update by IPN",
     *     description="Order update under a specific partner",
     *     @OA\Parameter(name="partner", description="partner id", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="delivery_req_id", description="order delivery request id", required=true, in="path", @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *          @OA\MediaType(mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="status", type="string", example="Completed"),
     *                  )
     *         )
     *      ),
     *     @OA\Response(response="200", description="Successful"),
     *     @OA\Response(response="404", description="No Order Found"),
     * )
     *
     * @param int $partner_id
     * @param string $delivery_req_id
     * @param DeliveryStatusUpdateIpnRequest $request
     * @return JsonResponse
     */
    public function updateOrderStatusForIpn(int $partner_id, DeliveryStatusUpdateIpnRequest $request): JsonResponse
    {
        return $this->orderService->updateOrderStatusByIpn($partner_id, $request);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/partners/{partner}/orders/{order_id}/logs",
     *      tags={"ORDER API"},
     *      summary="Order Log List",
     *      description="Order Log List",
     *      @OA\Parameter(name="partner",description="partner Id",required=true,in="path", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="order_id",description="Order Id",required=true,in="path", @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Message: No Logs found ",
     *      ),
     *         @OA\Response(
     *          response=500,
     *          description="Message: Internal Server Errorl ",
     *      )
     *     )
     */
    public function logs(int $partner_id, int $order_id)
    {
        return $this->orderService->logs($partner_id, $order_id);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/partners/{partner}/trending-products",
     *      operationId="getCustomerTrendingProduct",
     *      tags={"Trending Product List"},
     *      summary="Trending Product List",
     *      description="Trending Product List",
     *      @OA\Parameter(name="partner",description="partner Id",required=true,in="path", @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *             example={"message": "Successful","data":
     *     {{"id": 1002041,
     *      "category_id": 10048,
     *      "category_name": "Master Category 2",
     *      "sub_category_id": 10049,
     *      "sub_category_name": "Others",
     *      "collection_id": null,
     *      "name": "Test Product Variation",
     *      "description": "null",
     *      "vat_percentage": 1,
     *      "unit": {
     *      "id": 2,
     *      "name_bn": "স্কয়ার ফিট",
     *      "name_en": "sft"
     *      },
     *     "stock": 20,
     *     "rating": null,
     *     "rating_count": null,
     *     "app_thumb": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/pos/services/thumbs/default.jpg",
     *     "warranty": 1,
     *     "warranty_unit": "month",
     *     "image_gallery": {},
     *     "variations": {}
     *     }}}
     *          )),
     *      @OA\Response(
     *          response=404,
     *          description="Message: No product found ",
     *      ),
     *         @OA\Response(
     *          response=500,
     *          description="Message: Internal Server Errorl ",
     *      )
     *     )
     */
    public function getTrendingProducts($partner_id)
    {
        return $this->orderService->getTrendingProducts($partner_id);
    }

    public function generateLogInvoice(int $partner_id, int $order, int $log): JsonResponse
    {
        return $this->orderService->generateLogInvoice($partner_id, $order, $log);
    }

    /**
     * * @OA\Get(
     *      path="/api/v1/filters",
     *      operationId="filters",
     *      tags={"ORDER API"},
     *      summary="Get all filters",
     *      description="Return filters",
     *      @OA\Response(response=200, description="Successful",
     *          @OA\JsonContent(
     *          type="object",
     *          example={"message":"Successful",  "invoice": "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/invoices/pdf/20210810_pos_order_invoice_2001022_report_1628597035.pdf"}
     *          ),
     *     ),
     *      @OA\Response(response=404, description="message: Order Not Found")
     *  )
     *
     * @return JsonResponse
     */
    public function getFilteringOptions(): JsonResponse
    {
        return $this->orderService->getAllFilteringOptions();
    }

    public function sendEmail($partner, $order)
    {
        return $this->orderService->sendEmail($partner, $order);
    }
}
