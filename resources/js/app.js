import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// Persistent Work Timer (localStorage) for Alpine.js
document.addEventListener('alpine:init', () => {
    Alpine.store('timer', {
        running: false,
        startedAt: null,
        accumulatedSeconds: 0,
        elapsedSeconds: 0,
        intervalId: null,

        init() {
            const saved = localStorage.getItem('medtechlog_work_timer');
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    this.running = Boolean(data.running);
                    this.startedAt = data.startedAt ? Number(data.startedAt) : null;
                    this.accumulatedSeconds = Number(data.accumulatedSeconds || 0);
                } catch {
                    localStorage.removeItem('medtechlog_work_timer');
                }
            }
            this.syncElapsed();
            if (this.running) {
                this.startTicker();
            }
        },

        persist() {
            localStorage.setItem('medtechlog_work_timer', JSON.stringify({
                running: this.running,
                startedAt: this.startedAt,
                accumulatedSeconds: this.accumulatedSeconds,
            }));
        },

        syncElapsed() {
            if (this.running && this.startedAt) {
                const diff = Math.floor((Date.now() - this.startedAt) / 1000);
                this.elapsedSeconds = this.accumulatedSeconds + Math.max(0, diff);
            } else {
                this.elapsedSeconds = this.accumulatedSeconds;
            }
        },

        startTicker() {
            if (this.intervalId) clearInterval(this.intervalId);
            this.intervalId = setInterval(() => this.syncElapsed(), 1000);
        },

        start() {
            if (this.running) return;
            this.running = true;
            this.startedAt = Date.now();
            this.persist();
            this.startTicker();
        },

        pause() {
            if (!this.running) return;
            this.syncElapsed();
            this.accumulatedSeconds = this.elapsedSeconds;
            this.running = false;
            this.startedAt = null;
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
            this.persist();
        },

        reset() {
            this.running = false;
            this.startedAt = null;
            this.accumulatedSeconds = 0;
            this.elapsedSeconds = 0;
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
            localStorage.removeItem('medtechlog_work_timer');
        },

        stopAndGetMinutes() {
            this.syncElapsed();
            const mins = Math.max(1, Math.round(this.elapsedSeconds / 60));
            this.reset();
            return mins;
        },

        get formatted() {
            const hrs = Math.floor(this.elapsedSeconds / 3600);
            const mins = Math.floor((this.elapsedSeconds % 3600) / 60);
            const secs = this.elapsedSeconds % 60;
            return [hrs, mins, secs].map(v => String(v).padStart(2, '0')).join(':');
        },
    });
});

Alpine.start();
