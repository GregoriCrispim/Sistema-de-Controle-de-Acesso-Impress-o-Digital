@extends('layouts.app')
@section('title', 'Gerenciar Usuários')

@section('content')
<div class="max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gerenciar Usuários</h1>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Novo Usuário</a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">E-mail</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Papel</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $user->role === 'operator' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $user->role === 'company' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $user->role === 'fiscal' ? 'bg-amber-100 text-amber-800' : '' }}
                            {{ $user->role === 'management' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        @if($user->active)
                            <span class="text-green-600 font-medium">Ativo</span>
                        @else
                            <span class="text-red-600 font-medium">Inativo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-800">Editar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $users->links() }}</div>
    </div>
</div>
@endsection
