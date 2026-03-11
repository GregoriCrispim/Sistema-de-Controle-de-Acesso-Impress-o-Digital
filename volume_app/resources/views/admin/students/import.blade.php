@extends('layouts.app')
@section('title', 'Importar Alunos')

@section('content')
<div class="max-w-2xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Importar Alunos via Planilha</h1>

    <div class="bg-white rounded-xl shadow p-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Formato da planilha</h3>
            <p class="text-sm text-blue-700 mb-2">A planilha deve conter as seguintes colunas (cabeçalhos):</p>
            <ul class="text-sm text-blue-700 list-disc pl-5">
                <li><strong>nome</strong> - Nome completo do aluno</li>
                <li><strong>matricula</strong> - Número de matrícula (único)</li>
                <li><strong>data_nascimento</strong> - Data de nascimento (DD/MM/AAAA)</li>
                <li><strong>curso</strong> - "Ensino Médio" ou "PROEJA"</li>
                <li><strong>turma</strong> - Turma do aluno (ex: 3º A)</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo CSV ou Excel *</label>
                <input type="file" name="file" accept=".csv,.xlsx,.xls" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Importar</button>
                <a href="{{ route('admin.students.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
