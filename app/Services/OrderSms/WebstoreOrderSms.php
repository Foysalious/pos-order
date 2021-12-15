<?php namespace App\Services\OrderSms;

use App\Jobs\Job;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class WebstoreOrderSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const SMS_TYPE = 'web_store_order';

    private $orderId;
    protected $tries = 1;
    protected $status;
    private $partnerId;


    public function __construct($partnerId, $orderId)
    {
        $this->orderId = $orderId;
        $this->partnerId = $partnerId;
    }

    public function handle()
    {
        if ($this->attempts() > 2) return;
        $data = [
            'type' => self::SMS_TYPE,
            'type_id' => $this->orderId,
            'partner_id' => $this->partnerId
        ];
        $client = new Client();
        $client->post(config('sheba.api_url') . '/pos/v1/send-sms', $data);
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        app('sentry')->captureException($exception);
    }

}
