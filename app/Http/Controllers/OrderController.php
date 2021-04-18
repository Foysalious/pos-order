<?php namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\Order\Creator;
use App\Services\Order\StatusChanger;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    use ResponseAPI;

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



}
