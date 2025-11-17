<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Opportunity\Opportunity;
use App\Domain\Opportunity\OpportunityRepositoryInterface;
use App\Models\Opportunity as OpportunityModel;
use Illuminate\Pagination\LengthAwarePaginator;

class OpportunityRepositoryEloquent implements OpportunityRepositoryInterface
{
    public function __construct(
        private OpportunityModel $model
    ) {
    }

    public function findAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['lead', 'cliente'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id): ?Opportunity
    {
        $model = $this->model->with(['lead', 'cliente'])->find($id);

        return $model ? $model->toDomainEntity() : null;
    }

    public function save(Opportunity $opportunity): Opportunity
    {
        $data = $opportunity->toArray();
        
        $model = $this->model->create($data);

        return $model->toDomainEntity();
    }

    public function update(string $id, Opportunity $opportunity): Opportunity
    {
        $model = $this->model->findOrFail($id);
        
        $data = $opportunity->toArray();
        unset($data['id']); // Não atualizar o ID
        
        $model->update($data);

        return $model->fresh()->toDomainEntity();
    }

    public function delete(string $id): bool
    {
        $model = $this->model->findOrFail($id);
        
        return $model->delete();
    }

    public function findByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('status', $status)
            ->with(['lead', 'cliente'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByLeadId(string $leadId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('lead_id', $leadId)
            ->with(['lead', 'cliente'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByClienteId(string $clienteId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('cliente_id', $clienteId)
            ->with(['lead', 'cliente'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function titleExists(string $title, ?string $excludeId = null): bool
    {
        $query = $this->model->where('title', $title);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
