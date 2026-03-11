<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Meal;
use App\Models\FiscalValidation;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiscalValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $fiscal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fiscal = User::create([
            'name' => 'Fiscal Teste',
            'email' => 'fiscal@test.com',
            'password' => bcrypt('123456'),
            'role' => 'fiscal',
            'active' => true,
        ]);

        SystemSetting::set('meal_value', '15.00');

        $student = Student::create([
            'name' => 'Aluno',
            'enrollment_number' => 'T001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '1º A',
            'active' => true,
        ]);

        $operator = User::create([
            'name' => 'Op',
            'email' => 'op@test.com',
            'password' => bcrypt('123456'),
            'role' => 'operator',
            'active' => true,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            Meal::create([
                'student_id' => $student->id,
                'operator_id' => $operator->id,
                'method' => $i <= 4 ? 'biometric' : 'manual',
                'manual_reason' => $i > 4 ? 'Test' : null,
                'served_at' => now()->startOfMonth()->addDays($i - 1)->setHour(12),
            ]);
        }
    }

    public function test_fiscal_can_access_dashboard(): void
    {
        $this->actingAs($this->fiscal)
            ->get('/fiscal/dashboard')
            ->assertOk();
    }

    public function test_fiscal_can_validate_period(): void
    {
        $response = $this->actingAs($this->fiscal)->post('/fiscal/validate-period', [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('fiscal_validations', [
            'fiscal_id' => $this->fiscal->id,
            'total_meals' => 5,
        ]);
    }

    public function test_duplicate_period_validation_is_blocked(): void
    {
        FiscalValidation::create([
            'fiscal_id' => $this->fiscal->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'total_meals' => 5,
            'meal_value' => 15.00,
            'total_value' => 75.00,
            'biometric_count' => 4,
            'manual_count' => 1,
            'protocol_number' => 'VAL-TEST-001',
            'validated_at' => now(),
        ]);

        $response = $this->actingAs($this->fiscal)->post('/fiscal/validate-period', [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('period');
    }

    public function test_validation_creates_protocol_number(): void
    {
        $this->actingAs($this->fiscal)->post('/fiscal/validate-period', [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ]);

        $validation = FiscalValidation::first();
        $this->assertNotNull($validation);
        $this->assertStringStartsWith('VAL-', $validation->protocol_number);
    }

    public function test_validation_calculates_correct_value(): void
    {
        $this->actingAs($this->fiscal)->post('/fiscal/validate-period', [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ]);

        $validation = FiscalValidation::first();
        $this->assertEquals(5, $validation->total_meals);
        $this->assertEquals(75.00, (float) $validation->total_value);
    }
}
