<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::orderBy('created_at', 'desc')->paginate(25);
        return response()->json($leads);
    }

    // Return distinct sources used by leads (for populating comboboxes)
    public function sources()
    {
        $sources = Lead::whereNotNull('source')->select('source')->distinct()->pluck('source')->filter()->values();
        return response()->json($sources);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::in(['new','contacted','qualified','converted','lost'])],
            'seller_id' => 'nullable|string',
        ]);

        $lead = Lead::create($data);
        return response()->json($lead, 201);
    }

    public function show($id)
    {
        $lead = Lead::findOrFail($id);
        return response()->json($lead);
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::in(['new','contacted','qualified','converted','lost'])],
            'seller_id' => 'nullable|string',
        ]);
        $lead->update($data);
        return response()->json($lead);
    }

    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();
        return response()->json(null, 204);
    }
}
