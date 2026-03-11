@extends('layouts.app')
@section('title', 'Relatórios')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Relatórios</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('reports.daily') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="text-2xl mb-2 text-indigo-600"><i class="bi bi-clipboard-data"></i></div>
            <h3 class="font-semibold text-gray-800">Relatório Diário</h3>
            <p class="text-sm text-gray-500 mt-1">Alunos que almoçaram, horários, operadores</p>
        </a>
        <a href="{{ route('reports.monthly') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="text-2xl mb-2 text-indigo-600"><i class="bi bi-calendar-month"></i></div>
            <h3 class="font-semibold text-gray-800">Relatório Mensal</h3>
            <p class="text-sm text-gray-500 mt-1">Consolidado por dia, total do mês</p>
        </a>
        <div class="bg-white rounded-xl shadow p-6" x-data="{ studentId: '' }">
            <div class="text-2xl mb-2 text-green-600"><i class="bi bi-mortarboard-fill"></i></div>
            <h3 class="font-semibold text-gray-800 mb-3">Relatório por Estudante</h3>
            <form method="GET" action="{{ route('reports.by-student') }}" class="flex space-x-2">
                <input type="number" name="student_id" required placeholder="ID do Aluno" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Ver</button>
            </form>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-2xl mb-2 text-blue-600"><i class="bi bi-person-badge"></i></div>
            <h3 class="font-semibold text-gray-800 mb-3">Relatório por Operador</h3>
            <form method="GET" action="{{ route('reports.by-operator') }}" class="space-y-2">
                <div class="flex space-x-2">
                    <input type="date" name="start_date" required class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="date" name="end_date" required class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Gerar</button>
            </form>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-2xl mb-2 text-amber-600"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <h3 class="font-semibold text-gray-800 mb-3">Relatório de Exceções</h3>
            <form method="GET" action="{{ route('reports.exceptions') }}" class="space-y-2">
                <div class="flex space-x-2">
                    <input type="date" name="start_date" required class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <input type="date" name="end_date" required class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm">Gerar</button>
            </form>
        </div>
        <a href="{{ route('reports.payment') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
            <div class="text-2xl mb-2 text-green-600"><i class="bi bi-cash-coin"></i></div>
            <h3 class="font-semibold text-gray-800">Relatório para Pagamento</h3>
            <p class="text-sm text-gray-500 mt-1">Períodos validados com valores</p>
        </a>
    </div>
</div>
@endsection
