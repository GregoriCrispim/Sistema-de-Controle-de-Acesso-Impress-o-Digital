<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Student;
use App\Models\Occurrence;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $todayCount = Meal::today()->count();
        $totalActive = Student::where('active', true)->count();

        $courseStats = Student::where('active', true)
            ->selectRaw('course, COUNT(*) as total')
            ->groupBy('course')
            ->pluck('total', 'course');

        $classStats = Meal::today()
            ->join('students', 'meals.student_id', '=', 'students.id')
            ->selectRaw('students.class_name, COUNT(*) as total')
            ->groupBy('students.class_name')
            ->orderByDesc('total')
            ->pluck('total', 'class_name');

        $monthlyData = Meal::selectRaw('DATE(served_at) as day, COUNT(*) as total')
            ->whereMonth('served_at', now()->month)
            ->whereYear('served_at', now()->year)
            ->groupByRaw('DATE(served_at)')
            ->orderByRaw('DATE(served_at)')
            ->pluck('total', 'day');

        $recentOccurrences = Occurrence::with(['student', 'operator'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('management.dashboard', compact(
            'todayCount', 'totalActive', 'courseStats', 'classStats', 'monthlyData', 'recentOccurrences'
        ));
    }
}
