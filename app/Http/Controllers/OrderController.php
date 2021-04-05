<?php namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Partner;
use App\Services\Order\Creator;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    use ResponseAPI;

    public function store($partner,Request $request, Creator $creator)
    {
        $creator->setPartner($partner)->setData($request->all());
        return $order = $creator->create();
    }



}
