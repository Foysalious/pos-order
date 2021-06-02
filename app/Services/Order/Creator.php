<?php namespace App\Services\Order;

use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkuRepositoryInterface;
use App\Interfaces\PartnerRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Inventory\InventoryServerClient;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\Validators\OrderCreateValidator;
use App\Services\Order\Payment\Creator as PaymentCreator;
use App\Services\Discount\Handler as DiscountHandler;
use App\Services\OrderSku\Creator as OrderSkuCreator;
use App\Traits\ResponseAPI;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Sheba\Sms\Sms;
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
    /** @var Collection */
    private $sku_details;
    /** @var Order */
    private $order;
    /** @var OrderSkuRepositoryInterface */
    private $orderSkuRepository;
    /** @var PaymentCreator */
    private $paymentCreator;
    private ?int $customerId;
    /** @var Customer|null */
    private ?Customer $customer;
    private ?string $deliveryName;
    private ?string $deliveryMobile;
    private ?string $deliveryAddress;
    private ?int $emiMonth;
    private ?int $salesChannelId;
    private ?float $deliveryCharge;
    /** @var DiscountHandler */
    private DiscountHandler $discountHandler;
    private $discount;
    private $isDiscountPercentage;
    /**
     * @var OrderSkuCreator
     */
    private OrderSkuCreator $orderSkuCreator;


    public function __construct(OrderCreateValidator $createValidator,
                                OrderRepositoryInterface $orderRepositoryInterface, PartnerRepositoryInterface $partnerRepositoryInterface, InventoryServerClient $client,
                                OrderSkuRepositoryInterface $orderSkuRepository, PaymentCreator $paymentCreator, DiscountHandler $discountHandler, OrderSkuCreator $orderSkuCreator)
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
     * @param int|null $customerId
     * @return Creator
     */
    public function setCustomerId(?int $customerId): Creator
    {
        $this->customerId = $customerId;
        $this->customer = Customer::find($customerId);
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
     * @param array $skus
     * @return Creator
     */
    public function setSkus(array $skus): Creator
    {
        $this->skus = $skus;
        return $this;
    }

    /**
     * @param mixed $discount
     * @return Creator
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param mixed $isDiscountPercentage
     * @return Creator
     */
    public function setIsDiscountPercentage($isDiscountPercentage)
    {
        $this->isDiscountPercentage = $isDiscountPercentage;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        // $this->createValidator->setProducts(json_decode($this->data['services'], true));
        if (!isset($this->data['payment_method'])) $this->data['payment_method'] = 'cod';
        if (isset($this->data['customer_address'])) $this->setAddress($this->data['customer_address']);
        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    private function sendOrderPlaceSmsToCustomer()
    {
        (new Sms())->msg("Hello From The Other Side")
            ->to("8801715096710")
            ->shoot();
    }

    /**
     * @return Order
     * @throws ValidationException
     */
    public function create()
    {
        $order_data['partner_id'] = $this->partner->id;
        $order_data['partner_wise_order_id'] = $this->resolvePartnerWiseOrderId($this->partner);
        $order_data['customer_id'] = $this->resolveCustomerId();
        $order_data['delivery_name'] = $this->resolveDeliveryName();
        $order_data['delivery_mobile'] = $this->resolveDeliveryMobile();
        $order_data['delivery_address'] = $this->resolveDeliveryAddress();
        $order_data['sales_channel_id'] = $this->salesChannelId ?: SalesChannelIds::POS;
        $order_data['delivery_charge'] = $this->deliveryCharge ?: 0;
        $order_data['emi_month'] = $this->emiMonth ?? null;
        $order_data['status'] = $this->salesChannelId == SalesChannelIds::POS ? Statuses::COMPLETED : Statuses::PENDING;
        $order_data['discount'] = $this->discount;
        $order_data['is_discount_percentage'] = $this->isDiscountPercentage;
        $order = $this->orderRepositoryInterface->create($order_data);
        $this->discountHandler->setOrder($order)->setType(DiscountTypes::ORDER)->setData($order_data);
        if ($this->discountHandler->hasDiscount()) {
            $this->discountHandler->create();
        }
        $this->orderSkuCreator->setOrder($order)->setSkus($this->skus)->create();
//        $this->order->calculate();
//        $this->sendOrderPlaceSmsToCustomer();
        if (isset($this->data['paid_amount']) && $this->data['paid_amount'] > 0) {
            $payment_data['order_id'] = $order->id;
            $payment_data['amount'] = $this->data['paid_amount'];
            $payment_data['method'] = $this->data['payment_method'] ?: 'cod';
            $this->paymentCreator->credit($payment_data);
        }
        return $order;
    }

    private function resolveCustomerId()
    {
        if ($this->customer) return $this->customer->id;
        if (!isset($this->customerId) || !$this->customerId) return null;
        $customer = Customer::find($this->customerId);
        if (!$customer) throw new NotFoundHttpException("Customer #" . $this->customerId . " Doesn't Exists.");
        if ($customer->partner_id != $this->partner->id)
            throw new NotFoundHttpException("Customer #" . $this->customerId . " Doesn't Belong To Partner #" . $this->partner->id);
        return $this->customerId;
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
        if ($this->customer) return $this->customer->phone;
        return null;
    }

    private function resolveDeliveryAddress()
    {
        if ($this->deliveryAddress) return $this->deliveryAddress;
        if ($this->customer) return $this->customer->address;
        return null;
    }
}
