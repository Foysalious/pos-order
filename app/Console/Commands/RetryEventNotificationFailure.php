<?php

namespace App\Console\Commands;

use App\Listeners\Accounting\AccountingEventNotification;
use App\Models\EventNotification;
use App\Repositories\Accounting\Constants\UserType;
use App\Services\Accounting\AccountingEntryClient;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use App\Services\EventNotification\Request;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class RetryEventNotificationFailure extends Command
{
    use AccountingEventNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:retry-event-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry event notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws AccountingEntryServerError
     * @throws GuzzleException
     * @throws UnknownProperties
     */
    public function handle()
    {
        $event_notifications = EventNotification::where('status', 'failed')->with(['order' => function ($q) {
            $q->select('id', 'partner_id')->withTrashed();
        }])->orderBy('created_at')->get();
        foreach ($event_notifications as $event_notification) {
            try {
                /** @var Request $request */
                $request = json_decode($event_notification->request);
                $uri = $this->removeDomainFromUrl($request->url);
                /** @var AccountingEntryClient $accountingEntryClient */
                $accountingEntryClient = app(AccountingEntryClient::class);
                $accountingEntryClient->setEventNotification($event_notification)->setUserId($event_notification->order->partner_id)
                    ->setUserType(UserType::PARTNER)->call($request->method, $uri, (array)$request->json);
            } catch (\Throwable $e) {
                dump($event_notification->id . " " . $e->getMessage());
            }
        }
    }

    private function removeDomainFromUrl($url): string
    {
        $urlArr = explode('/', $url);
        $uri = '';
        for ($i = 3; $i < count($urlArr); $i++) {
            $uri .= ($urlArr[$i] . '/');
        }
        return rtrim($uri, "/");
    }
}
