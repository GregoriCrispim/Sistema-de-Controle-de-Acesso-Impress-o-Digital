@extends('layouts.app')
@section('title', 'Relatório por Operador')

@section('content')
<div class="max-w-5xl mx-auto px-4">
    <div class="flex justify-between items-center mb-2">
        <h1 class="text-2xl font-bold text-gray-800">Relatório por Operador</h1>
        <div class="flex space-x-2">
            <a href="{{ route('reports.by-operator', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Exportar PDF</a>
            <a href="{{ route('reports.by-operator', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'format' => 'csv']) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Exportar CSV</a>
        </div>
    </div>
    <p class="text-gray-500 mb-6">Período: {{ $start_date }} a {{ $end_date }}</p>

    <div class="space-y-4">
        @forelse($operator_stats as $stat)
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800">{{ $stat['operator']->name ?? 'N/A' }}</h3>
            <div class="grid grid-cols-3 gap-4 mt-3">
                <div class="text-center"><span class="text-2xl font-bold text-indigo-600">{{ $stat['total'] }}</span><p class="text-xs text-gray-500">Total</p></div>
                <div class="text-center"><span class="text-2xl font-bold text-blue-600">{{ $stat['biometric'] }}</span><p class="text-xs text-gray-500">Biometria</p></div>
                <div class="text-center"><span class="text-2xl font-bold text-amber-600">{{ $stat['manual'] }}</span><p class="text-xs text-gray-500">Manual</p></div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">Nenhum registro no período.</div>
        @endforelse
    </div>
    <p class="text-xs text-gray-400 mt-4">Gerado em: {{ $generated_at }}</p>
</div>
@endsection
