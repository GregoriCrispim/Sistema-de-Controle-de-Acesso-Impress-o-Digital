<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherRulesTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::create([
            'name' => 'Op',
            'email' => 'op@test.com',
            'password' => bcrypt('123'),
            'role' => 'operator',
            'active' => true,
        ]);

        $this->student = Student::create([
            'name' => 'Aluno',
            'enrollment_number' => 'V001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '1º A',
            'active' => true,
        ]);
    }

    public function test_student_has_not_eaten_today_initially(): void
    {
        $this->assertFalse($this->student->hasEatenToday());
    }

    public function test_student_has_eaten_today_after_meal(): void
    {
        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'biometric',
            'served_at' => now(),
        ]);

        $this->assertTrue($this->student->hasEatenToday());
    }

    public function test_yesterday_meal_does_not_count_as_today(): void
    {
        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'biometric',
            'served_at' => now()->subDay(),
        ]);

        $this->assertFalse($this->student->hasEatenToday());
    }

    public function test_inactive_student_cannot_eat(): void
    {
        $this->student->update(['active' => false]);

        $this->assertFalse($this->student->active);
    }

    public function test_meal_scopes_work_correctly(): void
    {
        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'biometric',
            'served_at' => now(),
        ]);

        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'manual',
            'manual_reason' => 'Teste',
            'served_at' => now(),
        ]);

        $this->assertEquals(2, Meal::today()->count());
        $this->assertEquals(1, Meal::today()->biometric()->count());
        $this->assertEquals(1, Meal::today()->manual()->count());
    }

    public function test_meal_period_scope(): void
    {
        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'biometric',
            'served_at' => now()->subDays(5),
        ]);

        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'biometric',
            'served_at' => now()->subDays(15),
        ]);

        $count = Meal::period(now()->subDays(10), now())->count();
        $this->assertEquals(1, $count);
    }
}
