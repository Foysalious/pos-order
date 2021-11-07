<?php namespace App\Services\Order;

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
        $partner = $this->client->get('v2/partners/' . $this->order->partner->name);
        $order_info = $this->orderService->getOrderDetails($this->order->partner_id, $this->order->id);
        $order_info = $order_info->getData()->order->items;
        Mail::send('emails.pos-order-bill', ['order' => $this->order, 'partner' => $partner,'order_info'=>$order_info], function ($m) {
            $m->to($this->order->customer->email)->subject('Order Bills');
        });
    }
}
