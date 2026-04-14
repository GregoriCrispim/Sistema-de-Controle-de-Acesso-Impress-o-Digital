<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Meal;
use App\Models\FiscalValidation;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

        $student = Student::create([
            'name' => 'Aluno Report',
            'enrollment_number' => 'RPT001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '3º A',
            'active' => true,
        ]);

        Meal::create([
            'student_id' => $student->id,
            'operator_id' => $this->admin->id,
            'method' => 'biometric',
            'served_at' => now()->setHour(12),
        ]);

        FiscalValidation::create([
            'fiscal_id' => $this->admin->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'total_meals' => 10,
            'meal_value' => 15.00,
            'total_value' => 150.00,
            'biometric_count' => 9,
            'manual_count' => 1,
            'protocol_number' => 'VAL-REPORT-001',
            'validated_at' => now(),
        ]);
    }

    public function test_reports_index_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports')
            ->assertOk();
    }

    public function test_daily_report_screen(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports/daily?date=' . today()->toDateString())
            ->assertOk()
            ->assertSee('Relatório Diário');
    }

    public function test_daily_report_csv_export(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/reports/daily?date=' . today()->toDateString() . '&format=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_monthly_report_screen(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports/monthly?month=' . now()->month . '&year=' . now()->year)
            ->assertOk()
            ->assertSee('Relatório Mensal');
    }

    public function test_monthly_report_csv_export(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/reports/monthly?month=' . now()->month . '&year=' . now()->year . '&format=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_by_student_report(): void
    {
        $student = Student::first();

        $this->actingAs($this->admin)
            ->get("/reports/by-student?student_id={$student->id}")
            ->assertOk();
    }

    public function test_by_student_csv(): void
    {
        $student = Student::first();

        $response = $this->actingAs($this->admin)
            ->get("/reports/by-student?student_id={$student->id}&format=csv");

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_by_operator_report(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports/by-operator?start_date=' . now()->startOfMonth()->toDateString() . '&end_date=' . now()->endOfMonth()->toDateString())
            ->assertOk();
    }

    public function test_exceptions_report(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports/exceptions?start_date=' . now()->startOfMonth()->toDateString() . '&end_date=' . now()->endOfMonth()->toDateString())
            ->assertOk();
    }

    public function test_exceptions_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/reports/exceptions?start_date=' . now()->startOfMonth()->toDateString() . '&end_date=' . now()->endOfMonth()->toDateString() . '&format=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_payment_report(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports/payment')
            ->assertOk();
    }

    public function test_payment_report_csv_export(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/reports/payment?format=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
