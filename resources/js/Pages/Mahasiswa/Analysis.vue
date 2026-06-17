<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import MahasiswaLayout from "@/Layouts/MahasiswaLayout.vue";
import {
    ArrowLeft,
    ChevronDown,
    FileSearch,
    LineChart,
    Scale,
    Sparkles,
    Target,
    Zap,
} from "@lucide/vue";

const props = defineProps({
    batch: { type: Object, default: null },
    candidate: { type: Object, default: null },
    result: { type: Object, default: null },
    evaluations: { type: Array, default: () => [] },
    rules: { type: Array, default: () => [] },
});

const showAllRules = ref(false);
const expandedRule = ref(null);

const CRITERION_LABELS = {
    ipk: "IPK",
    penghasilan: "Penghasilan Orang Tua",
    prestasi_akademis: "Prestasi Akademis",
    prestasi_non_akademis: "Prestasi Non-Akademis",
    tanggungan: "Tanggungan Keluarga",
};

const CONSEQUENT_META = {
    layak: { label: "Layak", tag: "tag-success" },
    dipertimbangkan: { label: "Dipertimbangkan", tag: "tag-warning" },
    tidak_layak: { label: "Tidak Layak", tag: "tag-error" },
};

const hasAnalysis = computed(() => props.batch && props.result);

const snapshot = computed(() => props.result?.input_snapshot ?? {});
const memberships = computed(() => snapshot.value.memberships ?? null);
const thresholds = computed(() => props.batch?.thresholds ?? {});

const threshold1 = computed(() => Number(thresholds.value.threshold_1 ?? 50));
const threshold2 = computed(() => Number(thresholds.value.threshold_2 ?? 75));
const scoreValue = computed(() => {
    const raw = props.result?.score;
    return raw === null || raw === undefined ? null : Number(raw);
});

const categoryMeta = computed(() => {
    const key = props.result?.category ?? "tidak_layak";
    return CONSEQUENT_META[key] ?? { label: key, tag: "tag-primary" };
});

const scorePosition = computed(() => {
    if (scoreValue.value === null) return 0;
    return Math.max(0, Math.min(100, scoreValue.value));
});

const rulesCatalog = computed(() => {
    if (props.rules.length) return props.rules;

    return props.evaluations.map((evaluation) => ({
        code: evaluation.rule_code,
        antecedents: {},
        consequent: evaluation.consequent,
        description: null,
        alpha: Number(evaluation.alpha),
        z: Number(evaluation.z),
        fired: Number(evaluation.alpha) > 0,
    }));
});

const firedRules = computed(() => rulesCatalog.value.filter((rule) => rule.fired));
const visibleRules = computed(() => (showAllRules.value ? rulesCatalog.value : firedRules.value));

const crispInputs = computed(() => {
    const rows = [];
    const data = snapshot.value;

    if (data.ipk !== undefined) rows.push({ key: "ipk", label: CRITERION_LABELS.ipk, value: Number(data.ipk).toFixed(2) });
    if (data.penghasilan !== undefined) rows.push({ key: "penghasilan", label: CRITERION_LABELS.penghasilan, value: formatCurrency(data.penghasilan) });
    if (data.prestasi_akademis !== undefined) rows.push({ key: "prestasi_akademis", label: CRITERION_LABELS.prestasi_akademis, value: String(data.prestasi_akademis) });
    if (data.prestasi_non_akademis !== undefined) rows.push({ key: "prestasi_non_akademis", label: CRITERION_LABELS.prestasi_non_akademis, value: String(data.prestasi_non_akademis) });
    if (data.tanggungan !== undefined) rows.push({ key: "tanggungan", label: CRITERION_LABELS.tanggungan, value: String(data.tanggungan) });

    return rows;
});

const membershipRows = computed(() => {
    if (!memberships.value) return [];

    return Object.entries(memberships.value).map(([criterion, sets]) => {
        const entries = Object.entries(sets ?? {})
            .map(([name, degree]) => ({ name, degree: Number(degree) }))
            .sort((a, b) => b.degree - a.degree);

        return {
            criterion,
            label: CRITERION_LABELS[criterion] ?? criterion,
            entries,
            dominant: entries[0] ?? null,
        };
    });
});

const executedLabel = computed(() => {
    const raw = props.batch?.completed_at ?? props.batch?.started_at;
    if (!raw) return null;
    return new Date(raw).toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" });
});

function formatCurrency(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(Number(value));
}

function labelize(value) {
    return String(value ?? "").replaceAll("_", " ");
}

function formatAlpha(value) {
    return Number(value).toFixed(3);
}

function alphaWidth(alpha) {
    return `${Math.max(4, Math.min(100, Number(alpha) * 100))}%`;
}

function toggleRule(code) {
    expandedRule.value = expandedRule.value === code ? null : code;
}
</script>

<template>
    <Head title="Mahasiswa · Hasil Analisa" />

    <MahasiswaLayout active="analysis">
        <div class="analysis-page space-y-7">
            <header class="reveal-stagger" style="--delay: 0ms">
                <Link :href="route('mahasiswa.dashboard')" class="back-link">
                    <ArrowLeft :size="16" />
                    Kembali ke dashboard
                </Link>
                <span class="section-label mt-4 block">Hasil Analisa Seleksi</span>
                <h1 class="display mt-3 text-[clamp(1.8rem,4vw,2.6rem)] text-[var(--ink)]">
                    <template v-if="hasAnalysis">Laporan analisa <span class="text-gradient">Anda</span></template>
                    <template v-else>Belum ada hasil analisa</template>
                </h1>
                <p v-if="hasAnalysis" class="mt-2 text-[15px] text-[var(--muted)]">
                    Batch <strong class="text-[var(--foreground)]">{{ batch.label }}</strong>
                    <span v-if="executedLabel"> · diproses {{ executedLabel }}</span>
                </p>
            </header>

            <section v-if="!hasAnalysis" class="bento col-8 reveal-stagger" style="--delay: 80ms">
                <div class="flex items-start gap-4">
                    <span class="bento-icon shrink-0"><LineChart :size="20" /></span>
                    <div>
                        <h2 class="display-md text-[1.15rem] text-[var(--ink)]">Menunggu proses seleksi</h2>
                        <p class="mt-1.5 text-[14.5px] leading-relaxed text-[var(--muted)]">
                            Hasil analisa akan muncul di sini setelah admin menjalankan batch seleksi.
                            Anda juga akan menerima notifikasi saat data sudah siap.
                        </p>
                    </div>
                </div>
            </section>

            <template v-else>
                <section v-if="!result.eligible" class="ineligible-banner reveal-stagger" style="--delay: 80ms" aria-live="polite">
                    <div class="ineligible-icon" aria-hidden="true">
                        <Scale :size="20" />
                    </div>
                    <div>
                        <h2 class="display-md text-[1.1rem] text-[var(--ink)]">Tidak Memenuhi Syarat Seleksi</h2>
                        <p class="mt-1 text-[14px] text-[var(--muted)]">
                            Data Anda tidak melanjutkan ke perhitungan fuzzy karena gagal prasyarat eligibility.
                        </p>
                        <ul class="mt-3 space-y-2">
                            <li v-for="reason in result.ineligibility_reasons" :key="reason" class="flex gap-2 text-[13.5px] text-[var(--muted)]">
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[var(--warning)]" />
                                {{ reason }}
                            </li>
                        </ul>
                    </div>
                </section>

                <section v-else class="verdict-grid reveal-stagger" style="--delay: 80ms">
                    <article class="window">
                        <div class="window-bar">
                            <span class="window-dot" style="background:#fb7185"></span>
                            <span class="window-dot" style="background:#fbbf24"></span>
                            <span class="window-dot" style="background:#34d399"></span>
                            <span class="window-title">skor-akhir — {{ batch.label }}</span>
                        </div>
                        <div class="window-body !p-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="mono text-[10px] uppercase tracking-[0.18em] text-[var(--muted)]">Skor Akhir · Tsukamoto</p>
                                    <p class="score-display tnum mt-2">{{ scoreValue?.toFixed?.(2) ?? scoreValue }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="tag capitalize" :class="categoryMeta.tag">{{ categoryMeta.label }}</span>
                                    <span v-if="result.rank" class="tag tag-primary mono">Rank #{{ result.rank }}</span>
                                </div>
                            </div>

                            <div class="threshold-track mt-6" role="img" :aria-label="`Skor ${scoreValue} pada skala 0-100`">
                                <div class="threshold-zones">
                                    <span class="zone zone-low">0 – {{ threshold1 }}</span>
                                    <span class="zone zone-mid">{{ threshold1 }} – {{ threshold2 }}</span>
                                    <span class="zone zone-high">{{ threshold2 }} – 100</span>
                                </div>
                                <div class="threshold-bar">
                                    <div class="threshold-marker t1" :style="{ left: `${threshold1}%` }"><span class="marker-label mono">T₁</span></div>
                                    <div class="threshold-marker t2" :style="{ left: `${threshold2}%` }"><span class="marker-label mono">T₂</span></div>
                                    <div class="score-pin" :style="{ left: `${scorePosition}%` }"><span class="score-pin-dot" /></div>
                                </div>
                            </div>

                            <p class="mt-4 text-[13px] leading-relaxed text-[var(--muted)]">
                                Skor dihitung dari agregasi rule fuzzy yang aktif (α &gt; 0).
                                T₁ = {{ threshold1 }} (batas layak), T₂ = {{ threshold2 }} (batas dipertimbangkan).
                            </p>
                        </div>
                    </article>

                    <div class="insight-grid">
                        <article class="bento">
                            <span class="bento-icon"><Zap :size="18" /></span>
                            <p class="display-md mt-4 text-[1.6rem] tnum">{{ firedRules.length }}</p>
                            <p class="text-[13px] text-[var(--muted)]">Rule aktif</p>
                        </article>
                        <article class="bento">
                            <span class="bento-icon"><Target :size="18" /></span>
                            <p class="display-md mt-4 text-[1.6rem] tnum">{{ rulesCatalog.length }}</p>
                            <p class="text-[13px] text-[var(--muted)]">Rule dalam snapshot</p>
                        </article>
                        <article class="bento">
                            <span class="bento-icon"><Sparkles :size="18" /></span>
                            <p class="display-md mt-4 text-[1.05rem] capitalize">{{ categoryMeta.label }}</p>
                            <p class="text-[13px] text-[var(--muted)]">Kategori output</p>
                        </article>
                    </div>
                </section>

                <section class="grid gap-5 lg:grid-cols-2 reveal-stagger" style="--delay: 160ms">
                    <article class="window">
                        <div class="window-bar">
                            <span class="window-dot" style="background:#fb7185"></span>
                            <span class="window-dot" style="background:#fbbf24"></span>
                            <span class="window-dot" style="background:#34d399"></span>
                            <span class="window-title">input-crisp.json</span>
                        </div>
                        <div class="window-body !p-5">
                            <h2 class="display-md text-[1rem] text-[var(--ink)]">Data Input Anda</h2>
                            <p class="mt-1 text-[13px] text-[var(--muted)]">Nilai mentah yang dipakai dalam perhitungan fuzzy.</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div v-for="row in crispInputs" :key="row.key" class="input-chip">
                                    <span class="input-label">{{ row.label }}</span>
                                    <span class="input-value mono tnum">{{ row.value }}</span>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="window">
                        <div class="window-bar">
                            <span class="window-dot" style="background:#fb7185"></span>
                            <span class="window-dot" style="background:#fbbf24"></span>
                            <span class="window-dot" style="background:#34d399"></span>
                            <span class="window-title">fuzzifikasi.json</span>
                        </div>
                        <div class="window-body !p-5">
                            <h2 class="display-md text-[1rem] text-[var(--ink)]">Fuzzifikasi</h2>
                            <p class="mt-1 text-[13px] text-[var(--muted)]">Derajat keanggotaan (μ) tiap himpunan fuzzy.</p>

                            <div v-if="membershipRows.length" class="mt-4 space-y-4">
                                <div v-for="row in membershipRows" :key="row.criterion" class="membership-block">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h3 class="text-[13px] font-medium text-[var(--ink)]">{{ row.label }}</h3>
                                        <span v-if="row.dominant" class="text-[11px] text-[var(--muted)]">
                                            Dominan: <strong>{{ labelize(row.dominant.name) }}</strong>
                                            <span class="mono">({{ formatAlpha(row.dominant.degree) }})</span>
                                        </span>
                                    </div>
                                    <div class="mt-2 space-y-2">
                                        <div v-for="entry in row.entries" :key="`${row.criterion}-${entry.name}`" class="membership-row">
                                            <span class="membership-set">{{ labelize(entry.name) }}</span>
                                            <div class="membership-track">
                                                <div
                                                    class="membership-fill"
                                                    :class="{ 'is-dominant': entry.name === row.dominant?.name }"
                                                    :style="{ width: alphaWidth(entry.degree) }"
                                                />
                                            </div>
                                            <span class="membership-degree mono tnum">{{ formatAlpha(entry.degree) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="mt-4 text-[13px] text-[var(--muted)]">Data fuzzifikasi tidak tersedia.</p>
                        </div>
                    </article>
                </section>

                <section class="window reveal-stagger" style="--delay: 240ms">
                    <div class="window-bar">
                        <span class="window-dot" style="background:#fb7185"></span>
                        <span class="window-dot" style="background:#fbbf24"></span>
                        <span class="window-dot" style="background:#34d399"></span>
                        <span class="window-title">rule-evaluations.json</span>
                    </div>
                    <div class="window-body !p-5">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <h2 class="display-md text-[1rem] text-[var(--ink)]">Rule yang Dipakai</h2>
                                <p class="mt-1 text-[13px] text-[var(--muted)]">
                                    Menampilkan {{ visibleRules.length }} rule
                                    {{ showAllRules ? "dari snapshot batch" : "yang aktif (α > 0)" }}.
                                </p>
                            </div>
                            <button type="button" class="toggle-btn" @click="showAllRules = !showAllRules">
                                <ChevronDown :size="14" :class="{ 'rotate-180': showAllRules }" />
                                {{ showAllRules ? "Hanya rule aktif" : "Tampilkan semua rule" }}
                            </button>
                        </div>

                        <div v-if="visibleRules.length" class="mt-4 space-y-2">
                            <article
                                v-for="rule in visibleRules"
                                :key="rule.code"
                                class="rule-card"
                                :class="{ 'is-fired': rule.fired }"
                            >
                                <button type="button" class="rule-head" @click="toggleRule(rule.code)">
                                    <div class="min-w-0 text-left">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="mono text-[12px] font-medium text-[var(--ink)]">{{ rule.code }}</span>
                                            <span v-if="rule.fired" class="tag tag-success">Aktif</span>
                                            <span v-else class="tag">Tidak aktif</span>
                                        </div>
                                        <p v-if="rule.description" class="mt-1 text-[13px] text-[var(--muted)]">{{ rule.description }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span v-if="rule.fired" class="mono text-[12px] tnum text-[var(--primary)]">α {{ formatAlpha(rule.alpha) }}</span>
                                        <ChevronDown :size="16" class="text-[var(--muted)] transition-transform" :class="{ 'rotate-180': expandedRule === rule.code }" />
                                    </div>
                                </button>
                                <div v-if="expandedRule === rule.code" class="rule-body">
                                    <p class="text-[12px] text-[var(--muted)]">
                                        Konsekuen: <strong class="text-[var(--foreground)]">{{ labelize(rule.consequent) }}</strong>
                                        <span v-if="rule.fired"> · Z = <span class="mono">{{ Number(rule.z).toFixed(2) }}</span></span>
                                    </p>
                                </div>
                            </article>
                        </div>
                        <p v-else class="mt-4 text-[13px] text-[var(--muted)]">Tidak ada rule yang aktif untuk profil Anda.</p>
                    </div>
                </section>

                <section class="bento col-8 reveal-stagger" style="--delay: 320ms">
                    <div class="flex items-start gap-4">
                        <span class="bento-icon shrink-0"><FileSearch :size="20" /></span>
                        <div>
                            <h2 class="display-md text-[1.05rem] text-[var(--ink)]">Bagaimana hasil ini diperoleh?</h2>
                            <p class="mt-1.5 text-[14px] leading-relaxed text-[var(--muted)]">
                                Sistem mengambil profil dan prestasi Anda, melakukan fuzzifikasi tiap kriteria,
                                mengevaluasi rule fuzzy dari snapshot batch, lalu mengagregasi skor dengan metode Tsukamoto.
                                Kategori akhir ditentukan oleh perbandingan skor dengan threshold T₁ dan T₂ batch ini.
                            </p>
                        </div>
                    </div>
                </section>
            </template>
        </div>
    </MahasiswaLayout>
</template>

<style scoped>
.analysis-page {
    --analysis-border: color-mix(in oklab, var(--border) 88%, transparent);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.82rem;
    color: var(--muted);
    transition: color 0.2s ease;
}

.back-link:hover {
    color: var(--primary);
}

.ineligible-banner {
    display: flex;
    gap: 1rem;
    padding: 1.25rem 1.35rem;
    border-radius: var(--radius-card);
    border: 1px solid color-mix(in oklab, var(--warning) 35%, var(--border));
    background: color-mix(in oklab, var(--warning) 8%, var(--surface));
}

.ineligible-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    color: var(--warning);
    background: color-mix(in oklab, var(--warning) 14%, transparent);
}

.verdict-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
}

.insight-grid {
    display: grid;
    gap: 0.85rem;
}

.score-display {
    font-size: clamp(2.4rem, 6vw, 3.2rem);
    line-height: 1;
    font-weight: 650;
    color: var(--ink);
}

.threshold-track {
    margin-top: 0.5rem;
}

.threshold-zones {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.35rem;
    margin-bottom: 0.45rem;
}

.zone {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
}

.threshold-bar {
    position: relative;
    height: 0.55rem;
    border-radius: 999px;
    background: linear-gradient(
        90deg,
        color-mix(in oklab, var(--danger) 35%, transparent) 0%,
        color-mix(in oklab, var(--warning) 35%, transparent) 50%,
        color-mix(in oklab, var(--success) 35%, transparent) 100%
    );
}

.threshold-marker {
    position: absolute;
    top: -0.35rem;
    transform: translateX(-50%);
}

.marker-label {
    font-size: 0.62rem;
    color: var(--muted);
}

.score-pin {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
}

.score-pin-dot {
    display: block;
    width: 0.85rem;
    height: 0.85rem;
    border-radius: 999px;
    border: 2px solid var(--surface);
    background: var(--primary);
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--primary) 25%, transparent);
}

.input-chip {
    padding: 0.75rem 0.85rem;
    border-radius: 0.85rem;
    border: 1px solid var(--analysis-border);
    background: var(--surface-2);
}

.input-label {
    display: block;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--muted);
}

.input-value {
    display: block;
    margin-top: 0.35rem;
    font-size: 1rem;
    color: var(--ink);
}

.membership-row {
    display: grid;
    grid-template-columns: 6.5rem minmax(0, 1fr) 3rem;
    gap: 0.55rem;
    align-items: center;
}

.membership-set {
    font-size: 0.72rem;
    color: var(--muted);
    text-transform: capitalize;
}

.membership-track {
    height: 0.45rem;
    border-radius: 999px;
    background: color-mix(in oklab, var(--border) 70%, transparent);
    overflow: hidden;
}

.membership-fill {
    height: 100%;
    border-radius: inherit;
    background: color-mix(in oklab, var(--primary) 55%, transparent);
}

.membership-fill.is-dominant {
    background: var(--primary);
}

.membership-degree {
    font-size: 0.72rem;
    text-align: right;
    color: var(--muted);
}

.toggle-btn,
.rule-head {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    color: var(--primary);
}

.rule-card {
    border: 1px solid var(--analysis-border);
    border-radius: 0.85rem;
    overflow: hidden;
    background: var(--surface);
}

.rule-card.is-fired {
    border-color: color-mix(in oklab, var(--success) 30%, var(--border));
}

.rule-head {
    width: 100%;
    justify-content: space-between;
    padding: 0.85rem 1rem;
    text-align: left;
    transition: background-color 0.2s ease;
}

.rule-head:hover {
    background: color-mix(in oklab, var(--primary-soft) 45%, transparent);
}

.rule-body {
    padding: 0 1rem 0.85rem;
}

@media (max-width: 960px) {
    .verdict-grid {
        grid-template-columns: 1fr;
    }
}
</style>
