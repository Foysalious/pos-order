<?php namespace App\Services\Customer;

use App\Interfaces\CustomerRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;

class CustomerService extends BaseService
{

    private $customer;

    public function __construct(private CustomerRepositoryInterface $customerRepository, private Updater $updater)
    {

    }

    public function update(string $customer_id,CustomerUpdateDto $updateDto): JsonResponse
    {
        $customerDetails = $this->customerRepository->find($customer_id);
        if (!$customerDetails) return $this->error('Customer Not Found', 404);
        //$this->updater->setPartner($request->name)->setEmail($request->email)->setPhone($request->phone)->setProfilePicture($request->picture)->setCustomer($customerDetails)->setCustomerId($customerDetails->id)->update();
        $this->customerRepository->update($customerDetails,$updateDto->toArray());
        return $this->success();
    }

    public function create(CustomerCreateDto $createDto): JsonResponse
    {

        $this->customerRepository->create($createDto->toArray());
        return $this->success();
    }
}

