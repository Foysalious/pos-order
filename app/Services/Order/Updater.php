<?php namespace App\Services\Order;

use App\Events\OrderCustomerUpdated;
use App\Events\OrderUpdated;
use App\Exceptions\OrderException;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\OrderDiscountRepositoryInterface;
use App\Interfaces\OrderPaymentRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\OrderSkusRepositoryInterface;
use App\Models\Order;
use App\Services\ClientServer\Exceptions\BaseClientServerError;
use App\Services\Delivery\Methods;
use App\Services\Discount\Handler;
use App\Services\EMI\Calculations as EmiCalculation;
use App\Services\Order\Constants\OrderLogTypes;
use App\Services\Order\Constants\PaymentMethods;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\Refund\AddProductInOrder;
use App\Services\Order\Refund\DeleteProductFromOrder;
use App\Services\Order\Refund\OrderUpdateFactory;
use App\Services\Order\Refund\UpdateProductInOrder;
use App\Services\OrderLog\Objects\OrderObject;
use App\Services\Payment\Creator as PaymentCreator;
use App\Services\Product\StockManageByChunk;
use App\Services\Product\StockManager;
use App\Services\Transaction\Constants\TransactionTypes;
use App\Traits\ModificationFields;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Updater
{
    use ModificationFields;

    protected $partner_id, $order_id, $customer_id, $status, $sales_channel_id, $emi_month, $interest, $delivery_charge;
    protected $bank_transaction_charge, $delivery_name, $delivery_mobile, $delivery_address, $note, $voucher_id, $discount;
    protected $skus, $existingOrder;
    protected Order $order;
    protected $orderLogCreator;
    protected $orderRepositoryInterface, $orderSkusRepositoryInterface, $orderPaymentRepository;
    protected $orderDiscountRepository;
    protected $orderPaymentCreator;
    protected $paymentMethod;
    protected $paidAmount;
    protected array $orderProductChangeData;
    protected string $orderLogType = OrderLogTypes::OTHERS;
    protected ?string $delivery_vendor_name;
    protected ?string $delivery_request_id;
    protected ?string $delivery_thana;
    protected ?string $delivery_district;
    private array $stockUpdateEntry = [];

    public function __construct(OrderRepositoryInterface              $orderRepositoryInterface,
                                OrderSkusRepositoryInterface          $orderSkusRepositoryInterface,
                                OrderLogCreator                       $orderLogCreator, OrderDiscountRepositoryInterface $orderDiscountRepository,
                                OrderPaymentRepositoryInterface       $orderPaymentRepository,
                                protected Handler                     $discountHandler,
                                protected PaymentCreator              $paymentCreator,
                                protected CustomerRepositoryInterface $customerRepository,
                                protected PriceCalculation            $orderCalculator,
                                protected EmiCalculation              $emiCalculation,
                                protected StockManageByChunk          $stockManager,
    )
    {
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->orderLogCreator = $orderLogCreator;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->orderSkusRepositoryInterface = $orderSkusRepositoryInterface;
        $this->orderDiscountRepository = $orderDiscountRepository;
    }

    /**
     * @param mixed $paymentMethod
     * @return Updater
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @param mixed $paidAmount
     * @return Updater
     */
    public function setPaidAmount($paidAmount)
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    /**
     * @param mixed $discount
     * @return Updater
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param string $orderLogType
     * @return Updater
     */
    public function setOrderLogType(string $orderLogType): Updater
    {
        $this->orderLogType = $orderLogType;
        return $this;
    }

    /**
     * @param mixed $order
     * @return Updater
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param mixed $updatedSkus
     * @return Updater
     */
    public function setSkus($updatedSkus)
    {
        $this->skus = $updatedSkus;
        return $this;
    }

    /**
     * @param mixed $voucher_id
     * @return Updater
     */
    public function setVoucherId($voucher_id)
    {
        $this->voucher_id = $voucher_id;
        return $this;
    }

    /**
     * @param mixed $note
     * @return Updater
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param mixed $delivery_address
     * @return Updater
     */
    public function setDeliveryAddress($delivery_address)
    {
        $this->delivery_address = $delivery_address;
        return $this;
    }

    /**
     * @param mixed $delivery_mobile
     * @return Updater
     */
    public function setDeliveryMobile($delivery_mobile)
    {
        $this->delivery_mobile = $delivery_mobile;
        return $this;
    }

    /**
     * @param mixed $delivery_name
     * @return Updater
     */
    public function setDeliveryName($delivery_name)
    {
        $this->delivery_name = $delivery_name;
        return $this;
    }

    /**
     * @param mixed $bank_transaction_charge
     * @return Updater
     */
    public function setBankTransactionCharge($bank_transaction_charge)
    {
        $this->bank_transaction_charge = $bank_transaction_charge;
        return $this;
    }

    /**
     * @param mixed $delivery_charge
     * @return Updater
     */
    public function setDeliveryCharge($delivery_charge)
    {
        $this->delivery_charge = $delivery_charge;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return Updater
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $emi_month
     * @return Updater
     */
    public function setEmiMonth($emi_month)
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @param mixed $sales_channel_id
     * @return Updater
     */
    public function setSalesChannelId($sales_channel_id)
    {
        $this->sales_channel_id = $sales_channel_id;
        return $this;
    }

    /**
     * @param mixed $status
     * @return Updater
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param mixed $customer_id
     * @return Updater
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param mixed $order_id
     * @return Updater
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param mixed $partner_id
     * @return Updater
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    public function setInvoiceLink(string $invoice)
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @param string|null $delivery_vendor_name
     * @return Updater
     */
    public function setDeliveryVendorName(?string $delivery_vendor_name): Updater
    {
        $this->delivery_vendor_name = $delivery_vendor_name;
        return $this;
    }

    /**
     * @param string|null $delivery_request_id
     * @return Updater
     */
    public function setDeliveryRequestId(?string $delivery_request_id): Updater
    {
        $this->delivery_request_id = $delivery_request_id;
        return $this;
    }

    /**
     * @param string|null $delivery_thana
     * @return Updater
     */
    public function setDeliveryThana(?string $delivery_thana): Updater
    {
        $this->delivery_thana = $delivery_thana;
        return $this;
    }

    /**
     * @param string|null $delivery_district
     * @return Updater
     */
    public function setDeliveryDistrict(?string $delivery_district): Updater
    {
        $this->delivery_district = $delivery_district;
        return $this;
    }


    /**
     * @throws Exception
     */
    public function update()
    {
        try {
            DB::beginTransaction();
            $previous_order = $this->order->replicate();
            $this->calculateOrderChangesAndUpdateSkus();
            if (isset($this->customer_id)) {
                $this->updateCustomer();
                $this->setDeliveryNameAndMobile();
            }
            $this->orderRepositoryInterface->update($this->order, $this->makeData());
            if (isset($this->voucher_id)) $this->updateVoucherDiscount();
            $this->updateOrderPayments();
            if (isset($this->discount)) $this->updateDiscount();
            $this->refundIfEligible();
            $this->createLog($previous_order, $this->order->refresh());
            if ($this->paymentMethod == PaymentMethods::EMI) {
                $this->validateEmiAndCalculateChargesForOrder($this->order->refresh());
            }
            if ($this->order->status == Statuses::PENDING || $this->order->status == Statuses::PROCESSING)
                $this->calculateDeliveryChargeAndSave($this->order);

            event(new OrderUpdated($this->order->refresh(), $this->orderProductChangeData ?? []));
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function calculateDeliveryChargeAndSave(Order $order): bool
    {
        /** @var OrderDeliveryPriceCalculation $deliveryPriceCalculation */
        $deliveryPriceCalculation = app(OrderDeliveryPriceCalculation::class);
        list($delivery_method, $delivery_charge) = $deliveryPriceCalculation->setOrder($order)->calculateDeliveryCharge();
        if ($delivery_charge) $order->update(['delivery_vendor_name'=> $delivery_method, 'delivery_charge' => $delivery_charge]);
        return false;
    }

    public function makeData(): array
    {
        $data = [];
        if (isset($this->sales_channel_id)) $data['sales_channel_id'] = $this->sales_channel_id;
        if (isset($this->emi_month)) $data['emi_month'] = $this->emi_month;
        if (isset($this->interest)) $data['interest'] = $this->interest;
        if (isset($this->delivery_charge)) {
            $data['delivery_charge'] = $this->delivery_charge;
            $this->setOrderLogType(OrderLogTypes::PRODUCTS_AND_PRICES);
        }
        if (isset($this->bank_transaction_charge)) $data['bank_transaction_charge'] = $this->bank_transaction_charge;
        if (isset($this->delivery_name)) $data['delivery_name'] = $this->delivery_name;
        if (isset($this->delivery_mobile)) $data['delivery_mobile'] = $this->delivery_mobile;
        if (isset($this->delivery_address)) $data['delivery_address'] = $this->delivery_address;
        if (isset($this->note)) $data['note'] = $this->note;
        if (isset($this->delivery_vendor_name)) $data['delivery_vendor_name'] = $this->delivery_vendor_name;
        if (isset($this->delivery_request_id)) $data['delivery_request_id'] = $this->delivery_request_id;
        if (isset($this->delivery_thana)) $data['delivery_thana'] = $this->delivery_thana;
        if (isset($this->delivery_district)) $data['delivery_district'] = $this->delivery_district;
        if (isset($this->invoice)) $data['invoice'] = $this->invoice;
        if (isset($this->voucher_id)) {
            $data['voucher_id'] = $this->voucher_id;
            $this->setOrderLogType(OrderLogTypes::PRODUCTS_AND_PRICES);
        }

        return $data + $this->modificationFields(false, true);
    }

    private function setExistingOrder()
    {
        $previous_order = clone $this->order;
        $order = $previous_order;
        $order->items = $previous_order->items;
        $order->customer = $previous_order->customer;
        $order->payments = $previous_order->payments;
        $order->discounts = $previous_order->discounts;
        return $previous_order;
    }

    private function createLog($previous_order, $updated_order)
    {
        $this->setPreviousOrder($previous_order);
        $this->setNewOrder($updated_order);
        $this->orderLogCreator->create();
    }

    private function getTypeOfChangeLog(): string
    {
        return $this->orderLogType;
    }

    private function setPreviousOrder($order)
    {
        /** @var OrderObject $orderObject */
        $orderObject = app(OrderObject::class);
        $orderObject->setOrder($order);
        $this->orderLogCreator->setExistingOrderData(json_encode($orderObject))->setOrderId($this->order_id);
    }

    private function setNewOrder($order)
    {
        /** @var OrderObject $orderObject */
        $orderObject = app(OrderObject::class);
        $orderObject->setOrder($order);
        $this->orderLogCreator->setChangedOrderData(json_encode($orderObject))->setType($this->getTypeOfChangeLog());
    }

    /**
     * @throws OrderException
     * @throws ValidationException
     */
    private function calculateOrderChangesAndUpdateSkus()
    {
        if ($this->skus === null) {
            return;
        }
        $this->validateOrderSkus();
        /** @var OrderComparator $comparator */
        $comparator = App::make(OrderComparator::class);
        $comparator->setOrder($this->order)->setOrderNewSkus($this->skus)->compare();

        if ($comparator->isProductDeleted()) {
            /** @var DeleteProductFromOrder $updater */
            $updater = OrderUpdateFactory::getProductDeletionUpdater($this->order, $this->skus);
            $return_data = $updater->update();
            $this->orderProductChangeData['deleted'] = $return_data;
            $this->stockUpdateEntry = array_merge($this->stockUpdateEntry, $updater->getStockUpdateData());
        }
        if ($comparator->isProductAdded()) {
            /** @var AddProductInOrder $updater */
            $updater = OrderUpdateFactory::getProductAddingUpdater($this->order, $this->skus);
            $return_data = $updater->update();
            $this->orderProductChangeData['new'] = $return_data;
            $this->stockUpdateEntry = array_merge($this->stockUpdateEntry, $updater->getStockUpdateData());
        }
        if ($comparator->isProductUpdated()) {
            /** @var UpdateProductInOrder $updater */
            $updater = OrderUpdateFactory::getOrderProductUpdater($this->order, $this->skus);
            $return_data = $updater->setProductChangeTrackerList($comparator->getProductChangeTrackerList())->update();
            $this->orderProductChangeData['refund_exchanged'] = $return_data;
            $this->stockUpdateEntry = array_merge($this->stockUpdateEntry, $updater->getStockUpdateData());
        }

        if (isset($return_data)) {
            $this->orderProductChangeData['paid_amount'] = is_null($this->paidAmount) ? 0 : $this->paidAmount;
            $this->orderLogType = OrderLogTypes::PRODUCTS_AND_PRICES;
        }
    }


    private function updateOrderPayments()
    {
        if (isset($this->paymentMethod) && ($this->paymentMethod == PaymentMethods::CASH_ON_DELIVERY || $this->paymentMethod == PaymentMethods::QR_CODE) && $this->paidAmount > 0) {
            $cash_details = json_encode(['payment_method_en' => 'Cash', 'payment_method_bn' => ' নগদ গ্রহন', 'payment_method_icon' => config('s3.url') . 'pos/payment/cash_v2.png']);
            $this->paymentCreator->setOrderId($this->order->id)->setAmount($this->paidAmount)->setMethod(PaymentMethods::CASH_ON_DELIVERY)->setMethodDetails($cash_details)
                ->setTransactionType(TransactionTypes::CREDIT)->create();
            $this->orderLogType = OrderLogTypes::PRODUCTS_AND_PRICES;
        } elseif ($this->paidAmount > 0 && $this->paymentMethod == PaymentMethods::PAYMENT_LINK) {
            $this->paymentCreator->setOrderId($this->order->id)->setAmount($this->paidAmount)->setMethod(PaymentMethods::PAYMENT_LINK)
                ->setTransactionType(TransactionTypes::CREDIT)->create();
            $this->orderLogType = OrderLogTypes::PRODUCTS_AND_PRICES;
        }
    }


    private function updateDiscount()
    {
        $discountData = json_decode($this->discount);
        $originalAmount = $discountData->original_amount;
        $hasDiscount = $this->validateDiscountData($originalAmount);
        if ($hasDiscount) $this->orderDiscountRepository->where('order_id', $this->order_id)->update($this->withUpdateModificationField($this->makeOrderDiscountData($discountData)));
        $this->setOrderLogType(OrderLogTypes::PRODUCTS_AND_PRICES);
    }

    private function validateDiscountData($originalAmount): bool
    {
        if ($originalAmount > 0) return true;
        return false;
    }

    private function makeOrderDiscountData($discountData): array
    {
        $data = [];
        $data['order_id'] = $this->order_id;
        $data['original_amount'] = $discountData->original_amount;
        $data['is_percentage'] = $discountData->is_percentage;
        $data['cap'] = $discountData->cap ?? null;
        $data['discount_details'] = $discountData->discount_details;
        $data['discount_id'] = $discountData->discount_id ?? null;
        $data['type_id'] = $discountData->item_id ?? null;
        if ($discountData->is_percentage) {
            /** @var PriceCalculation $orderPriceCalculation */
            $orderPriceCalculation = app(PriceCalculation::class);
            $orderTotalBill = $orderPriceCalculation->setOrder($this->order)->getProductDiscountedPrice();
            $data['amount'] = ($orderTotalBill * $discountData->is_percentage) / 100.00;
        } else {
            $data['amount'] = $discountData->original_amount;
        }
        return $data;
    }

    private function updateVoucherDiscount()
    {
        if (!isset($this->voucher_id)) return false;
        if (isset($this->voucher_id) && is_null($this->voucher_id)) {
            return $this->orderDiscountRepository->where('order_id', $this->order_id)->where('type', 'voucher')->delete();
        } else {
            $this->orderDiscountRepository->where('order_id', $this->order_id)->where('type', 'voucher')->delete();
            return $this->discountHandler->setVoucherId($this->voucher_id)->voucherDiscountCalculate($this->order);
        }
    }

    private function updateCustomer()
    {
        return $this->orderRepositoryInterface->where('id', $this->order->id)->update($this->withUpdateModificationField(['customer_id' => $this->customer_id]));
    }

    private function setDeliveryNameAndMobile()
    {
        $customer = $this->customerRepository->where('id', $this->customer_id)->first();
        $this->delivery_name = $customer->name;
        $this->delivery_mobile = $customer->mobile;
    }

    private function refundIfEligible()
    {
        $this->orderCalculator->setOrder($this->order->refresh());
        $total_paid = $this->orderCalculator->getPaid();
        $total_amount = $this->orderCalculator->getDiscountedPrice();
        $refunded = false;
        if ($total_paid > $total_amount) {
            $refund_amount = $total_paid - $total_amount;
            $this->paymentCreator->setOrderId($this->order->id);
            $this->paymentCreator->setAmount($refund_amount);
            $this->paymentCreator->setMethod(PaymentMethods::CASH_ON_DELIVERY);
            $this->paymentCreator->setTransactionType(TransactionTypes::DEBIT);
            $this->paymentCreator->create();
            $refunded = true;
        }
        if (($refunded == false && $this->orderCalculator->getDue() > 0)) {
            $this->order->paid_at = null;
            $this->order->update($this->modificationFields(false, true));
        }
    }

    /**
     * @throws OrderException|BaseClientServerError
     */
    private function validateEmiAndCalculateChargesForOrder(Order $order)
    {
        $amount = $this->orderCalculator->setOrder($order)->getDue();
        $min_emi_amount = config('emi.minimum_emi_amount');
        if ($amount < $min_emi_amount) {
            throw new OrderException("Emi is not available for order amount less than " . $min_emi_amount, 400);
        }
        $data = $this->emiCalculation->setEmiMonth((int)$order->emi_month)->setAmount($amount)->getEmiCharges();
        $emi_data['interest'] = !is_null($this->interest) ? $this->interest : $data['total_interest'];
        $emi_data['bank_transaction_charge'] = !is_null($this->bank_transaction_charge) ? $this->bank_transaction_charge : $data['bank_transaction_fee'];
        $order->update($this->withUpdateModificationField($emi_data));
    }

    public function updateStock()
    {
        if (count($this->stockUpdateEntry) > 0) {
            foreach ($this->stockUpdateEntry as $each_data) {
                if ($each_data['operation'] == StockManager::STOCK_INCREMENT)
                    $this->stockManager->setSku($each_data['sku_detail'])->increaseAndInsertInChunk($each_data['quantity']);
                if ($each_data['operation'] == StockManager::STOCK_DECREMENT)
                    $this->stockManager->setSku($each_data['sku_detail'])->decreaseAndInsertInChunk($each_data['quantity']);
            }
            $this->stockManager->updateStock();
        }
    }

    /**
     * @throws OrderException
     */
    private function validateOrderSkus()
    {
        $skus = collect(json_decode($this->skus, true));
        $requested_order_sku_ids = $skus->whereNotNull('order_sku_id')->pluck('order_sku_id');
        $current_order_sku_ids = $this->order->orderSkus->pluck('id');
        if ($requested_order_sku_ids->diff($current_order_sku_ids)->count() > 0) {
            throw new OrderException('Invalid order sku item given', 400);
        }
    }

    public function updatePaidOrderCustomer()
    {
        DB::beginTransaction();
        $previous_order = $this->setExistingOrder();
        $this->updateCustomer();
        $this->setDeliveryNameAndMobile();
        $this->orderRepositoryInterface->update($this->order, $this->makeData());
        $this->createLog($previous_order, $this->order->refresh());
        event(new OrderCustomerUpdated($this->order->refresh()));
        DB::commit();
    }
}
