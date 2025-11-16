<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Domain\Lead\Lead as LeadEntity;
use App\Domain\Lead\LeadRepositoryInterface;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Resources\LeadResource;

/**
 * @OA\Tag(
 *     name="Leads",
 *     description="Operações de CRUD para leads"
 * )
 */

class LeadController extends Controller
{
    public function __construct(private LeadRepositoryInterface $leads)
    {
    }
    /**
     * @OA\Get(
     *     path="/api/leads",
     *     tags={"Leads"},
     *     summary="Listar todos os leads",
     *     @OA\Response(response=200, description="Lista de leads")
     * )
     */
    public function index()
    {
        $result = $this->leads->findAll(50);
        return LeadResource::collection($result);
    }

    /**
     * @OA\Get(
     *     path="/api/leads/{id}",
     *     tags={"Leads"},
     *     summary="Buscar lead por ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lead encontrado")
     * )
     */
    public function show($id)
    {
        $lead = $this->leads->findById($id);
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }
        return new LeadResource($lead);
    }

    /**
     * @OA\Post(
     *     path="/api/leads",
     *     tags={"Leads"},
     *     summary="Criar novo lead",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Lead")
     *     ),
     *     @OA\Response(response=201, description="Lead criado")
     * )
     */
    public function store(StoreLeadRequest $request)
    {
        $v = $request->validated();

        $entity = LeadEntity::create(
            id: null,
            name: $v['name'] ?? $v['nome'] ?? '',
            email: $v['email'] ?? null,
            phone: $v['phone'] ?? $v['telefone'] ?? null,
            company: $v['company'] ?? $v['empresa'] ?? null,
            source: $v['source'] ?? $v['origem'] ?? null,
            status: $v['status'] ?? 'new',
            interest: $v['interest'] ?? $v['interesse'] ?? null,
            city: $v['city'] ?? $v['cidade'] ?? null,
            state: $v['state'] ?? $v['estado'] ?? null,
            notes: $v['notes'] ?? $v['observacoes'] ?? null
        );

        $saved = $this->leads->save($entity);
        return (new LeadResource($saved))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/leads/{id}",
     *     tags={"Leads"},
     *     summary="Atualizar lead",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Lead")
     *     ),
     *     @OA\Response(response=200, description="Lead atualizado")
     * )
     */
    public function update(UpdateLeadRequest $request, $id)
    {
        $existing = $this->leads->findById($id);
        if (!$existing) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $v = $request->validated();

        $entity = LeadEntity::create(
            id: $existing->getId(),
            name: $v['name'] ?? $v['nome'] ?? $existing->getName(),
            email: $v['email'] ?? $existing->getEmail(),
            phone: $v['phone'] ?? $v['telefone'] ?? $existing->getPhone(),
            company: $v['company'] ?? $v['empresa'] ?? $existing->getCompany(),
            source: $v['source'] ?? $v['origem'] ?? $existing->getSource(),
            status: $v['status'] ?? $existing->getStatus(),
            interest: $v['interest'] ?? $v['interesse'] ?? $existing->getInterest(),
            city: $v['city'] ?? $v['cidade'] ?? $existing->getCity(),
            state: $v['state'] ?? $v['estado'] ?? $existing->getState(),
            notes: $v['notes'] ?? $v['observacoes'] ?? $existing->getNotes()
        );

        $updated = $this->leads->update($id, $entity);
        return new LeadResource($updated);
    }

    /**
     * @OA\Delete(
     *     path="/api/leads/{id}",
     *     tags={"Leads"},
     *     summary="Excluir lead",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lead excluído")
     * )
     */
    public function destroy($id)
    {
        $deleted = $this->leads->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Lead not found'], 404);
        }
        return response()->json(['message' => 'Lead deleted']);
    }
}
