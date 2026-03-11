@extends('layouts.app')
@section('title', 'Ocorrências')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Registro de Ocorrências</h1>
        <a href="{{ route('operator.terminal') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Voltar ao Terminal</a>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Nova Ocorrência</h2>
        <form method="POST" action="{{ route('operator.occurrences.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Aluno (opcional)</label>
                    <select name="student_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">-- Nenhum aluno --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="biometric_issue">Problema com biometria</option>
                        <option value="student_behavior">Comportamento do aluno</option>
                        <option value="general">Observação geral</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                <textarea name="description" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Descreva a ocorrência..."></textarea>
            </div>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Registrar</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aluno</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($occurrences as $occurrence)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $occurrence->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($occurrence->type === 'biometric_issue')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Biometria</span>
                        @elseif($occurrence->type === 'student_behavior')
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Comportamento</span>
                        @else
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">Geral</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $occurrence->student->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($occurrence->description, 60) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Nenhuma ocorrência registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $occurrences->links() }}</div>
    </div>
</div>
@endsection
