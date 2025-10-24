*** End Patch
namespace App\Http\Controllers\Api;
        ], 204);
    }
}
 *     @OA\Property(property="company", type="string"),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="city", type="string"),
 *     @OA\Property(property="state", type="string"),
 *     @OA\Property(property="zip_code", type="string"),
 *     @OA\Property(property="country", type="string")
 * )
 */

class CustomerController
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
        return response()->json([
            'success' => true,
            'message' => 'Listagem de clientes',
            'data' => []
        ], 200);
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
        return response()->json([
            'success' => true,
            'message' => 'Cliente criado',
            'data' => []
        ], 201);
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
    public function show(string $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cliente encontrado',
            'data' => []
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
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado"
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cliente atualizado',
            'data' => []
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     summary="Excluir cliente",
     *     operationId="api_deleteCustomer",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do cliente",
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cliente excluído'
        ], 204);
    }
}

/**
 * @OA\Info(
 *     title="WK CRM API",
 *     version="1.0.0",
 *     description="Documentação da API WK CRM"
 * )
 * @OA\Server(
 *     url="http://localhost",
 *     description="Servidor local"
 * )
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
 */

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController
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
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listagem de clientes',
            'data' => []
        ], 200);
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
        return response()->json([
            'success' => true,
            'message' => 'Cliente criado',
            'data' => []
        ], 201);
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
    */


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
     *     ),
     *     @OA\Response(
     *         response=404,
    *         description="Cliente não encontrado"
    *     )
    */
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cliente atualizado',
            'data' => []
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     summary="Excluir cliente",
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
     *         description="Cliente excluído"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado"
     *     )
