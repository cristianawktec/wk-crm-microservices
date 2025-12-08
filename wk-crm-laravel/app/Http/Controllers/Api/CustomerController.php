<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Http\Controllers\Controller;

/**
 * @OA\Schema(
 *     schema="Customer",
 *     type="object",
 *     required={"id", "name", "email"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="company", type="string"),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="city", type="string"),
 *     @OA\Property(property="state", type="string"),
 *     @OA\Property(property="zip_code", type="string"),
 *     @OA\Property(property="country", type="string")
 * )
 */

/**
 * @OA\Tag(
 *     name="Customers",
 *     description="Operações de CRUD para clientes"
 * )
 */

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/customers",
     *     summary="Listar clientes",
     *     operationId="api_listCustomers",
     *     tags={"Customers"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de clientes",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Customer"))
     *     )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customers = Customer::all();
            return response()->json([
                'success' => true,
                'data' => $customers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/customers",
     *     summary="Criar cliente",
     *     operationId="api_createCustomer",
     *     tags={"Customers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cliente criado",
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:customers,email',
                'phone' => 'nullable|string',
                'status' => 'nullable|string',
                'company' => 'nullable|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'zip_code' => 'nullable|string',
                'country' => 'nullable|string',
            ]);
            
            $data['id'] = (string) \Illuminate\Support\Str::uuid();
            $customer = Customer::create($data);
            
            return response()->json([
                'success' => true,
                'data' => $customer
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/customers/{id}",
     *     summary="Buscar cliente por ID",
     *     operationId="api_getCustomerById",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do cliente",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado"
     *     )
     * )
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $customer
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/customers/{id}",
     *     summary="Atualizar cliente",
     *     operationId="api_updateCustomer",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do cliente",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente atualizado",
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     )
     * )
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'sometimes|required|string',
                'email' => 'sometimes|required|email|unique:customers,email,' . $customer->id,
                'phone' => 'nullable|string',
                'status' => 'nullable|string',
                'company' => 'nullable|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'zip_code' => 'nullable|string',
                'country' => 'nullable|string',
            ]);
            
            $customer->update($data);
            
            return response()->json([
                'success' => true,
                'data' => $customer
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
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     summary="Deletar cliente",
     *     operationId="api_deleteCustomer",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do cliente",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Cliente deletado"
     *     )
     * )
     */
    public function destroy(Customer $customer): JsonResponse
    {
        try {
            $customer->delete();
            return response()->json([
                'success' => true,
                'message' => 'Cliente deletado com sucesso'
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
