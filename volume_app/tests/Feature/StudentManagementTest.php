<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Fingerprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentManagementTest extends TestCase
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
    }

    public function test_admin_can_create_student(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)->post('/admin/students', [
            'name' => 'Novo Aluno',
            'enrollment_number' => 'NEW001',
            'birth_date' => '2005-06-15',
            'course' => 'Ensino Médio',
            'class_name' => '2º B',
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseHas('students', [
            'name' => 'Novo Aluno',
            'enrollment_number' => 'NEW001',
        ]);
    }

    public function test_enrollment_number_must_be_unique(): void
    {
        Storage::fake('public');

        Student::create([
            'name' => 'Existente',
            'enrollment_number' => 'DUP001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '1º A',
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/students', [
            'name' => 'Duplicado',
            'enrollment_number' => 'DUP001',
            'birth_date' => '2005-06-15',
            'course' => 'Ensino Médio',
            'class_name' => '1º A',
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        $response->assertSessionHasErrors('enrollment_number');
    }

    public function test_admin_can_deactivate_student(): void
    {
        $student = Student::create([
            'name' => 'Aluno',
            'enrollment_number' => 'S001',
            'birth_date' => '2005-01-01',
            'course' => 'PROEJA',
            'class_name' => '1º A',
            'active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/students/{$student->id}/deactivate");
        $response->assertRedirect();

        $student->refresh();
        $this->assertFalse($student->active);
    }

    public function test_student_uses_soft_deletes(): void
    {
        $student = Student::create([
            'name' => 'Aluno Soft',
            'enrollment_number' => 'SOFT001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '2º A',
            'active' => true,
        ]);

        $student->delete();

        $this->assertSoftDeleted('students', ['id' => $student->id]);
        $this->assertNotNull(Student::withTrashed()->find($student->id));
    }

    public function test_max_three_fingerprints(): void
    {
        $student = Student::create([
            'name' => 'Aluno FP',
            'enrollment_number' => 'FP001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '3º A',
            'active' => true,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $this->actingAs($this->admin)->post("/admin/students/{$student->id}/fingerprints", [
                'template_code' => "FP_CODE_{$i}",
                'finger_index' => $i,
            ]);
        }

        $this->assertEquals(3, $student->fingerprints()->count());

        $response = $this->actingAs($this->admin)->post("/admin/students/{$student->id}/fingerprints", [
            'template_code' => 'FP_CODE_4',
            'finger_index' => 4,
        ]);

        $this->assertEquals(3, $student->fingerprints()->count());
    }

    public function test_lgpd_anonymization(): void
    {
        $student = Student::create([
            'name' => 'João Silva',
            'enrollment_number' => 'LGPD001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '1º A',
            'active' => true,
        ]);

        Fingerprint::create([
            'student_id' => $student->id,
            'template_code' => 'FP_LGPD',
            'finger_index' => 1,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/students/{$student->id}/anonymize");
        $response->assertRedirect();

        $student->refresh();
        $this->assertStringContains('Anonimizado', $student->name);
        $this->assertStringStartsWith('ANON-', $student->enrollment_number);
        $this->assertFalse($student->active);
        $this->assertEquals(0, $student->fingerprints()->count());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student_anonymized_lgpd',
        ]);
    }

    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(str_contains($haystack, $needle), "String '{$haystack}' does not contain '{$needle}'");
    }
}
