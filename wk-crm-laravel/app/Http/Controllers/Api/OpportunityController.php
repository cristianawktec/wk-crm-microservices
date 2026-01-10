<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Services\NotificationService;
class OpportunityController extends Controller
{
    public function index(Request $request)
    {
        $query = Opportunity::with(['client','seller']);
        
        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhereHas('client', function($query) use ($search) {
                      $query->where('name', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('seller', function($query) use ($search) {
                      $query->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }
        
        $opps = $query->orderByDesc('created_at')->paginate(25);
        return response()->json($opps);
    }

    public function store(Request $request)
    {
        \Log::info('[OpportunityController@store] START');
        \Log::info('[OpportunityController@store] USER', ['id' => optional($request->user())->id]);
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

        \Log::info('[OpportunityController@store] DATA_VALIDATED', $data);
        $opp = Opportunity::create($data);
        \Log::info('[OpportunityController@store] CREATED', ['id' => $opp->id]);
        // Send notification BEFORE returning response
        try {
            $t0 = microtime(true);
            NotificationService::opportunityCreated($opp, $request->user());
            \Log::info('[OpportunityController@store] NOTIFIED', ['ms' => (int)((microtime(true) - $t0) * 1000)]);
        } catch (\Throwable $e) {
            // log silently to avoid breaking creation
            \Log::warning('Failed to send opportunity created notification: '.$e->getMessage());
        }
        \Log::info('[OpportunityController@store] END');
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
        
        // Guardar valores antigos ANTES do update
        $oldStatus = $opp->status;
        $oldValue = $opp->value;
        
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

        // Check for status change (comparar com valor antigo)
        if (isset($data['status']) && $oldStatus !== $data['status']) {
            try {
                NotificationService::opportunityStatusChanged($opp, $oldStatus, $data['status'], $request->user());
                \Log::info('[OpportunityController@update] Status notification sent', [
                    'opportunity_id' => $opp->id,
                    'old' => $oldStatus,
                    'new' => $data['status']
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to send status change notification: '.$e->getMessage());
            }
        }
        
        // Check for value change (comparar com valor antigo)
        if (isset($data['value']) && $oldValue != $data['value']) {
            try {
                NotificationService::opportunityValueChanged($opp, $oldValue, $data['value'], $request->user());
                \Log::info('[OpportunityController@update] Value notification sent', [
                    'opportunity_id' => $opp->id,
                    'old' => $oldValue,
                    'new' => $data['value']
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to send value change notification: '.$e->getMessage());
            }
        }

        return response()->json($opp);
    }

    public function destroy($id)
    {
        $opp = Opportunity::findOrFail($id);
        $opp->delete();
        return response()->json(null, 204);
    }
}
