<?php namespace App\Services\Customer;

use App\Http\Resources\Webstore\Customer\NotRatedSkuResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Services\BaseService;
use App\Services\Order\PriceCalculation;
use Illuminate\Http\JsonResponse;
use App\Traits\ModificationFields;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerService extends BaseService
{
    use ModificationFields;

    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private ReviewRepositoryInterface $reviewRepositoryInterface,
        private Updater $updater,
        private OrderSkuRepositoryInterface $orderSkuRepositoryInterface,
        protected OrderRepositoryInterface $orderRepository
    ){}

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

    public function getPuchaseAmountAndPromoUsed(string $customer_id)
    {
        $customer =  $this->customerRepository->where('id', $customer_id)->first();
        if(!$customer) {
            return $this->error('customer not found', 404);
        }
        $return_data = [
            'total_purchase_amount' => 0,
            'total_used_promo' => 0
        ];
        $all_orders = $this->orderRepository->where('customer_id', $customer->id)->get();
        /** @var PriceCalculation $order_calculator */
        $order_calculator = App::make(PriceCalculation::class);
        $all_orders->each(function ($order) use (&$return_data, $order_calculator){
            $order_calculator->setOrder($order);
            $return_data['total_purchase_amount'] += $order_calculator->getDiscountedPrice();
            $return_data['total_used_promo'] += $order_calculator->getPromoDiscount();
        });
        $return_data['total_purchase_amount'] = round($return_data['total_purchase_amount'],2);
        $return_data['total_used_promo'] = round($return_data['total_used_promo'],2);
        return $this->success('Successful', [ 'data' => $return_data ], 200);
    }
}

