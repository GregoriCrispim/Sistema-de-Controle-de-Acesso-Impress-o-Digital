@extends('layouts.app')
@section('title', 'Relatório por Estudante')

@section('content')
<div class="max-w-5xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Relatório - {{ $student->name }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('reports.by-student', ['student_id' => $student->id, 'format' => 'csv']) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">CSV</a>
            <a href="{{ route('reports.by-student', ['student_id' => $student->id, 'format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">PDF</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6 flex items-center space-x-6">
        @if($student->photo_path)
            <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-24 h-24 rounded-xl object-cover">
        @endif
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $student->name }}</p>
            <p class="text-gray-600">Matrícula: {{ $student->enrollment_number }}</p>
            <p class="text-gray-600">{{ $student->course }} - {{ $student->class_name }}</p>
            <p class="text-gray-600">Total de almoços: {{ $total }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Operador</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo Manual</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($meals as $meal)
                <tr class="{{ $meal->method === 'manual' ? 'bg-amber-50' : '' }}">
                    <td class="px-4 py-3 text-sm">{{ $meal->served_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($meal->method === 'biometric')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Biometria</span>
                        @else
                            <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs">Manual</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">{{ $meal->operator->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $meal->manual_reason ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Nenhum registro.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $meals->links() }}</div>
    </div>
    <p class="text-xs text-gray-400 mt-4">Gerado em: {{ $generated_at }}</p>
</div>
@endsection
