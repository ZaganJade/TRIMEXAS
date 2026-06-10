<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import Select from "@/components/ui/Select.vue";
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
        <div class="space-y-6">
        <header class="reveal-stagger" style="--delay: 0ms">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="display text-[clamp(1.8rem,4vw,2.4rem)] text-[var(--ink)] truncate">
                        {{ batch.label }}
                    </h1>
                    <p class="mono mt-1.5 text-sm text-[var(--muted)]">
                        <span v-if="batch.periode">{{ batch.periode }} {{ batch.tahun_akademik }}</span>
                        <span class="ml-2">· Status: <span class="tag tag-primary mono uppercase">{{ status }}</span></span>
                        <span class="ml-2">· <span class="tnum">{{ processed }} / {{ total }}</span> kandidat</span>
                    </p>
                </div>
                <div class="w-full sm:w-[360px]">
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
        <Card v-if="isRunning" variant="elevated" class="mt-6 p-6">
            <p class="text-sm font-medium flex items-center gap-2"><Calculator :size="14" /> Memproses kandidat…</p>
            <div class="meter mt-3"><i :style="{ width: `${percentage}%` }"></i></div>
            <p class="mt-2 text-xs text-[var(--muted)] mono">{{ percentage }}%</p>
        </Card>

        <Card v-if="errorSummary" variant="outline" class="mt-6 p-5 border-red-300/40 bg-red-50/40">
            <p class="font-medium text-red-700">Batch gagal</p>
            <pre class="mt-2 text-xs text-red-700 mono">{{ JSON.stringify(errorSummary, null, 2) }}</pre>
        </Card>

        <!-- Ringkasan 2 kartu -->
        <div class="bento-grid mt-6">
            <Card variant="elevated" class="col-4 p-6">
                <div class="flex items-center gap-3">
                    <span class="bento-icon" style="background: color-mix(in oklab, var(--success) 18%, transparent); color: var(--success)">
                        <CheckCircle2 :size="18" />
                    </span>
                    <div>
                        <p class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Eligible (dipertimbangkan / layak)</p>
                        <p class="display text-2xl text-[var(--ink)] tnum">{{ batch.total_eligible ?? 0 }}</p>
                    </div>
                </div>
            </Card>
            <Card variant="elevated" class="col-4 p-6">
                <div class="flex items-center gap-3">
                    <span class="bento-icon" style="background: color-mix(in oklab, var(--danger) 18%, transparent); color: var(--danger)">
                        <XCircle :size="18" />
                    </span>
                    <div>
                        <p class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Tidak eligible (gate / skor)</p>
                        <p class="display text-2xl text-[var(--ink)] tnum">{{ batch.total_ineligible ?? 0 }}</p>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Tabs -->
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <div class="inline-flex rounded-md border border-[var(--border)] bg-[var(--surface)] p-1 text-sm">
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-md transition-colors"
                    :class="tab === 'eligible' ? 'bg-[var(--primary-soft)] text-[var(--primary)]' : 'text-[var(--muted)] hover:text-[var(--ink)]'"
                    @click="tab = 'eligible'"
                >
                    Eligible ({{ results.length }})
                </button>
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-md transition-colors"
                    :class="tab === 'ineligible' ? 'bg-[var(--primary-soft)] text-[var(--primary)]' : 'text-[var(--muted)] hover:text-[var(--ink)]'"
                    @click="tab = 'ineligible'"
                >
                    Tidak eligible ({{ ineligible.length }})
                </button>
            </div>
            <div class="relative flex-1 min-w-[200px]">
                <Search :size="14" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--muted)]" />
                <Input v-model="q" class="pl-7" placeholder="Cari nama / NIM…" />
            </div>
        </div>

        <!-- Tabel Eligible -->
        <div v-if="tab === 'eligible'" class="window mt-4">
            <div class="window-bar">
                <span class="window-title text-xs">Ranking ({{ filteredResults.length }} kandidat)</span>
            </div>
            <div class="window-body p-0 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)] bg-[var(--surface)]">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3 text-right">Skor Z</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="r in filteredResults" :key="r.student_id" class="rank-row">
                            <td class="px-4 py-3 rank-pos mono">{{ r.rank }}</td>
                            <td class="px-4 py-3 font-medium">{{ r.name }}</td>
                            <td class="px-4 py-3 text-[var(--muted)] mono">{{ r.nim }}</td>
                            <td class="px-4 py-3 text-right mono tnum">{{ Number(r.score).toFixed(2) }}</td>
                            <td class="px-4 py-3"><span class="tag capitalize" :class="categoryVariant(r.category)">{{ r.category }}</span></td>
                            <td class="px-4 py-3">
                                <Link :href="route('admin.selection.audit', { batch: batch.id, candidate: r.student_id })" class="text-[var(--primary)] hover:underline text-sm">Audit</Link>
                            </td>
                        </tr>
                        <tr v-if="!filteredResults.length">
                            <td colspan="6" class="px-4 py-10 text-center text-[var(--muted)]">
                                Belum ada hasil ranking.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Tidak Eligible + alasan -->
        <div v-else class="window mt-4">
            <div class="window-bar">
                <span class="window-title text-xs">Tidak eligible ({{ filteredIneligible.length }})</span>
            </div>
            <div class="window-body p-0 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)] bg-[var(--surface)]">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <ListChecks :size="12" />
                                    Alasan
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="row in filteredIneligible" :key="row.student_id">
                            <td class="px-4 py-3 font-medium">{{ row.name }}</td>
                            <td class="px-4 py-3 text-[var(--muted)] mono">{{ row.nim }}</td>
                            <td class="px-4 py-3 text-[13px]">
                                <ul class="space-y-1">
                                    <li v-for="(r, i) in reasonList(row)" :key="i" class="flex items-start gap-2">
                                        <XCircle :size="13" class="mt-0.5 shrink-0 text-[var(--danger)]" />
                                        <span class="text-[var(--muted)]">{{ r }}</span>
                                    </li>
                                    <li v-if="!reasonList(row).length" class="text-[var(--muted)] italic">Tidak ada alasan tercatat.</li>
                                </ul>
                            </td>
                        </tr>
                        <tr v-if="!filteredIneligible.length">
                            <td colspan="3" class="px-4 py-10 text-center text-[var(--muted)]">
                                Tidak ada kandidat ineligible.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </AdminLayout>
</template>
