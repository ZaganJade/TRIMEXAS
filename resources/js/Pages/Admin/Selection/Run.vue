<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
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

function applyFilters() {
    router.get(
        route("admin.selection.run"),
        { q: q.value || undefined, batch_id: batchId.value || undefined },
        { preserveState: true, replace: true }
    );
}

function clearFilters() {
    q.value = "";
    batchId.value = null;
    applyFilters();
}

watch([q], () => applyFilters());

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
        <div class="space-y-6">
        <header class="reveal-stagger" style="--delay: 0ms">
            <h1 class="display text-[clamp(1.8rem,4vw,2.4rem)] text-[var(--ink)]">
                Jalankan Seleksi
            </h1>
            <p class="mt-2 text-[14.5px] text-[var(--muted)]">
                Snapshot parameter himpunan fuzzy, rules, dan thresholds akan dibekukan saat batch dimulai
                sehingga ranking historis tidak terpengaruh perubahan parameter.
            </p>
        </header>

        <!-- Status ringkas kandidat -->
        <div class="bento-grid mt-6" style="--delay: 40ms">
            <Card variant="elevated" class="b-4 p-6">
                <div class="flex items-center gap-3">
                    <span class="bento-icon" style="background: var(--primary-soft); color: var(--primary)">
                        <Users :size="18" />
                    </span>
                    <div>
                        <p class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Kandidat ter-approve</p>
                        <p class="display text-2xl text-[var(--ink)] tnum">{{ candidateCount }}</p>
                    </div>
                </div>
                <p class="mt-2 text-[12.5px] text-[var(--muted)]">
                    Mahasiswa approved yang akan diproses saat batch baru dijalankan.
                </p>
            </Card>
            <Card variant="elevated" class="b-4 p-6">
                <div class="flex items-center gap-3">
                    <span class="bento-icon" style="background: color-mix(in oklab, var(--accent) 18%, transparent); color: var(--accent)">
                        <Play :size="18" />
                    </span>
                    <div>
                        <p class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Total batch berjalan</p>
                        <p class="display text-2xl text-[var(--ink)] tnum">{{ batches.length }}</p>
                    </div>
                </div>
                <p class="mt-2 text-[12.5px] text-[var(--muted)]">
                    Batch yang sudah / sedang berjalan. Riwayat lengkap ada di panel kanan.
                </p>
            </Card>
        </div>

        <div class="bento-grid mt-6" style="--delay: 100ms">
            <!-- Form batch baru -->
            <Card variant="elevated" class="b-4 p-6">
                <h2 class="display-md flex items-center gap-2 text-[1.15rem] text-[var(--ink)]">
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
            <Card variant="outline" class="b-4 overflow-hidden">
                <div class="window-bar">
                    <span class="window-title text-xs">Riwayat batch</span>
                    <span class="ml-auto mono text-[10px] text-[var(--muted)]">{{ batches.length }} entri</span>
                </div>
                <div class="p-4 space-y-3 border-b border-[var(--border)]">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr,200px]">
                        <div class="space-y-1.5">
                            <Label for="search-q">Cari (label / periode / tahun)</Label>
                            <div class="relative">
                                <Search :size="14" class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--muted)]" />
                                <Input id="search-q" v-model="q" class="pl-7" placeholder="contoh: genap 2026" />
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="batch-pick">Filter batch</Label>
                            <Select
                                id="batch-pick"
                                :model-value="batchId"
                                @update:model-value="(v) => { batchId = v ? Number(v) : null; applyFilters(); }"
                                :options="batchOptions"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <Button size="sm" variant="ghost" type="button" @click="clearFilters">
                            <FilterX :size="13" class="mr-1.5" />
                            Reset
                        </Button>
                    </div>
                </div>
                <ul v-if="batches.length" class="divide-y divide-[var(--border)] max-h-[420px] overflow-auto">
                    <li v-for="b in batches" :key="b.id" class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ b.label }}</p>
                            <p class="text-xs text-[var(--muted)] mono">
                                <span v-if="b.periode">{{ b.periode }} · </span>
                                <span v-if="b.tahun_akademik">TA {{ b.tahun_akademik }} · </span>
                                {{ new Date(b.created_at).toLocaleString("id-ID") }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="tag mono text-xs uppercase" :class="statusVariant(b.status)">{{ b.status }}</span>
                            <span v-if="b.total_candidates != null" class="mono text-xs text-[var(--muted)]">
                                {{ b.total_eligible ?? 0 }}/{{ b.total_candidates }} eligible
                            </span>
                            <Link :href="route('admin.selection.show', b.id)" class="text-sm text-[var(--primary)] hover:underline">
                                Lihat
                            </Link>
                        </div>
                    </li>
                </ul>
                <div v-else class="px-4 py-10 text-center text-sm text-[var(--muted)]">
                    Tidak ada batch yang cocok dengan filter.
                </div>
            </Card>
        </div>

        <Card variant="outline" class="mt-6 p-6">
            <div class="flex items-start gap-3">
                <Info :size="16" class="mt-0.5 shrink-0 text-[var(--primary)]" />
                <div class="text-[13px] text-[var(--muted)]">
                    <p class="font-medium text-[var(--ink)]">Alur seleksi (Tsukamoto)</p>
                    <ol class="mt-1 list-decimal pl-5 space-y-0.5">
                        <li>4 gate: <span class="mono">status=aktif</span>, <span class="mono">semester≤6</span>, <span class="mono">IPK≥3.00</span>, <span class="mono">akun=approved</span></li>
                        <li>Fuzzifier: 5 kriteria × 3 himpunan fuzzy → nilai μ</li>
                        <li>Inference: 75 rule Tsukamoto, hitung α (min) dan z (monotonik per consequent)</li>
                        <li>Defuzzifier: weighted average → skor Z (0..100)</li>
                        <li>Kategori: <span class="mono">Z &lt; threshold_1</span> = tidak_layak, <span class="mono">≤ threshold_2</span> = dipertimbangkan, else = layak</li>
                    </ol>
                </div>
            </div>
        </Card>
        </div>
    </AdminLayout>
</template>
