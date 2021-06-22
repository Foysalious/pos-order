<?php namespace App\Services\Discount;


use App\Interfaces\OrderDiscountRepositoryInterface;
use App\Models\Order;
use App\Services\Discount\Constants\DiscountTypes;
use App\Services\Discount\DTO\Params\Order as OrderParam;
use App\Services\Discount\DTO\Params\Sku as SkuParam;
use App\Services\Discount\DTO\Params\Voucher as VoucherParams;
use Illuminate\Validation\ValidationException;

class Handler
{
    /** @var OrderDiscountRepositoryInterface $orderDiscountRepo */
    private OrderDiscountRepositoryInterface $orderDiscountRepo;
    private $type;
    private $data;
    /** @var Order $order */
    private $order;
    private $skuData;
    private $orderSkuId;

    public function __construct(OrderDiscountRepositoryInterface $orderDiscountRepo)
    {
        $this->orderDiscountRepo = $orderDiscountRepo;
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
        $discount_data = $this->getData();
        if (empty($discount_data)) return;
        $discount_data['order_id'] = $this->order->id;
        $this->orderDiscountRepo->create($discount_data);
    }

    public function getData()
    {
        $order_discount = null;
        if ($this->type == DiscountTypes::ORDER) {
            $order_discount = new OrderParam();
            $order_discount->setOrder($this->order)
                ->setType($this->type)
                ->setOriginalAmount($this->data['discount'])
                ->setIsPercentage($this->data['is_discount_percentage']);
        } else if ($this->type == DiscountTypes::SKU) {
            $order_discount = new SkuParam();
            $order_discount->setType($this->type)
                ->setAmount($this->skuData['discount']['discount'] * $this->skuData['quantity'])
                ->setOriginalAmount($this->skuData['discount']['discount'])
                ->setIsPercentage($this->skuData['discount']['is_discount_percentage'])
                ->setOrderSkuId($this->orderSkuId);
        } else if($this->type == DiscountTypes::VOUCHER) {
            $order_discount = new VoucherParams();
            $order_discount->setOrder($this->order)
                ->setType($this->type)
                ->setTotalAmount($this->data['voucher']['amount'])
                ->setIsPercentage($this->data['voucher']['is_amount_percentage']);
        }

        return $order_discount->getData();
    }
}
