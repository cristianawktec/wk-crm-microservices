<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Models\Customer;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Customer Dashboard
 * Endpoints do dashboard do cliente (customer portal)
 */
class CustomerDashboardController extends Controller
{
    /**
     * Get Customer Dashboard Stats
     * 
     * Retorna estatísticas do dashboard do cliente autenticado
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "totalOpportunities": 15,
     *     "totalValue": 125000.00,
     *     "openOpportunities": 8,
     *     "avgProbability": 45,
     *     "activities": [...]
     *   }
     * }
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get all opportunities for this customer
        $opportunities = Opportunity::where('customer_id', $user->id)->get();
        
        $totalOpportunities = $opportunities->count();
        $totalValue = $opportunities->sum('value') ?? 0;
        $openOpportunities = $opportunities->where('status', 'Aberta')->count();
        $avgProbability = $totalOpportunities > 0 
            ? round($opportunities->avg('probability') ?? 0) 
            : 0;
        
        // Get recent activities (opportunities created/updated in last 30 days)
        $activities = Opportunity::where('customer_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($opp) => [
                'id' => $opp->id,
                'type' => 'opportunity',
                'title' => 'Oportunidade: ' . $opp->title,
                'description' => 'Status: ' . $opp->status . ' | Valor: R$ ' . number_format($opp->value ?? 0, 2, ',', '.'),
                'timestamp' => $opp->updated_at->toIso8601String(),
                'icon' => 'briefcase'
            ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'totalOpportunities' => (int) $totalOpportunities,
                'totalValue' => (float) $totalValue,
                'openOpportunities' => (int) $openOpportunities,
                'avgProbability' => (int) $avgProbability,
                'activities' => $activities
            ]
        ], 200);
    }

    /**
     * Get Customer Profile
     * 
     * Retorna os dados do perfil do cliente autenticado
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": "uuid",
     *     "name": "João Silva",
     *     "email": "joao@example.com",
     *     "phone": "(11) 98765-4321",
     *     "company": "Empresa ABC"
     *   }
     * }
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'company' => $user->company ?? ''
            ]
        ], 200);
    }

    /**
     * Update Customer Profile
     * 
     * Atualiza os dados do perfil do cliente autenticado
     * 
     * @bodyParam name string required Nome do cliente
     * @bodyParam email string required Email do cliente
     * @bodyParam phone string Telefone do cliente
     * @bodyParam company string Empresa/Razão Social
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Perfil atualizado com sucesso",
     *   "data": {...}
     * }
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255'
        ]);
        
        $user->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'company' => $user->company
            ]
        ], 200);
    }

    /**
     * Get Customer Opportunities
     * 
     * Retorna todas as oportunidades do cliente autenticado
     * 
     * @queryParam status string Filter by status (Aberta, Em Negociação, etc)
     * @queryParam search string Search opportunities by title
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function getOpportunities(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = Opportunity::where('customer_id', $user->id);
        
        $hasFilters = ($request->has('status') && !empty($request->status)) 
                   || ($request->has('search') && !empty($request->search));
        
        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('title', 'ILIKE', "%{$search}%");
        }
        
        $opportunities = $query->orderByDesc('created_at')->get();
        
        $formattedOpps = $opportunities->map(fn($opp) => [
            'id' => $opp->id,
            'title' => $opp->title,
            'value' => (float) ($opp->value ?? 0),
            'status' => $opp->status,
            'probability' => (int) ($opp->probability ?? 0),
            'seller_id' => $opp->seller_id,
            'seller' => $opp->seller ? $opp->seller->name : 'Não atribuído',
            'created_at' => $opp->created_at->toIso8601String(),
            'notes' => $opp->description ?? ''
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $formattedOpps
        ], 200);
    }

    /**
     * Get Single Customer Opportunity
     * 
     * Retorna os detalhes de uma oportunidade específica do cliente.
     */
    public function getOpportunity(Opportunity $opportunity): JsonResponse
    {
        $user = Auth::user();
        
        // Verifica se a oportunidade pertence ao cliente autenticado
        if ($opportunity->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado a esta oportunidade.'
            ], 403);
        }

        $formatted = [
            'id' => $opportunity->id,
            'title' => $opportunity->title,
            'value' => $opportunity->value ?? 0,
            'status' => $opportunity->status,
            'probability' => $opportunity->probability ?? 0,
            'seller_id' => $opportunity->seller_id,
            'seller' => $opportunity->seller ? $opportunity->seller->name : 'Não atribuído',
            'created_at' => $opportunity->created_at->toIso8601String(),
            'notes' => $opportunity->description ?? ''
        ];

        return response()->json([
            'success' => true,
            'data' => $formatted
        ], 200);
    }

    /**
     * Create Customer Opportunity
     * 
     * Permite que o cliente crie uma nova oportunidade.
     */
    public function createOpportunity(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Garantir que existe um registro de customer para este usuário (FK obrigatória)
        $customer = Customer::find($user->id);
        if (!$customer) {
            $customer = Customer::create([
                'id' => $user->id,
                'name' => $user->name ?? 'Cliente',
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
            ]);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'value' => 'nullable|numeric',
            'probability' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string'
        ]);

        $opp = Opportunity::create([
            'title' => $data['title'],
            'value' => $data['value'] ?? 0,
            'probability' => $data['probability'] ?? 0,
            'status' => $data['status'] ?? 'Aberta',
            'description' => $data['notes'] ?? null,
            'customer_id' => $customer->id,
            'client_id' => $customer->id,
            'currency' => 'BRL'
        ]);

        // Dispara notificação para managers
        NotificationService::opportunityCreated($opp, $user);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $opp->id,
                'title' => $opp->title,
                'value' => (float) ($opp->value ?? 0),
                'status' => $opp->status,
                'probability' => (int) ($opp->probability ?? 0),
                'seller_id' => $opp->seller_id,
                'seller' => $opp->seller ? $opp->seller->name : 'Não atribuído',
                'created_at' => $opp->created_at->toIso8601String(),
                'notes' => $opp->description ?? ''
            ]
        ], 201);
    }

    /**
     * Update Customer Opportunity
     */
    public function updateOpportunity(Request $request, Opportunity $opportunity): JsonResponse
    {
        $user = Auth::user();

        // Ensure ownership
        if ($opportunity->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado'
            ], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'value' => 'nullable|numeric',
            'probability' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string'
        ]);

        $opportunity->update([
            'title' => $data['title'],
            'value' => $data['value'] ?? 0,
            'probability' => $data['probability'] ?? 0,
            'status' => $data['status'] ?? $opportunity->status,
            'description' => $data['notes'] ?? $opportunity->description,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $opportunity->id,
                'title' => $opportunity->title,
                'value' => (float) ($opportunity->value ?? 0),
                'status' => $opportunity->status,
                'probability' => (int) ($opportunity->probability ?? 0),
                'seller_id' => $opportunity->seller_id,
                'seller' => $opportunity->seller ? $opportunity->seller->name : 'Não atribuído',
                'created_at' => $opportunity->created_at->toIso8601String(),
                'notes' => $opportunity->description ?? ''
            ]
        ], 200);
    }

    /**
     * Delete Customer Opportunity
     */
    public function deleteOpportunity(Opportunity $opportunity): JsonResponse
    {
        $user = Auth::user();

        if ($opportunity->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado'
            ], 403);
        }

        $opportunity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Oportunidade excluída'
        ], 200);
    }
}
