<?php namespace App\Services\Customer;

use App\Exceptions\CustomerNotFound;
use App\Exceptions\OrderException;
use App\Http\Requests\CustomerOrderListRequest;
use App\Http\Resources\Webstore\Customer\NotRatedSkuResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Repositories\Accounting\AccountingRepository;
use App\Services\BaseService;
use App\Services\Order\Constants\PaymentStatuses;
use App\Services\Order\PriceCalculation;
use Exception;
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
        private AccountingRepository $accountingRepository
    ){}

    public function update(string $customer_id, CustomerUpdateDto $updateDto,$partner_id): JsonResponse
    {
        $customerDetails = $this->customerRepository->where('partner_id',$partner_id)->find($customer_id);
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

    public function getNotRatedOrderSkuList($partner_id, $customerId,$request): JsonResponse
    {
        list($offset, $limit) = calculatePagination($request);
        if(!$request->order)
            $request->order = 'desc';
        $not_rated_skus = $this->orderSkuRepositoryInterface->getNotRatedOrderSkuListOfCustomer($partner_id,$customerId,$offset, $limit,$request->order);
        if ($not_rated_skus->isEmpty())
            throw new NotFoundHttpException("No SKUS Found");
        $not_rated_skus = NotRatedSkuResource::collection($not_rated_skus);
        return $this->success('Successful', ['total_count' => count($not_rated_skus),'not_rated_order_skus' => $not_rated_skus]);
    }

    /**
     * @throws Exception
     */
    public function delete(int $partner_id, int|string $customer_id): JsonResponse
    {
        try {
            $customer = $this->customerRepository->find($customer_id);
            if (!$customer) return $this->error('Customer Not Found', 404);
            DB::beginTransaction();
            /** Turned OFF Accounting Hit for the time being as customer Id type string not supported in Accounting */
            //$this->accountingRepository->deleteCustomer($partner_id, $customer->id);
            $customer->delete();
            DB::commit();
            return $this->success();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @throws CustomerNotFound
     */
    public function getPurchaseAmountAndPromoUsed(int $partner_id, string $customer_id): JsonResponse
    {
        $customer =  $this->findTheCustomer($partner_id,$customer_id);
        $return_data = [
            'total_purchase_amount' => 0,
            'total_used_promo' => 0
        ];
        $all_orders = Order::with('orderSkus','payments','discounts')->where('partner_id', $partner_id)->where('customer_id', $customer->id)->get();
        /** @var PriceCalculation $order_calculator */
        $order_calculator = App::make(PriceCalculation::class);
        $all_orders->each(function ($order) use (&$return_data, $order_calculator){
            $order_calculator->setOrder($order);
            $return_data['total_purchase_amount'] += $order_calculator->getDiscountedPrice();
            $return_data['total_used_promo'] += $order_calculator->getPromoDiscount();
        });
        $return_data['total_purchase_amount'] = round($return_data['total_purchase_amount'],2);
        $return_data['total_used_promo'] = round($return_data['total_used_promo'],2);
        return $this->success('Successful', [ 'data' => $return_data ]);
    }

    /**
     * @throws CustomerNotFound
     */
    public function getOrdersByDateWise(CustomerOrderListRequest $request, int $partner_id, string $customer_id)
    {
        $customer = $this->findTheCustomer($partner_id,$customer_id);
        $status = $request->status ?? null;
        list($offset, $limit) = calculatePagination($request);
        $order_list = [];
        $all_orders = Order::with('orderSkus','payments','discounts')
            ->where('customer_id', $customer->id)
            ->where('partner_id', $partner_id)
            ->orderBy('created_at', 'desc')
            ->skip($offset)->take($limit)->get();
        foreach ($all_orders as $order) {
            $date = convertTimezone($order->created_at)->format('Y-m-d');
            $order_list[$date]['total_sale'] = $order_list[$date]['total_sale'] ?? 0;
            $order_list[$date]['total_due'] = $order_list[$date]['total_due'] ?? 0;
            /** @var PriceCalculation $order_calculator */
            $order_calculator = App::make(PriceCalculation::class);
            $order_calculator->setOrder($order);
            $order->discounted_price = $order_calculator->getDiscountedPrice();
            $order->due = $order_calculator->getDue();

            $order_list[$date]['total_sale'] += $order->discounted_price;
            $order_list[$date]['total_due'] += $order->due;
            if (!is_null($status) && ($status == PaymentStatuses::DUE || $status == 'Due' )) {
                if($order->due > 0) {
                    $order_list[$date]['orders'][] = $order->only(['id','partner_wise_order_id','status', 'discounted_price', 'due', 'created_at']);
                }
            } else {
                $order_list[$date]['orders'][] = $order->only(['id','partner_wise_order_id','status', 'discounted_price', 'due', 'created_at']);
            }
        }
        return $this->success('Successful', [ 'data' => $order_list ]);

    }


    /**
     * @throws CustomerNotFound
     */
    private function findTheCustomer(int $partner_id, string $customer_id)
    {
        $customer = $this->customerRepository->where('id', $customer_id)->where('partner_id', $partner_id)->first();
        if(!$customer) {
            throw new CustomerNotFound();
        } else {
            return $customer;
        }
    }
}

