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
        const allStudents = await this._getAllStudents();
        const capturedMinutiae = FingerprintMatcherJS.parseTemplate(fingerprint);

        for (const s of allStudents) {
            if (!s.fingerprints) continue;
            for (const fp of s.fingerprints) {
                const storedMinutiae = FingerprintMatcherJS.parseTemplate(fp.template_code);
                if (FingerprintMatcherJS.matchMinutiae(capturedMinutiae, storedMinutiae, 0.33)) {
                    return s;
                }
            }
        }
        return null;
    },

    async _getAllStudents() {
        const tx = this.db.transaction('students', 'readonly');
        const store = tx.objectStore('students');
        return new Promise((resolve) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => resolve([]);
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

/**
 * ISO 19794-2 fingerprint matcher in JavaScript for offline biometric comparison.
 * Mirrors the PHP FingerprintMatcher logic.
 */
const FingerprintMatcherJS = {
    SPATIAL_TOL: 18,
    ANGLE_TOL: 16,
    MAX_ANCHORS: 12,

    parseTemplate(hex) {
        if (!hex || hex.length < 60 || hex.length % 2 !== 0) return null;

        const bytes = new Uint8Array(hex.match(/.{2}/g).map(b => parseInt(b, 16)));
        if (bytes.length < 30) return null;

        if (String.fromCharCode(bytes[0], bytes[1], bytes[2]) !== 'FMR') return null;

        const numViews = bytes[22];
        if (numViews < 1) return null;

        let offset = 24;
        const numMinutiae = bytes[offset + 3];
        offset += 4;

        const minutiae = [];
        for (let i = 0; i < numMinutiae && (offset + 5) < bytes.length; i++) {
            const w1 = (bytes[offset] << 8) | bytes[offset + 1];
            const w2 = (bytes[offset + 2] << 8) | bytes[offset + 3];

            minutiae.push({
                type: (w1 >> 14) & 0x03,
                x: w1 & 0x3FFF,
                y: w2 & 0x3FFF,
                angle: bytes[offset + 4] * 1.40625,
                quality: bytes[offset + 5],
            });
            offset += 6;
        }

        return minutiae.length >= 3 ? minutiae : null;
    },

    matchMinutiae(m1, m2, threshold) {
        if (!m1 || !m2 || m1.length === 0 || m2.length === 0) return false;

        const anchors1 = this._selectAnchors(m1);
        const anchors2 = this._selectAnchors(m2);
        const spatialTolSq = this.SPATIAL_TOL * this.SPATIAL_TOL;
        let bestMatched = 0;

        for (const a1 of anchors1) {
            for (const a2 of anchors2) {
                const rotDeg = a1.angle - a2.angle;
                const rotRad = rotDeg * 0.017453292519943;
                const cosR = Math.cos(rotRad);
                const sinR = Math.sin(rotRad);

                const rx = a2.x * cosR - a2.y * sinR;
                const ry = a2.x * sinR + a2.y * cosR;
                const tx = a1.x - rx;
                const ty = a1.y - ry;

                let matched = 0;
                const used = new Set();

                for (const m of m2) {
                    const trX = m.x * cosR - m.y * sinR + tx;
                    const trY = m.x * sinR + m.y * cosR + ty;
                    const trAngle = ((m.angle + rotDeg) % 360 + 360) % 360;

                    let bestDSq = Infinity;
                    let bestIdx = -1;

                    for (let k = 0; k < m1.length; k++) {
                        if (used.has(k)) continue;
                        const dx = trX - m1[k].x;
                        const dy = trY - m1[k].y;
                        const dSq = dx * dx + dy * dy;

                        if (dSq < spatialTolSq && dSq < bestDSq) {
                            let aDiff = Math.abs(trAngle - m1[k].angle);
                            if (aDiff > 180) aDiff = 360 - aDiff;
                            if (aDiff < this.ANGLE_TOL) {
                                bestDSq = dSq;
                                bestIdx = k;
                            }
                        }
                    }

                    if (bestIdx >= 0) {
                        matched++;
                        used.add(bestIdx);
                    }
                }

                if (matched > bestMatched) bestMatched = matched;
            }
        }

        return bestMatched / Math.min(m1.length, m2.length) >= threshold;
    },

    _selectAnchors(minutiae) {
        if (minutiae.length <= this.MAX_ANCHORS) return minutiae;
        return [...minutiae].sort((a, b) => b.quality - a.quality).slice(0, this.MAX_ANCHORS);
    },
};
