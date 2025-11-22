<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Se for Domain Entity, converter para array
        if ($this->resource instanceof \App\Domain\Opportunity\Opportunity) {
            $data = $this->resource->toArray();
        } else {
            // Se for Model Eloquent
            $data = [
                'id' => $this->id,
                'title' => $this->title ?? $this->titulo,
                'amount' => $this->amount ?? $this->valor,
                'status' => $this->status ?? $this->etapa,
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ];
        }
        
        // Retorna campos em PT-BR para compatibilidade com frontend Angular
        return [
            'id' => $data['id'] ?? $this->id,
            'titulo' => $data['title'] ?? $this->title ?? $this->titulo,
            'valor' => $data['amount'] ?? $this->amount ?? $this->valor,
            'etapa' => $data['status'] ?? $this->status ?? $this->etapa,
            'created_at' => $data['created_at'] ?? $this->created_at?->toIso8601String(),
            'updated_at' => $data['updated_at'] ?? $this->updated_at?->toIso8601String(),
        ];
    }
}
