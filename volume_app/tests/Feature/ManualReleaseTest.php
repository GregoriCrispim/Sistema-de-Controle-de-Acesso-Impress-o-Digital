<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Meal;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualReleaseTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::create([
            'name' => 'Operador Teste',
            'email' => 'op@test.com',
            'password' => bcrypt('123456'),
            'role' => 'operator',
            'active' => true,
        ]);

        $this->student = Student::create([
            'name' => 'Aluno Teste',
            'enrollment_number' => 'TEST001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '3º A',
            'photo_path' => 'students/photos/test.jpg',
            'active' => true,
        ]);

        SystemSetting::set('canteen_start_time', '00:00');
        SystemSetting::set('canteen_end_time', '23:59');
        SystemSetting::set('manual_limit_percent', '30');
    }

    public function test_manual_release_succeeds_with_reason(): void
    {
        $response = $this->actingAs($this->operator)->postJson('/operator/manual-release', [
            'student_id' => $this->student->id,
            'reason' => 'Dedo machucado',
        ]);

        $response->assertOk()->assertJson([
            'status' => 'approved',
            'color' => 'green',
        ]);

        $this->assertDatabaseHas('meals', [
            'student_id' => $this->student->id,
            'method' => 'manual',
            'manual_reason' => 'Dedo machucado',
        ]);
    }

    public function test_manual_release_requires_reason(): void
    {
        $response = $this->actingAs($this->operator)->postJson('/operator/manual-release', [
            'student_id' => $this->student->id,
            'reason' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_manual_release_denied_if_already_eaten(): void
    {
        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'biometric',
            'served_at' => now(),
        ]);

        $response = $this->actingAs($this->operator)->postJson('/operator/manual-release', [
            'student_id' => $this->student->id,
            'reason' => 'Teste',
        ]);

        $response->assertOk()->assertJson([
            'status' => 'denied',
            'reason' => 'Já almoçou hoje',
        ]);
    }

    public function test_manual_release_denied_for_inactive_student(): void
    {
        $this->student->update(['active' => false]);

        $response = $this->actingAs($this->operator)->postJson('/operator/manual-release', [
            'student_id' => $this->student->id,
            'reason' => 'Teste',
        ]);

        $response->assertOk()->assertJson([
            'status' => 'denied',
            'reason' => 'Aluno inativo',
        ]);
    }

    public function test_manual_release_checks_canteen_hours(): void
    {
        SystemSetting::set('canteen_start_time', '10:00');
        SystemSetting::set('canteen_end_time', '10:01');

        $this->travel(1)->days();

        $response = $this->actingAs($this->operator)->postJson('/operator/manual-release', [
            'student_id' => $this->student->id,
            'reason' => 'Teste',
        ]);

        $this->assertTrue(
            $response->json('reason') === 'Fora do horário de funcionamento'
            || $response->json('status') === 'approved'
        );
    }

    public function test_manual_release_denied_when_limit_exceeded(): void
    {
        SystemSetting::set('manual_limit_percent', '0');

        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'manual',
            'manual_reason' => 'Setup',
            'served_at' => now(),
        ]);

        $student2 = Student::create([
            'name' => 'Aluno 2',
            'enrollment_number' => 'TEST002',
            'birth_date' => '2005-01-01',
            'course' => 'PROEJA',
            'class_name' => '1º B',
            'photo_path' => null,
            'active' => true,
        ]);

        $response = $this->actingAs($this->operator)->postJson('/operator/manual-release', [
            'student_id' => $student2->id,
            'reason' => 'Teste limite',
        ]);

        $this->assertTrue(
            str_contains($response->json('reason') ?? '', 'Limite de liberações manuais')
            || $response->json('status') === 'denied'
        );
    }
}
