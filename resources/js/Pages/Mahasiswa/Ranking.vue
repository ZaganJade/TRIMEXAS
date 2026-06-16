<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
import MahasiswaLayout from "@/Layouts/MahasiswaLayout.vue";
import Button from "@/components/ui/Button.vue";
import Input from "@/components/ui/Input.vue";
import { ListOrdered, Search } from "@lucide/vue";

const props = defineProps({
    batchLabel: { type: String, default: null },
    rankings: { type: Array, default: () => [] },
    q: { type: String, default: null },
});

const search = ref(props.q ?? "");
function doSearch() {
    router.get(route("mahasiswa.ranking.index"), search.value ? { q: search.value } : {}, {
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Ranking Beasiswa" />

    <MahasiswaLayout active="ranking">
        <div class="ranking-page">
            <!-- Heading -->
            <header class="page-header">
                <div class="flex items-center gap-3">
                    <span class="bento-icon" style="background: var(--primary-soft); color: var(--primary); border-color: color-mix(in oklab, var(--primary) 28%, transparent)">
                        <ListOrdered :size="20" />
                    </span>
                    <div>
                        <span class="section-label">Hasil Seleksi</span>
                        <h1 class="page-title">Ranking Beasiswa</h1>
                    </div>
                </div>
                <p class="page-subtitle">
                    <template v-if="batchLabel">
                        Batch terbaru: <span class="tag tag-primary">{{ batchLabel }}</span>
                    </template>
                    <template v-else>
                        Belum ada hasil seleksi yang completed.
                    </template>
                </p>
            </header>

            <!-- Search -->
            <section v-if="batchLabel" class="search-section">
                <form class="search-form" @submit.prevent="doSearch">
                    <div class="relative flex-1">
                        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)]" />
                        <Input v-model="search" placeholder="Cari nama mahasiswa…" class="!pl-10" />
                    </div>
                    <Button type="submit" variant="secondary">Cari</Button>
                </form>
            </section>

            <!-- Table -->
            <section v-if="batchLabel" class="table-section">
                <div class="data-window">
                    <div class="window-bar">
                        <span class="window-dot" style="background:#fb7185"></span>
                        <span class="window-dot" style="background:#fbbf24"></span>
                        <span class="window-dot" style="background:#34d399"></span>
                        <span class="window-title">ranking-{{ batchLabel }}.json</span>
                    </div>
                    <div class="window-body !p-0">
                        <div class="overflow-x-auto">
                            <table class="ranking-table">
                                <thead>
                                    <tr>
                                        <th class="col-rank">#</th>
                                        <th class="col-name">Nama</th>
                                        <th class="col-score">Skor</th>
                                        <th class="col-status">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(r, idx) in rankings" :key="r.name" class="ranking-row">
                                        <td class="col-rank">
                                            <span class="rank-badge" :class="idx < 3 ? `rank-badge-${idx + 1}` : ''">
                                                {{ idx + 1 }}
                                            </span>
                                        </td>
                                        <td class="col-name">
                                            <div class="name-cell">
                                                <span class="avatar">{{ r.name.charAt(0).toUpperCase() }}</span>
                                                <span class="font-medium text-[var(--ink)]">{{ r.name }}</span>
                                            </div>
                                        </td>
                                        <td class="col-score">
                                            <span class="score-value tnum">
                                                {{ r.score !== null ? Number(r.score).toFixed(2) : '—' }}
                                            </span>
                                        </td>
                                        <td class="col-status">
                                            <span
                                                v-if="r.status"
                                                class="tag"
                                                :class="{
                                                    'tag-success': r.status === 'lolos' || r.status === 'cadangan',
                                                    'tag-warning': r.status === 'tidak memenuhi syarat',
                                                }"
                                            >
                                                {{ r.status }}
                                            </span>
                                            <span v-else class="text-[var(--muted)]">—</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!rankings.length">
                                        <td colspan="4" class="empty-state">Tidak ada data ranking.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section v-else class="empty-section">
                <p class="text-[15px] text-[var(--muted)]">
                    Belum ada hasil seleksi yang completed.
                </p>
            </section>
        </div>
    </MahasiswaLayout>
</template>

<style scoped>
/* =====================================================
   Page Layout
   ===================================================== */
.ranking-page {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

/* =====================================================
   Header
   ===================================================== */
.page-header {
    margin-bottom: 0.25rem;
}

.section-label {
    font-family: var(--font-mono);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: var(--muted);
}

.page-title {
    font-family: var(--font-display);
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 600;
    color: var(--ink);
    margin-top: 4px;
}

.page-subtitle {
    margin-top: 0.75rem;
    font-size: 14.5px;
    color: var(--muted);
}

/* =====================================================
   Search
   ===================================================== */
.search-section {
    margin-top: 0;
}

.search-form {
    display: flex;
    gap: 0.5rem;
}

/* =====================================================
   Table Section
   ===================================================== */
.table-section {
    margin-top: 0;
}

.data-window {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    overflow: hidden;
}

/* =====================================================
   Ranking Table
   ===================================================== */
.ranking-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.ranking-table thead {
    text-align: left;
}

.ranking-table th {
    padding: 14px 20px;
    font-family: var(--font-mono);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--muted);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
}

/* Column widths */
.ranking-table .col-rank { width: 64px; text-align: center; }
.ranking-table .col-name { min-width: 180px; }
.ranking-table .col-score { width: 120px; text-align: right; }
.ranking-table .col-status { width: 180px; }

/* Rows */
.ranking-row {
    transition: background 0.15s ease;
}

.ranking-row:hover {
    background: var(--surface-2);
}

.ranking-table td {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.ranking-table tbody tr:last-child td {
    border-bottom: none;
}

/* Rank badges */
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    font-family: var(--font-mono);
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
    background: var(--surface-2);
}

.rank-badge-1 {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
}

.rank-badge-2 {
    background: linear-gradient(135deg, #e5e7eb, #d1d5db);
    color: #374151;
}

.rank-badge-3 {
    background: linear-gradient(135deg, #fef3c7, #fed7aa);
    color: #9a3412;
}

/* Name cell */
.name-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    font-family: var(--font-mono);
    font-size: 12px;
    font-weight: 600;
    color: var(--surface);
    background: var(--primary);
    border-radius: 50%;
    flex-shrink: 0;
}

/* Score */
.score-value {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
    color: var(--ink);
}

/* Empty state */
.empty-state {
    padding: 48px 20px;
    text-align: center;
    color: var(--muted);
    font-size: 14px;
}

.empty-section {
    text-align: center;
    padding: 2rem 0;
}

/* =====================================================
   Responsive
   ===================================================== */
@media (max-width: 640px) {
    .ranking-table th,
    .ranking-table td {
        padding: 12px 12px;
    }
}
</style>
