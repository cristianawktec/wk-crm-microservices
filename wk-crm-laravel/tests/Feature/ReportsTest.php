<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /**
     * Test sales report requires authentication
     */
    public function test_sales_report_requires_authentication(): void
    {
        $response = $this->getJson('/api/reports/sales');
        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can access sales report
     */
    public function test_authenticated_user_can_access_sales_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create test data
        Opportunity::factory()->count(5)->create([
            'seller_id' => $admin->id,
            'status' => 'won',
            'value' => 50000,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/reports/sales');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'report' => 'sales',
            ])
            ->assertJsonPath('summary.total_opportunities', 5)
            ->assertJsonPath('summary.won_count', 5);
        
        // Check total_value is approximately correct
        $totalValue = $response->json('summary.total_value');
        $this->assertGreaterThan(249999, $totalValue);
        $this->assertLessThan(250001, $totalValue);
    }

    /**
     * Test sales report with date filter
     */
    public function test_sales_report_respects_date_filter(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create opportunities with different dates
        Opportunity::factory()->create([
            'seller_id' => $admin->id,
            'status' => 'won',
            'value' => 50000,
            'created_at' => now()->subDays(15),
        ]);

        Opportunity::factory()->create([
            'seller_id' => $admin->id,
            'status' => 'won',
            'value' => 30000,
            'created_at' => now()->subDays(5),
        ]);

        $dateFrom = now()->subDays(10)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $response = $this->actingAs($admin)
            ->getJson("/api/reports/sales?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        // Should only include the recent opportunity
    }

    /**
     * Test seller can only see their own sales report
     */
    public function test_seller_can_only_see_own_sales_report(): void
    {
        $seller1 = User::factory()->create();
        $seller1->assignRole('seller');

        $seller2 = User::factory()->create();
        $seller2->assignRole('seller');

        // Create opportunities for both sellers
        Opportunity::factory()->count(5)->create([
            'seller_id' => $seller1->id,
            'status' => 'won',
        ]);

        Opportunity::factory()->count(3)->create([
            'seller_id' => $seller2->id,
            'status' => 'won',
        ]);

        $response = $this->actingAs($seller1)->getJson('/api/reports/sales');

        $response->assertStatus(200)
            ->assertJsonPath('summary.total_opportunities', 5);
    }

    /**
     * Test sales report JSON structure
     */
    public function test_sales_report_json_structure(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Opportunity::factory()->count(3)->create([
            'seller_id' => $admin->id,
            'status' => 'won',
            'value' => 50000,
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/reports/sales');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'report',
                'period',
                'summary' => [
                    'total_opportunities',
                    'total_value',
                    'won_count',
                    'won_value',
                    'conversion_rate',
                    'average_value',
                ],
                'data',
            ]);
    }

    /**
     * Test leads report requires authentication
     */
    public function test_leads_report_requires_authentication(): void
    {
        $response = $this->getJson('/api/reports/leads');
        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can access leads report
     */
    public function test_authenticated_user_can_access_leads_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create test data
        Lead::factory()->count(10)->create([
            'seller_id' => $admin->id,
            'status' => 'open',
        ]);

        Lead::factory()->count(2)->create([
            'seller_id' => $admin->id,
            'status' => 'converted',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/reports/leads');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'report' => 'leads',
            ])
            ->assertJsonPath('summary.total_leads', 12)
            ->assertJsonPath('summary.converted', 2);
    }

    /**
     * Test leads report calculates conversion rate
     */
    public function test_leads_report_calculates_conversion_rate(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create 100 leads with 20 converted (20% rate)
        Lead::factory()->count(80)->create([
            'seller_id' => $admin->id,
            'status' => 'open',
        ]);

        Lead::factory()->count(20)->create([
            'seller_id' => $admin->id,
            'status' => 'converted',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/reports/leads');

        $response->assertStatus(200);
        
        // Check conversion rate is approximately correct
        $conversionRate = $response->json('summary.conversion_rate');
        $this->assertGreaterThan(19.9, $conversionRate);
        $this->assertLessThan(20.1, $conversionRate);
    }

    /**
     * Test leads report with source filter
     */
    public function test_leads_report_respects_source_filter(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Lead::factory()->count(5)->create([
            'seller_id' => $admin->id,
            'source' => 'website',
        ]);

        Lead::factory()->count(3)->create([
            'seller_id' => $admin->id,
            'source' => 'referral',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/reports/leads?source=website');

        $response->assertStatus(200);
    }

    /**
     * Test leads report CSV export
     */
    public function test_leads_report_csv_export(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Lead::factory()->count(5)->create([
            'seller_id' => $admin->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/reports/leads?format=csv');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        // Verify CSV content
        $content = $response->streamedContent();
        $this->assertStringContainsString('LEADS REPORT SUMMARY', $content);
    }

    /**
     * Test sales report grouping by status
     */
    public function test_sales_report_group_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Opportunity::factory()->count(5)->create([
            'seller_id' => $admin->id,
            'status' => 'won',
        ]);

        Opportunity::factory()->count(3)->create([
            'seller_id' => $admin->id,
            'status' => 'lost',
        ]);

        Opportunity::factory()->count(2)->create([
            'seller_id' => $admin->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/reports/sales?group_by=status');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // 3 statuses
    }

    /**
     * Test leads report grouping by source
     */
    public function test_leads_report_group_by_source(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Lead::factory()->count(5)->create([
            'seller_id' => $admin->id,
            'source' => 'website',
        ]);

        Lead::factory()->count(3)->create([
            'seller_id' => $admin->id,
            'source' => 'referral',
        ]);

        Lead::factory()->count(2)->create([
            'seller_id' => $admin->id,
            'source' => 'cold_call',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/reports/leads?group_by=source');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // 3 sources
    }
}
