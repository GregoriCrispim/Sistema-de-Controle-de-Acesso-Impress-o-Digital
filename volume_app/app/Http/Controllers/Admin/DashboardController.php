<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Meal;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'active_students' => Student::where('active', true)->count(),
            'today_meals' => Meal::today()->count(),
            'today_biometric' => Meal::today()->biometric()->count(),
            'today_manual' => Meal::today()->manual()->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }
}
