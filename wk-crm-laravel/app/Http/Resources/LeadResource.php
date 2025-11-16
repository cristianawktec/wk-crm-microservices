<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para padronizar saída JSON de Lead
 * Controla exatamente quais dados são expostos na API
 */
class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Se for Domain Entity, usa getters
        if (method_exists($this->resource, 'toArray')) {
            return $this->resource->toArray();
        }
        
        // Se for Eloquent Model, acessa propriedades direto
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'source' => $this->source,
            'status' => $this->status,
            'interest' => $this->interest,
            'city' => $this->city,
            'state' => $this->state,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
