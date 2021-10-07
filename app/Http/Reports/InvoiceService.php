<?php namespace App\Http\Reports;


use App\Constants\ResponseMessages;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\BaseService;
use App\Services\Order\PriceCalculation;
use App\Services\Order\Updater;
use App\Traits\ModificationFields;
use Illuminate\Support\Facades\App;


class InvoiceService extends BaseService
{
    use ModificationFields;

    private $posOrder;

    public function __construct(protected ApiServerClient $client, private Updater $updater, private OrderRepositoryInterface $orderRepository)
    {

    }

    public function setOrder($order_id)
    {
        $this->order = $this->orderRepository->find($order_id);
        return $this;
    }

    public function isAlreadyGenerated()
    {
        $this->invoiceLink = $this->order->invoice;
        return $this;
    }

    public function getInvoiceLink()
    {
        return $this->invoiceLink;
    }

    public function generateInvoice()
    {
        $pdf_handler = new PdfHandler();
        $order = $this->order;
        /** @var PriceCalculation $price_calculator */
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($order);
        $partner = $this->client->setBaseUrl()->get('v2/partners/' . $order->partner->sub_domain);
        $info = [
            'order_id'=>$order->id,
            'amount' => $price_calculator->getOriginalPrice(),
            'created_at' => $order->created_at->format('jS M, Y, h:i A'),
            'payment_receiver' => [
                'name' => $partner["info"]["name"],
                'image' => $partner["info"]["logo"],
                'mobile' => $partner["info"]["mobile"],
                'address' => $partner["info"]["address"],
            ],

            'pos_order' => $order ? [
                'items' => $order->orderSkus,
                'orderSkusCount'=>count($order->orderSkus),
                'discount' => $price_calculator->getOrderDiscount(),
                'total' => $price_calculator->getDiscountedPriceWithoutVat(),
                'grand_total' => $price_calculator->getDiscountedPriceWithoutVat(),
                'promo_discount' => $price_calculator->getPromoDiscount(),
                'paid' => $price_calculator->getPaid(),
                'due' => $price_calculator->getDue(),
                'vat' => $price_calculator->getVat(),
                'delivery_charge' => $price_calculator->getDeliveryCharge(),
            ] : null
        ];

        if ($order->customer_id) {
            $customer = $order->customer;
            $info['user'] = [
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'address' => $order->address
            ];
        }
        $invoice_name = 'pos_order_invoice_' . $order->id;
        $link = $pdf_handler->setData($info)->setName($invoice_name)->setViewFile('transaction_invoice')->save();
        $this->updater->setPartnerId($order->partner_id)->setOrderId($order->id)->setOrder($this->order)->setInvoiceLink($link)->update();
        return $this->success(ResponseMessages::SUCCESS, ['invoice' =>  $link]);
    }
}
