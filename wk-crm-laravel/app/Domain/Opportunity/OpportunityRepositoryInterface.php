<?php

namespace App\Domain\Opportunity;

use Illuminate\Pagination\LengthAwarePaginator;

interface OpportunityRepositoryInterface
{
    /**
     * Buscar todas as oportunidades com paginação
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar oportunidade por ID
     *
     * @param string $id
     * @return Opportunity|null
     */
    public function findById(string $id): ?Opportunity;

    /**
     * Salvar nova oportunidade
     *
     * @param Opportunity $opportunity
     * @return Opportunity
     */
    public function save(Opportunity $opportunity): Opportunity;

    /**
     * Atualizar oportunidade existente
     *
     * @param string $id
     * @param Opportunity $opportunity
     * @return Opportunity
     */
    public function update(string $id, Opportunity $opportunity): Opportunity;

    /**
     * Deletar oportunidade
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Buscar oportunidades por status
     *
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findByStatus(string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar oportunidades por Lead
     *
     * @param string $leadId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findByLeadId(string $leadId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar oportunidades por Cliente
     *
     * @param string $clienteId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findByClienteId(string $clienteId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Verificar se título já existe
     *
     * @param string $title
     * @param string|null $excludeId
     * @return bool
     */
    public function titleExists(string $title, ?string $excludeId = null): bool;
}
