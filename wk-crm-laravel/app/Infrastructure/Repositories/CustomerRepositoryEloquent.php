<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Customer\Customer as CustomerEntity;
use App\Domain\Customer\CustomerRepositoryInterface;
use App\Models\Customer as CustomerModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Implementação Eloquent do Repository
 * Isola detalhes de persistência da lógica de domínio (DDD)
 */
class CustomerRepositoryEloquent implements CustomerRepositoryInterface
{
    public function __construct(
        private CustomerModel $model
    ) {}

    public function findAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function findById(string $id): ?CustomerEntity
    {
        $model = $this->model->find($id);
        
        return $model ? $model->toDomainEntity() : null;
    }

    public function findByEmail(string $email): ?CustomerEntity
    {
        $model = $this->model->where('email', $email)->first();
        
        return $model ? $model->toDomainEntity() : null;
    }

    public function save(CustomerEntity $customer): CustomerEntity
    {
        $data = $customer->toArray();
        
        $model = $this->model->create([
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

        return $model->toDomainEntity();
    }

    public function update(string $id, CustomerEntity $customer): CustomerEntity
    {
        $model = $this->model->findOrFail($id);
        $data = $customer->toArray();
        
        $model->update([
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
