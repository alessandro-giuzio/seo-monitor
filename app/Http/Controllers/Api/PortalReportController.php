<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\PortalReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalReportController extends Controller
{
    public function __invoke(Request $request, int $website, PortalReportService $reports): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        return response()->json($reports->forMonth(Website::findOrFail($website), $validated['month']));
    }
}
