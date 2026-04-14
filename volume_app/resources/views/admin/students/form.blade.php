@extends('layouts.app')
@section('title', isset($student) ? 'Editar Aluno' : 'Novo Aluno')

@section('content')
<div class="max-w-3xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ isset($student) ? 'Editar Aluno' : 'Novo Aluno' }}</h1>

    <form method="POST" enctype="multipart/form-data"
          action="{{ isset($student) ? route('admin.students.update', $student) : route('admin.students.store') }}"
          class="bg-white rounded-xl shadow p-6 space-y-4">
        @csrf
        @if(isset($student)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                <input type="text" name="name" value="{{ old('name', $student->name ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Matrícula *</label>
                <input type="text" name="enrollment_number" value="{{ old('enrollment_number', $student->enrollment_number ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento *</label>
                <input type="date" name="birth_date" value="{{ old('birth_date', isset($student) ? $student->birth_date->format('Y-m-d') : '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Curso *</label>
                <select name="course" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="Ensino Médio" {{ old('course', $student->course ?? '') === 'Ensino Médio' ? 'selected' : '' }}>Ensino Médio</option>
                    <option value="PROEJA" {{ old('course', $student->course ?? '') === 'PROEJA' ? 'selected' : '' }}>PROEJA</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Turma *</label>
                <input type="text" name="class_name" value="{{ old('class_name', $student->class_name ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ex: 3º A">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto {{ isset($student) ? '(trocar)' : '*' }}</label>
            @if(isset($student) && $student->photo_path)
                <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-24 h-24 rounded-xl object-cover mb-2">
            @endif
            <input type="file" name="photo" accept="image/*" {{ isset($student) ? '' : 'required' }} class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        @if(isset($student))
        <div class="flex items-center">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" id="active" {{ $student->active ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
            <label for="active" class="ml-2 text-sm text-gray-700">Ativo</label>
        </div>
        @endif

        <div class="flex space-x-3 pt-4">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ isset($student) ? 'Atualizar' : 'Cadastrar' }}</button>
            <a href="{{ route('admin.students.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancelar</a>
        </div>
    </form>

    @if(isset($student))
    <div class="bg-white rounded-xl shadow p-6 mt-6 border border-red-200">
        <h2 class="text-lg font-semibold text-red-700 mb-2"><i class="bi bi-shield-exclamation mr-1"></i>LGPD - Anonimização de Dados</h2>
        <p class="text-sm text-gray-500 mb-3">Anonimiza irreversivelmente os dados pessoais deste aluno (nome, matrícula, foto, digitais). O histórico de refeições é preservado de forma anônima.</p>
        <form method="POST" action="{{ route('admin.students.anonymize', $student) }}" onsubmit="return confirm('ATENÇÃO: Esta ação é IRREVERSÍVEL. Todos os dados pessoais do aluno serão anonimizados. Deseja continuar?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                <i class="bi bi-eraser-fill mr-1"></i>Anonimizar Dados (LGPD)
            </button>
        </form>
    </div>

    <div x-data="{ templateCode: '' }" class="bg-white rounded-xl shadow p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Impressões Digitais ({{ $student->fingerprints->count() }}/3)</h2>

        @if($student->fingerprints->count() < 3)
        <form method="POST" action="{{ route('admin.fingerprints.store', $student) }}" class="space-y-3 mb-4">
            @csrf
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Template da Digital (HEX)</label>
                    <input type="text" name="template_code" x-model="templateCode" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-xs"
                           placeholder="Clique aqui e use o leitor biométrico para colar o código HEX...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dedo</label>
                    <select name="finger_index" required class="px-3 py-2 border border-gray-300 rounded-lg">
                        @php
                            $usedFingers = $student->fingerprints->pluck('finger_index')->toArray();
                            $fingerNames = [1 => 'Polegar Dir.', 2 => 'Indicador Dir.', 3 => 'Médio Dir.', 4 => 'Anelar Dir.', 5 => 'Mínimo Dir.', 6 => 'Polegar Esq.', 7 => 'Indicador Esq.', 8 => 'Médio Esq.', 9 => 'Anelar Esq.', 10 => 'Mínimo Esq.'];
                        @endphp
                        @for($i = 1; $i <= 10; $i++)
                            @if(!in_array($i, $usedFingers))
                                <option value="{{ $i }}">{{ $fingerNames[$i] }}</option>
                            @endif
                        @endfor
                    </select>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" :disabled="!templateCode"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition">
                    <i class="bi bi-save mr-1"></i>Salvar Digital
                </button>
            </div>
            <div x-show="templateCode.length > 0" x-cloak class="text-xs text-gray-500">
                Tamanho: <span x-text="templateCode.length"></span> caracteres HEX
            </div>
        </form>
        @endif

        @foreach($student->fingerprints as $fp)
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-2">
            <div>
                <span class="font-medium">{{ $fingerNames[$fp->finger_index] ?? 'Dedo ' . $fp->finger_index }}</span>
                <span class="text-xs text-gray-400 ml-2 font-mono">{{ Str::limit($fp->template_code, 40) }}</span>
                <span class="text-xs text-gray-400 ml-1">({{ strlen($fp->template_code) }} chars)</span>
            </div>
            <form method="POST" action="{{ route('admin.fingerprints.destroy', [$student, $fp]) }}" onsubmit="return confirm('Remover esta digital?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="bi bi-trash mr-1"></i>Remover</button>
            </form>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
