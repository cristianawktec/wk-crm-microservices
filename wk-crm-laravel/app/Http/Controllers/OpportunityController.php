<?php

namespace App\Http\Controllers;

use App\Domain\Opportunity\Opportunity as OpportunityEntity;
use App\Domain\Opportunity\OpportunityRepositoryInterface;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Resources\OpportunityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Opportunities",
 *     description="Operações de CRUD para oportunidades"
 * )
 */
class OpportunityController extends Controller
{
    public function __construct(
        private OpportunityRepositoryInterface $opportunities
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/oportunidades",
     *     tags={"Opportunities"},
     *     summary="Listar todas as oportunidades",
     *     @OA\Response(response=200, description="Lista de oportunidades")
     * )
     */
    public function index(): JsonResponse
    {
        $opportunities = $this->opportunities->findAll(50);
        
        return response()->json(OpportunityResource::collection($opportunities));
    }

    /**
     * @OA\Get(
     *     path="/api/oportunidades/{id}",
     *     tags={"Opportunities"},
     *     summary="Buscar oportunidade por ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Oportunidade encontrada")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $opportunity = $this->opportunities->findById($id);
        
        if (!$opportunity) {
            return response()->json(['message' => 'Oportunidade não encontrada'], 404);
        }
        
        return response()->json(new OpportunityResource($opportunity));
    }

    /**
     * @OA\Post(
     *     path="/api/oportunidades",
     *     tags={"Opportunities"},
     *     summary="Criar nova oportunidade",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Opportunity")
     *     ),
     *     @OA\Response(response=201, description="Oportunidade criada")
     * )
     */
    public function store(StoreOpportunityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $opportunity = OpportunityEntity::create(
            id: (string) Str::uuid(),
            title: $validated['title'],
            description: $validated['description'] ?? null,
            amount: (float) $validated['amount'],
            expectedCloseDate: isset($validated['expected_close_date']) 
                ? new \DateTime($validated['expected_close_date']) 
                : null,
            status: $validated['status'],
            leadId: $validated['lead_id'] ?? null,
            clienteId: $validated['cliente_id'] ?? null
        );
        
        $saved = $this->opportunities->save($opportunity);
        
        return response()->json(new OpportunityResource($saved), 201);
    }

    /**
     * @OA\Put(
     *     path="/api/oportunidades/{id}",
     *     tags={"Opportunities"},
     *     summary="Atualizar oportunidade",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Opportunity")
     *     ),
     *     @OA\Response(response=200, description="Oportunidade atualizada")
     * )
     */
    public function update(UpdateOpportunityRequest $request, string $id): JsonResponse
    {
        $existing = $this->opportunities->findById($id);
        
        if (!$existing) {
            return response()->json(['message' => 'Oportunidade não encontrada'], 404);
        }
        
        $validated = $request->validated();
        
        $opportunity = OpportunityEntity::create(
            id: $id,
            title: $validated['title'] ?? $existing->getTitle(),
            description: $validated['description'] ?? $existing->getDescription(),
            amount: isset($validated['amount']) ? (float) $validated['amount'] : $existing->getAmount(),
            expectedCloseDate: isset($validated['expected_close_date']) 
                ? new \DateTime($validated['expected_close_date']) 
                : $existing->getExpectedCloseDate(),
            status: $validated['status'] ?? $existing->getStatus(),
            leadId: $validated['lead_id'] ?? $existing->getLeadId(),
            clienteId: $validated['cliente_id'] ?? $existing->getClienteId()
        );
        
        $updated = $this->opportunities->update($id, $opportunity);
        
        return response()->json(new OpportunityResource($updated));
    }

    /**
     * @OA\Delete(
     *     path="/api/oportunidades/{id}",
     *     tags={"Opportunities"},
     *     summary="Excluir oportunidade",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Oportunidade excluída")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $opportunity = $this->opportunities->findById($id);
        
        if (!$opportunity) {
            return response()->json(['message' => 'Oportunidade não encontrada'], 404);
        }
        
        $this->opportunities->delete($id);
        
        return response()->json(['message' => 'Oportunidade excluída com sucesso']);
    }
}
