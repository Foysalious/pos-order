<?php namespace App\Http\Controllers;


use App\Http\Requests\StatisticsRequest;
use App\Services\Statistics\StatisticsService;

class StatisticsController extends Controller
{
    public function __construct(protected StatisticsService $statisticsService){}

    public function index(StatisticsRequest $request, int $partnerId)
    {
        return $this->statisticsService->index($request, $partnerId);
    }
}
