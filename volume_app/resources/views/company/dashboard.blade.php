@extends('layouts.app')
@section('title', 'Dashboard Empresa')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="companyDash()" x-init="startPolling()">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard - Empresa</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <div class="text-sm font-medium text-gray-500 uppercase">Almoços Hoje</div>
            <div class="text-4xl font-bold text-indigo-600 mt-2" x-text="todayCount">{{ $todayCount }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <div class="text-sm font-medium text-gray-500 uppercase">Comparecimento</div>
            <div class="text-4xl font-bold text-green-600 mt-2" x-text="percent + '%'">{{ $totalActive > 0 ? round(($todayCount / $totalActive) * 100, 1) : 0 }}%</div>
            <div class="text-xs text-gray-400" x-text="todayCount + ' de ' + totalActive + ' ativos'">{{ $todayCount }} de {{ $totalActive }} ativos</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <div class="text-sm font-medium text-gray-500 uppercase">Biometria</div>
            <div class="text-4xl font-bold text-blue-600 mt-2" x-text="bioCount">{{ $biometricCount }}</div>
            <div class="text-xs text-gray-400" x-text="bioPercent + '% do total'">&nbsp;</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <div class="text-sm font-medium text-gray-500 uppercase">Manual</div>
            <div class="text-4xl font-bold text-amber-600 mt-2" x-text="manualCount">{{ $manualCount }}</div>
            <div class="text-xs text-gray-400" x-text="manualPercent + '% do total'">&nbsp;</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Distribuição por Hora</h3>
            <canvas id="hourlyChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Comparativo</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Hoje</span>
                    <span class="text-2xl font-bold text-indigo-600" x-text="todayCount">{{ $todayCount }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Ontem</span>
                    <span class="text-2xl font-bold text-gray-600">{{ $yesterdayCount }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Semana passada (mesmo dia)</span>
                    <span class="text-2xl font-bold text-gray-600">{{ $lastWeekCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-gray-700">Percentual de Comparecimento</h3>
            <span class="text-sm text-gray-400" x-text="todayCount + '/' + totalActive + ' alunos'"></span>
        </div>
        <div class="mt-4 w-full bg-gray-200 rounded-full h-6">
            <div class="bg-indigo-600 h-6 rounded-full transition-all duration-500 flex items-center justify-center text-white text-xs font-bold"
                 :style="'width:' + Math.min(percent, 100) + '%'"
                 x-text="percent + '%'">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function companyDash() {
    return {
        todayCount: {{ $todayCount }},
        totalActive: {{ $totalActive }},
        bioCount: {{ $biometricCount }},
        manualCount: {{ $manualCount }},
        chart: null,

        get percent() { return this.totalActive > 0 ? Math.round((this.todayCount / this.totalActive) * 1000) / 10 : 0; },
        get bioPercent() { return this.todayCount > 0 ? Math.round((this.bioCount / this.todayCount) * 1000) / 10 : 0; },
        get manualPercent() { return this.todayCount > 0 ? Math.round((this.manualCount / this.todayCount) * 1000) / 10 : 0; },

        startPolling() {
            this.renderChart(@json($hourlyData));
            setInterval(() => this.fetchData(), 10000);
        },

        async fetchData() {
            try {
                const res = await fetch('{{ route("company.api.realtime") }}');
                const data = await res.json();
                this.todayCount = data.today_count;
                this.bioCount = data.biometric_count;
                this.manualCount = data.manual_count;
                this.totalActive = data.total_active;
                this.updateChart(data.hourly);
            } catch (e) {}
        },

        renderChart(hourlyData) {
            const ctx = document.getElementById('hourlyChart').getContext('2d');
            const labels = [];
            const values = [];
            for (let h = 10; h <= 15; h++) {
                labels.push(h + 'h');
                values.push(hourlyData[h] || 0);
            }
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Almoços',
                        data: values,
                        backgroundColor: 'rgba(79, 70, 229, 0.7)',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { display: false } }
                }
            });
        },

        updateChart(hourlyData) {
            if (!this.chart) return;
            const values = [];
            for (let h = 10; h <= 15; h++) {
                values.push(hourlyData[h] || 0);
            }
            this.chart.data.datasets[0].data = values;
            this.chart.update();
        }
    }
}
</script>
@endpush
@endsection
