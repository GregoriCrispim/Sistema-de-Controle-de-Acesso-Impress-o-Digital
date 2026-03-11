<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => "{$role}@test.com",
            'password' => bcrypt('123456'),
            'role' => $role,
            'active' => true,
        ]);
    }

    public function test_operator_can_access_terminal(): void
    {
        $user = $this->createUser('operator');
        $this->actingAs($user)->get('/operator/terminal')->assertOk();
    }

    public function test_company_cannot_access_terminal(): void
    {
        $user = $this->createUser('company');
        $this->actingAs($user)->get('/operator/terminal')->assertForbidden();
    }

    public function test_company_can_access_dashboard(): void
    {
        $user = $this->createUser('company');
        $this->actingAs($user)->get('/company/dashboard')->assertOk();
    }

    public function test_operator_cannot_access_company_dashboard(): void
    {
        $user = $this->createUser('operator');
        $this->actingAs($user)->get('/company/dashboard')->assertForbidden();
    }

    public function test_admin_can_access_all_sections(): void
    {
        $user = $this->createUser('admin');
        $this->actingAs($user)->get('/admin/dashboard')->assertOk();
        $this->actingAs($user)->get('/operator/terminal')->assertOk();
        $this->actingAs($user)->get('/company/dashboard')->assertOk();
        $this->actingAs($user)->get('/fiscal/dashboard')->assertOk();
        $this->actingAs($user)->get('/management/dashboard')->assertOk();
    }

    public function test_management_cannot_access_admin(): void
    {
        $user = $this->createUser('management');
        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_management_can_access_occurrences(): void
    {
        $user = $this->createUser('management');
        $this->actingAs($user)->get('/management/occurrences')->assertOk();
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/operator/terminal')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/company/dashboard')->assertRedirect('/login');
    }
}
