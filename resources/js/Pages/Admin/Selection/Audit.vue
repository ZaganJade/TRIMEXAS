<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import {
    ArrowLeft,
    ChevronDown,
    Download,
    FileSearch,
    Info,
    Scale,
    Sparkles,
    Target,
    Zap,
} from "@lucide/vue";

const props = defineProps({
    batch: { type: Object, required: true },
    candidate: { type: Object, required: true },
    result: { type: Object, required: true },
    evaluations: { type: Array, default: () => [] },
    rules: { type: Array, default: () => [] },
});

const showAllRules = ref(false);
const showRawJson = ref(false);
const expandedRule = ref(null);

const CRITERION_LABELS = {
    ipk: "IPK",
    penghasilan: "Penghasilan Orang Tua",
    prestasi_akademis: "Prestasi Akademis",
    prestasi_non_akademis: "Prestasi Non-Akademis",
    tanggungan: "Tanggungan Keluarga",
};

const CONSEQUENT_META = {
    layak: { label: "Layak", tag: "tag-success", tone: "success" },
    dipertimbangkan: { label: "Dipertimbangkan", tag: "tag-warning", tone: "warning" },
    tidak_layak: { label: "Tidak Layak", tag: "audit-tag-danger", tone: "danger" },
};

const snapshot = computed(() => props.result.input_snapshot ?? {});
const memberships = computed(() => snapshot.value.memberships ?? null);
const thresholds = computed(() => props.batch.thresholds ?? {});

const threshold1 = computed(() => Number(thresholds.value.threshold_1 ?? 50));
const threshold2 = computed(() => Number(thresholds.value.threshold_2 ?? 75));
const scoreValue = computed(() => {
    const raw = props.result.score;
    return raw === null || raw === undefined ? null : Number(raw);
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

    if (data.ipk !== undefined) {
        rows.push({ key: "ipk", label: CRITERION_LABELS.ipk, value: Number(data.ipk).toFixed(2), unit: "" });
    }
    if (data.penghasilan !== undefined) {
        rows.push({
            key: "penghasilan",
            label: CRITERION_LABELS.penghasilan,
            value: formatCurrency(data.penghasilan),
            unit: "",
        });
    }
    if (data.prestasi_akademis !== undefined) {
        rows.push({
            key: "prestasi_akademis",
            label: CRITERION_LABELS.prestasi_akademis,
            value: String(data.prestasi_akademis),
            unit: " poin",
        });
    }
    if (data.prestasi_non_akademis !== undefined) {
        rows.push({
            key: "prestasi_non_akademis",
            label: CRITERION_LABELS.prestasi_non_akademis,
            value: String(data.prestasi_non_akademis),
            unit: " poin",
        });
    }
    if (data.tanggungan !== undefined) {
        rows.push({
            key: "tanggungan",
            label: CRITERION_LABELS.tanggungan,
            value: String(data.tanggungan),
            unit: " orang",
        });
    }

    return rows;
});

const membershipRows = computed(() => {
    if (!memberships.value) return [];

    return Object.entries(memberships.value).map(([criterion, sets]) => {
        const entries = Object.entries(sets ?? {})
            .map(([name, degree]) => ({ name, degree: Number(degree) }))
            .sort((a, b) => b.degree - a.degree);

        const dominant = entries[0] ?? null;

        return {
            criterion,
            label: CRITERION_LABELS[criterion] ?? criterion,
            entries,
            dominant,
        };
    });
});

const categoryMeta = computed(() => {
    const key = props.result.category ?? props.result.status;
    return CONSEQUENT_META[key] ?? { label: key ?? "—", tag: "tag-primary", tone: "primary" };
});

const scorePosition = computed(() => {
    if (scoreValue.value === null) return 0;
    return Math.max(0, Math.min(100, scoreValue.value));
});

const executedLabel = computed(() => {
    if (!props.batch.started_at) return null;
    return new Intl.DateTimeFormat("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(props.batch.started_at));
});

/* Narasi penjelasan skor (bahasa deskriptif) — disamakan dengan halaman
   analisa mahasiswa. Diturunkan dari data asli (keanggotaan, aturan
   yang cocok, posisi skor) agar selalu sesuai dengan skor. */

// Tiga ciri profil kandidat yang paling memengaruhi skor.
const topTraits = computed(() =>
    membershipRows.value
        .filter((row) => row.dominant && Number(row.dominant.degree) > 0)
        .map((row) => ({
            label: row.label,
            set: labelize(row.dominant.name),
            percent: Math.round(Number(row.dominant.degree) * 100),
        }))
        .sort((a, b) => b.percent - a.percent)
        .slice(0, 3),
);

// Bagaimana pertimbangan terbagi ke tiap keputusan (Layak / Dipertimbangkan / Belum Layak), dalam persen.
const evidenceShare = computed(() => {
    const groups = {};
    let total = 0;
    for (const rule of firedRules.value) {
        const key = rule.consequent || "tidak_layak";
        groups[key] = (groups[key] || 0) + (Number(rule.alpha) || 0);
        total += Number(rule.alpha) || 0;
    }
    if (total === 0) return [];
    return Object.entries(groups)
        .map(([consequent, alpha]) => ({
            consequent,
            label: (CONSEQUENT_META[consequent] ?? { label: labelize(consequent) }).label,
            share: Math.round((alpha / total) * 100),
        }))
        .sort((a, b) => b.share - a.share);
});

// Letak skor dibanding batas penilaian, dalam kalimat yang mudah dipahami.
const verdictMargin = computed(() => {
    const s = scoreValue.value;
    if (s === null) return null;
    const t1 = threshold1.value;
    const t2 = threshold2.value;
    if (s >= t2) {
        return { gap: Number(s - t2).toFixed(1), direction: "di atas", boundary: "batas kelayakan" };
    }
    if (s >= t1) {
        return { gap: Number(s - t1).toFixed(1), direction: "di atas", boundary: "batas pertimbangan" };
    }
    return { gap: Number(t1 - s).toFixed(1), direction: "di bawah", boundary: "batas pertimbangan" };
});

function formatCurrency(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(Number(value));
}

function toPercent(value) {
    return `${Math.round(Number(value) * 100)}%`;
}

function formatZ(value) {
    return Number(value).toFixed(2);
}

function labelize(value) {
    if (!value) return "—";
    return String(value).replaceAll("_", " ");
}

function consequentMeta(consequent) {
    return CONSEQUENT_META[consequent] ?? { label: labelize(consequent), tag: "tag-primary", tone: "primary" };
}

function antecedentChips(antecedents) {
    return Object.entries(antecedents ?? {}).map(([criterion, setName]) => ({
        criterion,
        criterionLabel: CRITERION_LABELS[criterion] ?? criterion,
        setName,
        setLabel: labelize(setName),
    }));
}

function toggleRule(code) {
    expandedRule.value = expandedRule.value === code ? null : code;
}

function alphaWidth(alpha) {
    return `${Math.max(4, Math.min(100, Number(alpha) * 100))}%`;
}
</script>

<template>
    <Head :title="`Audit · ${candidate.full_name}`" />
    <AdminLayout active="selection">
        <div class="audit-page">
            <!-- Header -->
            <header class="audit-header reveal">
                <div class="header-main">
                    <Link :href="route('admin.selection.show', batch.id)" class="back-link">
                        <ArrowLeft :size="16" />
                        Kembali ke {{ batch.label }}
                    </Link>
                    <div class="header-title-row">
                        <div class="header-icon" aria-hidden="true">
                            <FileSearch :size="22" />
                        </div>
                        <div class="min-w-0">
                            <p class="eyebrow">Rincian Penilaian Kandidat</p>
                            <h1 class="page-title">{{ candidate.full_name }}</h1>
                            <div class="header-meta">
                                <span class="meta-chip mono">{{ candidate.nim }}</span>
                                <span class="meta-chip">{{ batch.label }}</span>
                                <span v-if="executedLabel" class="meta-chip">Dijalankan {{ executedLabel }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <Button
                        as="a"
                        :href="route('admin.selection.audit.export.pdf', { batch: batch.id, candidate: candidate.id })"
                        variant="secondary"
                        size="sm"
                    >
                        <Download :size="15" />
                        Export PDF
                    </Button>
                </div>
            </header>

            <!-- Ineligible state -->
            <section v-if="!result.eligible" class="ineligible-banner reveal reveal-delay-1" aria-live="polite">
                <div class="ineligible-icon" aria-hidden="true">
                    <Scale :size="20" />
                </div>
                <div>
                    <h2 class="ineligible-title">Tidak Memenuhi Syarat Seleksi</h2>
                    <p class="ineligible-copy">
                        Kandidat tidak lanjut ke penilaian karena belum memenuhi syarat awal.
                    </p>
                    <ul class="reason-list">
                        <li v-for="reason in result.ineligibility_reasons" :key="reason" class="reason-item">
                            <span class="reason-dot" />
                            {{ reason }}
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Verdict hero -->
            <section v-else class="verdict-grid reveal reveal-delay-1">
                <Card variant="elevated" class="verdict-card">
                    <div class="verdict-top">
                        <div>
                            <p class="eyebrow">Skor Kelayakan</p>
                            <p class="score-display tnum">{{ scoreValue?.toFixed?.(2) ?? scoreValue }}</p>
                        </div>
                        <div class="verdict-badges">
                            <span class="tag capitalize" :class="categoryMeta.tag">{{ categoryMeta.label }}</span>
                            <span v-if="result.rank" class="rank-pill mono">Rank #{{ result.rank }}</span>
                        </div>
                    </div>

                    <div class="threshold-track" role="img" :aria-label="`Skor ${scoreValue} dari 100`">
                        <div class="threshold-zones">
                            <span class="zone zone-low">Belum layak</span>
                            <span class="zone zone-mid">Dipertimbangkan</span>
                            <span class="zone zone-high">Layak</span>
                        </div>
                        <div class="threshold-bar">
                            <div class="score-pin" :style="{ left: `${scorePosition}%` }">
                                <span class="score-pin-dot" />
                            </div>
                        </div>
                    </div>

                    <div class="score-narrative">
                        <p class="sn-paragraph">
                            Skor kelayakan kandidat <strong>{{ scoreValue?.toFixed?.(2) ?? scoreValue }}</strong> dari 100.
                            Dengan angka ini, profil kandidat dinilai
                            <span class="tag capitalize" :class="categoryMeta.tag">{{ categoryMeta.label }}</span>
                            untuk menerima beasiswa pada batch {{ batch.label }}.
                        </p>

                        <p v-if="topTraits.length" class="sn-paragraph">
                            Beberapa hal yang paling menentukan skor ini adalah
                            <template v-for="(t, i) in topTraits" :key="t.label">
                                <strong>{{ t.label }}</strong> yang tergolong {{ t.set }}
                                <span class="text-[var(--muted)]">({{ t.percent }}%)</span><template v-if="i < topTraits.length - 1">, </template>
                            </template>.
                            Kombinasi ciri-ciri inilah yang menarik hasil ke keputusan akhir kandidat.
                        </p>

                        <p v-if="evidenceShare.length" class="sn-paragraph">
                            Saat menilai, sistem menemukan <strong>{{ firedRules.length }} aturan</strong> penilaian yang cocok dengan profil kandidat.
                            Sebagian besar pertimbangan mengarah ke
                            <strong>{{ evidenceShare[0].label }}</strong> (sekitar {{ evidenceShare[0].share }}%)
                            <template v-if="evidenceShare.length > 1">
                                , dengan sedikit pertimbangan lain ke {{ evidenceShare[1].label }} ({{ evidenceShare[1].share }}%)
                            </template>.
                        </p>

                        <p v-if="verdictMargin" class="sn-paragraph">
                            Skor kandidat berada <strong>{{ verdictMargin.gap }} poin {{ verdictMargin.direction }} {{ verdictMargin.boundary }}</strong>.
                            <template v-if="categoryMeta.label === 'Layak'">Dengan selisih ini, hasil keputusan kandidat tergolong kuat.</template>
                            <template v-else-if="categoryMeta.label === 'Dipertimbangkan'">Kandidat berada di zona yang masih dipertimbangkan, jadi hasil bisa berubah pada batch berikutnya.</template>
                            <template v-else>Profil kandidat belum mencapai batas minimum pada batch ini.</template>
                        </p>
                    </div>
                </Card>

                <div class="insight-cards">
                    <Card variant="outline" class="insight-card">
                        <div class="insight-icon insight-icon-primary">
                            <Zap :size="18" />
                        </div>
                        <p class="insight-value mono tnum">{{ firedRules.length }}</p>
                        <p class="insight-label">Aturan berlaku</p>
                    </Card>
                    <Card variant="outline" class="insight-card">
                        <div class="insight-icon insight-icon-accent">
                            <Target :size="18" />
                        </div>
                        <p class="insight-value mono tnum">{{ rulesCatalog.length }}</p>
                        <p class="insight-label">Total aturan</p>
                    </Card>
                    <Card variant="outline" class="insight-card">
                        <div class="insight-icon insight-icon-success">
                            <Sparkles :size="18" />
                        </div>
                        <p class="insight-value capitalize">{{ categoryMeta.label }}</p>
                        <p class="insight-label">Status</p>
                    </Card>
                </div>
            </section>

            <!-- Input + Fuzzification -->
            <section class="analysis-grid reveal reveal-delay-2">
                <Card variant="elevated" class="panel-card">
                    <div class="panel-head">
                        <h2 class="panel-title">Data Input Kandidat</h2>
                        <p class="panel-desc">Data profil dan prestasi kandidat yang dinilai sistem.</p>
                    </div>
                    <div class="input-grid">
                        <div v-for="row in crispInputs" :key="row.key" class="input-item">
                            <span class="input-label">{{ row.label }}</span>
                            <span class="input-value mono tnum">{{ row.value }}<span class="input-unit">{{ row.unit }}</span></span>
                        </div>
                    </div>
                    <button type="button" class="raw-toggle" @click="showRawJson = !showRawJson">
                        <ChevronDown :size="14" :class="{ 'rotate-180': showRawJson }" />
                        {{ showRawJson ? "Sembunyikan JSON mentah" : "Tampilkan JSON mentah" }}
                    </button>
                    <pre v-if="showRawJson" class="raw-json">{{ JSON.stringify(snapshot, null, 2) }}</pre>
                </Card>

                <Card variant="elevated" class="panel-card">
                    <div class="panel-head">
                        <h2 class="panel-title">Profil Penilaian Kandidat</h2>
                        <p class="panel-desc">Seberapa cocok setiap nilai kandidat dengan kategori tertentu.</p>
                    </div>

                    <div v-if="membershipRows.length" class="membership-list">
                        <article v-for="row in membershipRows" :key="row.criterion" class="membership-block">
                            <div class="membership-head">
                                <h3 class="membership-title">{{ row.label }}</h3>
                                <span v-if="row.dominant" class="dominant-chip">
                                    Paling cocok: <strong>{{ labelize(row.dominant.name) }}</strong>
                                    <span class="mono tnum">({{ toPercent(row.dominant.degree) }})</span>
                                </span>
                            </div>
                            <div class="membership-bars">
                                <div
                                    v-for="entry in row.entries"
                                    :key="`${row.criterion}-${entry.name}`"
                                    class="membership-row"
                                >
                                    <span class="membership-set">{{ labelize(entry.name) }}</span>
                                    <div class="membership-track">
                                        <div
                                            class="membership-fill"
                                            :class="{ 'is-dominant': entry.name === row.dominant?.name }"
                                            :style="{ width: alphaWidth(entry.degree) }"
                                        />
                                    </div>
                                    <span class="membership-degree mono tnum">{{ toPercent(entry.degree) }}</span>
                                </div>
                            </div>
                        </article>
                    </div>
                    <p v-else class="empty-note">Detail profil penilaian belum tersedia untuk kandidat ini.</p>
                </Card>
            </section>

            <!-- Rules -->
            <section class="rules-section reveal reveal-delay-3">
                <div class="rules-head">
                    <div>
                        <h2 class="panel-title">Aturan Penilaian yang Berlaku</h2>
                        <p class="panel-desc">
                            Daftar aturan penilaian pada putaran ini. Hanya aturan yang cocok dengan profil kandidat yang memengaruhi skor akhir.
                        </p>
                    </div>
                    <div class="rules-toolbar">
                        <div class="legend">
                            <span class="legend-item"><span class="legend-dot legend-fired" /> Berlaku</span>
                            <span class="legend-item"><span class="legend-dot legend-idle" /> Tidak berlaku</span>
                        </div>
                        <Button size="sm" variant="ghost" @click="showAllRules = !showAllRules">
                            {{ showAllRules ? "Hanya yang berlaku" : `Tampilkan semua (${rulesCatalog.length})` }}
                        </Button>
                    </div>
                </div>

                <div class="rules-glossary">
                    <Info :size="14" />
                    <p>
                        <strong>Kecocokan</strong> menunjukkan seberapa pas aturan dengan profil kandidat.
                        <strong>Kontribusi skor</strong> adalah tambahan skor dari aturan tersebut.
                    </p>
                </div>

                <div v-if="visibleRules.length" class="rules-list">
                    <article
                        v-for="(rule, index) in visibleRules"
                        :key="rule.code"
                        class="rule-card"
                        :class="{
                            'is-fired': rule.fired,
                            'is-expanded': expandedRule === rule.code,
                        }"
                        :style="{ '--stagger': `${Math.min(index, 8) * 45}ms` }"
                    >
                        <button type="button" class="rule-card-head" @click="toggleRule(rule.code)">
                            <div class="rule-main">
                                <span class="rule-code mono">{{ rule.code }}</span>
                                <span class="tag capitalize" :class="consequentMeta(rule.consequent).tag">
                                    {{ consequentMeta(rule.consequent).label }}
                                </span>
                                <span v-if="rule.fired" class="fired-badge">Berlaku</span>
                            </div>
                            <div class="rule-metrics">
                                <div class="metric">
                                    <span class="metric-label">Kecocokan</span>
                                    <span class="metric-value mono tnum">{{ toPercent(rule.alpha) }}</span>
                                    <div class="metric-bar">
                                        <div class="metric-fill" :style="{ width: alphaWidth(rule.alpha) }" />
                                    </div>
                                </div>
                                <div class="metric metric-z">
                                    <span class="metric-label">Kontribusi</span>
                                    <span class="metric-value mono tnum">{{ formatZ(rule.z) }}</span>
                                </div>
                                <ChevronDown :size="16" class="rule-chevron" :class="{ 'rotate-180': expandedRule === rule.code }" />
                            </div>
                        </button>

                        <div v-if="expandedRule === rule.code" class="rule-detail">
                            <p v-if="rule.description" class="rule-description">{{ rule.description }}</p>
                            <p v-else class="rule-description muted">Deskripsi aturan belum tersedia.</p>

                            <div class="antecedent-grid">
                                <span
                                    v-for="chip in antecedentChips(rule.antecedents)"
                                    :key="`${rule.code}-${chip.criterion}`"
                                    class="antecedent-chip"
                                >
                                    <span class="chip-criterion">{{ chip.criterionLabel }}</span>
                                    <span class="chip-set">{{ chip.setLabel }}</span>
                                </span>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="empty-state">
                    Tidak ada aturan yang cocok dengan filter saat ini.
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
.audit-page {
    max-width: 1120px;
    margin: 0 auto;
    padding-bottom: 48px;
}

/* Header */
.audit-header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 28px;
}

.header-main {
    flex: 1;
    min-width: 0;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    margin-bottom: 14px;
    transition: opacity 0.15s ease;
}

.back-link:hover {
    opacity: 0.8;
}

.header-title-row {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.header-icon {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: var(--primary-soft);
    color: var(--primary);
    border: 1px solid color-mix(in oklab, var(--primary) 24%, transparent);
    flex-shrink: 0;
}

.page-title {
    font-family: var(--font-display);
    font-size: clamp(1.5rem, 2.5vw, 2rem);
    font-weight: 600;
    color: var(--ink);
    line-height: 1.15;
    letter-spacing: -0.02em;
}

.header-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: var(--radius-pill);
    font-size: 12px;
    color: var(--muted);
    background: var(--surface-2);
    border: 1px solid var(--border);
}

.header-actions {
    display: flex;
    gap: 10px;
}

/* Reveal animation */
.reveal {
    animation: auditReveal 0.55s var(--ease-soft) both;
}

.reveal-delay-1 { animation-delay: 0.06s; }
.reveal-delay-2 { animation-delay: 0.12s; }
.reveal-delay-3 { animation-delay: 0.18s; }

@keyframes auditReveal {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Ineligible */
.ineligible-banner {
    display: flex;
    gap: 16px;
    padding: 20px 22px;
    border-radius: var(--radius-card);
    border: 1px solid color-mix(in oklab, var(--danger) 35%, var(--border));
    background: color-mix(in oklab, var(--danger) 8%, var(--surface));
    margin-bottom: 24px;
}

.ineligible-icon {
    display: grid;
    place-items: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: color-mix(in oklab, var(--danger) 14%, transparent);
    color: var(--danger);
    flex-shrink: 0;
}

.ineligible-title {
    font-family: var(--font-display);
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--danger);
}

.ineligible-copy {
    margin-top: 4px;
    font-size: 14px;
    color: var(--muted);
}

.reason-list {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.reason-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 14px;
    color: var(--foreground);
}

.reason-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--danger);
    margin-top: 7px;
    flex-shrink: 0;
}

/* Verdict */
.verdict-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 24px;
}

@media (min-width: 900px) {
    .verdict-grid {
        grid-template-columns: 1.6fr 1fr;
    }
}

.verdict-card {
    padding: 24px;
}

.verdict-top {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.score-display {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 600;
    line-height: 1;
    letter-spacing: -0.03em;
    background: var(--gradient-brand);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-top: 6px;
}

.verdict-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.rank-pill {
    padding: 4px 10px;
    border-radius: var(--radius-pill);
    font-size: 12px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    color: var(--muted);
}

.threshold-track {
    margin-top: 24px;
}

.threshold-zones {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4px;
    margin-bottom: 8px;
}

.zone {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted);
    text-align: center;
}

.zone-low { color: color-mix(in oklab, var(--danger) 70%, var(--muted)); }
.zone-mid { color: color-mix(in oklab, var(--warning) 80%, var(--muted)); }
.zone-high { color: color-mix(in oklab, var(--success) 80%, var(--muted)); }

.threshold-bar {
    position: relative;
    height: 10px;
    border-radius: var(--radius-pill);
    background: linear-gradient(
        90deg,
        color-mix(in oklab, var(--danger) 35%, var(--surface-2)) 0%,
        color-mix(in oklab, var(--danger) 35%, var(--surface-2)) 33%,
        color-mix(in oklab, var(--warning) 35%, var(--surface-2)) 33%,
        color-mix(in oklab, var(--warning) 35%, var(--surface-2)) 66%,
        color-mix(in oklab, var(--success) 35%, var(--surface-2)) 66%,
        color-mix(in oklab, var(--success) 35%, var(--surface-2)) 100%
    );
    border: 1px solid var(--border);
}

.threshold-marker {
    position: absolute;
    top: -6px;
    transform: translateX(-50%);
    height: 22px;
    width: 2px;
    background: var(--foreground);
    opacity: 0.35;
}

.marker-label {
    position: absolute;
    top: -18px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    color: var(--muted);
}

.score-pin {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    z-index: 2;
}

.score-pin-dot {
    display: block;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--primary);
    border: 3px solid var(--surface);
    box-shadow: 0 0 0 2px var(--primary), 0 4px 12px rgba(49, 137, 198, 0.35);
}

.score-narrative {
    margin-top: 12px;
    padding-top: 14px;
    border-top: 1px solid var(--border);
}

.sn-paragraph {
    font-size: 0.875rem;
    line-height: 1.7;
    color: var(--muted);
}

.sn-paragraph + .sn-paragraph {
    margin-top: 0.6rem;
}

.sn-paragraph strong {
    color: var(--foreground);
    font-weight: 600;
}

.sn-paragraph .tag {
    margin: 0 0.1rem;
    vertical-align: middle;
}

.insight-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

@media (min-width: 640px) {
    .insight-cards {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 900px) {
    .insight-cards {
        grid-template-columns: 1fr;
    }
}

.insight-card {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.insight-icon {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 10px;
}

.insight-icon-primary {
    background: var(--primary-soft);
    color: var(--primary);
}

.insight-icon-accent {
    background: color-mix(in oklab, var(--accent) 16%, transparent);
    color: var(--accent-2);
}

.insight-icon-success {
    background: color-mix(in oklab, var(--success) 14%, transparent);
    color: var(--success);
}

.insight-value {
    font-family: var(--font-display);
    font-size: 1.35rem;
    font-weight: 600;
    color: var(--ink);
}

.insight-label {
    font-size: 12px;
    color: var(--muted);
}

/* Analysis panels */
.analysis-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 24px;
}

@media (min-width: 960px) {
    .analysis-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.panel-card {
    padding: 22px;
}

.panel-head {
    margin-bottom: 18px;
}

.panel-title {
    font-family: var(--font-display);
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--ink);
}

.panel-desc {
    margin-top: 4px;
    font-size: 13px;
    color: var(--muted);
    line-height: 1.5;
}

.input-grid {
    display: grid;
    gap: 10px;
}

.input-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    background: var(--surface-2);
    border: 1px solid var(--border);
}

.input-label {
    font-size: 13px;
    color: var(--muted);
}

.input-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
}

.input-unit {
    font-weight: 400;
    color: var(--muted);
    font-size: 12px;
}

.raw-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 14px;
    font-size: 12px;
    color: var(--primary);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.raw-toggle:hover {
    text-decoration: underline;
}

.raw-json {
    margin-top: 10px;
    overflow: auto;
    border-radius: 10px;
    padding: 12px;
    font-size: 11px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    color: var(--foreground);
}

.membership-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.membership-block {
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}

.membership-block:last-child {
    padding-bottom: 0;
    border-bottom: none;
}

.membership-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
}

.membership-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--foreground);
}

.dominant-chip {
    font-size: 11px;
    color: var(--muted);
}

.dominant-chip strong {
    color: var(--primary);
    text-transform: capitalize;
}

.membership-bars {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.membership-row {
    display: grid;
    grid-template-columns: 88px 1fr 44px;
    align-items: center;
    gap: 10px;
}

.membership-set {
    font-size: 12px;
    color: var(--muted);
    text-transform: capitalize;
}

.membership-track {
    height: 8px;
    border-radius: var(--radius-pill);
    background: var(--surface-2);
    border: 1px solid var(--border);
    overflow: hidden;
}

.membership-fill {
    height: 100%;
    border-radius: inherit;
    background: color-mix(in oklab, var(--primary) 45%, transparent);
    transition: width 0.45s var(--ease-soft);
}

.membership-fill.is-dominant {
    background: var(--gradient-brand);
}

.membership-degree {
    font-size: 11px;
    color: var(--muted);
    text-align: right;
}

.empty-note {
    font-size: 13px;
    color: var(--muted);
    padding: 12px 0;
}

/* Rules */
.rules-section {
    margin-top: 8px;
}

.rules-head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
}

.rules-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
}

.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 12px;
    color: var(--muted);
}

.legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.legend-fired {
    background: var(--primary);
}

.legend-idle {
    background: var(--border);
}

.rules-glossary {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 10px;
    background: var(--primary-soft);
    border: 1px solid color-mix(in oklab, var(--primary) 20%, transparent);
    font-size: 12px;
    line-height: 1.55;
    color: var(--foreground);
    margin-bottom: 16px;
}

.rules-glossary svg {
    color: var(--primary);
    flex-shrink: 0;
    margin-top: 2px;
}

.rules-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.rule-card {
    border: 1px solid var(--border);
    border-radius: 14px;
    background: var(--surface);
    overflow: hidden;
    animation: ruleStagger 0.4s var(--ease-soft) both;
    animation-delay: var(--stagger, 0ms);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.rule-card.is-fired {
    border-color: color-mix(in oklab, var(--primary) 35%, var(--border));
    box-shadow: 0 8px 24px -18px color-mix(in oklab, var(--primary) 35%, transparent);
}

@keyframes ruleStagger {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.rule-card-head {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
}

.rule-card-head:hover {
    background: var(--surface-2);
}

.rule-main {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

.rule-code {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
}

.fired-badge {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 2px 7px;
    border-radius: var(--radius-pill);
    background: var(--primary-soft);
    color: var(--primary);
}

.rule-metrics {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
}

.metric {
    display: grid;
    grid-template-columns: auto auto;
    grid-template-rows: auto auto;
    column-gap: 8px;
    row-gap: 4px;
    align-items: center;
    min-width: 120px;
}

.metric-z {
    min-width: auto;
    grid-template-columns: auto auto;
    grid-template-rows: auto;
}

.metric-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
}

.metric-value {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
}

.metric-bar {
    grid-column: 1 / -1;
    height: 4px;
    border-radius: var(--radius-pill);
    background: var(--surface-2);
    overflow: hidden;
}

.metric-fill {
    height: 100%;
    border-radius: inherit;
    background: var(--gradient-brand);
    transition: width 0.35s var(--ease-soft);
}

.rule-chevron {
    color: var(--muted);
    transition: transform 0.2s var(--ease-soft);
    flex-shrink: 0;
}

.rotate-180 {
    transform: rotate(180deg);
}

.rule-detail {
    padding: 0 16px 16px;
    border-top: 1px solid var(--border);
    animation: detailReveal 0.25s var(--ease-soft) both;
}

@keyframes detailReveal {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.rule-description {
    font-size: 13px;
    line-height: 1.6;
    color: var(--foreground);
    margin: 14px 0 12px;
}

.rule-description.muted {
    color: var(--muted);
}

.antecedent-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.antecedent-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border-radius: var(--radius-pill);
    background: var(--surface-2);
    border: 1px solid var(--border);
    font-size: 12px;
}

.chip-criterion {
    color: var(--muted);
}

.chip-set {
    color: var(--ink);
    font-weight: 600;
    text-transform: capitalize;
}

.audit-tag-danger {
    background: color-mix(in oklab, var(--danger) 14%, transparent);
    color: var(--danger);
    border-color: color-mix(in oklab, var(--danger) 24%, transparent);
}

.empty-state {
    padding: 40px 16px;
    text-align: center;
    color: var(--muted);
    font-size: 14px;
    border: 1px dashed var(--border);
    border-radius: 14px;
}

@media (max-width: 640px) {
    .membership-row {
        grid-template-columns: 72px 1fr 40px;
    }

    .rule-metrics {
        width: 100%;
        justify-content: space-between;
    }

    .metric {
        min-width: 0;
        flex: 1;
    }
}

@media (prefers-reduced-motion: reduce) {
    .reveal,
    .rule-card,
    .rule-detail,
    .membership-fill,
    .metric-fill {
        animation: none !important;
        transition: none !important;
    }
}
</style>
