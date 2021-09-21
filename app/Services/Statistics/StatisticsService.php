<?php namespace App\Services\Statistics;


use App\Helper\TimeFrame;
use App\Http\Requests\StatisticsRequest;
use App\Interfaces\OrderRepositoryInterface;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsService extends BaseService
{
    public function __construct(protected OrderRepositoryInterface $orderRepository){}

    public function index(StatisticsRequest $request, int $partnerId)
    {
        $timeFrame = makeTimeFrame($request);
        $statuses = $this->orderRepository->getOrderStatusStatByPartner($partnerId)->map(function ($status) {
            return [$status->status => $status->count];
        });
        $statistics = [
            'status_count' => $statuses
        ];
        return $this->success('Successful', ['statistics' => $statistics]);

    }


}
