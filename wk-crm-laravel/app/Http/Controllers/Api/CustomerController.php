<?php

namespace App\Http\Controllers\Api;

use App\Application\Customer\UseCases\CreateCustomerUseCase;
use App\Application\Customer\UseCases\CreateCustomerRequest;
use App\Application\Customer\UseCases\GetAllCustomersUseCase;
use App\Application\Customer\UseCases\GetAllCustomersRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CustomerController extends Controller
{
    private CreateCustomerUseCase $createCustomerUseCase;
    private GetAllCustomersUseCase $getAllCustomersUseCase;

    public function __construct(
        CreateCustomerUseCase $createCustomerUseCase,
        GetAllCustomersUseCase $getAllCustomersUseCase
    ) {
        $this->createCustomerUseCase = $createCustomerUseCase;
        $this->getAllCustomersUseCase = $getAllCustomersUseCase;
    }

    /**
     * @OA\Get(
     *     path="/api/customers",
     *     summary="Listar todos os clientes",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número de resultados por página (máximo 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=50)
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Número de resultados para pular",
     *         @OA\Schema(type="integer", minimum=0, default=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de clientes recuperada com sucesso"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $getAllRequest = new GetAllCustomersRequest($request->all());
            $response = $this->getAllCustomersUseCase->execute($getAllRequest);

            return response()->json([
                'success' => true,
                'message' => 'Clientes recuperados com sucesso',
                'data' => $response->toArray()
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
     * @OA\Post(
     *     path="/api/customers",
     *     summary="Criar novo cliente",
     *     tags={"Customers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@exemplo.com"),
     *             @OA\Property(property="phone", type="string", example="+5511999999999"),
     *             @OA\Property(property="company", type="string", example="Empresa ABC"),
     *             @OA\Property(property="address", type="string", example="Rua das Flores, 123"),
     *             @OA\Property(property="city", type="string", example="São Paulo"),
     *             @OA\Property(property="state", type="string", example="SP"),
     *             @OA\Property(property="zip_code", type="string", example="01234-567"),
     *             @OA\Property(property="country", type="string", example="Brasil")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cliente criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados de entrada inválidos"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validação básica do Laravel
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:255',
                'email' => 'required|email|max:320',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:50',
                'zip_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
            ]);

            $createRequest = new CreateCustomerRequest($validated);
            $response = $this->createCustomerUseCase->execute($createRequest);

            return response()->json([
                'success' => true,
                'message' => 'Cliente criado com sucesso',
                'data' => $response->toArray()
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de entrada inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
     * @OA\Get(
     *     path="/api/customers/{id}",
     *     summary="Buscar cliente por ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do cliente",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado"
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        // Implementar posteriormente
        return response()->json([
            'success' => false,
            'message' => 'Endpoint em desenvolvimento'
        ], 501);
    }

    /**
     * @OA\Put(
     *     path="/api/customers/{id}",
     *     summary="Atualizar cliente",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do cliente",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente atualizado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado"
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Implementar posteriormente
        return response()->json([
            'success' => false,
            'message' => 'Endpoint em desenvolvimento'
        ], 501);
    }

    /**
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     summary="Excluir cliente",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do cliente",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Cliente excluído com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado"
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        // Implementar posteriormente
        return response()->json([
            'success' => false,
            'message' => 'Endpoint em desenvolvimento'
        ], 501);
    }
}