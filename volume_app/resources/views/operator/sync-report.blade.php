@extends('layouts.app')
@section('title', 'Relatório de Sincronização')

@section('content')
<div class="max-w-5xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><i class="bi bi-arrow-repeat mr-2"></i>Relatório de Sincronização Offline</h1>
        <a href="{{ route('operator.terminal') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
            <i class="bi bi-arrow-left mr-1"></i>Voltar ao Terminal
        </a>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <p class="text-sm text-blue-700"><i class="bi bi-info-circle mr-1"></i>Este relatório mostra todas as sincronizações de registros offline com o servidor. A coluna "Sincronizados" indica quantos registros foram salvos com sucesso, e "Conflitos" indica registros que já existiam no servidor.</p>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operador</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sincronizados</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Conflitos</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($syncLogs as $log)
                <tr>
                    <td class="px-4 py-3 text-sm">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm font-medium">{{ $log->user->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-right">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{{ $log->details['synced'] ?? 0 }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-right">
                        @if(($log->details['conflicts'] ?? 0) > 0)
                            <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold">{{ $log->details['conflicts'] }}</span>
                        @else
                            <span class="text-gray-400">0</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhuma sincronização registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $syncLogs->links() }}</div>
    </div>
</div>
@endsection
