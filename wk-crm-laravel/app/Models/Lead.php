<?php

namespace App\Models;

use App\Domain\Lead\Lead as LeadEntity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Lead Model - Infrastructure Layer
 * Eloquent model for database persistence
 */
class Lead extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'source',
        'status',
        'interest',
        'city',
        'state',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Converte Model Eloquent para Domain Entity
     */
    public function toDomainEntity(): LeadEntity
    {
        return LeadEntity::create(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            phone: $this->phone,
            company: $this->company,
            source: $this->source,
            status: $this->status,
            interest: $this->interest,
            city: $this->city,
            state: $this->state,
            notes: $this->notes,
            createdAt: $this->created_at,
            updatedAt: $this->updated_at
        );
    }
}
