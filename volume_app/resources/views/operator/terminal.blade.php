@extends('layouts.app')
@section('title', 'Terminal do Operador')

@push('styles')
<style>
    .status-screen { min-height: 60vh; transition: all 0.3s ease; }
    .pulse-green { animation: pulseGreen 1s ease-in-out; }
    .pulse-red { animation: pulseRed 1s ease-in-out; }
    @keyframes pulseGreen { 0% { transform: scale(0.95); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }
    @keyframes pulseRed { 0% { transform: scale(0.95); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }
</style>
@endpush

@section('content')
<div x-data="terminalApp()" x-init="init()" class="max-w-5xl mx-auto px-4">
    {{-- Header bar --}}
    <div class="flex justify-between items-center mb-4 bg-white rounded-xl shadow p-4">
        <div class="flex items-center space-x-3">
            <i class="bi bi-shop text-2xl text-indigo-600"></i>
            <span class="text-2xl font-bold text-gray-800">CANTINA ESCOLAR - ALMOÇO</span>
        </div>
        <div class="flex items-center space-x-6">
            <div class="text-center">
                <span class="text-3xl font-bold text-indigo-600" x-text="todayCount">{{ $todayCount }}</span>
                <p class="text-sm text-gray-500">Almoços hoje</p>
            </div>
            <div class="text-lg font-mono text-gray-600" x-text="currentTime"></div>
        </div>
    </div>

    {{-- Offline indicator --}}
    <div x-show="isOffline" x-cloak class="bg-yellow-500 text-white text-center py-2 rounded-lg mb-4 text-xl font-bold animate-pulse">
        <i class="bi bi-wifi-off mr-2"></i> MODO OFFLINE - Os registros serão sincronizados quando a conexão retornar
    </div>

    {{-- Sync indicator --}}
    <div x-show="syncMessage" x-cloak x-transition class="bg-blue-500 text-white text-center py-2 rounded-lg mb-4 text-lg font-semibold">
        <i class="bi bi-arrow-repeat mr-2"></i> <span x-text="syncMessage"></span>
    </div>

    {{-- Pending offline count --}}
    <div x-show="pendingOfflineCount > 0" x-cloak class="bg-amber-100 text-amber-800 text-center py-2 rounded-lg mb-4 text-sm font-medium">
        <i class="bi bi-cloud-arrow-up mr-1"></i> <span x-text="pendingOfflineCount"></span> registro(s) pendente(s) de sincronização
    </div>

    {{-- Status Screen --}}
    <div class="status-screen rounded-2xl shadow-2xl flex flex-col items-center justify-center p-8 mb-6"
         :class="{
             'bg-gray-50': status === 'waiting',
             'bg-green-500 pulse-green': status === 'approved',
             'bg-red-500 pulse-red': status === 'denied'
         }">

        {{-- Waiting state --}}
        <template x-if="status === 'waiting'">
            <div class="text-center">
                <div class="text-6xl mb-4 text-indigo-400"><i class="bi bi-fingerprint"></i></div>
                <h2 class="text-4xl font-bold text-gray-700 mb-4">AGUARDANDO LEITURA</h2>
                <p class="text-xl text-gray-500">Posicione o dedo no leitor biométrico</p>
                <input type="text" x-ref="fingerprintInput" x-model="fingerprintCode"
                       @input="onFingerprintInput()"
                       class="mt-6 w-96 px-6 py-4 text-center text-2xl border-2 border-indigo-300 rounded-xl focus:ring-4 focus:ring-indigo-400 focus:border-indigo-500 outline-none"
                       placeholder="Aguardando digital..."
                       autofocus>
                <div x-show="processing" x-cloak class="mt-4 text-indigo-500">
                    <i class="bi bi-arrow-repeat animate-spin inline-block text-2xl"></i>
                    <span class="ml-2 text-lg">Verificando...</span>
                </div>
            </div>
        </template>

        {{-- Approved state --}}
        <template x-if="status === 'approved'">
            <div class="text-center text-white">
                <div class="text-7xl mb-4"><i class="bi bi-check-circle-fill"></i></div>
                <h2 class="text-6xl font-extrabold mb-6">LIBERADO!</h2>
                <div class="flex items-center justify-center space-x-8 mb-6">
                    <template x-if="studentData.photo_url">
                        <img :src="studentData.photo_url" class="w-40 h-40 rounded-2xl object-cover border-4 border-white shadow-lg">
                    </template>
                    <template x-if="!studentData.photo_url">
                        <div class="w-40 h-40 rounded-2xl bg-green-400 flex items-center justify-center border-4 border-white">
                            <i class="bi bi-person-fill text-6xl text-white"></i>
                        </div>
                    </template>
                    <div class="text-left">
                        <p class="text-4xl font-bold" x-text="studentData.name"></p>
                        <p class="text-2xl mt-2">Matrícula: <span x-text="studentData.enrollment_number"></span></p>
                        <p class="text-2xl">Turma: <span x-text="studentData.class_name"></span></p>
                        <p class="text-xl mt-1" x-text="studentData.course"></p>
                    </div>
                </div>
                <p class="text-2xl font-semibold"><i class="bi bi-shield-check mr-2"></i>Biometria verificada com sucesso!</p>
            </div>
        </template>

        {{-- Denied state --}}
        <template x-if="status === 'denied'">
            <div class="text-center text-white">
                <div class="text-7xl mb-4"><i class="bi bi-x-circle-fill"></i></div>
                <h2 class="text-6xl font-extrabold mb-6">BLOQUEADO</h2>
                <p class="text-3xl" x-text="denyReason"></p>
                <template x-if="studentData && studentData.name">
                    <div class="mt-6 flex items-center justify-center space-x-6">
                        <template x-if="studentData.photo_url">
                            <img :src="studentData.photo_url" class="w-32 h-32 rounded-2xl object-cover border-4 border-white shadow-lg">
                        </template>
                        <p class="text-2xl font-bold" x-text="studentData.name"></p>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Bottom actions --}}
    <div class="mb-6">
        {{-- Manual Search --}}
        <div class="bg-white rounded-xl shadow p-6" x-data="{ showSearch: false }">
            <button @click="showSearch = !showSearch; if(!showSearch) { searchQuery=''; searchResults=[]; } else { $nextTick(() => { $refs.manualSearchInput.focus(); $refs.manualSearchInput.scrollIntoView({ behavior: 'smooth', block: 'center' }); }); }" class="w-full py-3 px-6 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xl rounded-xl transition">
                <i class="bi bi-search mr-2"></i>Buscar Aluno (Manual)
            </button>
            <div x-show="showSearch" x-cloak class="mt-4">
                <input type="text" x-ref="manualSearchInput" x-model="searchQuery" @input.debounce.300ms="searchStudents()"
                       class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400"
                       placeholder="Nome ou matrícula...">
                <div x-ref="searchResultsList" class="mt-2 max-h-64 overflow-y-auto space-y-2">
                    <template x-for="student in searchResults" :key="student.id">
                        <div @click="selectStudent(student)"
                             class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-amber-50 border hover:border-amber-300 transition">
                            <template x-if="student.photo_url">
                                <img :src="student.photo_url" class="w-12 h-12 rounded-lg object-cover mr-3">
                            </template>
                            <template x-if="!student.photo_url">
                                <div class="w-12 h-12 rounded-lg bg-gray-300 flex items-center justify-center mr-3">
                                    <i class="bi bi-person-fill text-2xl text-gray-500"></i>
                                </div>
                            </template>
                            <div>
                                <p class="font-bold text-gray-800" x-text="student.name"></p>
                                <p class="text-sm text-gray-500">Mat: <span x-text="student.enrollment_number"></span> | <span x-text="student.class_name"></span></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Manual release modal --}}
    <div x-show="showManualModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-lg w-full mx-4">
            <h3 class="text-2xl font-bold text-gray-800 mb-4"><i class="bi bi-person-check mr-2"></i>Confirmar Liberação Manual</h3>
            <div class="flex items-center space-x-4 mb-4 p-4 bg-gray-50 rounded-xl">
                <template x-if="selectedStudent && selectedStudent.photo_url">
                    <img :src="selectedStudent.photo_url" class="w-24 h-24 rounded-xl object-cover">
                </template>
                <template x-if="selectedStudent && !selectedStudent.photo_url">
                    <div class="w-24 h-24 rounded-xl bg-gray-300 flex items-center justify-center"><i class="bi bi-person-fill text-4xl text-gray-500"></i></div>
                </template>
                <div>
                    <p class="text-xl font-bold" x-text="selectedStudent?.name"></p>
                    <p class="text-gray-600">Matrícula: <span x-text="selectedStudent?.enrollment_number"></span></p>
                    <p class="text-gray-600">Turma: <span x-text="selectedStudent?.class_name"></span></p>
                    <p class="text-gray-600" x-text="selectedStudent?.course"></p>
                </div>
            </div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo da liberação manual *</label>
            <textarea x-model="manualReason" rows="3"
                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400"
                      placeholder="Descreva o motivo..."></textarea>
            <div class="flex space-x-3 mt-4">
                <button @click="confirmManualRelease()" :disabled="!manualReason"
                        class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <i class="bi bi-check-lg mr-1"></i>Confirmar Liberação
                </button>
                <button @click="showManualModal = false; selectedStudent = null; manualReason = ''"
                        class="flex-1 py-3 bg-gray-400 hover:bg-gray-500 text-white font-bold rounded-xl transition">
                    <i class="bi bi-x-lg mr-1"></i>Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="/js/offline.js"></script>
<script>
function terminalApp() {
    return {
        status: 'waiting',
        fingerprintCode: '',
        todayCount: {{ $todayCount }},
        currentTime: '',
        studentData: {},
        denyReason: '',
        searchQuery: '',
        searchResults: [],
        selectedStudent: null,
        showManualModal: false,
        manualReason: '',
        isOffline: !navigator.onLine,
        statusTimeout: null,
        processing: false,
        inputTimer: null,
        offlineReady: false,
        pendingOfflineCount: 0,
        syncMessage: '',

        async init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);

            window.addEventListener('online', () => {
                this.isOffline = false;
                this.autoSync();
            });
            window.addEventListener('offline', () => { this.isOffline = true; });

            this.$nextTick(() => this.$refs.fingerprintInput?.focus());

            await OfflineManager.init();
            this.offlineReady = true;

            if (navigator.onLine) {
                await OfflineManager.refreshStudentCache();
                await this.autoSync();
            }

            this.pendingOfflineCount = (await OfflineManager.getPendingMeals()).length;

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            }
        },

        async autoSync() {
            const pending = await OfflineManager.getPendingMeals();
            if (pending.length === 0) return;

            this.syncMessage = `Sincronizando ${pending.length} registro(s)...`;
            const csrfToken = document.querySelector('meta[name=csrf-token]').content;
            const result = await OfflineManager.syncWithServer(csrfToken);

            if (result.synced > 0) {
                this.syncMessage = `${result.synced} registro(s) sincronizado(s) com sucesso!`;
                this.todayCount += result.synced;
            } else if (result.error) {
                this.syncMessage = 'Falha na sincronização. Tentando novamente em breve.';
            }

            this.pendingOfflineCount = (await OfflineManager.getPendingMeals()).length;
            setTimeout(() => { this.syncMessage = ''; }, 5000);
        },

        updateTime() {
            this.currentTime = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },

        onFingerprintInput() {
            if (this.inputTimer) clearTimeout(this.inputTimer);
            if (!this.fingerprintCode.trim()) return;

            this.inputTimer = setTimeout(() => {
                if (this.fingerprintCode.trim().length > 0 && this.status === 'waiting') {
                    this.checkBiometric();
                }
            }, 400);
        },

        resetStatus() {
            if (this.statusTimeout) clearTimeout(this.statusTimeout);
            this.statusTimeout = setTimeout(() => {
                this.status = 'waiting';
                this.studentData = {};
                this.denyReason = '';
                this.fingerprintCode = '';
                this.processing = false;
                this.$nextTick(() => this.$refs.fingerprintInput?.focus());
            }, 4000);
        },

        async checkBiometric() {
            if (!this.fingerprintCode.trim() || this.processing) return;
            this.processing = true;

            if (!this.isOffline) {
                try {
                    const res = await fetch('{{ route("operator.biometric.check") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ fingerprint_code: this.fingerprintCode })
                    });
                    const data = await res.json();
                    this.processing = false;

                    if (data.status === 'approved') {
                        this.status = 'approved';
                        this.studentData = data.student;
                        this.todayCount = data.today_count;
                    } else {
                        this.status = 'denied';
                        this.denyReason = data.reason;
                        this.studentData = data.student || {};
                    }
                } catch (e) {
                    this.processing = false;
                    await this.checkBiometricOffline();
                }
            } else {
                this.processing = false;
                await this.checkBiometricOffline();
            }

            this.resetStatus();
        },

        async checkBiometricOffline() {
            if (!this.offlineReady) {
                this.status = 'denied';
                this.denyReason = 'Modo offline não disponível';
                return;
            }

            const student = await OfflineManager.getStudent(this.fingerprintCode.trim());
            if (!student) {
                this.status = 'denied';
                this.denyReason = 'Digital não cadastrada (offline)';
                return;
            }

            if (!student.active) {
                this.status = 'denied';
                this.denyReason = 'Aluno inativo';
                return;
            }

            const alreadyEaten = await OfflineManager.hasEatenToday(student.id);
            if (alreadyEaten) {
                this.status = 'denied';
                this.denyReason = 'Já almoçou hoje (offline)';
                this.studentData = { name: student.name, enrollment_number: student.enrollment_number, photo_url: student.photo_path ? '/storage/' + student.photo_path : null };
                return;
            }

            await OfflineManager.saveMealOffline({ student_id: student.id, method: 'biometric' });

            this.pendingOfflineCount = (await OfflineManager.getPendingMeals()).length;
            this.todayCount++;
            this.status = 'approved';
            this.studentData = {
                name: student.name,
                enrollment_number: student.enrollment_number,
                course: student.course,
                class_name: student.class_name,
                photo_url: student.photo_path ? '/storage/' + student.photo_path : null,
            };
        },

        async searchStudents() {
            if (this.searchQuery.length < 2) { this.searchResults = []; return; }

            if (!this.isOffline) {
                try {
                    const res = await fetch(`{{ route("operator.search.student") }}?q=${encodeURIComponent(this.searchQuery)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    this.searchResults = await res.json();
                } catch (e) {
                    this.searchResults = await OfflineManager.searchStudents(this.searchQuery);
                    this.searchResults = this.searchResults.map(s => ({
                        ...s, photo_url: s.photo_path ? '/storage/' + s.photo_path : null
                    }));
                }
            } else {
                this.searchResults = await OfflineManager.searchStudents(this.searchQuery);
                this.searchResults = this.searchResults.map(s => ({
                    ...s, photo_url: s.photo_path ? '/storage/' + s.photo_path : null
                }));
            }

            if (this.searchResults.length > 0) {
                this.$nextTick(() => {
                    this.$refs.searchResultsList?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            }
        },

        selectStudent(student) {
            this.selectedStudent = student;
            this.showManualModal = true;
            this.manualReason = '';
        },

        async confirmManualRelease() {
            if (!this.selectedStudent || !this.manualReason) return;

            const student = this.selectedStudent;
            const reason = this.manualReason;

            this.showManualModal = false;
            this.selectedStudent = null;
            this.manualReason = '';
            this.searchQuery = '';
            this.searchResults = [];

            if (!this.isOffline) {
                try {
                    const res = await fetch('{{ route("operator.manual.release") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            student_id: student.id,
                            reason: reason
                        })
                    });
                    const data = await res.json();

                    if (data.status === 'approved') {
                        this.status = 'approved';
                        this.studentData = data.student;
                        this.todayCount = data.today_count;
                    } else {
                        this.status = 'denied';
                        this.denyReason = data.reason;
                    }
                } catch (e) {
                    await this.manualReleaseOffline(student, reason);
                }
            } else {
                await this.manualReleaseOffline(student, reason);
            }

            this.resetStatus();
        },

        async manualReleaseOffline(student, reason) {
            const alreadyEaten = await OfflineManager.hasEatenToday(student.id);
            if (alreadyEaten) {
                this.status = 'denied';
                this.denyReason = 'Já almoçou hoje (offline)';
                return;
            }

            await OfflineManager.saveMealOffline({
                student_id: student.id,
                method: 'manual',
                manual_reason: reason,
            });

            this.pendingOfflineCount = (await OfflineManager.getPendingMeals()).length;
            this.todayCount++;
            this.status = 'approved';
            this.studentData = {
                name: student.name,
                enrollment_number: student.enrollment_number,
                course: student.course,
                class_name: student.class_name,
                photo_url: student.photo_url || null,
            };
        }
    }
}
</script>
@endpush
@endsection
