<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AlunoBem') - Sistema de Controle de Almoço</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    @auth
    <nav class="bg-indigo-700 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-4">
                    <span class="text-xl font-bold tracking-tight">AlunoBem</span>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-600 {{ request()->routeIs('admin.*') ? 'bg-indigo-800' : '' }}">Admin</a>
                    @endif
                    @if(auth()->user()->isOperator() || auth()->user()->isAdmin())
                        <a href="{{ route('operator.terminal') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-600 {{ request()->routeIs('operator.*') ? 'bg-indigo-800' : '' }}">Terminal</a>
                    @endif
                    @if(auth()->user()->isCompany() || auth()->user()->isAdmin())
                        <a href="{{ route('company.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-600 {{ request()->routeIs('company.*') ? 'bg-indigo-800' : '' }}">Empresa</a>
                    @endif
                    @if(auth()->user()->isFiscal() || auth()->user()->isAdmin())
                        <a href="{{ route('fiscal.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-600 {{ request()->routeIs('fiscal.*') ? 'bg-indigo-800' : '' }}">Fiscal</a>
                    @endif
                    @if(auth()->user()->isManagement() || auth()->user()->isAdmin())
                        <a href="{{ route('management.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-600 {{ request()->routeIs('management.*') ? 'bg-indigo-800' : '' }}">Gestão</a>
                    @endif
                    @if(!auth()->user()->isOperator())
                        <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-600 {{ request()->routeIs('reports.*') ? 'bg-indigo-800' : '' }}">Relatórios</a>
                    @endif
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-indigo-200">{{ auth()->user()->name }}</span>
                    <span class="text-xs bg-indigo-500 px-2 py-1 rounded-full uppercase">{{ auth()->user()->role }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-600">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    @if(session('success'))
        <div class="max-w-7xl mx-auto mt-4 px-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
                <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">&times;</button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main class="@auth py-6 @endauth">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
