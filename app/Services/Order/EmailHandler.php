<?php namespace App\Services\Order;

use App\Models\Customer;
use App\Models\Order;
use App\Services\APIServerClient\ApiServerClient;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailHandler
{
    /**
     * @var Order
     */
    private $order;
    private ApiServerClient $client;
    private OrderService $orderService;

    public function __construct(ApiServerClient $client, OrderService $orderService)
    {
        $this->client = $client;
        $this->orderService = $orderService;
    }

    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function handle()
    {
        $partner = $this->client->get('v2/partners/' . $this->order->partner->sub_domain);
        $order_info = $this->orderService->getOrderInfo($this->order->partner_id, $this->order->id);
        $customer_id = $order_info['customer_id'];
        $order_info = $order_info['items'];
        $customer= Customer::where('partner_id',$this->order->partner_id)->where('id',$customer_id)->first();
        Mail::send('emails.pos-order-bill', ['order' => $this->order, 'partner' => $partner, 'order_info' => $order_info,'customer' => $customer], function ($m) {
            $m->to($this->order->customer->email)->subject('Order Bills');
        });
    }
}
