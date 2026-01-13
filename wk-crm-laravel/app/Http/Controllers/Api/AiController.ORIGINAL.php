<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function opportunityInsights(Request $request, AiService $aiService)
    {
        $data = $request->validate([
            'id' => ['nullable', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:100'],
        ]);

        $insight = $aiService->analyzeOpportunity($data);

        return response()->json([
            'success' => true,
            'data' => $insight,
        ]);
    }
}
