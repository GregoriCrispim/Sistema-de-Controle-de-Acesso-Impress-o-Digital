/**
 * AlunoBem Offline Module
 * Handles IndexedDB storage for offline meal registrations and auto-sync.
 */
const OfflineManager = {
    db: null,
    DB_NAME: 'alunobem_offline',
    DB_VERSION: 1,

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.DB_NAME, this.DB_VERSION);

            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('students')) {
                    db.createObjectStore('students', { keyPath: 'id' });
                }
                if (!db.objectStoreNames.contains('pending_meals')) {
                    const store = db.createObjectStore('pending_meals', { keyPath: 'local_id', autoIncrement: true });
                    store.createIndex('student_id', 'student_id', { unique: false });
                    store.createIndex('date', 'date', { unique: false });
                }
                if (!db.objectStoreNames.contains('sync_logs')) {
                    db.createObjectStore('sync_logs', { keyPath: 'id', autoIncrement: true });
                }
            };

            request.onsuccess = (e) => {
                this.db = e.target.result;
                resolve(this.db);
            };

            request.onerror = () => reject(request.error);
        });
    },

    async cacheStudents(students) {
        const tx = this.db.transaction('students', 'readwrite');
        const store = tx.objectStore('students');
        store.clear();
        students.forEach(s => store.put(s));
        return new Promise((resolve, reject) => {
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    },

    async getStudent(fingerprint) {
        const tx = this.db.transaction('students', 'readonly');
        const store = tx.objectStore('students');
        return new Promise((resolve) => {
            const request = store.getAll();
            request.onsuccess = () => {
                const students = request.result;
                const found = students.find(s =>
                    s.fingerprints && s.fingerprints.some(fp => fp.template_code === fingerprint)
                );
                resolve(found || null);
            };
            request.onerror = () => resolve(null);
        });
    },

    async getStudentById(id) {
        const tx = this.db.transaction('students', 'readonly');
        const store = tx.objectStore('students');
        return new Promise((resolve) => {
            const request = store.get(id);
            request.onsuccess = () => resolve(request.result || null);
            request.onerror = () => resolve(null);
        });
    },

    async searchStudents(query) {
        const tx = this.db.transaction('students', 'readonly');
        const store = tx.objectStore('students');
        return new Promise((resolve) => {
            const request = store.getAll();
            request.onsuccess = () => {
                const q = query.toLowerCase();
                const results = request.result.filter(s =>
                    s.active &&
                    (s.name.toLowerCase().includes(q) || s.enrollment_number.toLowerCase().includes(q))
                ).slice(0, 10);
                resolve(results);
            };
            request.onerror = () => resolve([]);
        });
    },

    async hasEatenToday(studentId) {
        const today = new Date().toISOString().split('T')[0];
        const tx = this.db.transaction('pending_meals', 'readonly');
        const store = tx.objectStore('pending_meals');
        const index = store.index('student_id');
        return new Promise((resolve) => {
            const request = index.getAll(studentId);
            request.onsuccess = () => {
                const todayMeal = request.result.find(m => m.date === today);
                resolve(!!todayMeal);
            };
            request.onerror = () => resolve(false);
        });
    },

    async saveMealOffline(mealData) {
        const today = new Date().toISOString().split('T')[0];
        const tx = this.db.transaction('pending_meals', 'readwrite');
        const store = tx.objectStore('pending_meals');
        store.add({
            ...mealData,
            date: today,
            served_at: new Date().toISOString(),
            synced: false,
        });
        return new Promise((resolve, reject) => {
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    },

    async getPendingMeals() {
        const tx = this.db.transaction('pending_meals', 'readonly');
        const store = tx.objectStore('pending_meals');
        return new Promise((resolve) => {
            const request = store.getAll();
            request.onsuccess = () => {
                resolve(request.result.filter(m => !m.synced));
            };
            request.onerror = () => resolve([]);
        });
    },

    async markMealsSynced(localIds) {
        const tx = this.db.transaction('pending_meals', 'readwrite');
        const store = tx.objectStore('pending_meals');
        localIds.forEach(id => store.delete(id));
        return new Promise((resolve) => {
            tx.oncomplete = resolve;
            tx.onerror = resolve;
        });
    },

    async logSync(result) {
        const tx = this.db.transaction('sync_logs', 'readwrite');
        const store = tx.objectStore('sync_logs');
        store.add({
            timestamp: new Date().toISOString(),
            ...result,
        });
        return new Promise((resolve) => {
            tx.oncomplete = resolve;
            tx.onerror = resolve;
        });
    },

    async getSyncLogs() {
        const tx = this.db.transaction('sync_logs', 'readonly');
        const store = tx.objectStore('sync_logs');
        return new Promise((resolve) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result.reverse());
            request.onerror = () => resolve([]);
        });
    },

    async clearSyncLogs() {
        const tx = this.db.transaction('sync_logs', 'readwrite');
        const store = tx.objectStore('sync_logs');
        store.clear();
        return new Promise((resolve) => {
            tx.oncomplete = resolve;
            tx.onerror = resolve;
        });
    },

    async syncWithServer(csrfToken) {
        const pending = await this.getPendingMeals();
        if (pending.length === 0) return { synced: 0, conflicts: [], pending: 0 };

        try {
            const response = await fetch('/api/sync/meals', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    meals: pending.map(m => ({
                        student_id: m.student_id,
                        method: m.method,
                        manual_reason: m.manual_reason || null,
                        served_at: m.served_at,
                    }))
                })
            });

            if (!response.ok) throw new Error('Sync failed');

            const result = await response.json();
            await this.markMealsSynced(pending.map(m => m.local_id));

            const syncResult = {
                synced: result.synced,
                conflicts: result.conflicts,
                pending: 0,
                total_sent: pending.length,
            };

            await this.logSync(syncResult);
            return syncResult;
        } catch (e) {
            return { synced: 0, conflicts: [], pending: pending.length, error: e.message };
        }
    },

    async refreshStudentCache() {
        try {
            const response = await fetch('/api/sync/students', {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Failed to fetch students');
            const students = await response.json();
            await this.cacheStudents(students);
            return students.length;
        } catch (e) {
            return 0;
        }
    }
};
