<?php namespace App\Http\Controllers;

use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\Customer\Creator;

use App\Http\Requests\OrderFilterRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Services\Order\OrderService;

use App\Http\Requests\OrderRequest;
use App\Models\Order;

use App\Services\Order\StatusChanger;
use App\Traits\ResponseAPI;

class CustomerController extends Controller
{
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function store(Request $request, Creator $creator)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'picture' => 'required',
        ]);
        $creator->setPartner($request->name)->setEmail($request->email)->setPhone($request->phone)->setProfilePicture($request->picture);
        return $creator->create();
    }

}
