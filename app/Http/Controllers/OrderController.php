<?php namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Partner;
use App\Services\Order\Creator;

class OrderController extends Controller
{
    public function store($partnerId,OrderRequest $request, Creator $creator)
    {
        $partner = Partner::find($partnerId);
        $creator->setPartner($partner)->setData($request->all());
        /*if ($error = $creator->hasDueError())
            return $error;*/

        $order = $creator->create();
    }

}
