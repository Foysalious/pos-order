<?php namespace App\Http\Reports;


use App\Interfaces\OrderRepositoryInterface;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Services\APIServerClient\ApiServerClient;
use App\Services\Order\PriceCalculation;
use App\Services\Order\Updater;
use App\Traits\ModificationFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class GenerateInvoice
{
    use ModificationFields;

    protected ApiServerClient $client;
    /**
     * @var Updater
     */
    private Updater $updater;
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    public function __construct(ApiServerClient $client, Updater $updater, OrderRepositoryInterface $orderRepository)
    {
        $this->client = $client;
        $this->updater = $updater;
        $this->orderRepository = $orderRepository;
    }


    public function generateInvoice($orderID)
    {
        $pdf_handler = new PdfHandler();
        $order = Order::find($orderID);

        /** @var PriceCalculation $price_calculator */
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($order);
        $partner = Partner::find($order->partner_id);
        $partner = $this->client->setBaseUrl()->get('v2/partners/' . $partner->sub_domain);
        $info = [
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
            $customer = Customer::find($order->customer_id);
            $info['user'] = [
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'address' => $order->address
            ];
        }
        $invoice_name = 'pos_order_invoice_' . $order->id;
        $link = $pdf_handler->setData($info)->setName($invoice_name)->setViewFile('transaction_invoice')->save();
        //dd($link,$order->partner_id,$orderID);
        $orderDetails = $this->orderRepository->where('partner_id', $order->partner_id)->find($orderID);
        $this->updater->setPartnerId($partner)->setOrderId($orderID)->setOrder($orderDetails)->setInvoiceLink($link)->update();
        return $link;
    }
}
