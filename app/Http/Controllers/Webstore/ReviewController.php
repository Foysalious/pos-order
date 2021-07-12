<?php namespace App\Http\Controllers\Webstore;


use App\Http\Controllers\Controller;
use App\Services\Webstore\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{

    private ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function getProductIdsByRating($partner_id,Request $request)
    {
        return $this->reviewService->setPartnerId($partner_id)->setRatings($request->ratings)->getProductIdsByRating();
    }
}
