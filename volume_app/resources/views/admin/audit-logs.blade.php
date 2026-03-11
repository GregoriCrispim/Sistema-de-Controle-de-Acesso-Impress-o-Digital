@extends('layouts.app')
@section('title', 'Logs de Auditoria')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Logs de Auditoria</h1>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ação</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detalhes</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->user->name ?? 'Sistema' }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium">{{ $log->action }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">
                        @if(is_array($log->details) && count($log->details))
                            <ul class="list-none space-y-0.5">
                                @foreach($log->details as $key => $value)
                                    <li>
                                        <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                        <span>{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhum log encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
