<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\PortalStatusService;
use Illuminate\Http\JsonResponse;

class PortalStatusController extends Controller
{
    public function __invoke(int $website, PortalStatusService $status): JsonResponse
    {
        return response()->json($status->forWebsite(Website::findOrFail($website)));
    }
}
