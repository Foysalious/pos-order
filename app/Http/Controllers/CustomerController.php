<?php namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\Customer\Creator;


class CustomerController extends Controller
{
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function store(Creator $creator, CustomerRequest $request)
    {
        $creator->setPartner($request->name)->setEmail($request->email)->setPhone($request->phone)->setProfilePicture($request->picture);
        return $creator->create();
    }

}
