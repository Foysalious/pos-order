<?php namespace App\Http\Controllers;

use App\Services\Partner\PartnerService;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct(protected PartnerService $partnerService)
    {
    }

    public function show(Request $request, $partnerId)
    {
        return $this->partnerService->show($partnerId);
    }

}
