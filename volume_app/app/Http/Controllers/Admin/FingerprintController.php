<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fingerprint;
use App\Models\Student;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class FingerprintController extends Controller
{
    public function store(Request $request, Student $student)
    {
        $request->validate([
            'template_code' => 'required|string|unique:fingerprints,template_code',
            'finger_index' => 'required|integer|between:1,10',
        ]);

        if ($student->fingerprints()->count() >= 3) {
            return back()->withErrors(['finger' => 'Máximo de 3 digitais por aluno.']);
        }

        $student->fingerprints()->create([
            'template_code' => $request->template_code,
            'finger_index' => $request->finger_index,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'fingerprint_registered',
            'details' => ['student_id' => $student->id, 'finger_index' => $request->finger_index],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Digital cadastrada com sucesso.');
    }

    public function destroy(Student $student, Fingerprint $fingerprint)
    {
        $fingerprint->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'fingerprint_deleted',
            'details' => ['student_id' => $student->id],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Digital removida com sucesso.');
    }
}
