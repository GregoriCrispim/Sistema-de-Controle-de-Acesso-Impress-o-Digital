<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Fingerprint;
use App\Models\Meal;
use App\Models\SystemSetting;
use App\Services\FingerprintTemplateGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiometricReleaseTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Student $student;
    private string $registeredTemplate;

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

        $this->registeredTemplate = FingerprintTemplateGenerator::generate('test_student_001', 30);

        Fingerprint::create([
            'student_id' => $this->student->id,
            'template_code' => $this->registeredTemplate,
            'finger_index' => 1,
        ]);

        SystemSetting::set('canteen_start_time', '00:00');
        SystemSetting::set('canteen_end_time', '23:59');
    }

    public function test_valid_fingerprint_releases_meal(): void
    {
        $capturedTemplate = FingerprintTemplateGenerator::generateVariant('test_student_001', 1, 30);

        $response = $this->actingAs($this->operator)->postJson('/operator/biometric-check', [
            'fingerprint_code' => $capturedTemplate,
        ]);

        $response->assertOk()->assertJson([
            'status' => 'approved',
            'color' => 'green',
        ]);

        $this->assertDatabaseHas('meals', [
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'biometric',
        ]);
    }

    public function test_exact_same_fingerprint_releases_meal(): void
    {
        $response = $this->actingAs($this->operator)->postJson('/operator/biometric-check', [
            'fingerprint_code' => $this->registeredTemplate,
        ]);

        $response->assertOk()->assertJson([
            'status' => 'approved',
            'color' => 'green',
        ]);
    }

    public function test_unknown_fingerprint_is_denied(): void
    {
        $unknownTemplate = FingerprintTemplateGenerator::generate('unknown_person', 30);

        $response = $this->actingAs($this->operator)->postJson('/operator/biometric-check', [
            'fingerprint_code' => $unknownTemplate,
        ]);

        $response->assertOk()->assertJson([
            'status' => 'denied',
            'reason' => 'Digital não cadastrada',
        ]);
    }

    public function test_invalid_hex_is_denied(): void
    {
        $response = $this->actingAs($this->operator)->postJson('/operator/biometric-check', [
            'fingerprint_code' => 'NOT_A_VALID_TEMPLATE',
        ]);

        $response->assertOk()->assertJson([
            'status' => 'denied',
            'reason' => 'Digital não cadastrada',
        ]);
    }

    public function test_inactive_student_is_denied(): void
    {
        $this->student->update(['active' => false]);

        $response = $this->actingAs($this->operator)->postJson('/operator/biometric-check', [
            'fingerprint_code' => $this->registeredTemplate,
        ]);

        $response->assertOk()->assertJson([
            'status' => 'denied',
        ]);
    }

    public function test_duplicate_meal_same_day_is_denied(): void
    {
        Meal::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'method' => 'biometric',
            'served_at' => now(),
        ]);

        $response = $this->actingAs($this->operator)->postJson('/operator/biometric-check', [
            'fingerprint_code' => $this->registeredTemplate,
        ]);

        $response->assertOk()->assertJson([
            'status' => 'denied',
            'reason' => 'Já almoçou hoje',
        ]);
    }

    public function test_meal_creates_audit_log(): void
    {
        $this->actingAs($this->operator)->postJson('/operator/biometric-check', [
            'fingerprint_code' => $this->registeredTemplate,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->operator->id,
            'action' => 'meal_released',
        ]);
    }

    public function test_matches_correct_student_among_multiple(): void
    {
        $otherStudent = Student::create([
            'name' => 'Outro Aluno',
            'enrollment_number' => 'TEST002',
            'birth_date' => '2005-06-15',
            'course' => 'Ensino Médio',
            'class_name' => '2º B',
            'photo_path' => null,
            'active' => true,
        ]);

        Fingerprint::create([
            'student_id' => $otherStudent->id,
            'template_code' => FingerprintTemplateGenerator::generate('other_student_002', 30),
            'finger_index' => 1,
        ]);

        $captured = FingerprintTemplateGenerator::generateVariant('test_student_001', 2, 30);

        $response = $this->actingAs($this->operator)->postJson('/operator/biometric-check', [
            'fingerprint_code' => $captured,
        ]);

        $response->assertOk()->assertJson([
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('meals', [
            'student_id' => $this->student->id,
            'method' => 'biometric',
        ]);

        $this->assertDatabaseMissing('meals', [
            'student_id' => $otherStudent->id,
        ]);
    }
}
