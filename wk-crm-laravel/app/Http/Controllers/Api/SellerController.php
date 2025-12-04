<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    public function index()
    {
        $sellers = Seller::orderBy('name')->paginate(25);
        return response()->json($sellers);
    }

    // Return distinct roles used by sellers (for populating comboboxes)
    public function roles()
    {
        $roles = Seller::whereNotNull('role')->select('role')->distinct()->pluck('role')->filter()->values();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:sellers,email',
            'phone' => 'nullable|string|max:50',
            'role' => 'nullable|string|max:100'
        ]);

        $seller = Seller::create($data);
        return response()->json($seller, 201);
    }

    public function show($id)
    {
        $seller = Seller::findOrFail($id);
        return response()->json($seller);
    }

    public function update(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable','email','max:255', Rule::unique('sellers','email')->ignore($seller->id)],
            'phone' => 'nullable|string|max:50',
            'role' => 'nullable|string|max:100'
        ]);

        $seller->update($data);
        return response()->json($seller);
    }

    public function destroy($id)
    {
        $seller = Seller::findOrFail($id);
        $seller->delete();
        return response()->json(null, 204);
    }
}
