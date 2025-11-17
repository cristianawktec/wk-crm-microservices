<?php

namespace App\Models;

use App\Domain\Opportunity\Opportunity as OpportunityEntity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'opportunities';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'description',
        'amount',
        'expected_close_date',
        'status',
        'lead_id',
        'cliente_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'expected_close_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com Lead
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    /**
     * Relacionamento com Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(\App\Cliente::class, 'cliente_id');
    }

    /**
     * Converte o Model Eloquent para Domain Entity
     *
     * @return OpportunityEntity
     */
    public function toDomainEntity(): OpportunityEntity
    {
        return OpportunityEntity::create(
            id: $this->id,
            title: $this->title,
            description: $this->description,
            amount: $this->amount,
            expectedCloseDate: $this->expected_close_date ? new \DateTime($this->expected_close_date) : null,
            status: $this->status,
            leadId: $this->lead_id,
            clienteId: $this->cliente_id,
            createdAt: $this->created_at ? new \DateTime($this->created_at) : null,
            updatedAt: $this->updated_at ? new \DateTime($this->updated_at) : null
        );
    }
}
