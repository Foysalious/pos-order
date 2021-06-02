<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderCustomerRequest;
use App\Services\Order\orderCustomerService;

class OrderCustomerController extends Controller
{
    protected $orderCustomerService;

    public function __construct(orderCustomerService $orderCustomerService)
    {
        $this->orderCustomerService = $orderCustomerService;
    }

    public function update(OrderCustomerRequest $request, $partner_id, $order_id)
    {
        return $this->orderCustomerService->update($request->validated()['customer_id'], $partner_id, $order_id);
    }
}
