<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_dashboard_with_platform_stats(): void
    {
        $user = $this->createSuperAdminUser();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Global Dashboard')
            ->assertSee('Active Children')
            ->assertSee('Published Stories');
    }

    public function test_super_admin_can_toggle_maintenance_mode(): void
    {
        $user = $this->createSuperAdminUser();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('toggleMaintenance')
            ->assertSet('maintenanceMode', true);

        $this->assertTrue(app()->isDownForMaintenance());

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('toggleMaintenance')
            ->assertSet('maintenanceMode', false);

        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function test_admin_routes_remain_available_during_maintenance(): void
    {
        $user = $this->createSuperAdminUser();
        Artisan::call('down', ['--retry' => 60, '--secret' => 'test-bypass']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();

        Artisan::call('up');
    }

    private function createSuperAdminUser(): User
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
