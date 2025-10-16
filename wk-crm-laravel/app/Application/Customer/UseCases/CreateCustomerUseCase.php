<?php

namespace App\Application\Customer\UseCases;

use App\Domain\Customer\Customer;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Domain\Customer\ValueObjects\CustomerEmail;
use App\Domain\Customer\ValueObjects\CustomerPhone;
use App\Domain\Shared\ValueObjects\Name;
use InvalidArgumentException;

class CreateCustomerUseCase
{
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function execute(CreateCustomerRequest $request): CreateCustomerResponse
    {
        // Validar se email já existe
        $email = new CustomerEmail($request->email);
        if ($this->customerRepository->existsByEmail($email)) {
            throw new InvalidArgumentException('Email já está em uso por outro cliente');
        }

        // Criar value objects
        $name = new Name($request->name);
        $phone = $request->phone ? new CustomerPhone($request->phone) : null;

        // Criar customer
        $customer = Customer::create(
            $name,
            $email,
            $phone,
            $request->status ?? 'active',
            $request->company,
            $request->address,
            $request->city,
            $request->state,
            $request->zipCode,
            $request->country ?? 'Brasil'
        );

        // Salvar
        $this->customerRepository->save($customer);

        return new CreateCustomerResponse($customer);
    }
}