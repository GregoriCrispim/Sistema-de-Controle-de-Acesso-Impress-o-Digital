<?php

namespace App\Http\Controllers\Fiscal;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Student;
use App\Models\FiscalValidation;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $todayCount = Meal::today()->count();
        $totalActive = Student::where('active', true)->count();
        $validations = FiscalValidation::orderByDesc('validated_at')->paginate(10);

        return view('fiscal.dashboard', compact('todayCount', 'totalActive', 'validations'));
    }

    public function validatePeriod(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $start = Carbon::parse($request->period_start)->startOfDay();
        $end = Carbon::parse($request->period_end)->endOfDay();

        $existing = FiscalValidation::where('period_start', $start->toDateString())
            ->where('period_end', $end->toDateString())
            ->exists();

        if ($existing) {
            return back()->withErrors(['period' => 'Este período já foi validado.']);
        }

        $totalMeals = Meal::period($start, $end)->count();
        $biometricCount = Meal::period($start, $end)->biometric()->count();
        $manualCount = Meal::period($start, $end)->manual()->count();
        $mealValue = (float) SystemSetting::get('meal_value', '15.00');

        $validation = FiscalValidation::create([
            'fiscal_id' => auth()->id(),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'total_meals' => $totalMeals,
            'meal_value' => $mealValue,
            'total_value' => $totalMeals * $mealValue,
            'biometric_count' => $biometricCount,
            'manual_count' => $manualCount,
            'protocol_number' => 'VAL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'validated_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'period_validated',
            'details' => [
                'validation_id' => $validation->id,
                'protocol' => $validation->protocol_number,
                'period' => "{$request->period_start} a {$request->period_end}",
                'total_meals' => $totalMeals,
                'total_value' => $validation->total_value,
            ],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Período validado com sucesso. Protocolo: {$validation->protocol_number}");
    }

    public function previewPeriod(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $start = Carbon::parse($request->period_start)->startOfDay();
        $end = Carbon::parse($request->period_end)->endOfDay();

        $totalMeals = Meal::period($start, $end)->count();
        $biometricCount = Meal::period($start, $end)->biometric()->count();
        $manualCount = Meal::period($start, $end)->manual()->count();
        $mealValue = (float) SystemSetting::get('meal_value', '15.00');

        $dailyBreakdown = Meal::period($start, $end)
            ->selectRaw('DATE(served_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(served_at)')
            ->orderByRaw('DATE(served_at)')
            ->get();

        return response()->json([
            'total_meals' => $totalMeals,
            'biometric_count' => $biometricCount,
            'manual_count' => $manualCount,
            'biometric_percent' => $totalMeals > 0 ? round(($biometricCount / $totalMeals) * 100, 1) : 0,
            'manual_percent' => $totalMeals > 0 ? round(($manualCount / $totalMeals) * 100, 1) : 0,
            'meal_value' => $mealValue,
            'total_value' => number_format($totalMeals * $mealValue, 2, ',', '.'),
            'daily_breakdown' => $dailyBreakdown,
        ]);
    }

    public function showValidation(FiscalValidation $validation)
    {
        return view('fiscal.validation-detail', compact('validation'));
    }
}
