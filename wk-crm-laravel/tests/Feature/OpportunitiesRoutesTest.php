<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunitiesRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    /**
     * Test GET /api/opportunities returns list
     */
    public function test_opportunities_index_returns_success()
    {
        $response = $this->getJson('/api/opportunities');
        $response->assertStatus(200);
    }

    /**
     * Test POST /api/opportunities creates opportunity
     */
    public function test_opportunities_store_creates_opportunity()
    {
        $customer = Customer::factory()->create();
        $data = [
            'title' => 'Deal ABC',
            'description' => 'Closing in Dec',
            'value' => 50000.00,
            'status' => 'open',
            'customer_id' => $customer->id,
        ];

        $response = $this->postJson('/api/opportunities', $data);
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Deal ABC', 'value' => 50000]);

        $this->assertDatabaseHas('opportunities', ['title' => 'Deal ABC']);
    }

    /**
     * Test GET /api/opportunities/{id} returns single opportunity
     */
    public function test_opportunities_show_returns_opportunity()
    {
        $opportunity = Opportunity::factory()->create();
        $response = $this->getJson("/api/opportunities/{$opportunity->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $opportunity->id, 'title' => $opportunity->title]);
    }

    /**
     * Test PUT /api/opportunities/{id} updates opportunity
     */
    public function test_opportunities_update_modifies_opportunity()
    {
        $opportunity = Opportunity::factory()->create();
        $updatedData = [
            'title' => 'Updated Deal',
            'value' => 75000.00,
            'status' => 'closed',
        ];

        $response = $this->putJson("/api/opportunities/{$opportunity->id}", $updatedData);
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Deal']);

        $this->assertDatabaseHas('opportunities', ['id' => $opportunity->id, 'title' => 'Updated Deal']);
    }

    /**
     * Test DELETE /api/opportunities/{id} deletes opportunity
     */
    public function test_opportunities_destroy_removes_opportunity()
    {
        $opportunity = Opportunity::factory()->create();
        $response = $this->deleteJson("/api/opportunities/{$opportunity->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('opportunities', ['id' => $opportunity->id]);
    }
}
