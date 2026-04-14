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
    @php
        $toastNotifications = [];

        if (session('success')) {
            $toastNotifications[] = [
                'type' => 'success',
                'message' => session('success'),
            ];
        }

        if (session('message')) {
            $toastNotifications[] = [
                'type' => 'info',
                'message' => session('message'),
            ];
        }

        if ($errors->any()) {
            foreach ($errors->all() as $error) {
                $toastNotifications[] = [
                    'type' => 'error',
                    'message' => $error,
                    'duration' => 7000,
                ];
            }
        }
    @endphp

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

    <div
        x-cloak
        x-data='toastNotifications(@json($toastNotifications))'
        class="fixed top-4 right-4 z-[100] w-full max-w-sm space-y-3 px-4 sm:px-0 pointer-events-none"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition:enter="transform ease-out duration-300"
                x-transition:enter-start="translate-x-8 opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-8 opacity-0"
                class="pointer-events-auto overflow-hidden rounded-xl border bg-white shadow-lg ring-1 ring-black/5"
                :class="toast.borderClass"
            >
                <div class="flex items-start gap-3 p-4">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold" :class="toast.iconClass">
                        <span x-text="toast.icon"></span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900" x-text="toast.title"></p>
                        <p class="mt-1 text-sm text-gray-600 break-words" x-text="toast.message"></p>
                    </div>

                    <button
                        type="button"
                        @click="removeToast(toast.id)"
                        class="shrink-0 text-gray-400 transition hover:text-gray-600"
                        aria-label="Fechar notificação"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <main class="@auth py-6 @endauth">
        @yield('content')
    </main>

    <script>
        function toastNotifications(initialToasts = []) {
            return {
                toasts: [],
                nextId: 1,

                init() {
                    initialToasts.forEach((toast) => this.addToast(toast));

                    window.addEventListener('app-toast', (event) => {
                        this.addToast(event.detail || {});
                    });
                },

                addToast({ message = '', type = 'info', duration = 5000 } = {}) {
                    if (!message) {
                        return;
                    }

                    const styles = {
                        success: {
                            title: 'Sucesso',
                            icon: '✓',
                            iconClass: 'bg-green-100 text-green-700',
                            borderClass: 'border-green-200',
                        },
                        error: {
                            title: 'Erro',
                            icon: '!',
                            iconClass: 'bg-red-100 text-red-700',
                            borderClass: 'border-red-200',
                        },
                        info: {
                            title: 'Aviso',
                            icon: 'i',
                            iconClass: 'bg-blue-100 text-blue-700',
                            borderClass: 'border-blue-200',
                        },
                    };

                    const toastType = styles[type] ? type : 'info';
                    const toast = {
                        id: this.nextId++,
                        message,
                        visible: true,
                        duration,
                        ...styles[toastType],
                    };

                    this.toasts.push(toast);

                    window.setTimeout(() => {
                        this.removeToast(toast.id);
                    }, toast.duration);
                },

                removeToast(id) {
                    const toast = this.toasts.find((item) => item.id === id);

                    if (!toast) {
                        return;
                    }

                    toast.visible = false;

                    window.setTimeout(() => {
                        this.toasts = this.toasts.filter((item) => item.id !== id);
                    }, 220);
                },
            };
        }

        window.appToast = function (message, type = 'info', options = {}) {
            window.dispatchEvent(new CustomEvent('app-toast', {
                detail: { message, type, ...options },
            }));
        };
    </script>

    @stack('scripts')
</body>
</html>
