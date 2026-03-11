<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\SchoolDay;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'canteen_start_time' => SystemSetting::get('canteen_start_time', '10:00'),
            'canteen_end_time' => SystemSetting::get('canteen_end_time', '15:00'),
            'meal_value' => SystemSetting::get('meal_value', '15.00'),
            'manual_limit_percent' => SystemSetting::get('manual_limit_percent', '30'),
        ];

        $currentMonth = request('school_month', now()->format('Y-m'));
        $monthStart = Carbon::parse($currentMonth . '-01');
        $monthEnd = $monthStart->copy()->endOfMonth();

        $schoolDays = SchoolDay::whereBetween('date', [$monthStart, $monthEnd])
            ->pluck('is_school_day', 'date')
            ->mapWithKeys(fn($v, $k) => [Carbon::parse($k)->format('Y-m-d') => $v]);

        $calendarDays = [];
        $period = CarbonPeriod::create($monthStart, $monthEnd);
        foreach ($period as $day) {
            $key = $day->format('Y-m-d');
            $calendarDays[] = [
                'date' => $key,
                'day' => $day->day,
                'weekday' => $day->dayOfWeek,
                'is_school_day' => $schoolDays[$key] ?? ($day->isWeekday()),
            ];
        }

        return view('admin.settings', compact('settings', 'calendarDays', 'currentMonth'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'canteen_start_time' => 'required|date_format:H:i',
            'canteen_end_time' => 'required|date_format:H:i|after:canteen_start_time',
            'meal_value' => 'required|numeric|min:0.01',
            'manual_limit_percent' => 'required|numeric|min:1|max:100',
        ]);

        $keys = ['canteen_start_time', 'canteen_end_time', 'meal_value', 'manual_limit_percent'];
        $changes = [];

        foreach ($keys as $key) {
            $old = SystemSetting::get($key);
            $new = $request->input($key);
            if ($old !== $new) {
                $changes[$key] = ['old' => $old, 'new' => $new];
                SystemSetting::set($key, $new);
            }
        }

        if (!empty($changes)) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'settings_updated',
                'details' => $changes,
                'ip_address' => $request->ip(),
            ]);
        }

        return back()->with('success', 'Configurações atualizadas com sucesso.');
    }

    public function updateSchoolDays(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'school_days' => 'nullable|array',
            'school_days.*' => 'date',
        ]);

        $monthStart = Carbon::parse($request->month . '-01');
        $monthEnd = $monthStart->copy()->endOfMonth();
        $selectedDays = collect($request->school_days ?? []);

        $period = CarbonPeriod::create($monthStart, $monthEnd);
        foreach ($period as $day) {
            $key = $day->format('Y-m-d');
            SchoolDay::updateOrCreate(
                ['date' => $key],
                ['is_school_day' => $selectedDays->contains($key)]
            );
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'school_days_updated',
            'details' => ['month' => $request->month, 'count' => $selectedDays->count()],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.settings', ['school_month' => $request->month])
            ->with('success', 'Dias letivos atualizados com sucesso.');
    }
}
