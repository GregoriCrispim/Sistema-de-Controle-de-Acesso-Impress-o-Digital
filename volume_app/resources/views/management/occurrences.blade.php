@extends('layouts.app')
@section('title', 'Ocorrências - Gestão')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Relatório de Ocorrências</h1>
        <a href="{{ route('management.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
            <i class="bi bi-arrow-left mr-1"></i>Voltar ao Dashboard
        </a>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <div class="text-sm text-gray-500">Total</div>
            <div class="text-2xl font-bold text-indigo-600">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <div class="text-sm text-gray-500">Biometria</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['biometric_issue'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <div class="text-sm text-gray-500">Comportamento</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['student_behavior'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <div class="text-sm text-gray-500">Geral</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['general'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Buscar aluno</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tipo</label>
                <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="biometric_issue" {{ request('type') === 'biometric_issue' ? 'selected' : '' }}>Biometria</option>
                    <option value="student_behavior" {{ request('type') === 'student_behavior' ? 'selected' : '' }}>Comportamento</option>
                    <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>Geral</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Data início</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Data fim</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filtrar</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aluno</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operador</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($occurrences as $occ)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $occ->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs
                            {{ $occ->type === 'biometric_issue' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $occ->type === 'student_behavior' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $occ->type === 'general' ? 'bg-blue-100 text-blue-800' : '' }}">
                            {{ $occ->type === 'biometric_issue' ? 'Biometria' : ($occ->type === 'student_behavior' ? 'Comportamento' : 'Geral') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium">{{ $occ->student->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $occ->operator->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($occ->description, 80) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhuma ocorrência encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $occurrences->links() }}</div>
    </div>
</div>
@endsection
