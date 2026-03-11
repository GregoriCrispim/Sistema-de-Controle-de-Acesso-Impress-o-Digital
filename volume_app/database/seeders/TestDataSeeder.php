<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Fingerprint;
use App\Models\Meal;
use App\Models\Occurrence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Criando diretório de fotos...');
        Storage::disk('public')->makeDirectory('students/photos');

        $students = [
            ['name' => 'João Pedro Silva', 'enrollment_number' => '2026001', 'birth_date' => '2008-03-15', 'course' => 'Ensino Médio', 'class_name' => '1º A', 'gender' => 'male'],
            ['name' => 'Maria Eduarda Santos', 'enrollment_number' => '2026002', 'birth_date' => '2007-07-22', 'course' => 'Ensino Médio', 'class_name' => '2º A', 'gender' => 'female'],
            ['name' => 'Lucas Gabriel Oliveira', 'enrollment_number' => '2026003', 'birth_date' => '2008-01-10', 'course' => 'Ensino Médio', 'class_name' => '1º B', 'gender' => 'male'],
            ['name' => 'Ana Beatriz Ferreira', 'enrollment_number' => '2026004', 'birth_date' => '2007-11-05', 'course' => 'Ensino Médio', 'class_name' => '2º B', 'gender' => 'female'],
            ['name' => 'Pedro Henrique Costa', 'enrollment_number' => '2026005', 'birth_date' => '2008-05-18', 'course' => 'Ensino Médio', 'class_name' => '1º A', 'gender' => 'male'],
            ['name' => 'Isabela Cristina Lima', 'enrollment_number' => '2026006', 'birth_date' => '2006-09-30', 'course' => 'PROEJA', 'class_name' => '1º P', 'gender' => 'female'],
            ['name' => 'Gabriel Souza Martins', 'enrollment_number' => '2026007', 'birth_date' => '2008-02-14', 'course' => 'Ensino Médio', 'class_name' => '3º A', 'gender' => 'male'],
            ['name' => 'Larissa Mendes Rocha', 'enrollment_number' => '2026008', 'birth_date' => '2007-06-25', 'course' => 'Ensino Médio', 'class_name' => '3º A', 'gender' => 'female'],
            ['name' => 'Matheus Almeida Ribeiro', 'enrollment_number' => '2026009', 'birth_date' => '2005-12-08', 'course' => 'PROEJA', 'class_name' => '2º P', 'gender' => 'male'],
            ['name' => 'Camila Rodrigues Araújo', 'enrollment_number' => '2026010', 'birth_date' => '2008-04-02', 'course' => 'Ensino Médio', 'class_name' => '1º B', 'gender' => 'female'],
            ['name' => 'Rafael Torres Barbosa', 'enrollment_number' => '2026011', 'birth_date' => '2007-08-19', 'course' => 'Ensino Médio', 'class_name' => '2º A', 'gender' => 'male'],
            ['name' => 'Juliana Pereira Nunes', 'enrollment_number' => '2026012', 'birth_date' => '2006-10-11', 'course' => 'PROEJA', 'class_name' => '1º P', 'gender' => 'female'],
            ['name' => 'Felipe Cardoso Dias', 'enrollment_number' => '2026013', 'birth_date' => '2008-07-07', 'course' => 'Ensino Médio', 'class_name' => '1º A', 'gender' => 'male'],
            ['name' => 'Beatriz Monteiro Gomes', 'enrollment_number' => '2026014', 'birth_date' => '2007-03-29', 'course' => 'Ensino Médio', 'class_name' => '3º B', 'gender' => 'female'],
            ['name' => 'Thiago Nascimento Pinto', 'enrollment_number' => '2026015', 'birth_date' => '2005-11-16', 'course' => 'PROEJA', 'class_name' => '2º P', 'gender' => 'male'],
            ['name' => 'Valentina Castro Moreira', 'enrollment_number' => '2026016', 'birth_date' => '2008-09-03', 'course' => 'Ensino Médio', 'class_name' => '1º B', 'gender' => 'female'],
            ['name' => 'Enzo Miguel Correia', 'enrollment_number' => '2026017', 'birth_date' => '2007-01-27', 'course' => 'Ensino Médio', 'class_name' => '2º B', 'gender' => 'male'],
            ['name' => 'Sofia Helena Teixeira', 'enrollment_number' => '2026018', 'birth_date' => '2008-06-12', 'course' => 'Ensino Médio', 'class_name' => '1º A', 'gender' => 'female'],
            ['name' => 'Arthur Vieira Campos', 'enrollment_number' => '2026019', 'birth_date' => '2006-04-20', 'course' => 'PROEJA', 'class_name' => '1º P', 'gender' => 'male'],
            ['name' => 'Manuela Freitas Azevedo', 'enrollment_number' => '2026020', 'birth_date' => '2007-12-01', 'course' => 'Ensino Médio', 'class_name' => '3º A', 'gender' => 'female'],
        ];

        $this->command->info('Baixando fotos e criando alunos...');
        $bar = $this->command->getOutput()->createProgressBar(count($students));

        foreach ($students as $index => $data) {
            $photoPath = $this->downloadPhoto($data['gender'], $index);

            $student = Student::create([
                'name' => $data['name'],
                'enrollment_number' => $data['enrollment_number'],
                'birth_date' => $data['birth_date'],
                'course' => $data['course'],
                'class_name' => $data['class_name'],
                'photo_path' => $photoPath,
                'active' => true,
            ]);

            $fingerprintCode = 'FP-' . $data['enrollment_number'] . '-D1';
            Fingerprint::create([
                'student_id' => $student->id,
                'template_code' => $fingerprintCode,
                'finger_index' => 1,
            ]);

            if ($index < 10) {
                $secondCode = 'FP-' . $data['enrollment_number'] . '-D2';
                Fingerprint::create([
                    'student_id' => $student->id,
                    'template_code' => $secondCode,
                    'finger_index' => 2,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();

        $this->command->info('Gerando refeições de teste dos últimos 30 dias...');
        $this->generateMealHistory();

        $this->command->info('Gerando ocorrências de teste...');
        $this->generateOccurrences();

        $this->command->info('Seeder de teste concluído!');
    }

    protected function downloadPhoto(string $gender, int $index): ?string
    {
        try {
            $seed = $gender . '_student_' . $index;
            $url = "https://api.dicebear.com/9.x/avataaars/jpg?seed={$seed}&size=256&backgroundColor=b6e3f4,c0aede,d1d4f9,ffd5dc,ffdfbf";

            $response = Http::timeout(15)->get($url);

            if ($response->successful()) {
                $filename = 'students/photos/student_' . ($index + 1) . '.jpg';
                Storage::disk('public')->put($filename, $response->body());
                return $filename;
            }
        } catch (\Exception $e) {
            $this->command->warn("  Falha ao baixar foto #{$index}: {$e->getMessage()}");
        }

        return null;
    }

    protected function generateMealHistory(): void
    {
        $students = Student::all();
        $operators = User::where('role', 'operator')->get();

        if ($operators->isEmpty()) {
            $operators = User::where('role', 'admin')->get();
        }

        for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);

            if ($date->isWeekend()) continue;

            $mealCount = rand((int) ($students->count() * 0.6), $students->count());
            $selectedStudents = $students->random(min($mealCount, $students->count()));

            foreach ($selectedStudents as $student) {
                $hour = rand(10, 14);
                $minute = rand(0, 59);
                $servedAt = $date->copy()->setTime($hour, $minute, rand(0, 59));

                $isBiometric = rand(1, 100) <= 95;

                Meal::create([
                    'student_id' => $student->id,
                    'operator_id' => $operators->random()->id,
                    'method' => $isBiometric ? 'biometric' : 'manual',
                    'manual_reason' => $isBiometric ? null : $this->randomManualReason(),
                    'served_at' => $servedAt,
                    'synced' => true,
                ]);
            }
        }
    }

    protected function generateOccurrences(): void
    {
        $students = Student::all();
        $operators = User::where('role', 'operator')->get();

        if ($operators->isEmpty()) {
            $operators = User::where('role', 'admin')->get();
        }

        $occurrences = [
            ['type' => 'biometric_issue', 'description' => 'Leitor não reconheceu a digital do aluno após 3 tentativas.'],
            ['type' => 'biometric_issue', 'description' => 'Aluno com dedo machucado, não conseguiu usar biometria.'],
            ['type' => 'biometric_issue', 'description' => 'Leitor biométrico com leitura lenta, demorou mais que o normal.'],
            ['type' => 'student_behavior', 'description' => 'Aluno tentou utilizar o cartão de outro colega.'],
            ['type' => 'student_behavior', 'description' => 'Aluno foi orientado sobre a fila e organização na cantina.'],
            ['type' => 'general', 'description' => 'Equipamento do leitor biométrico reiniciado no início do turno.'],
            ['type' => 'general', 'description' => 'Falta de energia por 10 minutos. Liberações feitas manualmente.'],
            ['type' => 'general', 'description' => 'Teste de conectividade com o servidor realizado com sucesso.'],
        ];

        foreach ($occurrences as $occ) {
            Occurrence::create([
                'student_id' => $students->random()->id,
                'operator_id' => $operators->random()->id,
                'type' => $occ['type'],
                'description' => $occ['description'],
            ]);
        }
    }

    protected function randomManualReason(): string
    {
        $reasons = [
            'Leitor biométrico fora de serviço',
            'Aluno com dedo machucado',
            'Digital não reconhecida após múltiplas tentativas',
            'Novo aluno, digital ainda não cadastrada',
            'Problemas técnicos com o leitor',
        ];

        return $reasons[array_rand($reasons)];
    }
}
