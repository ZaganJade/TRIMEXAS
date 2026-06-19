<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import Select from "@/components/ui/Select.vue";
import { Play, Search, Users, FilterX, Info } from "@lucide/vue";

const props = defineProps({
    batches: { type: Array, default: () => [] },
    candidateCount: { type: Number, default: 0 },
    filters: { type: Object, default: () => ({ q: "", batch_id: null }) },
    periodeOptions: { type: Array, default: () => [] },
});

const form = useForm({
    label: "",
    periode: "ganjil",
    tahun_akademik: new Date().getFullYear(),
});

const q = ref(props.filters.q ?? "");
const batchId = ref(props.filters.batch_id ?? null);

function submit() {
    form.post(route("admin.selection.run"));
}

// Batch history filtering is fully client-side — typing in the search box
// never hits the server, so the input keeps focus on every keystroke.
const filteredBatches = computed(() => {
    const term = q.value.trim().toLowerCase();
    return props.batches.filter((b) => {
        if (batchId.value !== null && Number(b.id) !== Number(batchId.value)) {
            return false;
        }
        if (!term) return true;
        return (
            String(b.label ?? "").toLowerCase().includes(term) ||
            String(b.periode ?? "").toLowerCase().includes(term) ||
            String(b.tahun_akademik ?? "").toLowerCase().includes(term)
        );
    });
});

function clearFilters() {
    q.value = "";
    batchId.value = null;
}

const batchOptions = computed(() => [
    { value: null, label: "Semua batch" },
    ...props.batches.map((b) => ({
        value: b.id,
        label: `${b.label}${b.periode ? " · " + b.periode : ""}${b.tahun_akademik ? " " + b.tahun_akademik : ""}`,
    })),
]);

function statusVariant(status) {
    return {
        queued: "tag-primary",
        running: "tag-warning",
        completed: "tag-success",
        failed: "tag-error",
    }[status] ?? "tag-primary";
}
</script>

<template>
    <Head title="Jalankan Seleksi" />
    <AdminLayout active="selection">
        <div class="selection-run-page">
            <!-- Header -->
            <header class="page-header">
                <h1 class="page-title">Jalankan Seleksi</h1>
                <p class="page-subtitle">
                    Pengaturan penilaian — kriteria, aturan, dan batas — akan dikunci saat putaran dimulai,
                    sehingga hasil riwayat tidak berubah walau pengaturan diperbarui kemudian.
                </p>
            </header>

            <!-- Stats row -->
            <div class="stats-grid">
                <Card variant="elevated" class="stat-card">
                    <div class="flex items-center gap-3">
                        <span class="bento-icon" style="background: var(--primary-soft); color: var(--primary)">
                            <Users :size="18" />
                        </span>
                        <div>
                            <p class="stat-label">Kandidat siap dinilai</p>
                            <p class="stat-value tnum">{{ candidateCount }}</p>
                        </div>
                    </div>
                    <p class="stat-hint">
                        Mahasiswa yang sudah disetujui dan akan dinilai pada putaran berikutnya.
                    </p>
                </Card>
                <Card variant="elevated" class="stat-card">
                    <div class="flex items-center gap-3">
                        <span class="bento-icon" style="background: color-mix(in oklab, var(--accent) 18%, transparent); color: var(--accent)">
                            <Play :size="18" />
                        </span>
                        <div>
                            <p class="stat-label">Total batch berjalan</p>
                            <p class="stat-value tnum">{{ batches.length }}</p>
                        </div>
                    </div>
                    <p class="stat-hint">
                        Batch yang sudah / sedang berjalan. Riwayat lengkap ada di panel kanan.
                    </p>
                </Card>
            </div>

            <!-- Main 2-column: Form + History -->
            <div class="main-grid">
                <!-- Form batch baru -->
                <Card variant="elevated" class="form-card">
                    <h2 class="form-title">
                        <Play :size="18" />
                        Batch baru
                    </h2>
                    <form class="mt-5 space-y-4" @submit.prevent="submit">
                        <div class="space-y-1.5">
                            <Label for="label" required>Label batch</Label>
                            <Input
                                id="label"
                                v-model="form.label"
                                placeholder="contoh: Seleksi Genap 2026"
                                :invalid="!!form.errors.label"
                            />
                            <p v-if="form.errors.label" class="text-xs text-red-600">{{ form.errors.label }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <Label for="periode">Periode</Label>
                                <Select id="periode" v-model="form.periode" :options="periodeOptions" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="tahun">Tahun akademik</Label>
                                <Input id="tahun" v-model.number="form.tahun_akademik" type="number" min="2018" max="2100" />
                            </div>
                        </div>
                        <Button type="submit" :disabled="form.processing" class="btn-primary w-full">
                            {{ form.processing ? "Memproses…" : "Run Selection" }}
                        </Button>
                    </form>
                </Card>

                <!-- Riwayat batch -->
                <Card variant="outline" class="history-card">
                    <div class="history-header">
                        <div class="flex items-center justify-between">
                            <span class="history-title">Riwayat batch</span>
                            <span class="history-count">{{ filteredBatches.length }} entri</span>
                        </div>
                        <div class="history-filters">
                            <div class="filter-search">
                                <Label for="search-q">Cari (label / periode / tahun)</Label>
                                <div class="relative">
                                    <Search :size="14" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--muted)]" />
                                    <Input id="search-q" v-model="q" class="pl-7" placeholder="contoh: genap 2026" />
                                </div>
                            </div>
                            <div class="filter-select">
                                <Label for="batch-pick">Filter batch</Label>
                                <Select
                                    id="batch-pick"
                                    :model-value="batchId"
                                    @update:model-value="(v) => { batchId = v ? Number(v) : null; }"
                                    :options="batchOptions"
                                />
                            </div>
                            <Button size="sm" variant="ghost" type="button" @click="clearFilters" class="filter-reset">
                                <FilterX :size="13" class="mr-1.5" />
                                Reset
                            </Button>
                        </div>
                    </div>
                    <div class="history-body">
                        <table v-if="filteredBatches.length" class="history-table">
                            <thead>
                                <tr>
                                    <th>Batch</th>
                                    <th>Status</th>
                                    <th>Eligible</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="b in filteredBatches" :key="b.id">
                                    <td>
                                        <div class="batch-info">
                                            <span class="batch-name">{{ b.label }}</span>
                                            <span class="batch-meta">
                                                <span v-if="b.periode">{{ b.periode }} · </span>
                                                <span v-if="b.tahun_akademik">TA {{ b.tahun_akademik }} · </span>
                                                {{ new Date(b.created_at).toLocaleString("id-ID") }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="tag mono text-xs uppercase" :class="statusVariant(b.status)">{{ b.status }}</span>
                                    </td>
                                    <td>
                                        <span v-if="b.total_candidates != null" class="mono text-xs text-[var(--muted)]">
                                            {{ b.total_eligible ?? 0 }}/{{ b.total_candidates }}
                                        </span>
                                    </td>
                                    <td>
                                        <Link :href="route('admin.selection.show', b.id)" class="view-link">Lihat</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="empty-state">
                            Tidak ada batch yang cocok dengan pencarian Anda.
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Info card -->
            <Card variant="outline" class="info-card">
                <div class="flex items-start gap-3">
                    <Info :size="16" class="mt-0.5 shrink-0 text-[var(--primary)]" />
                    <div class="info-content">
                        <p class="info-title">Cara Kerja Penilaian</p>
                        <ol class="info-steps">
                            <li>Empat syarat awal: mahasiswa aktif, semester ≤ 6, IPK ≥ 3,00, dan akun sudah disetujui.</li>
                            <li>Setiap kriteria dinilai seberapa cocok dengan kategori tertentu.</li>
                            <li>Aturan penilaian dicocokkan dengan profil kandidat untuk menentukan keputusan.</li>
                            <li>Seluruh pertimbangan digabung menjadi satu skor kelayakan (0–100).</li>
                            <li>Skor menentukan status akhir: belum layak, dipertimbangkan, atau layak.</li>
                        </ol>
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* =====================================================
   Page Layout
   ===================================================== */
.selection-run-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 1400px;
}

/* =====================================================
   Header
   ===================================================== */
.page-header {
    margin-bottom: 0.5rem;
}

.page-title {
    font-family: var(--font-display);
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 600;
    color: var(--ink);
    line-height: 1.2;
}

.page-subtitle {
    margin-top: 0.5rem;
    font-size: 14.5px;
    color: var(--muted);
    line-height: 1.6;
}

/* =====================================================
   Stats Grid
   ===================================================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    padding: 1.5rem;
}

.stat-label {
    font-family: var(--font-mono);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: var(--muted);
}

.stat-value {
    font-family: var(--font-display);
    font-size: 2rem;
    font-weight: 600;
    color: var(--ink);
    line-height: 1;
    margin-top: 4px;
}

.stat-hint {
    margin-top: 0.5rem;
    font-size: 12.5px;
    color: var(--muted);
}

/* =====================================================
   Main Grid: Form + History
   ===================================================== */
.main-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .main-grid {
        grid-template-columns: 1fr;
    }
}

/* =====================================================
   Form Card
   ===================================================== */
.form-card {
    padding: 1.5rem;
}

.form-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: var(--font-display);
    font-size: 1.15rem;
    font-weight: 600;
    color: var(--ink);
}

/* =====================================================
   History Card
   ===================================================== */
.history-card {
    overflow: hidden;
}

.history-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface-2);
}

.history-title {
    font-family: var(--font-mono);
    font-size: 12px;
    font-weight: 500;
    color: var(--ink);
}

.history-count {
    font-family: var(--font-mono);
    font-size: 10px;
    color: var(--muted);
}

.history-filters {
    display: grid;
    grid-template-columns: 1fr 200px auto;
    gap: 0.75rem;
    align-items: end;
    margin-top: 0.75rem;
}

@media (max-width: 640px) {
    .history-filters {
        grid-template-columns: 1fr;
    }
}

.filter-reset {
    align-self: end;
}

.history-body {
    max-height: 420px;
    overflow: auto;
}

/* =====================================================
   History Table
   ===================================================== */
.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.history-table thead {
    text-align: left;
}

.history-table th {
    padding: 10px 16px;
    font-family: var(--font-mono);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--muted);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
}

.history-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.history-table tbody tr:last-child td {
    border-bottom: none;
}

.history-table tbody tr {
    transition: background 0.15s ease;
}

.history-table tbody tr:hover {
    background: var(--surface-2);
}

/* Batch info */
.batch-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.batch-name {
    font-weight: 500;
    color: var(--ink);
}

.batch-meta {
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--muted);
}

.view-link {
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    white-space: nowrap;
}

.view-link:hover {
    text-decoration: underline;
}

/* =====================================================
   Info Card
   ===================================================== */
.info-card {
    padding: 1.5rem;
}

.info-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
}

.info-content {
    font-size: 13px;
    color: var(--muted);
}

.info-steps {
    margin-top: 4px;
    list-style: decimal;
    padding-left: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.info-steps .mono {
    font-family: var(--font-mono);
    font-size: 12px;
}

/* =====================================================
   Empty State
   ===================================================== */
.empty-state {
    padding: 40px 16px;
    text-align: center;
    color: var(--muted);
    font-size: 14px;
}
</style>
