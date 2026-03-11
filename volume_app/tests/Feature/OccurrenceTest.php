<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Occurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccurrenceTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::create([
            'name' => 'Operador',
            'email' => 'op@test.com',
            'password' => bcrypt('123456'),
            'role' => 'operator',
            'active' => true,
        ]);

        $this->student = Student::create([
            'name' => 'Aluno',
            'enrollment_number' => 'OCC001',
            'birth_date' => '2005-01-01',
            'course' => 'Ensino Médio',
            'class_name' => '1º A',
            'active' => true,
        ]);
    }

    public function test_operator_can_register_occurrence(): void
    {
        $response = $this->actingAs($this->operator)->post('/operator/occurrences', [
            'student_id' => $this->student->id,
            'type' => 'biometric_issue',
            'description' => 'Leitor não reconheceu a digital',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('occurrences', [
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'type' => 'biometric_issue',
        ]);
    }

    public function test_occurrence_types_are_validated(): void
    {
        $response = $this->actingAs($this->operator)->post('/operator/occurrences', [
            'student_id' => $this->student->id,
            'type' => 'invalid_type',
            'description' => 'Teste',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_management_can_view_occurrences(): void
    {
        Occurrence::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'type' => 'general',
            'description' => 'Observação de teste',
        ]);

        $management = User::create([
            'name' => 'Gestão',
            'email' => 'gestao@test.com',
            'password' => bcrypt('123456'),
            'role' => 'management',
            'active' => true,
        ]);

        $this->actingAs($management)
            ->get('/management/occurrences')
            ->assertOk()
            ->assertSee('Observação de teste');
    }

    public function test_occurrence_linked_to_student_and_operator(): void
    {
        $occ = Occurrence::create([
            'student_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'type' => 'student_behavior',
            'description' => 'Teste vínculo',
        ]);

        $this->assertEquals($this->student->id, $occ->student->id);
        $this->assertEquals($this->operator->id, $occ->operator->id);
    }
}
