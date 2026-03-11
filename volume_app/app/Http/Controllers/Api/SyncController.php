<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Student;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function syncMeals(Request $request)
    {
        $request->validate([
            'meals' => 'required|array',
            'meals.*.student_id' => 'required|exists:students,id',
            'meals.*.method' => 'required|in:biometric,manual',
            'meals.*.manual_reason' => 'nullable|string',
            'meals.*.served_at' => 'required|date',
        ]);

        $synced = 0;
        $conflicts = [];

        foreach ($request->meals as $mealData) {
            $servedAt = Carbon::parse($mealData['served_at']);

            $exists = Meal::where('student_id', $mealData['student_id'])
                ->whereDate('served_at', $servedAt->toDateString())
                ->exists();

            if ($exists) {
                $conflicts[] = [
                    'student_id' => $mealData['student_id'],
                    'served_at' => $mealData['served_at'],
                    'reason' => 'Registro já existente para este dia',
                ];
                continue;
            }

            Meal::create([
                'student_id' => $mealData['student_id'],
                'operator_id' => auth()->id(),
                'method' => $mealData['method'],
                'manual_reason' => $mealData['manual_reason'] ?? null,
                'served_at' => $servedAt,
                'synced' => true,
            ]);
            $synced++;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'offline_sync',
            'details' => [
                'synced' => $synced,
                'conflicts' => count($conflicts),
            ],
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'synced' => $synced,
            'conflicts' => $conflicts,
        ]);
    }

    public function getStudents()
    {
        $students = Student::where('active', true)
            ->with('fingerprints:id,student_id,template_code')
            ->get(['id', 'name', 'enrollment_number', 'course', 'class_name', 'photo_path', 'active']);

        return response()->json($students);
    }

    public function syncLogs()
    {
        $logs = AuditLog::where('action', 'offline_sync')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'user' => $log->user->name ?? 'N/A',
                'synced' => $log->details['synced'] ?? 0,
                'conflicts' => $log->details['conflicts'] ?? 0,
                'timestamp' => $log->created_at->format('d/m/Y H:i:s'),
            ]);

        return response()->json($logs);
    }
}
