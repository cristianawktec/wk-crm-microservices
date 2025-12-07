<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeadsRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    /**
     * Check that the specific metadata route exists and returns 200.
     */
    public function test_leads_sources_route_returns_success()
    {
        $response = $this->getJson('/api/leads/sources');
        $response->assertStatus(200);
    }

    /**
     * Ensure the `opportunities` table has the `value` column present.
     */
    public function test_opportunities_table_has_value_column()
    {
        // RefreshDatabase will run migrations; ensure migrations created the table
        $this->assertTrue(Schema::hasTable('opportunities'));
        $this->assertTrue(Schema::hasColumn('opportunities', 'value'));
    }
}
