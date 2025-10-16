<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CustomerSimpleController extends Controller
{
    /**
     * Lista todos os clientes (versão simplificada sem DI)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 50), 100);
            $offset = max($request->get('offset', 0), 0);

            $customers = DB::table('customers')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();

            $total = DB::table('customers')->count();

            return response()->json([
                'success' => true,
                'message' => 'Clientes recuperados com sucesso',
                'data' => [
                    'customers' => $customers,
                    'pagination' => [
                        'total' => $total,
                        'limit' => $limit,
                        'offset' => $offset,
                        'current_page' => floor($offset / $limit) + 1,
                        'total_pages' => ceil($total / $limit),
                        'has_next' => ($offset + $limit) < $total,
                        'has_previous' => $offset > 0,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar novo cliente (versão simplificada)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Tentar obter dados de diferentes formas
            $input = $request->all();
            
            if (empty($input) && $request->getContent()) {
                $input = json_decode($request->getContent(), true) ?: [];
            }
            
            if (empty($input)) {
                $input = $request->json()->all();
            }
            
            \Log::info('Final input data:', $input);

            // Validação básica usando o input processado
            $validator = \Validator::make($input, [
                'name' => 'required|string|min:2|max:255',
                'email' => 'required|email|max:320|unique:customers,email',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:50',
                'zip_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Gerar UUID
            $customerId = (string) \Ramsey\Uuid\Uuid::uuid4();

            $customerData = [
                'id' => $customerId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => 'active',
                'company' => $validated['company'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'zip_code' => $validated['zip_code'] ?? null,
                'country' => $validated['country'] ?? 'Brasil',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('customers')->insert($customerData);

            return response()->json([
                'success' => true,
                'message' => 'Cliente criado com sucesso',
                'data' => $customerData
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de entrada inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar cliente por ID
     */
    public function show(string $id): JsonResponse
    {
        try {
            $customer = DB::table('customers')->where('id', $id)->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente encontrado',
                'data' => $customer
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar cliente
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            // Verificar se cliente existe
            $customer = DB::table('customers')->where('id', $id)->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ], 404);
            }

            // Tentar obter dados de diferentes formas
            $input = $request->all();
            
            if (empty($input) && $request->getContent()) {
                $input = json_decode($request->getContent(), true) ?: [];
            }
            
            if (empty($input)) {
                $input = $request->json()->all();
            }

            // Validação (email único excluindo o próprio registro)
            $validator = \Validator::make($input, [
                'name' => 'sometimes|string|min:2|max:255',
                'email' => 'sometimes|email|max:320|unique:customers,email,' . $id . ',id',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:50',
                'zip_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'status' => 'sometimes|in:active,inactive,pending'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            $validated['updated_at'] = now();

            // Atualizar cliente
            $updated = DB::table('customers')
                ->where('id', $id)
                ->update($validated);

            if ($updated === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma alteração foi feita'
                ], 400);
            }

            // Buscar dados atualizados
            $updatedCustomer = DB::table('customers')->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Cliente atualizado com sucesso',
                'data' => $updatedCustomer
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar cliente
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Verificar se cliente existe
            $customer = DB::table('customers')->where('id', $id)->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não encontrado'
                ], 404);
            }

            // Deletar cliente
            $deleted = DB::table('customers')->where('id', $id)->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao deletar cliente'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente deletado com sucesso',
                'data' => [
                    'deleted_id' => $id,
                    'deleted_at' => now()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}