<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Student;
use App\Models\FiscalValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function daily(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $meals = Meal::with(['student', 'operator'])
            ->whereDate('served_at', $date)
            ->orderBy('served_at')
            ->get();

        $data = [
            'title' => 'Relatório Diário',
            'date' => Carbon::parse($date)->format('d/m/Y'),
            'meals' => $meals,
            'total' => $meals->count(),
            'biometric' => $meals->where('method', 'biometric')->count(),
            'manual' => $meals->where('method', 'manual')->count(),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.daily-pdf', $data);
            return $pdf->download("relatorio-diario-{$date}.pdf");
        }

        if ($request->get('format') === 'csv') {
            return $this->exportCsv($meals, "relatorio-diario-{$date}.csv");
        }

        return view('reports.daily', $data);
    }

    public function monthly(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $meals = Meal::with(['student', 'operator'])
            ->whereMonth('served_at', $month)
            ->whereYear('served_at', $year)
            ->orderBy('served_at')
            ->get();

        $dailyStats = $meals->groupBy(fn($m) => $m->served_at->format('Y-m-d'))
            ->map(fn($dayMeals) => [
                'total' => $dayMeals->count(),
                'biometric' => $dayMeals->where('method', 'biometric')->count(),
                'manual' => $dayMeals->where('method', 'manual')->count(),
            ]);

        $data = [
            'title' => 'Relatório Mensal',
            'month' => $month,
            'year' => $year,
            'month_name' => Carbon::create($year, $month)->translatedFormat('F Y'),
            'daily_stats' => $dailyStats,
            'total' => $meals->count(),
            'biometric' => $meals->where('method', 'biometric')->count(),
            'manual' => $meals->where('method', 'manual')->count(),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.monthly-pdf', $data);
            return $pdf->download("relatorio-mensal-{$year}-{$month}.pdf");
        }

        if ($request->get('format') === 'csv') {
            return $this->exportMonthlyCsv($dailyStats, "relatorio-mensal-{$year}-{$month}.csv");
        }

        return view('reports.monthly', $data);
    }

    public function byStudent(Request $request)
    {
        $studentId = $request->get('student_id');
        $student = Student::findOrFail($studentId);
        $meals = $student->meals()
            ->with('operator')
            ->orderByDesc('served_at')
            ->paginate(30);

        $data = [
            'title' => 'Relatório por Estudante',
            'student' => $student,
            'meals' => $meals,
            'total' => $student->meals()->count(),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        if ($request->get('format') === 'pdf') {
            $allMeals = $student->meals()->with('operator')->orderByDesc('served_at')->get();
            $data['meals'] = $allMeals;
            $pdf = Pdf::loadView('reports.student-pdf', $data);
            return $pdf->download("relatorio-aluno-{$student->enrollment_number}.pdf");
        }

        if ($request->get('format') === 'csv') {
            $allMeals = $student->meals()->with('operator')->orderByDesc('served_at')->get();
            return $this->exportCsv($allMeals, "relatorio-aluno-{$student->enrollment_number}.csv");
        }

        return view('reports.by-student', $data);
    }

    public function byOperator(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $operatorStats = Meal::period($start, $end)
            ->with('operator')
            ->get()
            ->groupBy('operator_id')
            ->map(function ($meals) {
                $operator = $meals->first()->operator;
                return [
                    'operator' => $operator,
                    'total' => $meals->count(),
                    'biometric' => $meals->where('method', 'biometric')->count(),
                    'manual' => $meals->where('method', 'manual')->count(),
                ];
            });

        $data = [
            'title' => 'Relatório por Operador',
            'start_date' => $start->format('d/m/Y'),
            'end_date' => $end->format('d/m/Y'),
            'operator_stats' => $operatorStats,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.operator-pdf', $data);
            return $pdf->download("relatorio-operadores.pdf");
        }

        if ($request->get('format') === 'csv') {
            return $this->exportOperatorCsv($operatorStats, $start, $end, "relatorio-operadores.csv");
        }

        return view('reports.by-operator', $data);
    }

    public function exceptions(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $manualMeals = Meal::period($start, $end)
            ->manual()
            ->with(['student', 'operator'])
            ->orderBy('served_at')
            ->get();

        $data = [
            'title' => 'Relatório de Exceções (Liberações Manuais)',
            'start_date' => $start->format('d/m/Y'),
            'end_date' => $end->format('d/m/Y'),
            'meals' => $manualMeals,
            'total' => $manualMeals->count(),
            'generated_at' => now()->format('d/m/Y H:i'),
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.exceptions-pdf', $data);
            return $pdf->download("relatorio-excecoes.pdf");
        }

        if ($request->get('format') === 'csv') {
            return $this->exportCsv($manualMeals, "relatorio-excecoes.csv");
        }

        return view('reports.exceptions', $data);
    }

    public function payment(Request $request)
    {
        $query = FiscalValidation::with('fiscal')
            ->orderByDesc('validated_at');

        $format = $request->get('format');

        if (in_array($format, ['pdf', 'csv'], true)) {
            $validations = (clone $query)->get();
            $data = [
                'title' => 'Relatório para Pagamento',
                'validations' => $validations,
                'generated_at' => now()->format('d/m/Y H:i'),
                'total_periods' => $validations->count(),
                'total_meals' => $validations->sum('total_meals'),
                'total_amount' => $validations->sum(fn ($validation) => (float) $validation->total_value),
            ];

            if ($format === 'pdf') {
                $pdf = Pdf::loadView('reports.payment-pdf', $data);
                return $pdf->download('relatorio-pagamentos.pdf');
            }

            return $this->exportPaymentCsv($validations, 'relatorio-pagamentos.csv');
        }

        $validations = $query->paginate(20);

        return view('reports.payment', [
            'title' => 'Relatório para Pagamento',
            'validations' => $validations,
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);
    }

    protected function exportCsv($meals, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($meals) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Data/Hora', 'Aluno', 'Matrícula', 'Método', 'Operador', 'Motivo Manual'], ';');

            foreach ($meals as $meal) {
                fputcsv($file, [
                    $meal->served_at->format('d/m/Y H:i'),
                    $meal->student->name ?? 'N/A',
                    $meal->student->enrollment_number ?? 'N/A',
                    $meal->method === 'biometric' ? 'Biometria' : 'Manual',
                    $meal->operator->name ?? 'N/A',
                    $meal->manual_reason ?? '',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportMonthlyCsv($dailyStats, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($dailyStats) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Data', 'Total', 'Biometria', 'Manual'], ';');

            foreach ($dailyStats as $date => $stats) {
                fputcsv($file, [
                    Carbon::parse($date)->format('d/m/Y'),
                    $stats['total'],
                    $stats['biometric'],
                    $stats['manual'],
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportOperatorCsv($operatorStats, $start, $end, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($operatorStats, $start, $end) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ["Período: {$start->format('d/m/Y')} a {$end->format('d/m/Y')}"], ';');
            fputcsv($file, ['Operador', 'Total', 'Biometria', 'Manual'], ';');

            foreach ($operatorStats as $stat) {
                fputcsv($file, [
                    $stat['operator']->name ?? 'N/A',
                    $stat['total'],
                    $stat['biometric'],
                    $stat['manual'],
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportPaymentCsv($validations, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($validations) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Protocolo', 'Período', 'Almoços', 'Valor Unitário', 'Valor Total', 'Fiscal', 'Validado em'], ';');

            foreach ($validations as $validation) {
                fputcsv($file, [
                    $validation->protocol_number,
                    $validation->period_start->format('d/m/Y') . ' - ' . $validation->period_end->format('d/m/Y'),
                    $validation->total_meals,
                    number_format((float) $validation->meal_value, 2, ',', '.'),
                    number_format((float) $validation->total_value, 2, ',', '.'),
                    $validation->fiscal->name ?? 'N/A',
                    $validation->validated_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
