<?php namespace App\Services\Customer;

use App\Http\Resources\Webstore\Customer\NotRatedSkuResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use App\Traits\ModificationFields;

class CustomerService extends BaseService
{
    use ModificationFields;

    public function __construct(private CustomerRepositoryInterface $customerRepository, private ReviewRepositoryInterface $reviewRepositoryInterface, private Updater $updater, private OrderSkuRepositoryInterface $orderSkuRepositoryInterface)
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

        if (isset($updateDto->name)) $data['name'] = $updateDto->name;
        if (isset($updateDto->email)) $data['email'] = $updateDto->email;
        if (isset($updateDto->mobile)) $data['mobile'] = $updateDto->mobile;
        if (isset($updateDto->pro_pic)) $data['pro_pic'] = $updateDto->pro_pic;

        return $data;
    }

    public function create(CustomerCreateDto $createDto): JsonResponse
    {
        $this->customerRepository->create($createDto->toArray());
        return $this->success();
    }

    public function getNotRatedOrderSkuList(int $customerId,$request)
    {
        list($offset, $limit) = calculatePagination($request);
        $not_rated_skus = $this->orderSkuRepositoryInterface->getNotRatedOrderSkuListOfCustomer($customerId,$offset, $limit);
        $not_rated_skus = NotRatedSkuResource::collection($not_rated_skus);
        return $this->success('Successful', ['not_rated_skus' => $not_rated_skus], 200);
    }
}

