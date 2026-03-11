<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Fingerprint;
use App\Models\Meal;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TerminalController extends Controller
{
    public function index()
    {
        $todayCount = Meal::today()->count();
        $totalActive = Student::where('active', true)->count();
        return view('operator.terminal', compact('todayCount', 'totalActive'));
    }

    public function biometricCheck(Request $request)
    {
        $request->validate(['fingerprint_code' => 'required|string']);

        $fingerprint = Fingerprint::findByTemplate($request->fingerprint_code);

        if (!$fingerprint) {
            return response()->json([
                'status' => 'denied',
                'reason' => 'Digital não cadastrada',
                'color' => 'red',
            ]);
        }

        $student = $fingerprint->student;

        if (!$student->active) {
            return response()->json([
                'status' => 'denied',
                'reason' => 'Aluno inativo',
                'color' => 'red',
            ]);
        }

        if ($student->hasEatenToday()) {
            return response()->json([
                'status' => 'denied',
                'reason' => 'Já almoçou hoje',
                'color' => 'red',
                'student' => [
                    'name' => $student->name,
                    'enrollment_number' => $student->enrollment_number,
                    'photo_url' => $student->photo_path ? asset('storage/' . $student->photo_path) : null,
                ],
            ]);
        }

        $startTime = SystemSetting::get('canteen_start_time', '10:00');
        $endTime = SystemSetting::get('canteen_end_time', '15:00');
        $now = now();

        if ($now->format('H:i') < $startTime || $now->format('H:i') > $endTime) {
            return response()->json([
                'status' => 'denied',
                'reason' => 'Fora do horário de funcionamento',
                'color' => 'red',
            ]);
        }

        $meal = Meal::create([
            'student_id' => $student->id,
            'operator_id' => auth()->id(),
            'method' => 'biometric',
            'served_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'meal_released',
            'details' => [
                'student_id' => $student->id,
                'method' => 'biometric',
                'meal_id' => $meal->id,
            ],
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'approved',
            'color' => 'green',
            'student' => [
                'name' => $student->name,
                'enrollment_number' => $student->enrollment_number,
                'course' => $student->course,
                'class_name' => $student->class_name,
                'photo_url' => $student->photo_path ? asset('storage/' . $student->photo_path) : null,
            ],
            'today_count' => Meal::today()->count(),
        ]);
    }

    public function searchStudent(Request $request)
    {
        $query = $request->get('q');

        $students = Student::where('active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('enrollment_number', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'enrollment_number', 'course', 'class_name', 'photo_path']);

        return response()->json($students->map(function ($s) {
            $s->photo_url = $s->photo_path ? asset('storage/' . $s->photo_path) : null;
            return $s;
        }));
    }

    public function syncReport()
    {
        $syncLogs = AuditLog::where('action', 'offline_sync')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('operator.sync-report', compact('syncLogs'));
    }

    public function manualRelease(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'reason' => 'required|string|max:500',
        ]);

        $student = Student::findOrFail($request->student_id);

        if (!$student->active) {
            return response()->json(['status' => 'denied', 'reason' => 'Aluno inativo', 'color' => 'red']);
        }

        if ($student->hasEatenToday()) {
            return response()->json(['status' => 'denied', 'reason' => 'Já almoçou hoje', 'color' => 'red']);
        }

        $startTime = SystemSetting::get('canteen_start_time', '10:00');
        $endTime = SystemSetting::get('canteen_end_time', '15:00');
        $now = now();

        if ($now->format('H:i') < $startTime || $now->format('H:i') > $endTime) {
            return response()->json([
                'status' => 'denied',
                'reason' => 'Fora do horário de funcionamento',
                'color' => 'red',
            ]);
        }

        $todayTotal = Meal::today()->count();
        $todayManual = Meal::today()->manual()->count();
        $manualLimit = (float) SystemSetting::get('manual_limit_percent', '30');

        if ($todayTotal > 0 && ($todayManual / $todayTotal) * 100 >= $manualLimit) {
            return response()->json([
                'status' => 'denied',
                'reason' => "Limite de liberações manuais atingido ({$manualLimit}%)",
                'color' => 'red',
            ]);
        }

        $meal = Meal::create([
            'student_id' => $student->id,
            'operator_id' => auth()->id(),
            'method' => 'manual',
            'manual_reason' => $request->reason,
            'served_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'meal_released',
            'details' => [
                'student_id' => $student->id,
                'method' => 'manual',
                'reason' => $request->reason,
                'meal_id' => $meal->id,
            ],
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'approved',
            'color' => 'green',
            'student' => [
                'name' => $student->name,
                'enrollment_number' => $student->enrollment_number,
                'course' => $student->course,
                'class_name' => $student->class_name,
                'photo_url' => $student->photo_path ? asset('storage/' . $student->photo_path) : null,
            ],
            'today_count' => Meal::today()->count(),
        ]);
    }
}
