<?php namespace App\Jobs\Order;

use App\Services\Order\PriceCalculation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class RewardOnOrderCreate
{
    protected $model;

    use InteractsWithQueue, SerializesModels;

    private const ORDER_CREATE_REWARD_EVENT_NAME = 'pos_order_create';
    private const ORDER_CREATE_REWARDABLE_TYPE = 'partner';

    public function __construct($model)
    {
        $this->model = $model;
        $this->queue = 'reward';
    }

    public function handle()
    {
        $order =  $this->model;
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($order);
        $data = [
            'event' => self::ORDER_CREATE_REWARD_EVENT_NAME,
            'rewardable_type' => self::ORDER_CREATE_REWARDABLE_TYPE,
            'rewardable_id' => $order->partner_id,
            'event_data' => [
                'id' => $order->id,
                'paymnet_status' => $order->status,
                'net_bill' => $price_calculator->getOriginalPrice(),
                'client_pos_order_id' => request()->client_pos_order_id ?? null,
                'partner_wise_order_id' => $order->partner_wise_order_id,
                'portal_name' => $order->apiRequest->portal_name
            ]
        ];
        try{
            $client = new Client();
            $client->post(config('sheba.api_url').'/pos/v1/reward/action',$data);
        }catch (GuzzleException $e){}

    }

    public function getJobId()
    {
        // TODO: Implement getJobId() method.
    }

    public function getRawBody()
    {
        // TODO: Implement getRawBody() method.
    }

}
