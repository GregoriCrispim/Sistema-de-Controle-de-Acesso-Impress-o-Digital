<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_operator_can_login(): void
    {
        $user = User::create([
            'name' => 'Operador',
            'email' => 'op@test.com',
            'password' => bcrypt('password123'),
            'role' => 'operator',
            'active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'op@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/operator/terminal');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_redirects_to_admin_dashboard(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'name' => 'Inativo',
            'email' => 'inactive@test.com',
            'password' => bcrypt('password123'),
            'role' => 'operator',
            'active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    public function test_wrong_password_fails(): void
    {
        User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => bcrypt('password123'),
            'role' => 'operator',
            'active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'user@test.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    public function test_login_creates_login_log(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => bcrypt('password123'),
            'role' => 'operator',
            'active' => true,
        ]);

        $this->post('/login', [
            'email' => 'user@test.com',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $user->id,
        ]);
    }
}
