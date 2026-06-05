<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class WorkspaceApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private Tenant $tenant;
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::query()->create(['name' => 'Test Tenant', 'slug' => 'test-tenant', 'status' => 'active']);

        TenantContext::set((int) $this->tenant->id);

        $this->user = User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    public function test_can_list_resources_members(): void
    {
        $response = $this->getJson('/api/resources/members', $this->authHeaders());

        $response->assertOk();
    }

    public function test_can_create_resources_member(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'phone' => $this->faker->phoneNumber,
            'role' => 'Developer',
            'hourly_rate' => 50.00,
            'status' => 'active',
        ];

        $response = $this->postJson('/api/resources/members', $payload, $this->authHeaders());

        $response->assertCreated();
        $response->assertJsonFragment(['name' => $payload['name']]);
    }

    public function test_can_list_quality_test_cases(): void
    {
        $response = $this->getJson('/api/quality/test-cases', $this->authHeaders());

        $response->assertOk();
    }

    public function test_can_create_quality_test_case(): void
    {
        $payload = [
            'project_id' => 1,
            'title' => 'Login validation test',
            'test_type' => 'unit',
            'status' => 'pending',
        ];

        $response = $this->postJson('/api/quality/test-cases', $payload, $this->authHeaders());

        $response->assertCreated();
    }

    public function test_can_list_initiation_stakeholders(): void
    {
        $response = $this->getJson('/api/initiation/stakeholders', $this->authHeaders());

        $response->assertOk();
    }

    public function test_can_list_agile_sprints(): void
    {
        $response = $this->getJson('/api/agile/sprints', $this->authHeaders());

        $response->assertOk();
    }

    public function test_can_update_preferences(): void
    {
        $payload = ['timezone' => 'Africa/Accra', 'currency' => 'GHC'];

        $response = $this->putJson('/api/settings/preferences', $payload, $this->authHeaders());

        $response->assertOk();
    }

    public function test_unauthenticated_access_is_rejected(): void
    {
        $response = $this->getJson('/api/resources/members');

        $response->assertUnauthorized();
    }

    public function test_cross_tenant_isolation(): void
    {
        $tenantB = Tenant::query()->create(['name' => 'Tenant B', 'slug' => 'tenant-b', 'status' => 'active']);
        $userB = User::query()->create([
            'name' => 'User B',
            'email' => 'userb@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenantB->id,
        ]);
        $tokenB = $userB->createToken('test-token')->plainTextToken;

        TenantContext::set((int) $this->tenant->id);
        $member = \App\Models\TeamMember::query()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Tenant A Member',
            'email' => 'a@example.com',
            'phone' => '123',
            'role' => 'Dev',
        ]);

        TenantContext::set((int) $tenantB->id);

        $response = $this->getJson('/api/resources/members', ['Authorization' => 'Bearer '.$tokenB]);
        $response->assertOk();

        $records = $response->json('records') ?? $response->json();
        $names = array_column((array) $records, 'name');
        $this->assertNotContains('Tenant A Member', $names);
    }
}
