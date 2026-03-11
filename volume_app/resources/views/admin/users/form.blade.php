@extends('layouts.app')
@section('title', isset($user) ? 'Editar Usuário' : 'Novo Usuário')

@section('content')
<div class="max-w-2xl mx-auto px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ isset($user) ? 'Editar Usuário' : 'Novo Usuário' }}</h1>

    <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Senha {{ isset($user) ? '(deixe vazio para manter)' : '*' }}</label>
            <input type="password" name="password" {{ isset($user) ? '' : 'required' }} class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
            <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Papel *</label>
            <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="operator" {{ old('role', $user->role ?? '') === 'operator' ? 'selected' : '' }}>Operador</option>
                <option value="company" {{ old('role', $user->role ?? '') === 'company' ? 'selected' : '' }}>Empresa</option>
                <option value="fiscal" {{ old('role', $user->role ?? '') === 'fiscal' ? 'selected' : '' }}>Fiscal</option>
                <option value="management" {{ old('role', $user->role ?? '') === 'management' ? 'selected' : '' }}>Gestão</option>
            </select>
        </div>

        @if(isset($user))
        <div class="flex items-center">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" id="active" {{ $user->active ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
            <label for="active" class="ml-2 text-sm text-gray-700">Ativo</label>
        </div>
        @endif

        <div class="flex space-x-3 pt-4">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ isset($user) ? 'Atualizar' : 'Criar' }}</button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancelar</a>
        </div>
    </form>
</div>
@endsection
