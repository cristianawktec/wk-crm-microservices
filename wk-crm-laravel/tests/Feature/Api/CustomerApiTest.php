<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de integração para Customer API
 * TDD - Test Driven Development
 */
class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa listagem de customers
     */
    public function test_can_list_customers(): void
    {
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'cpf',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    /**
     * Testa criação de customer com dados válidos
     */
    public function test_can_create_customer_with_valid_data(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'phone' => '11999999999',
            'cpf' => '123.456.789-00',
            'address' => 'Rua Exemplo, 123',
            'city' => 'São Paulo',
            'state' => 'SP',
            'postal_code' => '01234-567'
        ];

        $response = $this->postJson('/api/customers', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'cpf',
                    'address',
                    'city',
                    'state',
                    'postal_code',
                    'status',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJsonPath('data.name', 'João Silva')
            ->assertJsonPath('data.email', 'joao@exemplo.com');

        $this->assertDatabaseHas('customers', [
            'email' => 'joao@exemplo.com'
        ]);
    }

    /**
     * Testa validação de campos obrigatórios
     */
    public function test_create_customer_validates_required_fields(): void
    {
        $response = $this->postJson('/api/customers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    /**
     * Testa validação de email único
     */
    public function test_create_customer_validates_unique_email(): void
    {
        Customer::factory()->create(['email' => 'existente@exemplo.com']);

        $response = $this->postJson('/api/customers', [
            'name' => 'Teste',
            'email' => 'existente@exemplo.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa exibição de customer específico
     */
    public function test_can_show_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $customer->id)
            ->assertJsonPath('data.name', $customer->name);
    }

    /**
     * Testa erro 404 para customer inexistente
     */
    public function test_show_returns_404_for_nonexistent_customer(): void
    {
        $response = $this->getJson('/api/customers/invalid-id');

        $response->assertStatus(404);
    }

    /**
     * Testa atualização de customer
     */
    public function test_can_update_customer(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Nome Original'
        ]);

        $response = $this->putJson("/api/customers/{$customer->id}", [
            'name' => 'Nome Atualizado'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nome Atualizado');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Nome Atualizado'
        ]);
    }

    /**
     * Testa remoção de customer
     */
    public function test_can_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id
        ]);
    }
}
