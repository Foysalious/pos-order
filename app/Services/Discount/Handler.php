<?php namespace App\Services\Discount;


use App\Interfaces\OrderDiscountRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Discount\DTO\Params\Order as OrderParam;
use App\Services\Discount\DTO\Params\Sku as SkuParam;
use App\Services\Discount\DTO\Params\Voucher as VoucherParams;
use App\Traits\ModificationFields;
use Illuminate\Validation\ValidationException;

class Handler
{
    use ModificationFields;
    /** @var OrderDiscountRepositoryInterface $orderDiscountRepo */
    private OrderDiscountRepositoryInterface $orderDiscountRepo;
    private $type;
    private $data;
    /** @var Order $order */
    private $order;
    private $skuData;
    private $orderSkuId;
    private ?int $voucher_id;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderDiscountRepositoryInterface $orderDiscountRepo, OrderRepositoryInterface $orderRepository)
    {
        $this->orderDiscountRepo = $orderDiscountRepo;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param $type
     * @return $this
     * @throws ValidationException
     */
    public function setType($type)
    {
        DiscountTypes::checkIfValid($type);
        $this->type = $type;
        return $this;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function setSkuData($skuData)
    {
        $this->skuData = $skuData;
        return $this;
    }

    /**
     * @param mixed $orderSkuId
     * @return Handler
     */
    public function setOrderSkuId($orderSkuId)
    {
        $this->orderSkuId = $orderSkuId;
        return $this;
    }

    /**
     * @param $data
     * @return Handler
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param int|null $voucher_id
     * @return Handler
     */
    public function setVoucherId(?int $voucher_id): Handler
    {
        $this->voucher_id = $voucher_id;
        return $this;
    }

    public function hasDiscount()
    {
        if ($this->type == DiscountTypes::ORDER) {
            return isset($this->data['discount']) && $this->data['discount'] > 0;
        } else if ($this->type == DiscountTypes::SKU) {
            return $this->skuData['discount']['discount'] && $this->skuData['discount']['discount'] > 0;
        }
        return false;
    }

    public function create()
    {
        $discount_data = $this->makeDiscountData();
        if (empty($discount_data)) return false;
        $discount_data['order_id'] = $this->order->id;
        return $this->orderDiscountRepo->create($this->withCreateModificationField($discount_data));
    }

    public function makeDiscountData()
    {
        $order_discount = null;
        if ($this->type == DiscountTypes::ORDER) {
            $order_discount = $this->getOrderDiscount();
        } else if ($this->type == DiscountTypes::SKU) {
            $order_discount = $this->getSkuDiscount();
        } else if($this->type == DiscountTypes::VOUCHER) {
            $order_discount = $this->getVoucherDiscount();
        }

        return $order_discount->getData();
    }

    public function voucherDiscountCalculate($order)
    {
        $voucherDetails = $this->orderRepository->getVoucherInformation($this->voucher_id);
        return $this->setOrder($order)->setType(DiscountTypes::VOUCHER)->setData($voucherDetails)->create();
    }

    private function getOrderDiscount()
    {
        $order_discount = new OrderParam();
        $order_discount->setOrder($this->order)
            ->setType($this->type)
            ->setOriginalAmount($this->data['discount'])
            ->setIsPercentage($this->data['is_discount_percentage']);
        return $order_discount;
    }

    private function getSkuDiscount()
    {
        $order_discount = new SkuParam();
        $order_discount->setType($this->type)
            ->setAmount($this->getDiscount())
            ->setOriginalAmount($this->skuData['discount']['discount'])
            ->setIsPercentage($this->skuData['discount']['is_discount_percentage'])
            ->setOrderSkuId($this->orderSkuId);
        return $order_discount;
    }

    private function getVoucherDiscount()
    {
        $order_discount = new VoucherParams();
        $order_discount->setOrder($this->order)
            ->setType($this->type)
            ->setTotalAmount($this->data['voucher']['amount'])
            ->setIsPercentage($this->data['voucher']['is_amount_percentage'])
            ->setDiscountDetails([ 'promo_code' => $this->data['voucher']['code'] ]);
        return $order_discount;
    }

    public function getDiscount()
    {
        $unit_price = $this->skuData['unit_price'];
        $quantity = $this->skuData['quantity'];
        $discount = $this->skuData['discount'];
        if ($discount['is_discount_percentage']) {
            $amount = (($unit_price * $quantity) * $discount['discount']) / 100;
            if ($discount['cap']) {
                $amount = ($amount > $discount['cap']) ? $discount['cap'] : $amount;
            }
        } else {
            $amount = $discount * $quantity;
        }

        return ($amount < 0) ? 0 : (float)$amount;
    }
}
