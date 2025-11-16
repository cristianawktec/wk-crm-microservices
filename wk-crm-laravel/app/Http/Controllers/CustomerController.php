<?php

namespace App\Http\Controllers;

use App\Domain\Customer\Customer as CustomerEntity;
use App\Domain\Customer\CustomerRepositoryInterface;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller de Customer seguindo DDD + SOLID
 * - Dependency Injection do Repository (DIP)
 * - Single Responsibility (apenas coordena request/response)
 * - Open/Closed (extensível via Repository sem modificar controller)
 * 
 * @OA\Tag(
 *     name="Customers",
 *     description="Operações de CRUD para clientes"
 * )
 */
class CustomerController extends Controller
{
    public function __construct(
        private CustomerRepositoryInterface $repository
    ) {}

    /**
     * Lista todos os customers com paginação
     * 
     * @OA\Get(
     *     path="/api/customers",
     *     tags={"Customers"},
     *     summary="Lista clientes",
     *     @OA\Response(response=200, description="Lista de clientes")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $customers = $this->repository->findAll();
        
        return CustomerResource::collection($customers);
    }

    /**
     * Cria novo customer
     * 
     * @OA\Post(
     *     path="/api/customers",
     *     tags={"Customers"},
     *     summary="Cria cliente",
     *     @OA\Response(response=201, description="Cliente criado")
     * )
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Suporta campos em PT-BR ou EN
        $customerEntity = CustomerEntity::create(
            id: null,
            name: $validated['name'] ?? $validated['nome'],
            email: $validated['email'],
            phone: $validated['phone'] ?? $validated['telefone'] ?? null,
            cpf: $validated['cpf'] ?? null,
            address: $validated['address'] ?? $validated['endereco'] ?? null,
            city: $validated['city'] ?? $validated['cidade'] ?? null,
            state: $validated['state'] ?? $validated['estado'] ?? null,
            postalCode: $validated['postal_code'] ?? $validated['cep'] ?? null
        );

        $saved = $this->repository->save($customerEntity);

        return (new CustomerResource($saved))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Exibe customer específico
     * 
     * @OA\Get(
     *     path="/api/customers/{id}",
     *     tags={"Customers"},
     *     summary="Exibe cliente",
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Detalhes do cliente")
     * )
     */
    public function show(string $id): CustomerResource
    {
        $customer = $this->repository->findById($id);

        if (!$customer) {
            abort(404, 'Customer não encontrado');
        }

        return new CustomerResource($customer);
    }

    /**
     * Atualiza customer existente
     * 
     * @OA\Put(
     *     path="/api/customers/{id}",
     *     tags={"Customers"},
     *     summary="Atualiza cliente",
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Cliente atualizado")
     * )
     */
    public function update(UpdateCustomerRequest $request, string $id): CustomerResource
    {
        $validated = $request->validated();
        
        $current = $this->repository->findById($id);
        
        if (!$current) {
            abort(404, 'Customer não encontrado');
        }

        $currentData = $current->toArray();
        
        $updated = CustomerEntity::create(
            id: $id,
            name: $validated['name'] ?? $validated['nome'] ?? $currentData['name'],
            email: $validated['email'] ?? $currentData['email'],
            phone: $validated['phone'] ?? $validated['telefone'] ?? $currentData['phone'],
            cpf: $validated['cpf'] ?? $currentData['cpf'],
            address: $validated['address'] ?? $validated['endereco'] ?? $currentData['address'],
            city: $validated['city'] ?? $validated['cidade'] ?? $currentData['city'],
            state: $validated['state'] ?? $validated['estado'] ?? $currentData['state'],
            postalCode: $validated['postal_code'] ?? $validated['cep'] ?? $currentData['postal_code']
        );

        if (isset($validated['status'])) {
            if ($validated['status'] === 'active') {
                $updated->activate();
            } else {
                $updated->deactivate();
            }
        }

        $saved = $this->repository->update($id, $updated);

        return new CustomerResource($saved);
    }

    /**
     * Remove customer
     * 
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     tags={"Customers"},
     *     summary="Remove cliente",
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=204, description="Cliente removido")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (!$deleted) {
            abort(404, 'Customer não encontrado');
        }

        return response()->json(null, 204);
    }
}

