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
            return [
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'expected_close_date' => $data['expected_close_date'],
                'status' => $data['status'],
                'lead_id' => $data['lead_id'],
                'cliente_id' => $data['cliente_id'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ];
        }
        
        // Se for Model Eloquent
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'expected_close_date' => $this->expected_close_date?->format('Y-m-d'),
            'status' => $this->status,
            'lead_id' => $this->lead_id,
            'cliente_id' => $this->cliente_id,
            'lead' => $this->whenLoaded('lead', function () {
                return [
                    'id' => $this->lead->id,
                    'name' => $this->lead->name,
                    'email' => $this->lead->email,
                ];
            }),
            'cliente' => $this->whenLoaded('cliente', function () {
                return [
                    'id' => $this->cliente->id,
                    'name' => $this->cliente->name ?? $this->cliente->nome,
                    'email' => $this->cliente->email,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
