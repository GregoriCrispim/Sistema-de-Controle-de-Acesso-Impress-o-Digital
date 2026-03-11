@extends('layouts.app')
@section('title', 'Protocolo de Validação')

@section('content')
<div class="max-w-3xl mx-auto px-4">
    <div class="bg-white rounded-xl shadow p-8">
        <div class="text-center border-b pb-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Protocolo de Validação Fiscal</h1>
            <p class="text-lg font-mono text-indigo-600 mt-2">{{ $validation->protocol_number }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div><span class="text-sm text-gray-500">Período:</span><p class="font-medium">{{ $validation->period_start->format('d/m/Y') }} a {{ $validation->period_end->format('d/m/Y') }}</p></div>
            <div><span class="text-sm text-gray-500">Data da Validação:</span><p class="font-medium">{{ $validation->validated_at->format('d/m/Y H:i') }}</p></div>
            <div><span class="text-sm text-gray-500">Total de Almoços:</span><p class="text-2xl font-bold text-indigo-700">{{ $validation->total_meals }}</p></div>
            <div><span class="text-sm text-gray-500">Valor Total:</span><p class="text-2xl font-bold text-green-700">R$ {{ number_format($validation->total_value, 2, ',', '.') }}</p></div>
            <div><span class="text-sm text-gray-500">Valor por Refeição:</span><p class="font-medium">R$ {{ number_format($validation->meal_value, 2, ',', '.') }}</p></div>
            <div><span class="text-sm text-gray-500">Fiscal Responsável:</span><p class="font-medium">{{ $validation->fiscal->name }} ({{ $validation->fiscal->email }})</p></div>
            <div><span class="text-sm text-gray-500">Liberações por Biometria:</span><p class="font-medium">{{ $validation->biometric_count }}</p></div>
            <div><span class="text-sm text-gray-500">Liberações Manuais:</span><p class="font-medium">{{ $validation->manual_count }}</p></div>
        </div>

        <div class="bg-green-50 border border-green-300 rounded-lg p-4 text-center">
            <p class="text-green-800 font-semibold">Período validado e aprovado para pagamento</p>
        </div>
    </div>
</div>
@endsection
