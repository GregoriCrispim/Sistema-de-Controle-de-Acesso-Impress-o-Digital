@extends('layouts.app')
@section('title', 'Dashboard Fiscal')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="fiscalDash()">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard - Fiscal</h1>

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

    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Validar Período para Pagamento</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                <input type="date" x-model="periodStart" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                <input type="date" x-model="periodEnd" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex items-end">
                <button @click="previewPeriod()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Consultar Período</button>
            </div>
        </div>

        <div x-show="previewData" x-cloak class="border-t pt-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-600">Total de Almoços</div>
                    <div class="text-2xl font-bold text-blue-700" x-text="previewData?.total_meals"></div>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-600">Valor Total</div>
                    <div class="text-2xl font-bold text-green-700">R$ <span x-text="previewData?.total_value"></span></div>
                </div>
                <div class="bg-indigo-50 rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-600">Biometria</div>
                    <div class="text-xl font-bold text-indigo-700"><span x-text="previewData?.biometric_count"></span> (<span x-text="previewData?.biometric_percent"></span>%)</div>
                </div>
                <div class="bg-amber-50 rounded-lg p-4 text-center">
                    <div class="text-sm text-gray-600">Manual</div>
                    <div class="text-xl font-bold text-amber-700"><span x-text="previewData?.manual_count"></span> (<span x-text="previewData?.manual_percent"></span>%)</div>
                </div>
            </div>

            <div x-show="previewData?.daily_breakdown?.length > 0" class="mb-4">
                <h4 class="font-medium text-gray-600 mb-2">Detalhamento por Dia</h4>
                <div class="max-h-64 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left">Data</th>
                                <th class="px-3 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="day in previewData?.daily_breakdown" :key="day.day">
                                <tr>
                                    <td class="px-3 py-2" x-text="formatDate(day.day)"></td>
                                    <td class="px-3 py-2 text-right font-medium" x-text="day.total"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <form method="POST" action="{{ route('fiscal.validate.period') }}" onsubmit="return confirm('Confirmar validação deste período? Esta ação não pode ser desfeita.')">
                @csrf
                <input type="hidden" name="period_start" :value="periodStart">
                <input type="hidden" name="period_end" :value="periodEnd">
                <button type="submit" class="px-8 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 text-lg">
                    Validar Período
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <h2 class="text-lg font-semibold text-gray-700 p-6 pb-3">Períodos Validados</h2>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Protocolo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Almoços</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Validação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($validations as $v)
                <tr>
                    <td class="px-4 py-3 text-sm font-mono font-medium text-indigo-600">{{ $v->protocol_number }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $v->period_start->format('d/m/Y') }} - {{ $v->period_end->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $v->total_meals }}</td>
                    <td class="px-4 py-3 text-sm font-medium text-green-700">R$ {{ number_format($v->total_value, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $v->validated_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhum período validado.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $validations->links() }}</div>
    </div>
</div>

@push('scripts')
<script>
function fiscalDash() {
    return {
        periodStart: '',
        periodEnd: '',
        previewData: null,

        async previewPeriod() {
            if (!this.periodStart || !this.periodEnd) return;
            try {
                const res = await fetch('{{ route("fiscal.preview.period") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ period_start: this.periodStart, period_end: this.periodEnd })
                });
                this.previewData = await res.json();
            } catch (e) {
                alert('Erro ao consultar período.');
            }
        },

        formatDate(dateStr) {
            const parts = dateStr.split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
    }
}
</script>
@endpush
@endsection
