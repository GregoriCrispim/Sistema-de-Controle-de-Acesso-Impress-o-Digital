@extends('layouts.app')
@section('title', 'Relatório Diário')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Relatório Diário - {{ $date }}</h1>
        <div class="flex space-x-2">
            <form method="GET" class="flex items-center space-x-2">
                <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm hover:bg-gray-700">Buscar</button>
            </form>
            <a href="{{ route('reports.daily', ['date' => request('date', today()->toDateString()), 'format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Exportar PDF</a>
            <a href="{{ route('reports.daily', ['date' => request('date', today()->toDateString()), 'format' => 'csv']) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Exportar CSV</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <div class="text-sm text-gray-500">Total</div>
            <div class="text-3xl font-bold text-indigo-600">{{ $total }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <div class="text-sm text-gray-500">Biometria</div>
            <div class="text-3xl font-bold text-blue-600">{{ $biometric }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <div class="text-sm text-gray-500">Manual</div>
            <div class="text-3xl font-bold text-amber-600">{{ $manual }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horário</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aluno</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matrícula</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operador</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo Manual</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($meals as $meal)
                <tr class="{{ $meal->method === 'manual' ? 'bg-amber-50' : '' }}">
                    <td class="px-4 py-3 text-sm">{{ $meal->served_at->format('H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm font-medium">{{ $meal->student->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $meal->student->enrollment_number ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($meal->method === 'biometric')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">Biometria</span>
                        @else
                            <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">Manual</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">{{ $meal->operator->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $meal->manual_reason ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhum registro para esta data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-400 mt-4">Gerado em: {{ $generated_at }}</p>
</div>
@endsection
