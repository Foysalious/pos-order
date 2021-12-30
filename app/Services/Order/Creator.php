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
use App\Services\APIServerClient\ApiServerClient;
use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Services\ClientServer\SmanagerUser\SmanagerUserServerClient;
use App\Services\Customer\CustomerResolver;
use App\Services\Delivery\Methods;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\EMI\Calculations as EmiCalculation;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\OrderLogTypes;
use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\Statuses;
use App\Services\OrderLog\Objects\OrderObject;
use App\Services\Payment\Creator as PaymentCreator;
use App\Services\Discount\Handler as DiscountHandler;
use App\Services\OrderSku\Creator as OrderSkuCreator;
use App\Services\Transaction\Constants\TransactionTypes;
use App\Traits\ModificationFields;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Creator
{
    use ModificationFields;

    private $createValidator;
    private $partnerId;
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
    private ?string $deliveryAddressId;
    private ?string $deliveryMethod;
    private ?string $totalWeight;
    private ?string $deliveryDistrict;
    private ?string $deliveryThana;


    public function __construct(
        OrderRepositoryInterface              $orderRepositoryInterface,
        PartnerRepositoryInterface            $partnerRepositoryInterface,
        InventoryServerClient                 $client,
        OrderSkuRepositoryInterface           $orderSkuRepository,
        PaymentCreator                        $paymentCreator,
        DiscountHandler                       $discountHandler,
        OrderSkuCreator                       $orderSkuCreator,
        protected CustomerRepositoryInterface $customerRepository,
        protected SmanagerUserServerClient    $smanagerUserServerClient,
        protected PriceCalculation            $priceCalculation,
        protected EmiCalculation              $emiCalculation,
        protected ApiServerClient             $apiServerClient,
        protected CustomerResolver            $customerResolver,
        private Partner                       $partner,
        private OrderLogCreator               $orderLogCreator

    )
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->partnerRepositoryInterface = $partnerRepositoryInterface;
        $this->orderSkuRepository = $orderSkuRepository;
        $this->paymentCreator = $paymentCreator;
        $this->client = $client;
        $this->discountHandler = $discountHandler;
        $this->orderSkuCreator = $orderSkuCreator;
    }

    public function setPartnerId($partnerId): Creator
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    private function setPartner(Partner $partner)
    {
        $this->partner = $partner;
    }

    /**
     * @throws BaseClientServerError
     */
    private function resolvePartner()
    {
        $partner = Partner::find($this->partnerId);
        if (!$partner) {
            $partnerInfo = $this->getPartnerInfo();
            $data = [
                'id' => $this->partnerId,
                'sub_domain' => $partnerInfo['sub_domain'],
                'delivery_charge' => $partnerInfo['delivery_charge'],
            ];
            $partner = $this->partnerRepositoryInterface->create($data);
        }
        $this->setPartner($partner);
    }

    /**
     * @throws BaseClientServerError
     */
    private function getPartnerInfo()
    {
        return $this->apiServerClient->get('pos/v1/partners/' . $this->partnerId)['partner'];
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
     * @param mixed $deliveryAddressId
     * @return Creator
     */
    public function setDeliveryAddressId(?string $deliveryAddressId): Creator
    {
        $this->deliveryAddressId = $deliveryAddressId;
        return $this;
    }

    /**
     * @param string|null $totalWeight
     * @return Creator
     */
    public function setTotalWeight(?string $totalWeight): Creator
    {
        $this->totalWeight = $totalWeight;
        return $this;
    }

    /**
     * @param string|null $deliveryMethod
     * @return Creator
     */
    public function setDeliveryMethod(?string $deliveryMethod): Creator
    {
        $this->deliveryMethod = $deliveryMethod;
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


    public function setDeliveryDistrict($deliveryDistrict)
    {
        $this->deliveryDistrict = $deliveryDistrict;
        return $this;
    }

    public function setDeliveryThana($deliveryThana)
    {
        $this->deliveryThana = $deliveryThana;
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
            $this->resolvePartner();
            $order_data = $this->makeOrderData();
            if ($order_data['sales_channel_id'] == SalesChannelIds::WEBSTORE) $order_data['delivery_vendor'] = $this->createDeliveryVendor($this->getDeliveryMethod());
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
            if ($this->paymentMethod == PaymentMethods::EMI) {
                $this->validateEmiAndCalculateChargesForOrder($order, new PriceCalculation());
                /** @var OrderObject $orderObject */
                $orderObject = app(OrderObject::class);
                $orderObject->setOrder($order);
                $this->orderLogCreator->setOrderId($order->id)->setType(OrderLogTypes::EMI)->setChangedOrderData(json_encode($orderObject))->create();
            }
            if (isset($this->paymentMethod) && ($this->paymentMethod == PaymentMethods::CASH_ON_DELIVERY || $this->paymentMethod == PaymentMethods::QR_CODE) && $this->paidAmount > 0) {
                $this->priceCalculation->setOrder($order->refresh());
                $net_bill = $this->priceCalculation->setOrder($order->refresh())->getDiscountedPrice();
                if ($this->paidAmount > $net_bill) {
                    $this->paidAmount = $net_bill;
                }
                $cash_details = json_encode(['payment_method_en' => 'Cash', 'payment_method_bn' => ' নগদ গ্রহন', 'payment_method_icon' => config('s3.url') . 'pos/payment/cash_v2.png']);
                $this->paymentCreator->setOrderId($order->id)->setAmount($this->paidAmount)->setMethod($this->paymentMethod)
                    ->setTransactionType(TransactionTypes::CREDIT)->setEmiMonth($order->emi_month)
                    ->setInterest($order->interest)->setMethodDetails($cash_details)->create();
            }
            $this->calculateDeliveryChargeAndSave($order);
            if ($this->hasDueError($order->refresh())) {
                throw new OrderException("Can not make due order without customer", 403);
            }
            if ($this->getDueAmount($order) > 0) {
                /** @var OrderObject $orderObject */
                $orderObject = app(OrderObject::class);
                $orderObject->setOrder($order);
                $this->orderLogCreator->setOrderId($order->id)->setType(OrderLogTypes::DUE_BILL)->setExistingOrderData(null)->setChangedOrderData(json_encode($orderObject))->create();
            }
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        event(new OrderPlaceTransactionCompleted($order));
        return $order->refresh();
    }


    private function resolveCustomer(): Creator
    {
        if (!isset($this->customerId)) return $this->setCustomer(null);
        $customer = $this->customerResolver->setPartnerId($this->partnerId)->setCustomerId($this->customerId)->resolveCustomer();
        return $this->setCustomer($customer);
    }

    private function resolvePartnerWiseOrderId($partnerId)
    {
        $lastOrder = Order::where('partner_id', $partnerId)->orderBy('id', 'desc')->first();
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
        $order_data['partner_id'] = $this->partnerId;
        $order_data['partner_wise_order_id'] = $this->resolvePartnerWiseOrderId($this->partnerId);
        $order_data['customer_id'] = $this->customer->id ?? null;
        $order_data['delivery_name'] = $this->resolveDeliveryName();
        $order_data['delivery_mobile'] = $this->resolveDeliveryMobile();
        $order_data['delivery_address'] = $this->deliveryAddress;
        $order_data['delivery_thana'] = $this->deliveryThana;
        $order_data['delivery_district'] = $this->deliveryDistrict;
        $order_data['sales_channel_id'] = $this->salesChannelId ?: SalesChannelIds::POS;
        $order_data['emi_month'] = ($this->paymentMethod == PaymentMethods::EMI && !is_null($this->emiMonth)) ? $this->emiMonth : null;
        $order_data['status'] = ($this->salesChannelId == SalesChannelIds::POS || is_null($this->salesChannelId)) ? Statuses::COMPLETED : Statuses::PENDING;
        $order_data['discount'] = json_decode($this->discount)->original_amount ?? 0;
        $order_data['is_discount_percentage'] = json_decode($this->discount)->is_percentage ?? 0;
        $order_data['voucher_id'] = $this->voucher_id;
        $order_data['api_request_id'] = $this->apiRequest;
        return $order_data + $this->modificationFields(true, false);
    }

    private function hasDueError(Order $order): bool
    {
        if ($this->getDueAmount($order) > 0 && is_null($this->customer)) {
            return true;
        }
        return false;
    }

    private function getDueAmount(Order $order): float
    {
        /** @var PriceCalculation $order_bill */
        $order_bill = App::make(PriceCalculation::class);
        $order_bill = $order_bill->setOrder($order);
        return $order_bill->getDue();

    }

    /**
     * @throws OrderException|BaseClientServerError
     */
    private function validateEmiAndCalculateChargesForOrder($order, PriceCalculation $price_calculator)
    {
        $total_amount = $price_calculator->setOrder($order)->getDiscountedPrice();
        $min_emi_amount = config('emi.minimum_emi_amount');
        if ($total_amount < $min_emi_amount) {
            throw new OrderException("Emi is not available for order amount less than " . $min_emi_amount, 400);
        }
        $data = $this->emiCalculation->setEmiMonth($this->emiMonth)->setAmount($total_amount)->getEmiCharges();
        $order->interest = $data['total_interest'];
        $order->bank_transaction_charge = $data['bank_transaction_fee'];
        $order->save();
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function calculateDeliveryChargeAndSave(Order $order): bool
    {
        /** @var OrderDeliveryPriceCalculation $deliveryPriceCalculation */
        $deliveryPriceCalculation = app(OrderDeliveryPriceCalculation::class);
        $delivery_charge = $deliveryPriceCalculation->setOrder($order)->calculateDeliveryCharge();
        if ($delivery_charge) $order->update(['delivery_charge' => $delivery_charge]);
        return false;
    }

    private function createDeliveryVendor($deliveryMethod)
    {
        if ($deliveryMethod == Methods::SDELIVERY)
            $deliveryMethod = Methods::PAPERFLY;
        $delivery_methods = config('delivery');
        $delivery_vendor['name'] = isset($delivery_methods[$deliveryMethod]) ? $delivery_methods[$deliveryMethod]['name'] : null;
        $delivery_vendor['image'] = isset($delivery_methods[$deliveryMethod]) ? $delivery_methods[$deliveryMethod]['image'] : null;
        return json_encode($delivery_vendor);
    }

    private function getDeliveryMethod()
    {
        /** @var ApiServerClient $apiServerClient */
        $apiServerClient = app(ApiServerClient::class);
        return $apiServerClient->get('pos/v1/partners/' . $this->partner->id)['partner']['delivery_method'];
    }
}


