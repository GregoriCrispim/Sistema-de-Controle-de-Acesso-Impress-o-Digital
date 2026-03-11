<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Student([
            'name' => $row['nome'],
            'enrollment_number' => $row['matricula'],
            'birth_date' => $this->parseDate($row['data_nascimento'] ?? null),
            'course' => $row['curso'],
            'class_name' => $row['turma'],
            'active' => true,
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'matricula' => 'required|string|unique:students,enrollment_number',
            'curso' => 'required|in:Ensino Médio,PROEJA',
            'turma' => 'required|string|max:50',
        ];
    }

    protected function parseDate($value)
    {
        if (!$value) return null;

        try {
            if (is_numeric($value)) {
                return \Carbon\Carbon::createFromTimestamp(($value - 25569) * 86400)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
