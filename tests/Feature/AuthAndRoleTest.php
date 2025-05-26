<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_default_role(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'testregister@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'testregister@example.com',
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_users_can_login(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin Login Test',
            'email' => 'superlogin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $admin = User::create([
            'name' => 'Admin Login Test',
            'email' => 'adminlogin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'User Login Test',
            'email' => 'userlogin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'email_verified_at' => now(),
        ]);

        foreach ([$superAdmin, $admin, $user] as $testUser) {
            $response = $this->post(route('login'), [
                'email' => $testUser->email,
                'password' => 'password',
            ]);
            $response->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($testUser);
            $this->post(route('logout')); // Log out before next iteration
        }
    }

    public function test_super_admin_can_access_dashboards(): void
    {
        $superAdminUser = User::create([
            'name' => 'Super Admin Access Test',
            'email' => 'superaccess@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($superAdminUser);

        $this->get(route('superadmin.dashboard'))->assertStatus(200);
        $this->get(route('admin.dashboard'))->assertStatus(200);
        $this->get(route('dashboard'))->assertStatus(200);
    }

    public function test_admin_can_access_dashboards(): void
    {
        $adminUser = User::create([
            'name' => 'Admin Access Test',
            'email' => 'adminaccess@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($adminUser);

        $this->get(route('admin.dashboard'))->assertStatus(200);
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('superadmin.dashboard'))->assertStatus(403);
    }

    public function test_user_can_access_dashboards(): void
    {
        $regularUser = User::create([
            'name' => 'User Access Test',
            'email' => 'useraccess@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($regularUser);

        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('admin.dashboard'))->assertStatus(403);
        $this->get(route('superadmin.dashboard'))->assertStatus(403);
    }

    public function test_guest_access_to_routes(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('superadmin.dashboard'))->assertRedirect(route('login'));
    }
}
