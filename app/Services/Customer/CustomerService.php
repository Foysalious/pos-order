<?php namespace App\Services\Customer;

use App\Http\Resources\Webstore\Customer\NotRatedSkuResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use App\Traits\ModificationFields;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerService extends BaseService
{
    use ModificationFields;

    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private ReviewRepositoryInterface $reviewRepositoryInterface,
        private Updater $updater,
        private OrderSkuRepositoryInterface $orderSkuRepositoryInterface){}

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

    public function getNotRatedOrderSkuList(int $customerId,$request): JsonResponse
    {
        list($offset, $limit) = calculatePagination($request);
        if(!$request->order)
            $request->order = 'desc';
        $not_rated_skus = $this->orderSkuRepositoryInterface->getNotRatedOrderSkuListOfCustomer($customerId,$offset, $limit,$request->order);
        if ($not_rated_skus->isEmpty())
            throw new NotFoundHttpException("No SKUS Found");
        $not_rated_skus = NotRatedSkuResource::collection($not_rated_skus);
        return $this->success('Successful', ['skus' => $not_rated_skus], 200);
    }

    public function delete(int $customer_id)
    {
        try {
            $customer = $this->customerRepository->find($customer_id);
            if (!$customer) return $this->error('Customer Not Found', 404);
            DB::beginTransaction();
            $customer->orders()->delete();
            $customer->delete();
            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }
}

