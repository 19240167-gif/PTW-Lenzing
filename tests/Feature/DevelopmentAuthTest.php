<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dev_login_page_is_accessible_in_development(): void
    {
        $response = $this->get(route('auth.dev-login'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.dev-login');
    }

    /** @test */
    public function dev_login_page_shows_active_users(): void
    {
        $dept = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        User::create([
            'name' => 'Test User', 'domain' => 'LAGGRF', 'username' => 'testuser',
            'upn' => 'test@pt-spv.com', 'email' => 'test@pt-spv.com',
            'department_id' => $dept->id, 'is_active' => true,
        ]);

        $response = $this->get(route('auth.dev-login'));
        $response->assertSee('Test User');
    }

    /** @test */
    public function inactive_user_is_not_shown_on_dev_login(): void
    {
        User::create([
            'name' => 'Inactive User', 'domain' => 'LAGGRF', 'username' => 'inactive',
            'upn' => 'inactive@pt-spv.com', 'email' => 'inactive@pt-spv.com',
            'is_active' => false,
        ]);

        $response = $this->get(route('auth.dev-login'));
        $response->assertDontSee('Inactive User');
    }

    /** @test */
    public function user_can_login_via_dev_dropdown(): void
    {
        $user = User::create([
            'name' => 'Budi Santoso', 'domain' => 'LAGGRF', 'username' => 'budi',
            'upn' => 'budi@pt-spv.com', 'email' => 'budi@pt-spv.com',
            'is_active' => true,
        ]);

        $response = $this->post(route('auth.dev-login.post'), ['user_id' => $user->id]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function inactive_user_cannot_login(): void
    {
        $user = User::create([
            'name' => 'Inactive', 'domain' => 'LAGGRF', 'username' => 'inactive2',
            'upn' => 'inactive2@pt-spv.com', 'email' => 'inactive2@pt-spv.com',
            'is_active' => false,
        ]);

        $this->post(route('auth.dev-login.post'), ['user_id' => $user->id]);
        $this->assertGuest();
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('auth.dev-login'));
    }

    /** @test */
    public function authenticated_user_can_access_dashboard(): void
    {
        $this->withoutExceptionHandling();

        $user = User::create([
            'name' => 'Test User', 'domain' => 'LAGGRF', 'username' => 'testuser2',
            'upn' => 'test2@pt-spv.com', 'email' => 'test2@pt-spv.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
    }
}
