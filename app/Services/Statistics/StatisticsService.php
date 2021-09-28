<?php namespace App\Services\Statistics;


use App\Http\Requests\StatisticsRequest;
use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\BaseService;
use App\Services\Order\Constants\SalesChannelIds;
use App\Services\Order\Constants\Statuses;
use App\Services\Order\PriceCalculation;
use stdClass;

class StatisticsService extends BaseService
{
    public function __construct(protected OrderRepositoryInterface $orderRepository){}

    public function index(StatisticsRequest $request, int $partnerId)
    {
        $timeFrame = makeTimeFrame($request);
        $statuses = new stdClass();
        $this->orderRepository->getOrderStatusStatByPartner($partnerId)->each(function ($status) use ($statuses) {
            $name = $status->status;
            $statuses->$name = $status->count;
        });
        $webstore_orders = $this->orderRepository->getOrdersBetweenDatesByPartner($partnerId, $timeFrame, [SalesChannelIds::WEBSTORE]);
        $webstore_orders->map(function ($webstore_order) {
            /** @var Order $webstore_order */
            /** @var PriceCalculation $priceCalculation */
            $priceCalculation = app(PriceCalculation::class);
            $webstore_order->sale = $priceCalculation->setOrder($webstore_order)->getDiscountedPrice();
        });
        $webstore_sales_count = $webstore_orders->count();
        $webstore_sales = $webstore_orders->where('status', Statuses::COMPLETED)->sum('sale');
        $statistics = [
            'status_count' => $statuses,
            'total_order' => $webstore_sales_count,
            'total_sales' => $webstore_sales
        ];
        return $this->success('Successful', ['statistics' => $statistics]);

    }


}
