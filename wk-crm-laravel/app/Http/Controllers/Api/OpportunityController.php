<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OpportunityController extends Controller
{
    public function index()
    {
        $opps = Opportunity::with(['client','seller'])->orderByDesc('created_at')->paginate(25);
        return response()->json($opps);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|string',
            'client_id' => 'nullable|string',
            'seller_id' => 'nullable|string',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'probability' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string',
            'close_date' => 'nullable|date'
        ]);

        // Backward compatibility: accept either client_id or customer_id
        $data['customer_id'] = $data['customer_id'] ?? $data['client_id'] ?? null;
        unset($data['client_id']);

        $opp = Opportunity::create($data);
        return response()->json($opp, 201);
    }

    public function show($id)
    {
        $opp = Opportunity::with(['client','seller'])->findOrFail($id);
        return response()->json($opp);
    }

    public function update(Request $request, $id)
    {
        $opp = Opportunity::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|string',
            'client_id' => 'nullable|string',
            'seller_id' => 'nullable|string',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'probability' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string',
            'close_date' => 'nullable|date'
        ]);

        // Backward compatibility: accept either client_id or customer_id
        $data['customer_id'] = $data['customer_id'] ?? $data['client_id'] ?? $opp->customer_id;
        unset($data['client_id']);

        $opp->update($data);
        return response()->json($opp);
    }

    public function destroy($id)
    {
        $opp = Opportunity::findOrFail($id);
        $opp->delete();
        return response()->json(null, 204);
    }
}
