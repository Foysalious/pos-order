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
    private Customer $customer;

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

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function handle()
    {
        $partner = $this->client->get('v2/partners/' . $this->order->partner->sub_domain);
        $order_info = $this->orderService->getOrderInfo($this->order->partner_id, $this->order->id);
        $order_info = $order_info['items'];
        Mail::send('emails.pos-order-bill', ['order' => $this->order, 'partner' => $partner, 'order_info' => $order_info, 'customer' => $this->customer], function ($m) {
            $m->to($this->customer->email)->subject('Order Bills');
        });
    }
}
