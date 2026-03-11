<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $todayCount = Meal::today()->count();
        $totalActive = Student::where('active', true)->count();
        $biometricCount = Meal::today()->biometric()->count();
        $manualCount = Meal::today()->manual()->count();

        $yesterdayCount = Meal::whereDate('served_at', Carbon::yesterday())->count();
        $lastWeekCount = Meal::whereDate('served_at', now()->subWeek())->count();

        $hourlyData = Meal::today()
            ->selectRaw('HOUR(served_at) as hour, COUNT(*) as count')
            ->groupByRaw('HOUR(served_at)')
            ->orderByRaw('HOUR(served_at)')
            ->pluck('count', 'hour')
            ->toArray();

        return view('company.dashboard', compact(
            'todayCount', 'totalActive', 'biometricCount', 'manualCount',
            'yesterdayCount', 'lastWeekCount', 'hourlyData'
        ));
    }

    public function apiRealtime()
    {
        return response()->json([
            'today_count' => Meal::today()->count(),
            'biometric_count' => Meal::today()->biometric()->count(),
            'manual_count' => Meal::today()->manual()->count(),
            'total_active' => Student::where('active', true)->count(),
            'hourly' => Meal::today()
                ->selectRaw('HOUR(served_at) as hour, COUNT(*) as count')
                ->groupByRaw('HOUR(served_at)')
                ->orderByRaw('HOUR(served_at)')
                ->pluck('count', 'hour'),
        ]);
    }
}
