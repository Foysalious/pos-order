<?php namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Partner;
use App\Services\Order\Creator;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    public function store(Request $request, Creator $creator)
    {
        dd(1);
        $partner = Partner::find($partnerId);
        $creator->setPartner($partner)->setData($request->all());
        /*if ($error = $creator->hasDueError())
            return $error;*/

        $order = $creator->create();
    }

    public function index(Request $request)
    {
        dd(1);
    }

}
