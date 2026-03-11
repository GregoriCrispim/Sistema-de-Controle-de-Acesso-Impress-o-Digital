<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Occurrence;
use App\Models\Student;
use Illuminate\Http\Request;

class OccurrenceController extends Controller
{
    public function index()
    {
        $occurrences = Occurrence::where('operator_id', auth()->id())
            ->with('student')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('operator.occurrences.index', compact('occurrences'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'type' => 'required|in:biometric_issue,student_behavior,general',
            'description' => 'required|string|max:1000',
        ]);

        Occurrence::create([
            'student_id' => $request->student_id,
            'operator_id' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Ocorrência registrada com sucesso.');
    }
}
