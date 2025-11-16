<?php

namespace App\Domain\Customer;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface do Repository - DDD Pattern
 * Define contrato sem conhecer detalhes de persistência
 */
interface CustomerRepositoryInterface
{
    /**
     * Lista todos os customers com paginação
     */
    public function findAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * Busca customer por ID
     */
    public function findById(string $id): ?Customer;

    /**
     * Busca customer por email
     */
    public function findByEmail(string $email): ?Customer;

    /**
     * Salva novo customer
     */
    public function save(Customer $customer): Customer;

    /**
     * Atualiza customer existente
     */
    public function update(string $id, Customer $customer): Customer;

    /**
     * Remove customer
     */
    public function delete(string $id): bool;

    /**
     * Verifica se email já existe (exceto para o próprio customer)
     */
    public function emailExists(string $email, ?string $exceptId = null): bool;
}
