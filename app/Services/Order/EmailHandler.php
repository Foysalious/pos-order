<?php namespace App\Services\Order;

use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailHandler
{
    /**
     * @var Order
     */
    private $order;

    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function handle()
    {
        Mail::send('emails.pos-order-bill', ['order' => $this->order], function ($m) {
            $m->to($this->order->customer->email)->subject('Order Bills');
        });
    }
}
