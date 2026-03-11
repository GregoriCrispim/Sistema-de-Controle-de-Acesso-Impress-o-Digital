@extends('layouts.app')
@section('title', 'Relatório Mensal')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Relatório Mensal - {{ $month_name }}</h1>
        <div class="flex space-x-2">
            <form method="GET" class="flex items-center space-x-2">
                <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $i)->translatedFormat('F') }}</option>
                    @endfor
                </select>
                <input type="number" name="year" value="{{ $year }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-24">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm hover:bg-gray-700">Buscar</button>
            </form>
            <a href="{{ route('reports.monthly', ['month' => $month, 'year' => $year, 'format' => 'csv']) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">CSV</a>
            <a href="{{ route('reports.monthly', ['month' => $month, 'year' => $year, 'format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">PDF</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <div class="text-sm text-gray-500">Total do Mês</div>
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Biometria</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Manual</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($daily_stats as $date => $stats)
                <tr>
                    <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($date)->format('d/m/Y (l)') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium">{{ $stats['total'] }}</td>
                    <td class="px-4 py-3 text-sm text-right text-blue-600">{{ $stats['biometric'] }}</td>
                    <td class="px-4 py-3 text-sm text-right text-amber-600">{{ $stats['manual'] }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Nenhum registro para este mês.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-400 mt-4">Gerado em: {{ $generated_at }}</p>
</div>
@endsection
