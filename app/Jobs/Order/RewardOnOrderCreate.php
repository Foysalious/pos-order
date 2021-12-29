<?php namespace App\Jobs\Order;

use App\Jobs\Job;
use App\Services\Order\PriceCalculation;
use App\Services\Reward\RewardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;


class RewardOnOrderCreate extends Job implements ShouldQueue
{
    protected $model;

    use Dispatchable, InteractsWithQueue, SerializesModels;

    private const ORDER_CREATE_REWARD_EVENT_NAME = 'pos_order_create';
    private const ORDER_CREATE_REWARDABLE_TYPE = 'partner';

    public function __construct($model)
    {
        $this->model = $model;
    }


    public function handle()
    {
        if($this->attempts() > 2) return;
        $order = $this->model;
        $price_calculator = (App::make(PriceCalculation::class))->setOrder($order);
        $data = [
            'event' => self::ORDER_CREATE_REWARD_EVENT_NAME,
            'rewardable_type' => self::ORDER_CREATE_REWARDABLE_TYPE,
            'rewardable_id' => $order->partner_id,
            'event_data' => json_encode([
                'id' => $order->id,
                'payment_status' => $order->paid_at ? 'Paid' : 'Due',
                'net_bill' => $price_calculator->getOriginalPrice(),
                'client_pos_order_id' => request()->client_pos_order_id ?? null,
                'partner_wise_order_id' => $order->partner_wise_order_id,
                'portal_name' => $order->apiRequest->portal_name
            ])
        ];
        /** @var RewardService $rewardService */
        $rewardService = app(RewardService::class);
        $rewardService->setData($data)->store();
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
