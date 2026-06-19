<script setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";
import { Loader2, Database, Cpu, Sparkles } from "@lucide/vue";

/**
 * LazySkeleton — status "queue worker sedang memproses".
 *
 * Tampil saat data belum tersedia karena pekerjaan (queue job) belum selesai.
 * Memberi sinyal jelas kepada pengguna bahwa sistem sedang bekerja, bukan
 * kosong/error. Bertiga: (1) header status, (2) ringkasan progres opsional,
 * (3) baris shimmer yang meniru tata letak konten asli.
 *
 * Props:
 *  - title:        String  judul status (mis. "Menyiapkan ranking…")
 *  - subtitle:     String  deskripsi sekunder
 *  - running:      Boolean apakah worker benar-benar sedang jalan
 *  - processed:    Number  jumlah item sudah diproses (opsional)
 *  - total:        Number  total item (opsional)
 *  - rows:         Number  jumlah baris shimmer (default 6)
 *  - columns:      Number  jumlah kolom shimmer (default 4)
 *  - icon:         String  'database' | 'cpu' | 'sparkles' (default 'database')
 */
const props = defineProps({
    title: { type: String, default: "Menyiapkan data…" },
    subtitle: {
        type: String,
        default: "Worker sedang memproses di latar belakang. Data akan muncul otomatis setelah selesai.",
    },
    running: { type: Boolean, default: true },
    processed: { type: Number, default: null },
    total: { type: Number, default: null },
    rows: { type: Number, default: 6 },
    columns: { type: Number, default: 4 },
    icon: { type: String, default: "database" },
});

const ICONS = { database: Database, cpu: Cpu, sparkles: Sparkles };
const iconComp = computed(() => ICONS[props.icon] ?? Database);

const showProgress = computed(
    () => props.processed !== null && props.total !== null && props.total > 0,
);
const percentage = computed(() =>
    showProgress.value ? Math.min(100, Math.round((props.processed / props.total) * 100)) : 0,
);

const rowList = computed(() => Array.from({ length: props.rows }, (_, i) => i));
const colList = computed(() => Array.from({ length: props.columns }, (_, i) => i));

// variasi lebar bar agar shimmer terlihat alami (tidak seragam)
function barWidth(row, col) {
    const widths = [90, 70, 85, 60, 95, 75, 65, 80];
    return `${widths[(row + col) % widths.length]}%`;
}
</script>

<template>
    <div class="lazy-skeleton" role="status" aria-live="polite">
        <!-- Status header -->
        <div class="ls-head">
            <div class="ls-icon" :class="{ 'is-spinning': running }">
                <Loader2 v-if="running" :size="18" />
                <component :is="iconComp" v-else :size="18" />
            </div>
            <div class="ls-head-text">
                <p class="ls-title">{{ title }}</p>
                <p class="ls-subtitle">{{ subtitle }}</p>
            </div>
            <span v-if="running" class="ls-badge">
                <span class="ls-badge-dot" />
                Worker berjalan
            </span>
        </div>

        <!-- Progress summary (opsional) -->
        <div v-if="showProgress" class="ls-progress">
            <div class="ls-progress-meta">
                <span class="ls-progress-label">
                    Memproses <strong>{{ processed }}</strong> dari <strong>{{ total }}</strong> data
                </span>
                <span class="ls-progress-pct mono tnum">{{ percentage }}%</span>
            </div>
            <div class="ls-progress-bar">
                <i :style="{ width: `${percentage}%` }"></i>
            </div>
        </div>

        <!-- Shimmer rows (meniru tata letak tabel/konten) -->
        <div class="ls-shimmer">
            <div v-for="row in rowList" :key="`row-${row}`" class="ls-row" :style="{ '--cols': columns }">
                <div
                    v-for="col in colList"
                    :key="`col-${row}-${col}`"
                    class="ls-cell"
                >
                    <span class="ls-bar" :style="{ width: barWidth(row, col) }"></span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.lazy-skeleton {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding: 1.5rem;
}

/* ---- status header ---- */
.ls-head {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.ls-icon {
    display: grid;
    place-items: center;
    width: 2.5rem;
    height: 2.5rem;
    flex-shrink: 0;
    border-radius: 0.75rem;
    color: var(--primary);
    background: var(--primary-soft);
    border: 1px solid color-mix(in oklab, var(--primary) 22%, transparent);
}

.ls-icon.is-spinning svg {
    animation: ls-spin 0.9s linear infinite;
}

@keyframes ls-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.ls-head-text {
    flex: 1;
    min-width: 12rem;
}

.ls-title {
    font-family: var(--font-display);
    font-size: 1rem;
    font-weight: 600;
    color: var(--ink);
    line-height: 1.3;
}

.ls-subtitle {
    margin-top: 0.25rem;
    font-size: 0.8125rem;
    line-height: 1.55;
    color: var(--muted);
}

.ls-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.7rem;
    border-radius: var(--radius-pill);
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--primary);
    background: color-mix(in oklab, var(--primary) 10%, transparent);
    border: 1px solid color-mix(in oklab, var(--primary) 25%, transparent);
    white-space: nowrap;
}

.ls-badge-dot {
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 50%;
    background: var(--primary);
    animation: ls-pulse 1.4s ease-in-out infinite;
}

@keyframes ls-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.75); }
}

/* ---- progress ---- */
.ls-progress {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.ls-progress-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.78rem;
    color: var(--muted);
}

.ls-progress-label strong {
    color: var(--foreground);
    font-weight: 600;
}

.ls-progress-pct {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--primary);
}

.ls-progress-bar {
    height: 0.5rem;
    border-radius: var(--radius-pill);
    background: var(--surface-2);
    border: 1px solid var(--border);
    overflow: hidden;
}

.ls-progress-bar i {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: var(--gradient-brand);
    transition: width 0.6s var(--ease-soft);
}

/* ---- shimmer rows ---- */
.ls-shimmer {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    border-top: 1px solid var(--border);
    padding-top: 1.25rem;
}

.ls-row {
    display: grid;
    grid-template-columns: repeat(var(--cols, 4), minmax(0, 1fr));
    gap: 0.75rem;
    align-items: center;
    padding: 0.35rem 0;
}

.ls-cell {
    min-width: 0;
}

.ls-bar {
    display: block;
    height: 0.7rem;
    border-radius: 0.35rem;
    background: linear-gradient(
        90deg,
        var(--surface-2) 0%,
        color-mix(in oklab, var(--surface-2) 60%, var(--border)) 50%,
        var(--surface-2) 100%
    );
    background-size: 200% 100%;
    animation: ls-shimmer 1.5s infinite linear;
}

@keyframes ls-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ---- responsive ---- */
@media (max-width: 640px) {
    .lazy-skeleton {
        padding: 1.1rem;
    }
    .ls-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* ---- reduced motion ---- */
@media (prefers-reduced-motion: reduce) {
    .ls-icon.is-spinning svg,
    .ls-badge-dot,
    .ls-bar {
        animation: none;
    }
    .ls-bar {
        background: var(--surface-2);
    }
}
</style>
