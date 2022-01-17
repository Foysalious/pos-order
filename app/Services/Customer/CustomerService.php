<?php namespace App\Services\Customer;

use App\Constants\ResponseMessages;
use App\Http\Requests\CustomerOrderListRequest;
use App\Http\Resources\CustomerOrderResourceForPos;
use App\Http\Resources\Webstore\Customer\NotRatedSkuResource;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\ReviewRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Repositories\Accounting\AccountingRepository;
use App\Services\APIServerClient\ApiServerClient;
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
        private ReviewRepositoryInterface   $reviewRepositoryInterface,
        private Updater                     $updater,
        private OrderSkuRepositoryInterface $orderSkuRepositoryInterface,
        private AccountingRepository        $accountingRepository,
        protected OrderRepositoryInterface  $orderRepository
    )
    {
    }

    public function update(string $customer_id, CustomerUpdateDto $updateDto, $partner_id): JsonResponse
    {
        $customerDetails = $this->customerRepository->where('partner_id', $partner_id)->find($customer_id);
        if (!$customerDetails) return $this->success();
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
        if (isset($updateDto->is_supplier)) $data['is_supplier'] = $updateDto->is_supplier;
        return $data;
    }

    public function create(CustomerCreateDto $createDto): JsonResponse
    {
        $this->customerRepository->create($createDto->toArray());
        return $this->success();
    }

    public function getNotRatedOrderSkuList($partner_id, $customerId, $request): JsonResponse
    {
        list($offset, $limit) = calculatePagination($request);
        if (!$request->order)
            $request->order = 'desc';
        $not_rated_skus_count = count($this->orderSkuRepositoryInterface->getNotRatedOrderSkuListOfCustomerCount($partner_id, $customerId, $request->order));
        $not_rated_skus = $this->orderSkuRepositoryInterface->getNotRatedOrderSkuListOfCustomer($partner_id, $customerId, $offset, $limit, $request->order);
        if ($not_rated_skus->isEmpty())
            throw new NotFoundHttpException("No SKUS Found");
        $not_rated_skus = NotRatedSkuResource::collection($not_rated_skus);
        return $this->success(ResponseMessages::SUCCESS, ['total_count' => $not_rated_skus_count, 'not_rated_order_skus' => $not_rated_skus]);
    }

    /**
     * @param int $partner_id
     * @param int|string $customer_id
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(int $partner_id, int|string $customer_id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $customer = Customer::where([['id', $customer_id], ['partner_id', $partner_id]])->first();
            if (!$customer) return $this->error('Customer Not Found', 404);
            $this->deleteOrders($partner_id, $customer_id);
            $customer->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            app('sentry')->captureException($e);
            throw $e;
        }
        return $this->success();
    }

    private function deleteOrders($partnerId, $customerId)
    {
        $orders = Order::byPartnerAndCustomer($partnerId, $customerId)->get();
        foreach ($orders as $order) {
            $order->delete();
        }
    }

    public function getPurchaseAmountAndPromoUsed(int $partner_id, string $customer_id): JsonResponse
    {
        $customer = $this->findTheCustomer($partner_id, $customer_id);
        if (!$customer) return $this->error('Customer Not Found', 404);
        $return_data = [
            'total_purchase_amount' => 0,
            'total_used_promo' => 0
        ];
        $orders = $this->orderRepository->getAllOrdersOfPartnersCustomer($partner_id, $customer_id);
        /** @var PriceCalculation $order_calculator */
        $order_calculator = App::make(PriceCalculation::class);
        $orders->each(function ($order) use (&$return_data, $order_calculator) {
            $order_calculator->setOrder($order);
            $return_data['total_purchase_amount'] += $order_calculator->getDiscountedPrice();
            $return_data['total_used_promo'] += $order_calculator->getPromoDiscount();
        });
        $return_data['total_purchase_amount'] = round($return_data['total_purchase_amount'], 2);
        $return_data['total_used_promo'] = round($return_data['total_used_promo'], 2);
        return $this->success(ResponseMessages::SUCCESS, ['data' => $return_data]);
    }


    public function getOrdersByDateWise(CustomerOrderListRequest $request, int $partner_id, string $customer_id): JsonResponse
    {
        $customer = $this->findTheCustomer($partner_id, $customer_id);
        $delivery_info = $this->getDeliveryInformation($partner_id);
        if (!$customer) return $this->error('No order belongs to this customer', 404);
        $status = $request->status ?? null;
        list($offset, $limit) = calculatePagination($request);
        $order_list = [];
        $orders = $this->orderRepository->getAllOrdersOfPartnersCustomer($partner_id, $customer_id, 'desc', $limit, $offset);
        foreach ($orders as $order) {
            $date = convertTimezone($order->created_at)?->format('Y-m-d');
            $order_list[$date]['total_sale'] = $order_list[$date]['total_sale'] ?? 0;
            $order_list[$date]['total_due'] = $order_list[$date]['total_due'] ?? 0;
            /** @var PriceCalculation $order_calculator */
            $order_calculator = App::make(PriceCalculation::class);
            $order_calculator->setOrder($order);
            $order->discounted_price = $order_calculator->getDiscountedPrice();
            $order->due = $order_calculator->getDue();
            $order_list[$date]['total_sale'] += $order->discounted_price;
            $order_list[$date]['total_due'] += $order->due;
            if (!is_null($status) && ($status == PaymentStatuses::DUE || $status == 'Due')) {
                if (is_null($order->paid_at)) {
                    $order_list[$date]['orders'][] = new CustomerOrderResourceForPos($order, $delivery_info);
                }
            } else {
                $order_list[$date]['orders'][] = new CustomerOrderResourceForPos($order, $delivery_info);
            }
        }
        //no datwise format needed
        $final_report = [];
        collect($order_list)->each(function ($value, $date_key) use (&$final_report) {
            $final_report [] = array_merge(['date' => $date_key], $value);
        });
        return $this->success(ResponseMessages::SUCCESS, ['data' => $final_report]);

    }

    private function findTheCustomer(int $partner_id, string $customer_id): bool|Customer
    {
        $customer = $this->customerRepository->where('id', $customer_id)->where('partner_id', $partner_id)->first();
        return is_null($customer) ? false : $customer;
    }

    private function getDeliveryInformation($partnerId)
    {
        /** @var ApiServerClient $apiServerClient */
        $apiServerClient = app(ApiServerClient::class);
        $partnerInfo = $apiServerClient->get('pos/v1/partners/' . $partnerId)['partner'];
        return [
            'delivery_method' => $partnerInfo['delivery_method'],
            'is_registered_for_sdelivery' => $partnerInfo['is_registered_for_sdelivery'],
        ];
    }
}

