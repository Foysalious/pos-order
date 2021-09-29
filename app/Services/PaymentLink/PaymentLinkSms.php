<?php namespace App\Services\PaymentLink;

use App\Jobs\Job;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PaymentLinkSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const SMS_TYPE = 'payment_link';

    private $orderId;
    protected $tries = 1;
    protected $status;
    private $partnerId;


    public function __construct($message, $mobile, $orderId)
    {
        $this->orderId = $orderId;
        $this->message = $message;
        $this->mobile = $mobile;
    }

    public function handle()
    {
        if ($this->attempts() > 2) return;
        $data = [
            'type' => self::SMS_TYPE,
            'type_id' => $this->orderId,
            'partner_id' => $this->partnerId
        ];
        try {
            $client = new Client();
            $client->post(config('sheba.api_url') . '/pos/v1/send-sms', $data);
        } catch (GuzzleException $e) {
        }
    }

}
