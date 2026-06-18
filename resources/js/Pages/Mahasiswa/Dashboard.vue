<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    User,
    Trophy,
    ListOrdered,
    Clock,
    XCircle,
    CheckCircle2,
    ArrowUpRight,
    LineChart,
    GraduationCap,
    ShieldCheck,
    Bell,
    Sparkles,
    ClipboardCheck,
    Target,
} from "@lucide/vue";
import MahasiswaLayout from "@/Layouts/MahasiswaLayout.vue";

const props = defineProps({
    profile: { type: Object, required: true },
    student: { type: Object, default: null },
    stats: { type: Object, default: () => ({}) },
    checklist: { type: Array, default: () => [] },
    latestBatch: { type: Object, default: null },
    myResult: { type: Object, default: null },
    recentResults: { type: Array, default: () => [] },
    unreadNotifications: { type: Number, default: 0 },
});

const STATUS_LABELS = {
    layak: "Layak",
    dipertimbangkan: "Dipertimbangkan",
    tidak_layak: "Tidak Layak",
};

const isApproved = computed(() => props.profile.approval_status === "approved");
const isPending = computed(() => props.profile.approval_status === "pending");
const isRejected = computed(() => props.profile.approval_status === "rejected");

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return "Selamat pagi";
    if (hour < 15) return "Selamat siang";
    if (hour < 18) return "Selamat sore";
    return "Selamat malam";
});

const statCards = computed(() => {
    if (!isApproved.value) return [];

    return [
        {
            label: "IPK Terkini",
            value: props.stats.ipk != null ? Number(props.stats.ipk).toFixed(2) : "—",
            hint: props.student?.semester ? `Semester ${props.student.semester}` : "Perbarui di profil",
            icon: GraduationCap,
            color: "primary",
            href: route("mahasiswa.profile.show"),
        },
        {
            label: "Prestasi",
            value: props.stats.achievements_total ?? 0,
            hint: `${props.stats.achievements_verified ?? 0} terverifikasi admin`,
            icon: Trophy,
            color: "amber",
            href: route("mahasiswa.achievements.index"),
        },
        {
            label: "Batch Diikuti",
            value: props.stats.selection_count ?? 0,
            hint: "Riwayat partisipasi seleksi",
            icon: Target,
            color: "violet",
            href: route("mahasiswa.analysis.index"),
        },
        {
            label: "Kelengkapan Profil",
            value: `${props.stats.profile_completion ?? 0}%`,
            hint: "Checklist persiapan seleksi",
            icon: ClipboardCheck,
            color: "emerald",
            href: route("mahasiswa.profile.show"),
        },
    ];
});

const actions = computed(() => [
    {
        title: "Profil & Data Diri",
        body: "Perbarui IPK, kontak, dan data ekonomi keluarga",
        icon: User,
        href: route("mahasiswa.profile.show"),
        color: "blue",
    },
    {
        title: "Kelola Prestasi",
        body: "Unggah dan susun capaian akademis maupun non-akademis",
        icon: Trophy,
        href: route("mahasiswa.achievements.index"),
        color: "amber",
    },
    {
        title: "Hasil Analisa",
        body: "Baca laporan seleksi pribadi beserta aturan fuzzy",
        icon: LineChart,
        href: route("mahasiswa.analysis.index"),
        color: "violet",
    },
    {
        title: "Ranking Publik",
        body: "Lihat peringkat seleksi yang dibuka untuk mahasiswa",
        icon: ListOrdered,
        href: route("mahasiswa.ranking.index"),
        color: "rose",
    },
]);

function statusLabel(status) {
    return STATUS_LABELS[status] ?? status;
}

function formatDate(value) {
    if (!value) return "—";
    return new Date(value).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}
</script>

<template>
    <Head title="Mahasiswa · Dashboard" />

    <MahasiswaLayout active="dashboard">
        <div class="dashboard-page">
            <header class="page-header reveal-on-scroll">
                <div>
                    <p class="page-kicker">{{ greeting }}, {{ profile.name.split(" ")[0] }}</p>
                    <h1 class="page-title">Dashboard Mahasiswa</h1>
                    <p class="page-subtitle">
                        Pantau kelengkapan profil, prestasi, dan hasil analisa seleksi dari satu tempat.
                    </p>
                </div>
                <div v-if="unreadNotifications > 0" class="notify-chip">
                    <Bell :size="14" />
                    {{ unreadNotifications }} notifikasi baru
                </div>
            </header>

            <!-- Account status -->
            <section class="reveal-on-scroll">
                <article v-if="isPending" class="status-banner status-pending">
                    <Clock :size="22" />
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="status-title">Akun menunggu verifikasi</h2>
                            <span class="tag tag-warning">Pending</span>
                        </div>
                        <p class="status-copy">
                            Pengelola sedang meninjau pendaftaranmu. Notifikasi akan muncul saat akun
                            disetujui atau ditolak.
                        </p>
                    </div>
                </article>

                <article v-else-if="isRejected" class="status-banner status-rejected">
                    <XCircle :size="22" />
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="status-title">Akun ditolak</h2>
                            <span class="tag tag-error">Ditolak</span>
                        </div>
                        <p class="status-copy">
                            Silakan hubungi admin beasiswa untuk informasi lebih lanjut.
                        </p>
                    </div>
                </article>

                <article v-else class="status-banner status-approved">
                    <CheckCircle2 :size="22" />
                    <div class="flex flex-wrap items-start justify-between gap-4 w-full">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="status-title">Akun aktif</h2>
                                <span class="tag tag-success">Approved</span>
                            </div>
                            <p class="status-copy">
                                Profil siap diproses. Pastikan data dan prestasi tetap mutakhir sebelum batch berikutnya.
                            </p>
                        </div>
                        <div v-if="student" class="identity-chip">
                            <ShieldCheck :size="14" />
                            <span>{{ student.nim }}</span>
                        </div>
                    </div>
                </article>
            </section>

            <template v-if="isApproved">
                <!-- Stats -->
                <section class="stats-section reveal-on-scroll">
                    <div class="stats-grid">
                        <Link
                            v-for="stat in statCards"
                            :key="stat.label"
                            :href="stat.href"
                            class="stat-card group"
                        >
                            <div class="flex items-start justify-between">
                                <div :class="`stat-icon stat-${stat.color}`">
                                    <component :is="stat.icon" :size="20" />
                                </div>
                                <ArrowUpRight
                                    :size="16"
                                    class="text-[var(--muted)] transition-all group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-[var(--primary)]"
                                />
                            </div>
                            <p class="mt-5 stat-label">{{ stat.label }}</p>
                            <p class="stat-value">{{ stat.value }}</p>
                            <p class="stat-hint">{{ stat.hint }}</p>
                        </Link>
                    </div>
                </section>

                <div class="main-content-grid">
                    <div class="primary-column">
                        <!-- Latest result -->
                        <section class="panel reveal-on-scroll">
                            <div class="section-header">
                                <h2 class="section-title">Hasil Seleksi Terakhir</h2>
                                <p class="section-subtitle">
                                    Ringkasan batch paling baru yang melibatkan datamu.
                                </p>
                            </div>

                            <article v-if="latestBatch && myResult" class="result-panel">
                                <div class="result-score">
                                    <span class="mono text-[10px] uppercase tracking-[0.18em] text-[var(--muted)]">
                                        Skor Anda
                                    </span>
                                    <span class="score-dial mt-2 text-[2.6rem] leading-none text-[var(--ink)] tnum">
                                        {{ myResult.eligible ? Number(myResult.score).toFixed(2) : "—" }}
                                    </span>
                                    <span
                                        class="tag mt-3"
                                        :class="myResult.eligible ? 'tag-success' : 'tag-warning'"
                                    >
                                        {{ myResult.eligible ? statusLabel(myResult.status) : "Tidak memenuhi syarat" }}
                                    </span>
                                </div>

                                <div class="result-details">
                                    <p class="result-batch">
                                        Batch <strong>{{ latestBatch.label }}</strong>
                                        · {{ formatDate(latestBatch.completed_at) }}
                                    </p>
                                    <p v-if="myResult.eligible && myResult.rank" class="result-meta">
                                        Peringkat internal batch: #{{ myResult.rank }}
                                    </p>

                                    <ul
                                        v-if="!myResult.eligible && myResult.reasons?.length"
                                        class="reason-list"
                                    >
                                        <li v-for="reason in myResult.reasons" :key="reason">
                                            {{ reason }}
                                        </li>
                                    </ul>

                                    <div class="result-actions">
                                        <Link
                                            :href="route('mahasiswa.analysis.show', latestBatch.id)"
                                            class="btn-primary-soft"
                                        >
                                            <LineChart :size="15" />
                                            Lihat analisa lengkap
                                        </Link>
                                        <Link
                                            v-if="myResult.eligible"
                                            :href="route('mahasiswa.ranking.index')"
                                            class="btn-link"
                                        >
                                            Lihat ranking publik
                                        </Link>
                                    </div>
                                </div>
                            </article>

                            <article v-else-if="latestBatch && !myResult" class="empty-panel">
                                <LineChart :size="22" class="text-[var(--muted)]" />
                                <div>
                                    <h3 class="empty-title">Belum diikutkan</h3>
                                    <p class="empty-copy">
                                        Kamu belum masuk batch terbaru ({{ latestBatch.label }}).
                                    </p>
                                </div>
                            </article>

                            <article v-else class="empty-panel">
                                <Clock :size="22" class="text-[var(--muted)]" />
                                <div>
                                    <h3 class="empty-title">Menunggu batch berikutnya</h3>
                                    <p class="empty-copy">
                                        Lengkapi profil dan prestasi agar siap saat seleksi dibuka.
                                    </p>
                                </div>
                            </article>
                        </section>

                        <!-- Quick actions -->
                        <section class="panel reveal-on-scroll">
                            <div class="section-header">
                                <h2 class="section-title">Aksi Cepat</h2>
                                <p class="section-subtitle">Langkah yang paling sering dibutuhkan mahasiswa.</p>
                            </div>

                            <div class="actions-grid">
                                <Link
                                    v-for="action in actions"
                                    :key="action.title"
                                    :href="action.href"
                                    class="action-card group"
                                >
                                    <div class="flex items-start justify-between">
                                        <div :class="`action-icon action-${action.color}`">
                                            <component :is="action.icon" :size="20" />
                                        </div>
                                        <ArrowUpRight
                                            :size="16"
                                            class="text-[var(--muted)] transition-all group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-[var(--primary)]"
                                        />
                                    </div>
                                    <h3 class="action-title">{{ action.title }}</h3>
                                    <p class="action-description">{{ action.body }}</p>
                                </Link>
                            </div>
                        </section>
                    </div>

                    <aside class="dashboard-sidebar">
                        <!-- Checklist -->
                        <div class="sidebar-card reveal-on-scroll">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="sidebar-card-title">Checklist Persiapan</h3>
                                <span class="completion-pill">{{ stats.profile_completion ?? 0 }}%</span>
                            </div>
                            <ul class="checklist">
                                <li
                                    v-for="item in checklist"
                                    :key="item.key"
                                    class="checklist-item"
                                    :class="{ 'is-done': item.done }"
                                >
                                    <span class="checklist-dot">
                                        <CheckCircle2 v-if="item.done" :size="14" />
                                    </span>
                                    <div>
                                        <p class="checklist-label">{{ item.label }}</p>
                                        <p v-if="!item.done" class="checklist-hint">{{ item.hint }}</p>
                                    </div>
                                </li>
                            </ul>
                            <Link :href="route('mahasiswa.profile.show')" class="view-all-link mt-4 inline-flex">
                                Lengkapi profil
                            </Link>
                        </div>

                        <!-- Recent results -->
                        <div class="sidebar-card reveal-on-scroll">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="sidebar-card-title">Riwayat Analisa</h3>
                                <Link :href="route('mahasiswa.analysis.index')" class="view-all-link">
                                    Semua
                                </Link>
                            </div>

                            <div v-if="recentResults.length" class="history-list">
                                <Link
                                    v-for="row in recentResults"
                                    :key="row.batch_id"
                                    :href="route('mahasiswa.analysis.show', row.batch_id)"
                                    class="history-item group"
                                >
                                    <div>
                                        <p class="history-label">{{ row.batch_label }}</p>
                                        <p class="history-meta">
                                            {{ formatDate(row.completed_at) }}
                                            ·
                                            {{
                                                row.eligible
                                                    ? `Skor ${Number(row.score).toFixed(2)}`
                                                    : "Tidak memenuhi syarat"
                                            }}
                                        </p>
                                    </div>
                                    <ArrowUpRight
                                        :size="14"
                                        class="text-[var(--muted)] transition-all group-hover:text-[var(--primary)]"
                                    />
                                </Link>
                            </div>

                            <p v-else class="empty-sidebar">
                                Belum ada riwayat analisa. Hasil akan muncul setelah batch seleksi selesai.
                            </p>
                        </div>

                        <!-- Tip -->
                        <div class="sidebar-card sidebar-tip reveal-on-scroll">
                            <div class="flex items-start gap-3">
                                <div class="tip-icon">
                                    <Sparkles :size="16" />
                                </div>
                                <div>
                                    <h3 class="tip-title">Tips seleksi</h3>
                                    <p class="tip-description">
                                        Prestasi terverifikasi admin memberi bobot lebih kuat. Pastikan sertifikat
                                        valid dan data profil konsisten sebelum batch dibuka.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </template>
        </div>
    </MahasiswaLayout>
</template>

<style scoped>
.dashboard-page {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.page-header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.page-kicker {
    font-size: 13px;
    font-weight: 500;
    color: var(--primary);
    letter-spacing: 0.04em;
}

.page-title {
    margin-top: 0.35rem;
    font-family: var(--font-display);
    font-size: clamp(1.75rem, 4vw, 2.35rem);
    font-weight: 600;
    color: var(--ink);
}

.page-subtitle {
    margin-top: 0.5rem;
    max-width: 42rem;
    font-size: 15px;
    line-height: 1.6;
    color: var(--muted);
}

.notify-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 0.85rem;
    border-radius: 999px;
    background: color-mix(in oklab, var(--primary) 12%, transparent);
    border: 1px solid color-mix(in oklab, var(--primary) 24%, transparent);
    color: var(--primary);
    font-size: 12px;
    font-weight: 600;
}

.status-banner {
    display: flex;
    gap: 1rem;
    padding: 1.25rem 1.35rem;
    border-radius: var(--radius-card);
    border: 1px solid var(--border);
    background: var(--card);
}

.status-pending {
    border-color: color-mix(in oklab, var(--warning) 30%, var(--border));
    background: color-mix(in oklab, var(--warning) 8%, var(--card));
    color: var(--warning);
}

.status-rejected {
    border-color: color-mix(in oklab, var(--danger) 30%, var(--border));
    background: color-mix(in oklab, var(--danger) 8%, var(--card));
    color: var(--danger);
}

.status-approved {
    border-color: color-mix(in oklab, var(--success) 28%, var(--border));
    background: color-mix(in oklab, var(--success) 8%, var(--card));
    color: var(--success);
}

.status-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--ink);
}

.status-copy {
    margin-top: 0.35rem;
    font-size: 14px;
    line-height: 1.6;
    color: var(--muted);
}

.identity-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--ink);
    font-size: 12px;
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

@media (max-width: 1100px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    padding: 1.25rem;
    border-radius: var(--radius-card);
    background: var(--card);
    border: 1px solid var(--border);
    text-decoration: none;
    transition: all 0.22s cubic-bezier(0.2, 0, 0, 1);
}

.stat-card:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow-card);
    transform: translateY(-2px);
}

.stat-icon {
    display: grid;
    height: 40px;
    width: 40px;
    place-items: center;
    border-radius: 10px;
}

.stat-primary .stat-icon {
    background: var(--primary-light);
    color: var(--primary);
}

.stat-amber .stat-icon {
    background: color-mix(in oklab, var(--warning) 16%, transparent);
    color: var(--warning);
}

.stat-violet .stat-icon {
    background: color-mix(in oklab, var(--accent) 16%, transparent);
    color: var(--accent);
}

.stat-emerald .stat-icon {
    background: color-mix(in oklab, var(--success) 16%, transparent);
    color: var(--success);
}

.stat-label {
    font-size: 13px;
    color: var(--muted);
}

.stat-value {
    margin-top: 0.35rem;
    font-family: var(--font-display);
    font-size: 1.85rem;
    font-weight: 600;
    color: var(--ink);
}

.stat-hint {
    margin-top: 0.5rem;
    font-size: 12px;
    color: var(--muted);
}

.main-content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .main-content-grid {
        grid-template-columns: 1fr;
    }
}

.primary-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.panel,
.sidebar-card {
    padding: 1.25rem;
    border-radius: var(--radius-card);
    background: var(--card);
    border: 1px solid var(--border);
}

.section-header {
    margin-bottom: 1rem;
}

.section-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--ink);
}

.section-subtitle {
    margin-top: 0.25rem;
    font-size: 14px;
    color: var(--muted);
}

.result-panel {
    display: grid;
    grid-template-columns: 180px minmax(0, 1fr);
    gap: 1.25rem;
    padding: 1rem;
    border-radius: 14px;
    background: var(--surface-2);
    border: 1px solid var(--border);
}

@media (max-width: 768px) {
    .result-panel {
        grid-template-columns: 1fr;
    }
}

.result-score {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    border-radius: 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    text-align: center;
}

.result-batch,
.result-meta {
    font-size: 14px;
    color: var(--foreground);
}

.result-meta {
    margin-top: 0.35rem;
    color: var(--muted);
}

.reason-list {
    margin-top: 0.85rem;
    padding-left: 1rem;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.6;
}

.result-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1rem;
}

.btn-primary-soft,
.btn-link {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
}

.btn-primary-soft {
    padding: 0.55rem 0.9rem;
    border-radius: var(--radius-input);
    border: 1px solid color-mix(in oklab, var(--primary) 30%, transparent);
    background: var(--primary-soft);
    color: var(--primary);
}

.btn-link {
    color: var(--primary);
}

.empty-panel {
    display: flex;
    gap: 0.85rem;
    align-items: flex-start;
    padding: 1rem;
    border-radius: 14px;
    background: var(--surface-2);
    border: 1px dashed var(--border);
}

.empty-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
}

.empty-copy {
    margin-top: 0.25rem;
    font-size: 13px;
    line-height: 1.6;
    color: var(--muted);
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1rem;
}

.action-card {
    display: flex;
    flex-direction: column;
    padding: 1.1rem;
    border-radius: 14px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    text-decoration: none;
    transition: all 0.22s cubic-bezier(0.2, 0, 0, 1);
}

.action-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-card);
}

.action-icon {
    display: grid;
    height: 40px;
    width: 40px;
    place-items: center;
    border-radius: 10px;
}

.action-blue .action-icon {
    background: color-mix(in oklab, var(--primary) 14%, transparent);
    color: var(--primary);
}

.action-amber .action-icon {
    background: color-mix(in oklab, var(--warning) 14%, transparent);
    color: var(--warning);
}

.action-violet .action-icon {
    background: color-mix(in oklab, var(--accent) 14%, transparent);
    color: var(--accent);
}

.action-rose .action-icon {
    background: color-mix(in oklab, var(--danger) 12%, transparent);
    color: var(--danger);
}

.action-title {
    margin-top: 0.85rem;
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
}

.action-description {
    margin-top: 0.35rem;
    font-size: 13px;
    line-height: 1.55;
    color: var(--muted);
}

.dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.sidebar-card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
}

.completion-pill {
    display: inline-flex;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    background: var(--primary-soft);
    color: var(--primary);
    font-size: 11px;
    font-weight: 700;
}

.checklist {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1rem;
}

.checklist-item {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.checklist-dot {
    display: grid;
    height: 22px;
    width: 22px;
    place-items: center;
    border-radius: 999px;
    border: 1px solid var(--border);
    color: transparent;
    flex-shrink: 0;
}

.checklist-item.is-done .checklist-dot {
    border-color: color-mix(in oklab, var(--success) 30%, transparent);
    background: color-mix(in oklab, var(--success) 12%, transparent);
    color: var(--success);
}

.checklist-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
}

.checklist-hint {
    margin-top: 0.15rem;
    font-size: 12px;
    line-height: 1.5;
    color: var(--muted);
}

.view-all-link {
    font-size: 12px;
    font-weight: 500;
    color: var(--primary);
    text-decoration: none;
}

.view-all-link:hover {
    text-decoration: underline;
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 1rem;
}

.history-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    text-decoration: none;
    transition: border-color 0.2s ease;
}

.history-item:hover {
    border-color: var(--primary);
}

.history-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
}

.history-meta {
    margin-top: 0.15rem;
    font-size: 11px;
    color: var(--muted);
}

.empty-sidebar {
    margin-top: 1rem;
    font-size: 12px;
    line-height: 1.6;
    color: var(--muted);
}

.sidebar-tip {
    background: linear-gradient(135deg, var(--primary-light) 0%, rgba(156, 179, 166, 0.08) 100%);
    border-color: color-mix(in oklab, var(--primary) 24%, var(--border));
}

.tip-icon {
    display: grid;
    height: 32px;
    width: 32px;
    place-items: center;
    border-radius: 8px;
    background: var(--primary);
    color: white;
    flex-shrink: 0;
}

.tip-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
}

.tip-description {
    margin-top: 0.25rem;
    font-size: 12px;
    line-height: 1.55;
    color: var(--muted);
}

.reveal-on-scroll {
    animation: rise-in 0.45s cubic-bezier(0.2, 0, 0, 1) forwards;
    opacity: 0;
    /* Override global blur — no IntersectionObserver on dashboard pages */
    filter: blur(0);
    transform: translateY(12px);
}

@keyframes rise-in {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
