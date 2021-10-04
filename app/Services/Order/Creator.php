<?php namespace App\Services\Order;

use App\Events\OrderPlaceTransactionCompleted;
use App\Exceptions\OrderException;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Services\ClientServer\SmanagerUser\SmanagerUserServerClient;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\EMI\Calculations;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\Validators\OrderCreateValidator;
use App\Services\Payment\Creator as PaymentCreator;
use App\Services\Discount\Handler as DiscountHandler;
use App\Services\OrderSku\Creator as OrderSkuCreator;
use App\Services\Transaction\Constants\TransactionTypes;
use App\Traits\ResponseAPI;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Creator
{
    use ResponseAPI;

    private $createValidator;
    private $partner;
    /**  @var array */
    private $data;
    private $status;
    private $orderRepositoryInterface;
    /** @var PartnerRepositoryInterface */
    private $partnerRepositoryInterface;
    /** @var InventoryServerClient */
    private $client;
    /** @var array */
    private array $skus;
    /** @var OrderSkuRepositoryInterface */
    private $orderSkuRepository;
    /** @var PaymentCreator */
    private $paymentCreator;
    private ?string $customerId;
    /** @var Customer|null */
    private ?Customer $customer;
    private ?string $deliveryName;
    private ?string $deliveryMobile;
    private ?string $deliveryAddress;
    private ?int $emiMonth;
    private ?int $salesChannelId;
    private ?float $deliveryCharge;
    private ?int $voucher_id;
    /** @var DiscountHandler */
    private DiscountHandler $discountHandler;
    private $discount;
    private $isDiscountPercentage;
    private $paidAmount;
    private $paymentMethod;
    /**
     * @var OrderSkuCreator
     */
    private OrderSkuCreator $orderSkuCreator;
    private $codAmount;


    public function __construct(
        OrderCreateValidator                  $createValidator,
        OrderRepositoryInterface              $orderRepositoryInterface,
        PartnerRepositoryInterface            $partnerRepositoryInterface,
        InventoryServerClient                 $client,
        OrderSkuRepositoryInterface           $orderSkuRepository,
        PaymentCreator                        $paymentCreator,
        DiscountHandler                       $discountHandler,
        OrderSkuCreator                       $orderSkuCreator,
        protected CustomerRepositoryInterface $customerRepository,
        protected SmanagerUserServerClient    $smanagerUserServerClient,
    )
    {
        $this->createValidator = $createValidator;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->partnerRepositoryInterface = $partnerRepositoryInterface;
        $this->orderSkuRepository = $orderSkuRepository;
        $this->paymentCreator = $paymentCreator;
        $this->client = $client;
        $this->discountHandler = $discountHandler;
        $this->orderSkuCreator = $orderSkuCreator;
    }

    public function setPartner($partner): Creator
    {
        $partner = Partner::find($partner);
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param string|null $customerId
     * @return Creator
     */
    public function setCustomerId(?string $customerId): Creator
    {
        $this->customerId = $customerId;
        $this->resolveCustomer();
        return $this;
    }

    /**
     * @param Customer|null $customer
     * @return Creator
     */
    private function setCustomer(?Customer $customer): Creator
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param string|null $deliveryName
     * @return Creator
     */
    public function setDeliveryName(?string $deliveryName): Creator
    {
        $this->deliveryName = $deliveryName;
        return $this;
    }

    /**
     * @param string|null $deliveryMobile
     * @return Creator
     */
    public function setDeliveryMobile(?string $deliveryMobile): Creator
    {
        $this->deliveryMobile = $deliveryMobile;
        return $this;
    }

    /**
     * @param string|null $deliveryAddress
     * @return Creator
     */
    public function setDeliveryAddress(?string $deliveryAddress): Creator
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    /**
     * @param int|null $emiMonth
     * @return Creator
     */
    public function setEmiMonth(?int $emiMonth): Creator
    {
        $this->emiMonth = $emiMonth;
        return $this;
    }

    /**
     * @param int|null $salesChannelId
     * @return Creator
     */
    public function setSalesChannelId(?int $salesChannelId): Creator
    {
        $this->salesChannelId = $salesChannelId;
        return $this;
    }

    /**
     * @param float|null $deliveryCharge
     * @return Creator
     */
    public function setDeliveryCharge(?float $deliveryCharge): Creator
    {
        $this->deliveryCharge = $deliveryCharge;
        return $this;
    }

    /**
     * @param string $skus
     * @return Creator
     */
    public function setSkus(string $skus): Creator
    {
        $this->skus = json_decode($skus);
        return $this;
    }

    /**
     * @param mixed $discount
     * @return Creator
     */
    public function setDiscount($discount): Creator
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param mixed $isDiscountPercentage
     * @return Creator
     */
    public function setIsDiscountPercentage($isDiscountPercentage): Creator
    {
        $this->isDiscountPercentage = $isDiscountPercentage;
        return $this;
    }

    /**
     * @param mixed $paidAmount
     * @return Creator
     */
    public function setPaidAmount($paidAmount): Creator
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    /**
     * @param mixed $paymentMethod
     * @return Creator
     */
    public function setPaymentMethod($paymentMethod): Creator
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @param int $voucher_id
     * @return Creator
     */
    public function setVoucherId(?int $voucher_id): Creator
    {
        $this->voucher_id = $voucher_id;
        return $this;
    }

    public function setData(array $data): Creator
    {
        $this->data = $data;
        if (!isset($this->data['payment_method'])) $this->data['payment_method'] = 'cod';
        if (isset($this->data['customer_address'])) $this->setAddress($this->data['customer_address']);
        return $this;
    }

    public function setAddress($address): Creator
    {
        $this->address = $address;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }


    public function setApiRequest($apiRequest)
    {
        $this->apiRequest = $apiRequest;
        return $this;
    }

    /**
     * @param mixed $codAmount
     * @return Creator
     */
    public function setCodAmount($codAmount)
    {
        $this->codAmount = $codAmount;
        return $this;
    }


    /**
     * @return mixed
     * @throws OrderException
     * @throws ValidationException|BaseClientServerError
     */
    public function create()
    {
        try {
            DB::beginTransaction();
            $order_data = $this->makeOrderData();
            $order = $this->orderRepositoryInterface->create($order_data);
            $this->orderSkuCreator->setOrder($order)->setIsPaymentMethodEmi($this->paymentMethod == PaymentMethods::EMI)
                ->setSkus($this->skus)->create();
            $this->discountHandler->setOrder($order)->setType(DiscountTypes::ORDER)->setData($order_data);
            if ($this->discountHandler->hasDiscount()) {
                $this->discountHandler->create();
            }
            if (isset($this->voucher_id)) {
                $this->discountHandler->setVoucherId($this->voucher_id)->voucherDiscountCalculate($order);
            }
            if ($this->paidAmount > 0) {
                $this->paymentCreator->setOrderId($order->id)->setAmount($this->paidAmount)->setMethod($this->paymentMethod)
                    ->setTransactionType(TransactionTypes::CREDIT)->setEmiMonth($order->emi_month)
                    ->setInterest($order->interest)->create();
            }
            if ($this->hasDueError($order)) {
                throw new OrderException("Can not make due order without customer", 421);
            }
            if ($this->paymentMethod == PaymentMethods::EMI) {
                $this->validateEmiAndCalculateChargesForOrder($order, new PriceCalculation());
            }
            if ($order) event(new OrderPlaceTransactionCompleted($order));
            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $order;
    }

    private function resolveCustomer(): Creator
    {
        if (!isset($this->customerId)) return $this->setCustomer(null);
        $customer = $this->customerRepository->find($this->customerId);
        if (!$customer) {
            $customer = $this->smanagerUserServerClient->setBaseUrl()->get('/api/v1/partners/' . $this->partner->id . '/users/' . $this->customerId);
            if (!$customer) throw new NotFoundHttpException("Customer #" . $this->customerId . " Doesn't Exists.");
            $data = [
                'id' => $customer['_id'],
                'name' => $customer['name'],
                'email' => $customer['email'],
                'partner_id' => $customer['partner_id'],
                'mobile' => $customer['mobile'],
                'pro_pic' => $customer['pro_pic'],
            ];
            $customer = $this->customerRepository->create($data);
        }
        if ($customer->partner_id != $this->partner->id) {
            throw new NotFoundHttpException("Customer #" . $this->customerId . " Doesn't Belong To Partner #" . $this->partner->id);
        }
        return $this->setCustomer($customer);
    }

    private function resolvePartnerWiseOrderId(Partner $partner)
    {
        $lastOrder = $partner->orders()->orderBy('id', 'desc')->first();
        $lastOrder_id = $lastOrder ? $lastOrder->partner_wise_order_id : 0;
        return $lastOrder_id + 1;
    }

    private function resolveDeliveryName()
    {
        if ($this->deliveryName) return $this->deliveryName;
        if ($this->customer) return $this->customer->name;
        return null;
    }

    private function resolveDeliveryMobile()
    {
        if ($this->deliveryMobile) return $this->deliveryMobile;
        if ($this->customer) return $this->customer->mobile;
        return null;
    }

    private function makeOrderData(): array
    {
        $order_data = [];
        $order_data['partner_id'] = $this->partner->id;
        $order_data['partner_wise_order_id'] = $this->resolvePartnerWiseOrderId($this->partner);
        $order_data['customer_id'] = $this->customer->id ?? null;
        $order_data['delivery_name'] = $this->resolveDeliveryName();
        $order_data['delivery_mobile'] = $this->resolveDeliveryMobile();
        $order_data['delivery_address'] = $this->deliveryAddress;
        $order_data['sales_channel_id'] = $this->salesChannelId ?: SalesChannelIds::POS;
        $order_data['delivery_charge'] = $this->deliveryCharge ?: 0;
        $order_data['emi_month'] = ($this->paymentMethod == PaymentMethods::EMI && !is_null($this->emiMonth)) ? $this->emiMonth : null;
        $order_data['status'] = ($this->salesChannelId == SalesChannelIds::POS || is_null($this->salesChannelId)) ? Statuses::COMPLETED : Statuses::PENDING;
        $order_data['discount'] = json_decode($this->discount)->original_amount ?? 0;
        $order_data['is_discount_percentage'] = json_decode($this->discount)->is_percentage ?? 0;
        $order_data['voucher_id'] = $this->voucher_id;
        $order_data['api_request_id'] = $this->apiRequest;
        return $order_data;
    }

    private function hasDueError(Order $order)
    {
        /** @var PriceCalculation $order_bill */
        $order_bill = App::make(PriceCalculation::class);
        $order_bill = $order_bill->setOrder($order);
        if ($order_bill->getDue() > 0 && is_null($this->customer)) {
            return true;
        }
        return false;
    }

    /**
     * @throws OrderException
     */
    private function validateEmiAndCalculateChargesForOrder($order, PriceCalculation $price_calculator)
    {
        $total_amount = $price_calculator->setOrder($order)->getDiscountedPrice();
        $min_emi_amount = config('emi.minimum_emi_amount');
        if($total_amount < $min_emi_amount) {
            throw new OrderException("Emi is not available for order amount < " .$min_emi_amount);
        }
        $data = Calculations::getMonthData($total_amount, (int)$order->emi_month, false);
        $order->interest = $data['total_interest'];
        $order->bank_transaction_charge = $data['bank_transaction_fee'];
        $order->save();
    }
}


