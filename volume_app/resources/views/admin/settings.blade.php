@extends('layouts.app')
@section('title', 'Configurações do Sistema')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Configurações do Sistema</h1>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-xl shadow p-6 space-y-4 mb-8">
        @csrf @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Horário de Início da Cantina *</label>
                <input type="time" name="canteen_start_time" value="{{ old('canteen_start_time', $settings['canteen_start_time']) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Horário de Término da Cantina *</label>
                <input type="time" name="canteen_end_time" value="{{ old('canteen_end_time', $settings['canteen_end_time']) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor por Refeição (R$) *</label>
            <input type="number" step="0.01" min="0.01" name="meal_value" value="{{ old('meal_value', $settings['meal_value']) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Limite de Liberações Manuais (%) *</label>
            <input type="number" step="1" min="1" max="100" name="manual_limit_percent" value="{{ old('manual_limit_percent', $settings['manual_limit_percent']) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            <p class="text-xs text-gray-400 mt-1">Percentual máximo de liberações manuais sobre o total diário</p>
        </div>

        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Salvar Configurações</button>
    </form>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4"><i class="bi bi-calendar3 mr-2"></i>Dias Letivos</h2>

        <div class="flex items-center space-x-4 mb-4">
            <a href="{{ route('admin.settings', ['school_month' => \Carbon\Carbon::parse($currentMonth . '-01')->subMonth()->format('Y-m')]) }}"
               class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm"><i class="bi bi-chevron-left"></i></a>
            <span class="text-lg font-medium">{{ \Carbon\Carbon::parse($currentMonth . '-01')->translatedFormat('F Y') }}</span>
            <a href="{{ route('admin.settings', ['school_month' => \Carbon\Carbon::parse($currentMonth . '-01')->addMonth()->format('Y-m')]) }}"
               class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm"><i class="bi bi-chevron-right"></i></a>
        </div>

        <form method="POST" action="{{ route('admin.settings.school-days') }}">
            @csrf @method('PUT')
            <input type="hidden" name="month" value="{{ $currentMonth }}">

            <div class="grid grid-cols-7 gap-1 text-center text-sm mb-2">
                <div class="font-medium text-red-500 py-1">Dom</div>
                <div class="font-medium text-gray-600 py-1">Seg</div>
                <div class="font-medium text-gray-600 py-1">Ter</div>
                <div class="font-medium text-gray-600 py-1">Qua</div>
                <div class="font-medium text-gray-600 py-1">Qui</div>
                <div class="font-medium text-gray-600 py-1">Sex</div>
                <div class="font-medium text-red-500 py-1">Sáb</div>
            </div>

            <div class="grid grid-cols-7 gap-1" x-data>
                @php $firstWeekday = $calendarDays[0]['weekday']; @endphp
                @for($i = 0; $i < $firstWeekday; $i++)
                    <div></div>
                @endfor

                @foreach($calendarDays as $day)
                <label class="relative cursor-pointer">
                    <input type="checkbox" name="school_days[]" value="{{ $day['date'] }}"
                           {{ $day['is_school_day'] ? 'checked' : '' }}
                           class="peer sr-only">
                    <div class="p-2 text-center rounded-lg border transition-colors
                                peer-checked:bg-indigo-100 peer-checked:border-indigo-400 peer-checked:text-indigo-800
                                {{ in_array($day['weekday'], [0, 6]) ? 'bg-red-50 text-red-400' : 'bg-gray-50 text-gray-700' }}
                                hover:bg-indigo-50">
                        <span class="text-sm font-medium">{{ $day['day'] }}</span>
                    </div>
                </label>
                @endforeach
            </div>

            <div class="flex items-center justify-between mt-4">
                <p class="text-xs text-gray-400">Clique nos dias para marcar/desmarcar como dia letivo. Dias marcados em azul são letivos.</p>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Salvar Dias Letivos</button>
            </div>
        </form>
    </div>
</div>
@endsection
