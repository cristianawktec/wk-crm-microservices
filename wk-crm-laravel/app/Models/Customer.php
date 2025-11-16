<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Model para persistência de Customer
 * Separado do Domain Entity para seguir DDD
 */
class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf',
        'address',
        'city',
        'state',
        'postal_code',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Converte para Domain Entity
     */
    public function toDomainEntity(): \App\Domain\Customer\Customer
    {
        return \App\Domain\Customer\Customer::create(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            phone: $this->phone,
            cpf: $this->cpf,
            address: $this->address,
            city: $this->city,
            state: $this->state,
            postalCode: $this->postal_code
        );
    }

    /**
     * Cria Model a partir de Domain Entity
     */
    public static function fromDomainEntity(\App\Domain\Customer\Customer $customer): self
    {
        $data = $customer->toArray();
        
        return new self([
            'id' => $data['id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'cpf' => $data['cpf'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postal_code'],
            'status' => $data['status']
        ]);
    }
}
