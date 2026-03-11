@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Painel Administrativo</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-sm font-medium text-gray-500 uppercase">Total de Usuários</div>
            <div class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['total_users'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-sm font-medium text-gray-500 uppercase">Alunos Ativos</div>
            <div class="text-3xl font-bold text-green-600 mt-2">{{ $stats['active_students'] }}</div>
            <div class="text-sm text-gray-400">de {{ $stats['total_students'] }} total</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-sm font-medium text-gray-500 uppercase">Almoços Hoje</div>
            <div class="text-3xl font-bold text-amber-600 mt-2">{{ $stats['today_meals'] }}</div>
            <div class="text-sm text-gray-400">Bio: {{ $stats['today_biometric'] }} | Manual: {{ $stats['today_manual'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('admin.users.index') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex items-center space-x-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center text-2xl text-indigo-600"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="font-semibold text-gray-800">Gerenciar Usuários</div>
                <div class="text-sm text-gray-500">Criar, editar e desativar</div>
            </div>
        </a>
        <a href="{{ route('admin.students.index') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex items-center space-x-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-2xl text-green-600"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <div class="font-semibold text-gray-800">Gerenciar Alunos</div>
                <div class="text-sm text-gray-500">Cadastro, fotos, digitais</div>
            </div>
        </a>
        <a href="{{ route('admin.students.import.form') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex items-center space-x-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-2xl text-blue-600"><i class="bi bi-cloud-upload-fill"></i></div>
            <div>
                <div class="font-semibold text-gray-800">Importar Alunos</div>
                <div class="text-sm text-gray-500">Via planilha CSV/Excel</div>
            </div>
        </a>
        <a href="{{ route('admin.settings') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex items-center space-x-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-2xl text-purple-600"><i class="bi bi-gear-fill"></i></div>
            <div>
                <div class="font-semibold text-gray-800">Configurações</div>
                <div class="text-sm text-gray-500">Horários, valores, limites</div>
            </div>
        </a>
        <a href="{{ route('admin.audit.logs') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex items-center space-x-4">
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-2xl text-gray-600"><i class="bi bi-journal-text"></i></div>
            <div>
                <div class="font-semibold text-gray-800">Logs de Auditoria</div>
                <div class="text-sm text-gray-500">Todas as ações do sistema</div>
            </div>
        </a>
        <a href="{{ route('reports.index') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition flex items-center space-x-4">
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-2xl text-amber-600"><i class="bi bi-bar-chart-line-fill"></i></div>
            <div>
                <div class="font-semibold text-gray-800">Relatórios</div>
                <div class="text-sm text-gray-500">Diário, mensal, por aluno</div>
            </div>
        </a>
    </div>
</div>
@endsection
