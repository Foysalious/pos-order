<?php namespace App\Http\Controllers;

namespace App\Http\Controllers;

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
     *      description="Return all orders with searching parameters",
     *      @OA\Response(
     *          response=200,
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
    public function index($partner_id, Request $request)
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
