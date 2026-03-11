<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Occurrence;
use Illuminate\Http\Request;

class OccurrenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Occurrence::with(['student', 'operator'])->orderByDesc('created_at');

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('student', fn($q) => $q->where('name', 'LIKE', "%{$search}%"));
        }

        if ($request->get('start_date') && $request->get('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        $occurrences = $query->paginate(25)->withQueryString();

        $stats = [
            'total' => Occurrence::count(),
            'biometric_issue' => Occurrence::where('type', 'biometric_issue')->count(),
            'student_behavior' => Occurrence::where('type', 'student_behavior')->count(),
            'general' => Occurrence::where('type', 'general')->count(),
        ];

        return view('management.occurrences', compact('occurrences', 'stats'));
    }
}
