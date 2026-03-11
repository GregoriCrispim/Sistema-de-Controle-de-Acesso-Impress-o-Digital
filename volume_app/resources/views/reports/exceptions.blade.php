@extends('layouts.app')
@section('title', 'Relatório de Exceções')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center mb-2">
        <h1 class="text-2xl font-bold text-gray-800">Relatório de Exceções (Liberações Manuais)</h1>
        <div class="flex space-x-2">
            <a href="{{ route('reports.exceptions', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'format' => 'csv']) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">CSV</a>
            <a href="{{ route('reports.exceptions', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">PDF</a>
        </div>
    </div>
    <p class="text-gray-500 mb-6">Período: {{ $start_date }} a {{ $end_date }} | Total: {{ $total }}</p>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aluno</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matrícula</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operador</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($meals as $meal)
                <tr class="bg-amber-50">
                    <td class="px-4 py-3 text-sm">{{ $meal->served_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm font-medium">{{ $meal->student->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $meal->student->enrollment_number ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $meal->operator->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $meal->manual_reason ?? 'Não informado' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhuma exceção no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-400 mt-4">Gerado em: {{ $generated_at }}</p>
</div>
@endsection
