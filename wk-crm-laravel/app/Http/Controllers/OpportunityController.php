<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Opportunity\Opportunity;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;

/**
 * @OA\Tag(
 *     name="Opportunities",
 *     description="Operações de CRUD para oportunidades"
 * )
 */

class OpportunityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/opportunities",
     *     tags={"Opportunities"},
     *     summary="Listar todas as oportunidades",
     *     @OA\Response(response=200, description="Lista de oportunidades")
     * )
     */
    public function index()
    {
        return response()->json(Opportunity::all());
    }

    /**
     * @OA\Get(
     *     path="/api/opportunities/{id}",
     *     tags={"Opportunities"},
     *     summary="Buscar oportunidade por ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Oportunidade encontrada")
     * )
     */
    public function show($id)
    {
        $opportunity = Opportunity::findOrFail($id);
        return response()->json($opportunity);
    }

    /**
     * @OA\Post(
     *     path="/api/opportunities",
     *     tags={"Opportunities"},
     *     summary="Criar nova oportunidade",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Opportunity")
     *     ),
     *     @OA\Response(response=201, description="Oportunidade criada")
     * )
     */
    public function store(StoreOpportunityRequest $request)
    {
        $validated = $request->validated();
        
        $data = [
            'id' => (string) \Str::uuid(),
            'title' => $validated['title'] ?? $validated['titulo'] ?? null,
            'description' => $validated['description'] ?? $validated['descricao'] ?? null,
            'value' => $validated['value'] ?? $validated['valor'] ?? 0,
            'customer_id' => $validated['customer_id'] ?? $validated['cliente_id'] ?? null,
            'status' => $validated['status'] ?? 'open',
        ];
        
        $opportunity = Opportunity::create($data);
        return response()->json($opportunity, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/opportunities/{id}",
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
    public function update(UpdateOpportunityRequest $request, $id)
    {
        $opportunity = Opportunity::findOrFail($id);
        $validated = $request->validated();
        
        $data = [
            'title' => $validated['title'] ?? $validated['titulo'] ?? $opportunity->title,
            'description' => $validated['description'] ?? $validated['descricao'] ?? $opportunity->description,
            'value' => $validated['value'] ?? $validated['valor'] ?? $opportunity->value,
            'customer_id' => $validated['customer_id'] ?? $validated['cliente_id'] ?? $opportunity->customer_id,
            'status' => $validated['status'] ?? $opportunity->status,
        ];
        
        $opportunity->update($data);
        return response()->json($opportunity);
    }

    /**
     * @OA\Delete(
     *     path="/api/opportunities/{id}",
     *     tags={"Opportunities"},
     *     summary="Excluir oportunidade",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Oportunidade excluída")
     * )
     */
    public function destroy($id)
    {
        $opportunity = Opportunity::findOrFail($id);
        $opportunity->delete();
        return response()->json(['message' => 'Opportunity deleted']);
    }
}
