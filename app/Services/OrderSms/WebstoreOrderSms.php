<?php namespace App\Services\OrderSms;

use App\Jobs\Job;
use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebstoreOrderSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    const SMS_TYPE = 'WebStoreOrder';

    /**
     * @var Order
     */
    private $order;
    protected $tries = 1;
    protected $status;


    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        if ($this->attempts() > 2) return;
        $data = [
            'type' => self::SMS_TYPE,
            'type_id' => $this->orderId
        ];
        try {
            $client = new Client();
            $client->post(config('sheba.api_url') . '/pos/v1/send-sms', $data);
        } catch (GuzzleException $e) {
        }
    }

}
