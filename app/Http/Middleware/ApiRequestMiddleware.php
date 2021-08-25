<?php

namespace App\Http\Middleware;

use App\Interfaces\ApiRequestRepositoryInterface;
use Closure;
use Illuminate\Http\Request;

class ApiRequestMiddleware
{
    public function __construct(private ApiRequestRepositoryInterface $apiRequestRepository)
    {

    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $api_request = $this->apiRequestRepository->create([
            'route' => $request->fullUrl(),
            'ip_address' => getIp(),
            'user_agent' => $request->header('User-Agent'),
            'portal_name' => $request->header('portal-name'),
            'portal_version' => $request->header('Version-Code'),
        ]);

        $request->merge(['api_request' => $api_request]);
        return $next($request);
    }
}
