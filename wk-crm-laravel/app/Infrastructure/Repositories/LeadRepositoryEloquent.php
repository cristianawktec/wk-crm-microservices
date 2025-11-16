<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Lead\Lead as LeadEntity;
use App\Domain\Lead\LeadRepositoryInterface;
use App\Models\Lead as LeadModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Implementação Eloquent do Repository de Lead
 * Isola detalhes de persistência da lógica de domínio (DDD)
 */
class LeadRepositoryEloquent implements LeadRepositoryInterface
{
    public function __construct(
        private LeadModel $model
    ) {}

    public function findAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(string $id): ?LeadEntity
    {
        $model = $this->model->find($id);
        
        return $model ? $model->toDomainEntity() : null;
    }

    public function findByEmail(string $email): ?LeadEntity
    {
        $model = $this->model->where('email', $email)->first();
        
        return $model ? $model->toDomainEntity() : null;
    }

    public function save(LeadEntity $lead): LeadEntity
    {
        $data = $lead->toArray();
        
        $model = $this->model->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'company' => $data['company'],
            'source' => $data['source'],
            'status' => $data['status'],
            'interest' => $data['interest'],
            'city' => $data['city'],
            'state' => $data['state'],
            'notes' => $data['notes'],
        ]);

        return $model->toDomainEntity();
    }

    public function update(string $id, LeadEntity $lead): LeadEntity
    {
        $model = $this->model->findOrFail($id);
        $data = $lead->toArray();
        
        $model->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'company' => $data['company'],
            'source' => $data['source'],
            'status' => $data['status'],
            'interest' => $data['interest'],
            'city' => $data['city'],
            'state' => $data['state'],
            'notes' => $data['notes'],
        ]);

        return $model->fresh()->toDomainEntity();
    }

    public function delete(string $id): bool
    {
        $model = $this->model->findOrFail($id);
        
        return $model->delete();
    }

    public function emailExists(string $email, ?string $exceptId = null): bool
    {
        $query = $this->model->where('email', $email);
        
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }
        
        return $query->exists();
    }
}
