<?php namespace App\Services\Customer;

use App\Interfaces\CustomerRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use App\Traits\ModificationFields;

class CustomerService extends BaseService
{
    use ModificationFields;
    public function __construct(private CustomerRepositoryInterface $customerRepository, private Updater $updater)
    {

    }


    public function update(string $customer_id, CustomerUpdateDto $updateDto): JsonResponse
    {

        $customerDetails = $this->customerRepository->find($customer_id);
        if (!$customerDetails) return $this->error('Customer Not Found', 404);
        $this->customerRepository->update($customerDetails, $this->makeData($updateDto));
        return $this->success();
    }

    public function makeData($updateDto)
    {
        $data = [];

        if(isset($updateDto->name))$data['name'] = $updateDto->name;
        if(isset($updateDto->email))$data['email'] = $updateDto->email;
        if(isset($updateDto->phone))$data['phone'] = $updateDto->phone;
        if(isset($updateDto->pro_pic))$data['pro_pic'] = $updateDto->pro_pic;
        if(isset($updateDto->id))$data['id'] = $updateDto->id;

        return $data ;
    }

    public function create(CustomerCreateDto $createDto): JsonResponse
    {
        $this->customerRepository->create($createDto->toArray());
        return $this->success();
    }
}

