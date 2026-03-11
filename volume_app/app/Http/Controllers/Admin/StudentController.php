<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('enrollment_number', 'LIKE', "%{$search}%");
            });
        }

        if ($course = $request->get('course')) {
            $query->where('course', $course);
        }

        if ($request->get('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->get('status') === 'inactive') {
            $query->where('active', false);
        }

        $students = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'enrollment_number' => 'required|string|unique:students',
            'birth_date' => 'required|date',
            'course' => 'required|in:Ensino Médio,PROEJA',
            'class_name' => 'required|string|max:50',
            'photo' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('students/photos', 'public');
        }
        unset($validated['photo']);

        $student = Student::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'student_created',
            'details' => ['student_id' => $student->id],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Aluno cadastrado com sucesso.');
    }

    public function edit(Student $student)
    {
        $student->load('fingerprints');
        return view('admin.students.form', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'enrollment_number' => 'required|string|unique:students,enrollment_number,' . $student->id,
            'birth_date' => 'required|date',
            'course' => 'required|in:Ensino Médio,PROEJA',
            'class_name' => 'required|string|max:50',
            'photo' => 'nullable|image|max:2048',
            'active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('students/photos', 'public');
        }
        unset($validated['photo']);
        $validated['active'] = $request->boolean('active');

        $student->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'student_updated',
            'details' => ['student_id' => $student->id],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Aluno atualizado com sucesso.');
    }

    public function deactivate(Student $student)
    {
        $student->update(['active' => false]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'student_deactivated',
            'details' => ['student_id' => $student->id],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Aluno desativado com sucesso.');
    }

    public function anonymize(Student $student)
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->fingerprints()->delete();

        $student->update([
            'name' => 'Aluno Anonimizado #' . $student->id,
            'enrollment_number' => 'ANON-' . $student->id,
            'birth_date' => '2000-01-01',
            'photo_path' => null,
            'active' => false,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'student_anonymized_lgpd',
            'details' => ['student_id' => $student->id],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Dados do aluno anonimizados conforme LGPD.');
    }

    public function importForm()
    {
        return view('admin.students.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:10240',
        ]);

        try {
            Excel::import(new StudentsImport, $request->file('file'));
            return redirect()->route('admin.students.index')->with('success', 'Alunos importados com sucesso.');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Erro ao importar: ' . $e->getMessage()]);
        }
    }
}
