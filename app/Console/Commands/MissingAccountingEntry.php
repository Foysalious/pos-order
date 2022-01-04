<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Repositories\Accounting\Constants\UserType;
use App\Services\Accounting\AccountingEntryClient;
use App\Services\Accounting\CreateEntry;
use App\Services\Accounting\Exceptions\AccountingEntryServerError;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MissingAccountingEntry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:missing-accounting-entry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     */
    public function handle()
    {
        $partnerId = (int)$this->ask('Partner Id');
        $startDateTime = $this->ask('Start Date Time');
        $endDateTime = $this->ask('End Date Time');
        $startDateTimeUTC = convertTimezone(Carbon::parse($startDateTime)->shiftTimezone('Asia/Dhaka'), 'UTC')->format('Y-m-d H:i:s');
        $endDateTimeUTC = convertTimezone(Carbon::parse($endDateTime)->shiftTimezone('Asia/Dhaka'), 'UTC')->format('Y-m-d H:i:s');
        $url = 'api/entries/partner/orders?start_date=' . $startDateTime .'&end_date=' . $endDateTime;
        /** @var AccountingEntryClient $client */
        $client = app(AccountingEntryClient::class);
        $response = $client->setUserType(UserType::PARTNER)->setUserId($partnerId)->get($url);
        Order::where('partner_id', $partnerId)->whereBetween('created_at', [$startDateTimeUTC, $endDateTimeUTC])->whereNotIn('id', $response)
            ->with(['orderSkus' => function($q) {
                $q->with('discount');
            }, 'discounts', 'payments'])->chunk(50, function ($orders) {
            foreach ($orders as $order) {
                /** @var CreateEntry $createEntry */
                $createEntry =  app(CreateEntry::class);
                $createEntry->setOrder($order)->create();
                dump("Entry For order " . $order->id);
            }
        });
        dump("done");
    }
}
