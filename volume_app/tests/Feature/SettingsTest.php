<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456'),
            'role' => 'admin',
            'active' => true,
        ]);

        SystemSetting::set('canteen_start_time', '10:00');
        SystemSetting::set('canteen_end_time', '15:00');
        SystemSetting::set('meal_value', '15.00');
        SystemSetting::set('manual_limit_percent', '30');
    }

    public function test_admin_can_view_settings(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Configurações do Sistema');
    }

    public function test_admin_can_update_settings(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', [
            'canteen_start_time' => '11:00',
            'canteen_end_time' => '14:00',
            'meal_value' => '18.50',
            'manual_limit_percent' => '20',
        ]);

        $this->assertEquals('11:00', SystemSetting::get('canteen_start_time'));
        $this->assertEquals('18.50', SystemSetting::get('meal_value'));
    }

    public function test_settings_update_creates_audit_log(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', [
            'canteen_start_time' => '09:00',
            'canteen_end_time' => '16:00',
            'meal_value' => '20.00',
            'manual_limit_percent' => '25',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'settings_updated',
        ]);
    }

    public function test_system_setting_get_returns_default(): void
    {
        $this->assertEquals('default_val', SystemSetting::get('nonexistent', 'default_val'));
    }

    public function test_system_setting_set_and_get(): void
    {
        SystemSetting::set('test_key', 'test_value');
        $this->assertEquals('test_value', SystemSetting::get('test_key'));
    }

    public function test_school_days_update(): void
    {
        $month = now()->format('Y-m');
        $day1 = now()->startOfMonth()->format('Y-m-d');
        $day2 = now()->startOfMonth()->addDay()->format('Y-m-d');

        $this->actingAs($this->admin)->put('/admin/settings/school-days', [
            'month' => $month,
            'school_days' => [$day1, $day2],
        ]);

        $this->assertDatabaseHas('school_days', [
            'date' => $day1,
            'is_school_day' => true,
        ]);
    }
}
