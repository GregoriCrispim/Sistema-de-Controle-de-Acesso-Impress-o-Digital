@extends('layouts.app')
@section('title', 'Gerenciar Alunos')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gerenciar Alunos</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.students.import.form') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Importar CSV</a>
            <a href="{{ route('admin.students.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Novo Aluno</a>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Nome ou matrícula...">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Curso</label>
            <select name="course" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Todos</option>
                <option value="Ensino Médio" {{ request('course') === 'Ensino Médio' ? 'selected' : '' }}>Ensino Médio</option>
                <option value="PROEJA" {{ request('course') === 'PROEJA' ? 'selected' : '' }}>PROEJA</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Todos</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativos</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativos</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Filtrar</button>
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matrícula</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Curso</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Turma</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($students as $student)
                <tr>
                    <td class="px-4 py-3">
                        @if($student->photo_path)
                            <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400"><i class="bi bi-person-fill"></i></div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $student->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $student->enrollment_number }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $student->course }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $student->class_name }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($student->active)
                            <span class="text-green-600 font-medium">Ativo</span>
                        @else
                            <span class="text-red-600 font-medium">Inativo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm space-x-2">
                        <a href="{{ route('admin.students.edit', $student) }}" class="text-indigo-600 hover:text-indigo-800">Editar</a>
                        @if($student->active)
                            <form method="POST" action="{{ route('admin.students.deactivate', $student) }}" class="inline" onsubmit="return confirm('Desativar este aluno?')">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800">Desativar</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Nenhum aluno encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $students->links() }}</div>
    </div>
</div>
@endsection
