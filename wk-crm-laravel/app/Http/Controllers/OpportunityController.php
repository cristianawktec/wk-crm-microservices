<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Domain\Opportunity\Opportunity;

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
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'value' => 'required|numeric',
            'status' => 'nullable|string',
            'customer_id' => 'required|uuid|exists:customers,id',
        ]);
        $data['id'] = (string) \Str::uuid();
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
    public function update(Request $request, $id)
    {
        $opportunity = Opportunity::findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'value' => 'sometimes|required|numeric',
            'status' => 'nullable|string',
            'customer_id' => 'sometimes|required|uuid|exists:customers,id',
        ]);
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
