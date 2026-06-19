<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import Select from "@/components/ui/Select.vue";
import LazySkeleton from "@/components/ui/LazySkeleton.vue";
import { Search, CheckCircle2, XCircle, ListChecks, Calculator } from "@lucide/vue";

const props = defineProps({
    batch: { type: Object, required: true },
    results: { type: Array, default: () => [] },
    ineligible: { type: Array, default: () => [] },
    batches: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ q: "", batch_id: null }) },
});

const status = ref(props.batch.status);
const total = ref(props.batch.total_candidates ?? 0);
const processed = ref(props.batch.processed_count ?? 0);
const errorSummary = ref(null);

const percentage = computed(() => (total.value > 0 ? Math.round((processed.value / total.value) * 100) : 0));
const isRunning = computed(() => status.value === "queued" || status.value === "running");

const q = ref(props.filters.q ?? "");
const batchId = ref(props.filters.batch_id ?? null);
const tab = ref("eligible"); // eligible | ineligible

const batchOptions = computed(() => [
    { value: null, label: "Pilih batch lain…" },
    ...props.batches.map((b) => ({
        value: b.id,
        label: `${b.label}${b.periode ? " · " + b.periode : ""}${b.tahun_akademik ? " " + b.tahun_akademik : ""}`,
    })),
]);

const filteredResults = computed(() => {
    if (!q.value) return props.results;
    const term = q.value.toLowerCase();
    return props.results.filter(
        (r) => (r.name ?? "").toLowerCase().includes(term) || (r.nim ?? "").toLowerCase().includes(term)
    );
});
const filteredIneligible = computed(() => {
    if (!q.value) return props.ineligible;
    const term = q.value.toLowerCase();
    return props.ineligible.filter(
        (r) => (r.name ?? "").toLowerCase().includes(term) || (r.nim ?? "").toLowerCase().includes(term)
    );
});

function reasonList(row) {
    if (!row.reasons) return [];
    if (Array.isArray(row.reasons)) return row.reasons;
    if (typeof row.reasons === "string") {
        try { return JSON.parse(row.reasons); } catch { return [row.reasons]; }
    }
    return [];
}

function categoryVariant(cat) {
    return {
        layak: "tag-success",
        dipertimbangkan: "tag-warning",
        tidak_layak: "tag-error",
    }[cat] ?? "tag-primary";
}

watch(batchId, (v) => {
    if (v && Number(v) !== props.batch.id) {
        router.get(route("admin.selection.show", v), { q: q.value || undefined, batch_id: v }, { replace: true });
    }
});

let pollHandle = null;
async function poll() {
    try {
        const res = await fetch(route("admin.selection.progress", props.batch.id), {
            headers: { Accept: "application/json" },
        });
        const json = await res.json();
        status.value = json.status;
        total.value = json.total;
        processed.value = json.processed;
        errorSummary.value = json.error_summary;
        if (json.status === "completed" || json.status === "failed") {
            stopPoll();
            router.reload({ preserveScroll: true });
        }
    } catch (_) {}
}
function startPoll() { if (!pollHandle) pollHandle = setInterval(poll, 2000); }
function stopPoll() { if (pollHandle) { clearInterval(pollHandle); pollHandle = null; } }
onMounted(() => { if (isRunning.value) startPoll(); });
onBeforeUnmount(stopPoll);
</script>

<template>
    <Head :title="`Batch ${batch.label}`" />
    <AdminLayout active="selection">
        <div class="selection-detail-page">
            <!-- Header -->
            <header class="page-header">
                <div class="header-row">
                    <div class="min-w-0">
                        <h1 class="page-title">{{ batch.label }}</h1>
                        <div class="page-meta">
                            <span v-if="batch.periode" class="meta-chip">{{ batch.periode }} {{ batch.tahun_akademik }}</span>
                            <span class="meta-chip">
                                <span
                                    class="status-dot-sm"
                                    :class="{
                                        'bg-emerald-500': status === 'completed',
                                        'bg-amber-500': status === 'running' || status === 'queued',
                                        'bg-red-500': status === 'failed',
                                    }"
                                ></span>
                                <span class="mono uppercase">{{ status }}</span>
                            </span>
                            <span class="meta-chip tnum">{{ processed }} / {{ total }} kandidat</span>
                        </div>
                    </div>
                    <div class="batch-switch">
                        <Label for="batch-switch" class="sr-only">Switch batch</Label>
                        <Select
                            id="batch-switch"
                            :model-value="batchId"
                            @update:model-value="(v) => { batchId = v ? Number(v) : null; }"
                            :options="batchOptions"
                        />
                    </div>
                </div>
            </header>

            <!-- Progress bar -->
            <Card v-if="isRunning" variant="elevated" class="progress-card">
                <p class="progress-label"><Calculator :size="14" /> Memproses kandidat…</p>
                <div class="meter mt-3"><i :style="{ width: `${percentage}%` }"></i></div>
                <p class="mt-2 text-xs text-[var(--muted)] mono">{{ percentage }}%</p>
            </Card>

            <Card v-if="errorSummary" variant="outline" class="error-card">
                <p class="font-medium text-red-700">Batch gagal</p>
                <pre class="mt-2 text-xs text-red-700 mono">{{ JSON.stringify(errorSummary, null, 2) }}</pre>
            </Card>

            <!-- Summary cards -->
            <div class="summary-grid">
                <Card variant="elevated" class="summary-card">
                    <div class="flex items-center gap-3">
                        <span class="bento-icon" style="background: color-mix(in oklab, var(--success) 18%, transparent); color: var(--success)">
                            <CheckCircle2 :size="18" />
                        </span>
                        <div>
                            <p class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Eligible</p>
                            <p class="display text-2xl text-[var(--ink)] tnum">{{ batch.total_eligible ?? 0 }}</p>
                        </div>
                    </div>
                </Card>
                <Card variant="elevated" class="summary-card">
                    <div class="flex items-center gap-3">
                        <span class="bento-icon" style="background: color-mix(in oklab, var(--danger) 18%, transparent); color: var(--danger)">
                            <XCircle :size="18" />
                        </span>
                        <div>
                            <p class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Tidak eligible</p>
                            <p class="display text-2xl text-[var(--ink)] tnum">{{ batch.total_ineligible ?? 0 }}</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Tab bar -->
            <div class="tab-bar">
                <div class="tab-group">
                    <button
                        type="button"
                        class="tab-btn"
                        :class="{ 'tab-active': tab === 'eligible' }"
                        @click="tab = 'eligible'"
                    >
                        Eligible ({{ results.length }})
                    </button>
                    <button
                        type="button"
                        class="tab-btn"
                        :class="{ 'tab-active': tab === 'ineligible' }"
                        @click="tab = 'ineligible'"
                    >
                        Tidak eligible ({{ ineligible.length }})
                    </button>
                </div>
                <div class="search-box">
                    <Search :size="14" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--muted)]" />
                    <Input v-model="q" class="pl-7" placeholder="Cari nama / NIM…" />
                </div>
            </div>

            <!-- Eligible table -->
            <div v-if="tab === 'eligible'" class="data-window">
                <div class="window-bar">
                    <span class="window-title text-xs">Ranking ({{ filteredResults.length }} kandidat)</span>
                </div>
                <div class="window-body p-0 overflow-auto">
                    <table class="ranking-table">
                        <thead>
                            <tr>
                                <th class="col-rank">#</th>
                                <th class="col-name">Nama</th>
                                <th class="col-nim">NIM</th>
                                <th class="col-score">Skor Z</th>
                                <th class="col-category">Kategori</th>
                                <th class="col-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in filteredResults" :key="r.student_id" class="ranking-row">
                                <td class="col-rank">
                                    <span class="rank-badge" :class="r.rank <= 3 ? `rank-badge-${r.rank}` : ''">{{ r.rank }}</span>
                                </td>
                                <td class="col-name">
                                    <span class="font-medium text-[var(--ink)]">{{ r.name }}</span>
                                </td>
                                <td class="col-nim">
                                    <span class="mono text-[var(--muted)]">{{ r.nim }}</span>
                                </td>
                                <td class="col-score">
                                    <span class="score-value tnum">{{ Number(r.score).toFixed(2) }}</span>
                                </td>
                                <td class="col-category">
                                    <span class="tag capitalize" :class="categoryVariant(r.category)">{{ r.category }}</span>
                                </td>
                                <td class="col-action">
                                    <Link :href="route('admin.selection.audit', { batch: batch.id, candidate: r.student_id })" class="audit-link">
                                        Audit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="isRunning && !filteredResults.length">
                                <td colspan="6" class="p-0">
                                    <LazySkeleton
                                        title="Menyiapkan ranking…"
                                        subtitle="Worker sedang menghitung skor setiap kandidat. Daftar peringkat akan muncul otomatis saat selesai."
                                        :running="status === 'running'"
                                        :processed="processed"
                                        :total="total"
                                        :rows="6"
                                        :columns="6"
                                        icon="cpu"
                                    />
                                </td>
                            </tr>
                            <tr v-else-if="!filteredResults.length">
                                <td colspan="6" class="empty-state">Belum ada hasil ranking.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ineligible table -->
            <div v-else class="data-window">
                <div class="window-bar">
                    <span class="window-title text-xs">Tidak eligible ({{ filteredIneligible.length }})</span>
                </div>
                <div class="window-body p-0 overflow-auto">
                    <table class="ineligible-table">
                        <thead>
                            <tr>
                                <th class="col-name">Nama</th>
                                <th class="col-nim">NIM</th>
                                <th class="col-reasons">
                                    <span class="inline-flex items-center gap-1.5">
                                        <ListChecks :size="12" />
                                        Alasan
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in filteredIneligible" :key="row.student_id" class="ineligible-row">
                                <td class="col-name"><span class="font-medium text-[var(--ink)]">{{ row.name }}</span></td>
                                <td class="col-nim"><span class="mono text-[var(--muted)]">{{ row.nim }}</span></td>
                                <td class="col-reasons">
                                    <ul class="reason-list">
                                        <li v-for="(r, i) in reasonList(row)" :key="i" class="reason-item">
                                            <XCircle :size="13" class="shrink-0 text-[var(--danger)]" />
                                            <span>{{ r }}</span>
                                        </li>
                                        <li v-if="!reasonList(row).length" class="text-[var(--muted)] italic">Tidak ada alasan tercatat.</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr v-if="isRunning && !filteredIneligible.length">
                                <td colspan="3" class="p-0">
                                    <LazySkeleton
                                        title="Menunggu hasil verifikasi…"
                                        subtitle="Daftar kandidat yang belum memenuhi syarat akan muncul setelah worker selesai."
                                        :running="status === 'running'"
                                        :processed="processed"
                                        :total="total"
                                        :rows="5"
                                        :columns="3"
                                        icon="sparkles"
                                    />
                                </td>
                            </tr>
                            <tr v-else-if="!filteredIneligible.length">
                                <td colspan="3" class="empty-state">Tidak ada kandidat ineligible.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* =====================================================
   Page Layout
   ===================================================== */
.selection-detail-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* =====================================================
   Header
   ===================================================== */
.page-header {
    margin-bottom: 0.5rem;
}

.header-row {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
}

.page-title {
    font-family: var(--font-display);
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 600;
    color: var(--ink);
    line-height: 1.2;
}

.page-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: var(--muted);
    font-family: var(--font-mono);
}

.status-dot-sm {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}

.batch-switch {
    width: 100%;
    max-width: 360px;
}

@media (min-width: 640px) {
    .batch-switch {
        width: 360px;
    }
}

/* =====================================================
   Progress / Error Cards
   ===================================================== */
.progress-card {
    padding: 1.5rem;
}

.progress-label {
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.error-card {
    padding: 1.25rem;
    border-color: rgba(252, 165, 165, 0.4);
    background: rgba(254, 242, 242, 0.4);
}

/* =====================================================
   Summary Cards
   ===================================================== */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 640px) {
    .summary-grid {
        grid-template-columns: 1fr;
    }
}

.summary-card {
    padding: 1.5rem;
}

/* =====================================================
   Tab Bar
   ===================================================== */
.tab-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}

.tab-group {
    display: inline-flex;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--surface);
    padding: 3px;
}

.tab-btn {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    border: none;
    background: none;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.tab-btn:hover {
    color: var(--ink);
}

.tab-btn.tab-active {
    background: var(--primary-light);
    color: var(--primary);
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 200px;
}

/* =====================================================
   Data Window
   ===================================================== */
.data-window {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    overflow: hidden;
}

/* =====================================================
   Ranking Table
   ===================================================== */
.ranking-table,
.ineligible-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.ranking-table thead,
.ineligible-table thead {
    text-align: left;
}

.ranking-table th,
.ineligible-table th {
    padding: 12px 16px;
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
.ranking-table .col-rank { width: 56px; text-align: center; }
.ranking-table .col-name { min-width: 160px; }
.ranking-table .col-nim { width: 120px; }
.ranking-table .col-score { width: 100px; text-align: right; }
.ranking-table .col-category { width: 160px; }
.ranking-table .col-action { width: 80px; }

.ineligible-table .col-name { min-width: 160px; }
.ineligible-table .col-nim { width: 120px; }
.ineligible-table .col-reasons { min-width: 240px; }

/* Ranking rows */
.ranking-row,
.ineligible-row {
    transition: background 0.15s ease;
}

.ranking-row:hover,
.ineligible-row:hover {
    background: var(--surface-2);
}

.ranking-table td,
.ineligible-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.ranking-table tbody tr:last-child td,
.ineligible-table tbody tr:last-child td {
    border-bottom: none;
}

/* Rank badges */
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
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

/* Score */
.score-value {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 15px;
    color: var(--ink);
}

/* Audit link */
.audit-link {
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    transition: color 0.15s;
}

.audit-link:hover {
    text-decoration: underline;
}

/* Reasons */
.reason-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.reason-item {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    font-size: 13px;
    color: var(--muted);
}

/* Empty state */
.empty-state {
    padding: 48px 16px;
    text-align: center;
    color: var(--muted);
    font-size: 14px;
}

/* =====================================================
   Responsive
   ===================================================== */
@media (max-width: 640px) {
    .ranking-table .col-nim,
    .ineligible-table .col-nim {
        display: none;
    }
}
</style>
