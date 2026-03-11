@extends('layouts.app')
@section('title', 'Relatório para Pagamento')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Relatório para Pagamento - Períodos Validados</h1>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Protocolo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Almoços</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor Unit.</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fiscal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Validado em</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($validations as $v)
                <tr>
                    <td class="px-4 py-3 text-sm font-mono text-indigo-600">{{ $v->protocol_number }}</td>
                    <td class="px-4 py-3 text-sm">{{ $v->period_start->format('d/m/Y') }} - {{ $v->period_end->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-right">{{ $v->total_meals }}</td>
                    <td class="px-4 py-3 text-sm text-right">R$ {{ number_format($v->meal_value, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-right font-bold text-green-700">R$ {{ number_format($v->total_value, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm">{{ $v->fiscal->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm">{{ $v->validated_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Nenhum período validado.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $validations->links() }}</div>
    </div>
    <p class="text-xs text-gray-400 mt-4">Gerado em: {{ $generated_at }}</p>
</div>
@endsection
