<?php

namespace App\Domain\Lead;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Lead Repository Interface - Domain Layer
 * Define o contrato para persistência de Leads
 */
interface LeadRepositoryInterface
{
    public function findAll(int $perPage = 15): LengthAwarePaginator;
    
    public function findById(string $id): ?Lead;
    
    public function findByEmail(string $email): ?Lead;
    
    public function save(Lead $lead): Lead;
    
    public function update(string $id, Lead $lead): Lead;
    
    public function delete(string $id): bool;
    
    public function emailExists(string $email, ?string $exceptId = null): bool;
}
