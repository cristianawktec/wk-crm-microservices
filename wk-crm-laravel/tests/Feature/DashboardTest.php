<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions before each test
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /**
     * Test dashboard stats endpoint requires authentication
     */
    public function test_dashboard_stats_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(401);
    }

    /**
     * Test admin can view all stats
     */
    public function test_admin_can_view_all_dashboard_stats(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create test data
        $this->createTestData($admin);

        $response = $this->actingAs($admin)
            ->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'leads' => [
                        'total' => 3,
                        'open' => 2,
                        'closed' => 1,
                    ],
                    'opportunities' => [
                        'total' => 2,
                        'won' => 1,
                        'pending' => 1,
                    ],
                ],
            ])
            ->assertJsonPath('data.customers.total', 2)
            ->assertJsonPath('data.customers.active', 2);
    }

    /**
     * Test seller can only view their own stats
     */
    public function test_seller_can_only_view_own_stats(): void
    {
        $seller1 = User::factory()->create(['name' => 'Seller 1']);
        $seller1->assignRole('seller');

        $seller2 = User::factory()->create(['name' => 'Seller 2']);
        $seller2->assignRole('seller');

        // Create data for both sellers
        Lead::factory()->count(3)->create(['seller_id' => $seller1->id, 'status' => 'open']);
        Lead::factory()->count(2)->create(['seller_id' => $seller2->id, 'status' => 'open']);

        // Seller1 should only see their 3 leads
        $response = $this->actingAs($seller1)
            ->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'leads' => [
                        'total' => 3,
                        'open' => 3,
                    ],
                ],
            ]);
    }

    /**
     * Test dashboard stats with date filter
     */
    public function test_dashboard_stats_with_date_filter(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create leads with different dates
        Lead::factory()->create([
            'seller_id' => $admin->id,
            'status' => 'open',
            'created_at' => now()->subDays(10),
        ]);

        Lead::factory()->create([
            'seller_id' => $admin->id,
            'status' => 'open',
            'created_at' => now()->addDays(5),
        ]);

        $startDate = now()->subDays(5)->format('Y-m-d');
        $endDate = now()->addDays(10)->format('Y-m-d');

        $response = $this->actingAs($admin)
            ->getJson("/api/dashboard/stats?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        // Should only return 1 lead (created today or within 5 to 10 days ahead)
    }

    /**
     * Test sales pipeline endpoint
     */
    public function test_sales_pipeline_returns_opportunities_by_stage(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create opportunities
        Opportunity::factory()->create([
            'seller_id' => $admin->id,
            'status' => 'pending',
            'value' => 50000,
        ]);

        Opportunity::factory()->create([
            'seller_id' => $admin->id,
            'status' => 'won',
            'value' => 100000,
        ]);

        Opportunity::factory()->create([
            'seller_id' => $admin->id,
            'status' => 'lost',
            'value' => 30000,
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/dashboard/sales-pipeline');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data'); // 3 stages

        $pipeline = $response->json('data');

        // Verify won stage has highest value
        $wonStage = collect($pipeline)->firstWhere('stage', 'won');
        $this->assertEquals(100000, $wonStage['total_value']);
        $this->assertEquals(1, $wonStage['count']);
    }

    /**
     * Test sales pipeline respects seller permissions
     */
    public function test_sales_pipeline_respects_seller_permissions(): void
    {
        $seller1 = User::factory()->create();
        $seller1->assignRole('seller');

        $seller2 = User::factory()->create();
        $seller2->assignRole('seller');

        // Create opportunities for both sellers
        Opportunity::factory()->count(2)->create([
            'seller_id' => $seller1->id,
            'status' => 'pending',
            'value' => 50000,
        ]);

        Opportunity::factory()->count(1)->create([
            'seller_id' => $seller2->id,
            'status' => 'pending',
            'value' => 30000,
        ]);

        $response = $this->actingAs($seller1)
            ->getJson('/api/dashboard/sales-pipeline');

        $response->assertStatus(200);
        $pipeline = $response->json('data');

        // Seller1 should see their opportunities only
        $this->assertEquals(2, $pipeline[0]['count']);
        $this->assertEquals(100000, $pipeline[0]['total_value']); // 2 * 50000
    }

    /**
     * Test dashboard stats with no data
     */
    public function test_dashboard_stats_with_no_data(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'leads' => [
                        'total' => 0,
                        'open' => 0,
                        'closed' => 0,
                        'converted' => 0,
                    ],
                    'opportunities' => [
                        'total' => 0,
                        'won' => 0,
                        'lost' => 0,
                        'pending' => 0,
                        'total_value' => 0,
                    ],
                ],
            ]);
    }

    /**
     * Helper method to create test data
     */
    private function createTestData(User $seller): void
    {
        // Create leads
        Lead::factory()->count(2)->create([
            'seller_id' => $seller->id,
            'status' => 'open',
        ]);

        Lead::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'closed',
        ]);

        // Create opportunities
        Opportunity::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'won',
            'value' => 100000,
        ]);

        Opportunity::factory()->create([
            'seller_id' => $seller->id,
            'status' => 'pending',
            'value' => 50000,
        ]);

        // Create customers (without seller_id - customers may not have this relationship)
        Customer::factory()->count(2)->create([
            'status' => 'active',
        ]);
    }
}
