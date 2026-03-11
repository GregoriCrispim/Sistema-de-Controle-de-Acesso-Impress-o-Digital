@extends('layouts.app')
@section('title', 'Dashboard Gestão')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard - Gestão / Coordenação</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <div class="text-sm font-medium text-gray-500 uppercase">Almoços Hoje</div>
            <div class="text-4xl font-bold text-indigo-600 mt-2">{{ $todayCount }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <div class="text-sm font-medium text-gray-500 uppercase">Alunos Ativos</div>
            <div class="text-4xl font-bold text-green-600 mt-2">{{ $totalActive }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <div class="text-sm font-medium text-gray-500 uppercase">Comparecimento</div>
            <div class="text-4xl font-bold text-amber-600 mt-2">{{ $totalActive > 0 ? round(($todayCount / $totalActive) * 100, 1) : 0 }}%</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Almoços Hoje por Turma</h3>
            @forelse($classStats as $class => $count)
                <div class="flex justify-between items-center py-2 border-b last:border-0">
                    <span class="text-gray-600">{{ $class }}</span>
                    <span class="font-bold text-indigo-600">{{ $count }}</span>
                </div>
            @empty
                <p class="text-gray-400">Nenhum dado disponível.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Alunos Ativos por Curso</h3>
            @forelse($courseStats as $course => $count)
                <div class="flex justify-between items-center py-2 border-b last:border-0">
                    <span class="text-gray-600">{{ $course }}</span>
                    <span class="font-bold text-green-600">{{ $count }}</span>
                </div>
            @empty
                <p class="text-gray-400">Nenhum dado disponível.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h3 class="font-semibold text-gray-700 mb-4">Tendência Mensal ({{ now()->translatedFormat('F Y') }})</h3>
        <canvas id="monthlyChart" height="150"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-gray-700">Ocorrências Recentes</h3>
            <a href="{{ route('management.occurrences') }}" class="text-sm text-indigo-600 hover:text-indigo-800"><i class="bi bi-list-ul mr-1"></i>Ver todas</a>
        </div>
        <div class="space-y-3">
            @forelse($recentOccurrences as $occ)
                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs px-2 py-1 rounded-full
                                {{ $occ->type === 'biometric_issue' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $occ->type === 'student_behavior' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $occ->type === 'general' ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ $occ->type === 'biometric_issue' ? 'Biometria' : ($occ->type === 'student_behavior' ? 'Comportamento' : 'Geral') }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $occ->created_at->format('d/m H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-700 mt-1">{{ $occ->description }}</p>
                        <p class="text-xs text-gray-400">Aluno: {{ $occ->student->name ?? 'N/A' }} | Operador: {{ $occ->operator->name ?? 'N/A' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-400">Nenhuma ocorrência recente.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyData);
    const labels = Object.keys(monthlyData).map(d => {
        const parts = d.split('-');
        return parts[2] + '/' + parts[1];
    });
    const values = Object.values(monthlyData);

    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Almoços por Dia',
                data: values,
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endpush
@endsection
