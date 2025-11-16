<?php

namespace Tests\Unit\Domain\Customer;

use App\Domain\Customer\Customer;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários para Customer Domain Entity
 * TDD - Test Driven Development
 */
class CustomerTest extends TestCase
{
    /**
     * Testa criação de customer com dados válidos
     */
    public function test_can_create_customer_with_valid_data(): void
    {
        $customer = Customer::create(
            id: '123e4567-e89b-12d3-a456-426614174000',
            name: 'João Silva',
            email: 'joao@exemplo.com',
            phone: '11999999999',
            cpf: '123.456.789-00',
            address: 'Rua Exemplo, 123',
            city: 'São Paulo',
            state: 'SP',
            postalCode: '01234-567'
        );

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('João Silva', $customer->getName());
        $this->assertEquals('joao@exemplo.com', $customer->getEmail());
        $this->assertEquals('active', $customer->getStatus());
    }

    /**
     * Testa atualização de informações
     */
    public function test_can_update_customer_info(): void
    {
        $customer = Customer::create(
            id: null,
            name: 'João Silva',
            email: 'joao@exemplo.com',
            phone: '11999999999',
            cpf: '123.456.789-00',
            address: 'Rua Exemplo, 123',
            city: 'São Paulo',
            state: 'SP',
            postalCode: '01234-567'
        );

        $customer->updateInfo(
            name: 'João Santos',
            phone: '11988888888'
        );

        $this->assertEquals('João Santos', $customer->getName());
        $this->assertEquals('11988888888', $customer->getPhone());
    }

    /**
     * Testa ativação de customer
     */
    public function test_can_activate_customer(): void
    {
        $customer = Customer::create(
            id: null,
            name: 'João Silva',
            email: 'joao@exemplo.com',
            phone: null,
            cpf: null,
            address: null,
            city: null,
            state: null,
            postalCode: null
        );

        $customer->deactivate();
        $this->assertEquals('inactive', $customer->getStatus());

        $customer->activate();
        $this->assertEquals('active', $customer->getStatus());
    }

    /**
     * Testa desativação de customer
     */
    public function test_can_deactivate_customer(): void
    {
        $customer = Customer::create(
            id: null,
            name: 'João Silva',
            email: 'joao@exemplo.com',
            phone: null,
            cpf: null,
            address: null,
            city: null,
            state: null,
            postalCode: null
        );

        $customer->deactivate();

        $this->assertEquals('inactive', $customer->getStatus());
    }

    /**
     * Testa conversão para array
     */
    public function test_can_convert_to_array(): void
    {
        $customer = Customer::create(
            id: '123e4567-e89b-12d3-a456-426614174000',
            name: 'João Silva',
            email: 'joao@exemplo.com',
            phone: '11999999999',
            cpf: '123.456.789-00',
            address: 'Rua Exemplo, 123',
            city: 'São Paulo',
            state: 'SP',
            postalCode: '01234-567'
        );

        $array = $customer->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $array['id']);
        $this->assertEquals('João Silva', $array['name']);
        $this->assertEquals('joao@exemplo.com', $array['email']);
        $this->assertEquals('active', $array['status']);
    }
}
