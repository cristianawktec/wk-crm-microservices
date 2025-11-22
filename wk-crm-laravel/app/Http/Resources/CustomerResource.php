<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para padronizar saída JSON de Customer
 * Controla exatamente quais dados são expostos na API
 */
class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Se for Domain Entity, usa getters
        if (method_exists($this->resource, 'toArray')) {
            $data = $this->resource->toArray();
        } else {
            // Se for Eloquent Model, acessa propriedades direto
            $data = [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'cpf' => $this->cpf,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'status' => $this->status,
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            ];
        }
        
        // Retorna campos em PT-BR para compatibilidade com frontend Angular
        return [
            'id' => $data['id'] ?? $this->id,
            'nome' => $data['name'] ?? $this->name ?? $this->nome,
            'email' => $data['email'] ?? $this->email,
            'telefone' => $data['phone'] ?? $this->phone ?? $this->telefone,
            'created_at' => $data['created_at'] ?? $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $data['updated_at'] ?? $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
