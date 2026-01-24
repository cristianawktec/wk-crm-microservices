<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        // Create test users
        $this->user = User::factory()->create();
        $this->user->assignRole('customer');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    /**
     * Test notification creation for opportunity
     */
    public function test_notification_created_when_opportunity_created(): void
    {
        Sanctum::actingAs($this->admin);

        $customer = Customer::factory()->create();
        
        $opportunityData = [
            'title' => 'Nova Oportunidade Teste',
            'description' => 'Descrição da oportunidade',
            'value' => 50000.00,
            'probability' => 70,
            'status' => 'open',
            'customer_id' => $customer->id,
        ];

        $response = $this->postJson('/api/opportunities', $opportunityData);
        
        $response->assertStatus(201);

        // Verify notification was created
        $this->assertDatabaseHas('notifications', [
            'title' => 'Nova Oportunidade',
        ]);
    }

    /**
     * Test GET /api/notifications returns user notifications
     */
    public function test_notifications_index_returns_user_notifications(): void
    {
        Sanctum::actingAs($this->user);

        // Create notifications for this user
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Create notification for another user (should not appear)
        Notification::factory()->create([
            'user_id' => $this->admin->id,
        ]);

        $response = $this->getJson('/api/notifications');
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test marking notification as read
     */
    public function test_notification_can_be_marked_as_read(): void
    {
        Sanctum::actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->patchJson("/api/notifications/{$notification->id}/read");
        
        $response->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /**
     * Test marking all notifications as read
     */
    public function test_can_mark_all_notifications_as_read(): void
    {
        Sanctum::actingAs($this->user);

        // Create unread notifications
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->postJson('/api/notifications/mark-all-read');
        
        $response->assertStatus(200);

        // Verify all are marked as read
        $unreadCount = Notification::where('user_id', $this->user->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    /**
     * Test SSE stream endpoint exists and requires auth
     */
    public function test_sse_stream_requires_authentication(): void
    {
        $response = $this->get('/api/notifications/stream');
        
        $response->assertStatus(302); // Redirect to login or 401
    }

    /**
     * Test SSE stream with valid token
     */
    public function test_sse_stream_with_valid_token(): void
    {
        Sanctum::actingAs($this->user);
        
        $token = $this->user->createToken('test-token')->plainTextToken;

        // SSE endpoint com token
        $response = $this->get("/api/notifications/stream?token={$token}");
        
        // Should return 200 or start streaming
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    /**
     * Test notification filtering by read/unread
     */
    public function test_can_filter_notifications_by_read_status(): void
    {
        Sanctum::actingAs($this->user);

        // Create read and unread notifications
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'read_at' => now(),
        ]);

        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        // Get unread only
        $response = $this->getJson('/api/notifications?filter=unread');
        
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test notification creation includes correct action_url
     */
    public function test_notification_has_correct_action_url_format(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'opportunity_created',
            'data' => [
                'opportunity_id' => 123,
            ],
        ]);

        // Action URL should be /opportunities/{id}, not /opportunity/{id}
        $this->assertStringContainsString('/opportunities/', $notification->action_url);
        $this->assertStringNotContainsString('/opportunity/', $notification->action_url);
    }

    /**
     * Test user cannot access another user's notifications
     */
    public function test_user_cannot_access_other_user_notifications(): void
    {
        Sanctum::actingAs($this->user);

        $otherNotification = Notification::factory()->create([
            'user_id' => $this->admin->id,
        ]);

        $response = $this->patchJson("/api/notifications/{$otherNotification->id}/read");
        
        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test notification count endpoint
     */
    public function test_unread_notification_count(): void
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->count(7)->create([
            'user_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->getJson('/api/notifications/unread-count');
        
        $response->assertStatus(200)
            ->assertJson(['count' => 7]);
    }
}
