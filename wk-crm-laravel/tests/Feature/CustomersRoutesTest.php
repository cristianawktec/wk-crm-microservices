<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomersRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    /**
     * Test GET /api/customers returns list of customers
     */
    public function test_customers_index_returns_success()
    {
        $response = $this->getJson('/api/customers');
        $response->assertStatus(200);
    }

    /**
     * Test POST /api/customers creates a customer
     */
    public function test_customers_store_creates_customer()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'status' => 'active',
            'company' => 'Acme Inc',
        ];

        $response = $this->postJson('/api/customers', $data);
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'John Doe', 'email' => 'john@example.com']);

        $this->assertDatabaseHas('customers', ['email' => 'john@example.com']);
    }

    /**
     * Test GET /api/customers/{id} returns single customer
     */
    public function test_customers_show_returns_customer()
    {
        $customer = Customer::factory()->create();
        $response = $this->getJson("/api/customers/{$customer->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $customer->id, 'name' => $customer->name]);
    }

    /**
     * Test PUT /api/customers/{id} updates customer
     */
    public function test_customers_update_modifies_customer()
    {
        $customer = Customer::factory()->create();
        $updatedData = [
            'name' => 'Updated Name',
            'email' => $customer->email,
        ];

        $response = $this->putJson("/api/customers/{$customer->id}", $updatedData);
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Updated Name']);
    }

    /**
     * Test DELETE /api/customers/{id} deletes customer
     */
    public function test_customers_destroy_removes_customer()
    {
        $customer = Customer::factory()->create();
        $response = $this->deleteJson("/api/customers/{$customer->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
