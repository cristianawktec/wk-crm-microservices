<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Lead\Lead;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;

/**
 * @OA\Tag(
 *     name="Leads",
 *     description="Operações de CRUD para leads"
 * )
 */

class LeadController extends Controller
{
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
        return response()->json(Lead::all());
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
        $lead = Lead::findOrFail($id);
        return response()->json($lead);
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
        $validated = $request->validated();
        
        $data = [
            'id' => (string) \Str::uuid(),
            'name' => $validated['name'] ?? $validated['nome'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? $validated['telefone'] ?? null,
            'company' => $validated['company'] ?? $validated['empresa'] ?? null,
            'source' => $validated['source'] ?? $validated['origem'] ?? null,
            'status' => $validated['status'] ?? 'new',
        ];
        
        $lead = Lead::create($data);
        return response()->json($lead, 201);
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
        $lead = Lead::findOrFail($id);
        $validated = $request->validated();
        
        $data = [
            'name' => $validated['name'] ?? $validated['nome'] ?? $lead->name,
            'email' => $validated['email'] ?? $lead->email,
            'phone' => $validated['phone'] ?? $validated['telefone'] ?? $lead->phone,
            'company' => $validated['company'] ?? $validated['empresa'] ?? $lead->company,
            'source' => $validated['source'] ?? $validated['origem'] ?? $lead->source,
            'status' => $validated['status'] ?? $lead->status,
        ];
        
        $lead->update($data);
        return response()->json($lead);
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
        $lead = Lead::findOrFail($id);
        $lead->delete();
        return response()->json(['message' => 'Lead deleted']);
    }
}
