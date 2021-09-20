<?php

use App\Helper\TimeFrame;
use Carbon\Carbon;
use Illuminate\Http\Request;

if (!function_exists('findStartEndDateOfAMonth')) {
    /**
     * @param $month
     * @param $year
     * @return array
     */
    function findStartEndDateOfAMonth($month = null, $year = null): array
    {
        if ($month == 0 && $year != 0) {
            $start_time = Carbon::now()->year($year)->month(1)->day(1)->hour(0)->minute(0)->second(0);
            $end_time   = Carbon::now()->year($year)->month(12)->day(31)->hour(23)->minute(59)->second(59);
            return [
                'start_time'    => $start_time,
                'end_time'      => $end_time,
                'days_in_month' => 31
            ];
        }

        if (empty($month)) $month = Carbon::now()->month;
        if (empty($year)) $year = Carbon::now()->year;
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start_time    = Carbon::create($year, $month, 1, 0, 0, 0);
        $end_time      = Carbon::create($year, $month, $days_in_month, 23, 59, 59);
        return [
            'start_time'    => $start_time,
            'end_time'      => $end_time,
            'days_in_month' => $days_in_month
        ];
    }
}
if (!function_exists('makeTimeFrame')) {
    /**
     * @param Request $request
     * @param TimeFrame $time_frame
     * @return TimeFrame
     */
    function makeTimeFrame(Request $request)
    {
        $time_frame = app(TimeFrame::class);
        $date = Carbon::parse($request->date);
        switch ($request->frequency) {
            case "day":
                $time_frame = $time_frame->forADay($date);
                break;
            case "week":
                $time_frame = $time_frame->forSomeWeekFromNow($request->week);
                break;
            case "month":
                $time_frame = $time_frame->forAMonth($request->month, $request->year);
                break;
            case "year":
                $time_frame = $time_frame->forAYear($request->year);
                break;
            case "quarter":
                $time_frame = $time_frame->forAQuarter($date);
                break;
            default:
                echo "Invalid time frame";
        }
        return $time_frame;
    }
}
