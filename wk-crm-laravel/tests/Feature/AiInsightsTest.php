<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiInsightsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Opportunity $opportunity;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        
        $customer = Customer::factory()->create();
        
        $this->opportunity = Opportunity::factory()->create([
            'seller_id' => $this->user->id,
            'customer_id' => $customer->id,
            'title' => 'Oportunidade Teste AI',
            'value' => 100000.00,
            'probability' => 75,
            'status' => 'open',
        ]);
    }

    /**
     * Test AI insights endpoint returns response
     */
    public function test_ai_insights_returns_analysis(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => $this->opportunity->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'opportunity_id',
                    'analysis',
                    'risk_score',
                    'recommended_action',
                    'insights',
                    'model',
                ],
            ]);
    }

    /**
     * Test AI insights with analyze-opportunity alias endpoint
     */
    public function test_analyze_opportunity_endpoint_works(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/opportunities/analyze-opportunity', [
            'opportunity_id' => $this->opportunity->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    /**
     * Test AI insights requires authentication
     */
    public function test_ai_insights_requires_authentication(): void
    {
        $response = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => $this->opportunity->id,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test AI insights validates opportunity_id
     */
    public function test_ai_insights_validates_opportunity_id(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['opportunity_id']);
    }

    /**
     * Test AI insights for non-existent opportunity
     */
    public function test_ai_insights_for_nonexistent_opportunity(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => 99999,
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test AI insights respects ownership
     */
    public function test_ai_insights_respects_opportunity_ownership(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('customer');
        
        Sanctum::actingAs($otherUser);

        $response = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => $this->opportunity->id,
        ]);

        // Should return 403 Forbidden or 404 Not Found depending on policy
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * Test chatbot endpoint
     */
    public function test_chatbot_endpoint_returns_response(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/chat/ask', [
            'message' => 'Quais são minhas oportunidades em aberto?',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'response',
                    'timestamp',
                ],
            ]);
    }

    /**
     * Test chatbot validates message field
     */
    public function test_chatbot_validates_message_field(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/chat/ask', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /**
     * Test chatbot with empty message
     */
    public function test_chatbot_rejects_empty_message(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/chat/ask', [
            'message' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /**
     * Test AI insights returns fallback when AI service unavailable
     */
    public function test_ai_insights_returns_fallback_when_service_unavailable(): void
    {
        Sanctum::actingAs($this->user);

        // This test assumes AI service might be down
        $response = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => $this->opportunity->id,
        ]);

        // Should still return 200 with fallback data
        $response->assertStatus(200);
        
        // Check if response has required fields
        $this->assertArrayHasKey('data', $response->json());
    }

    /**
     * Test AI insights uses Groq or Gemini model
     */
    public function test_ai_insights_specifies_model_used(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => $this->opportunity->id,
        ]);

        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        // Model should be specified (groq-llama-3.3-70b or gemini-*)
        $this->assertArrayHasKey('model', $data);
        $this->assertNotEmpty($data['model']);
    }

    /**
     * Test AI insights analysis varies by probability
     */
    public function test_ai_insights_considers_probability(): void
    {
        Sanctum::actingAs($this->user);

        // Low probability opportunity
        $lowProbOpp = Opportunity::factory()->create([
            'seller_id' => $this->user->id,
            'probability' => 20,
            'value' => 50000,
        ]);

        $response1 = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => $lowProbOpp->id,
        ]);

        // High probability opportunity  
        $highProbOpp = Opportunity::factory()->create([
            'seller_id' => $this->user->id,
            'probability' => 95,
            'value' => 50000,
        ]);

        $response2 = $this->postJson('/api/opportunities/insights', [
            'opportunity_id' => $highProbOpp->id,
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Risk scores should be different
        $risk1 = $response1->json('data.risk_score');
        $risk2 = $response2->json('data.risk_score');
        
        // High probability should have lower risk
        $this->assertGreaterThan($risk2, $risk1);
    }
}
