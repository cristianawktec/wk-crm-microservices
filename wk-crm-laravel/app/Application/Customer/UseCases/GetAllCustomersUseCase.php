<?php

namespace App\Application\Customer\UseCases;

use App\Domain\Customer\Repositories\CustomerRepositoryInterface;

class GetAllCustomersUseCase
{
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function execute(GetAllCustomersRequest $request): GetAllCustomersResponse
    {
        $customers = $this->customerRepository->findAll($request->limit, $request->offset);
        $total = $this->customerRepository->count();

        return new GetAllCustomersResponse($customers, $total, $request->limit, $request->offset);
    }
}