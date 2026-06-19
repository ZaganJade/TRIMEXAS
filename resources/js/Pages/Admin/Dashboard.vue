<script setup>
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { computed, onMounted, onBeforeUnmount, ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    UserCheck,
    Users,
    PlayCircle,
    UserPlus,
    SlidersHorizontal,
    History,
    ScrollText,
    ArrowUpRight,
    TrendingUp,
    CheckCircle,
    Activity,
    Loader2,
} from "@lucide/vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isRefreshing = ref(false);
let pollTimer = null;
const POLL_INTERVAL = 30_000; // 30 seconds

// ---------- Real-time polling ----------
function refreshStats() {
    isRefreshing.value = true;
    router.reload({
        only: ['stats', 'recent_activities'],
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isRefreshing.value = false;
        },
        onError: () => {
            isRefreshing.value = false;
        },
    });
}

onMounted(() => {
    pollTimer = setInterval(refreshStats, POLL_INTERVAL);
});

onBeforeUnmount(() => {
    if (pollTimer) clearInterval(pollTimer);
});

// Stats data
const stats = computed(() => [
    {
        label: "Menunggu Verifikasi",
        value: page.props.stats?.pending ?? "—",
        hint: "Akun mahasiswa baru",
        icon: UserCheck,
        href: route("admin.students.pending"),
        color: "warning",
        trend: null,
    },
    {
        label: "Mahasiswa Aktif",
        value: page.props.stats?.active ?? "—",
        hint: "Terdaftar & disetujui",
        icon: Users,
        href: route("admin.students.index"),
        color: "success",
        trend: null,
    },
    {
        label: "Batch Berjalan",
        value: page.props.stats?.batch ?? "—",
        hint: "Seleksi periode ini",
        icon: PlayCircle,
        href: route("admin.selection.run"),
        color: "primary",
        trend: null,
    },
    {
        label: "Total Seleksi",
        value: page.props.stats?.total_selections ?? "—",
        hint: "Batch yang selesai",
        icon: History,
        href: route("admin.history.index"),
        color: "info",
        trend: null,
    },
]);

// Quick actions
const actions = computed(() => [
    {
        title: "Data Mahasiswa",
        body: "Kelola daftar, tambah, dan ubah profil mahasiswa",
        icon: Users,
        href: route("admin.students.index"),
        color: "primary",
        badge: null,
    },
    {
        title: "Verifikasi Akun",
        body: "Setujui atau tolak pendaftar yang masih menunggu",
        icon: UserPlus,
        href: route("admin.students.pending"),
        color: "warning",
        badge: page.props.stats?.pending || null,
    },
    {
        title: "Kriteria Penilaian",
        body: "Atur kategori dan batas penilaian kandidat",
        icon: SlidersHorizontal,
        href: route("admin.criteria.index"),
        color: "success",
        badge: null,
    },
    {
        title: "Jalankan Seleksi",
        body: "Proses satu putaran penilaian dan lihat hasilnya",
        icon: PlayCircle,
        href: route("admin.selection.run"),
        color: "violet",
        badge: null,
    },
    {
        title: "Riwayat Seleksi",
        body: "Telusuri putaran terdahulu beserta peringkatnya",
        icon: History,
        href: route("admin.history.index"),
        color: "rose",
        badge: null,
    },
    {
        title: "Log Aktivitas",
        body: "Pantau jejak perubahan dan tindakan pengelola",
        icon: ScrollText,
        href: route("admin.activity.index"),
        color: "info",
        badge: null,
    },
]);

// Map log_name to icon component
const activityIconMap = {
    user: UserCheck,
    student: Users,
    selection: PlayCircle,
    default: Activity,
};

// Recent activities — from backend (polled every 30s)
const recentActivities = computed(() =>
    (page.props.recent_activities || []).map((a) => ({
        ...a,
        icon: activityIconMap[a.type] || activityIconMap.default,
    }))
);

// System status
const systemStatus = computed(() => ({
    database: { status: "healthy", label: "Database" },
    queue: { status: "healthy", label: "Queue Worker" },
    storage: { status: "healthy", label: "File Storage" },
}));
</script>

<template>
    <Head title="Admin · Dashboard" />

    <AdminLayout active="dashboard">
        <div class="dashboard-page">
            <!-- =========================================================
                 DASHBOARD HEADER
                 ========================================================= -->
            <header class="page-header reveal-on-scroll">
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                        <p class="page-subtitle">Pusat kendali seleksi beasiswa Triv × MEXC</p>
                    </div>
                </div>
                <button
                    class="refresh-btn"
                    :class="{ 'is-refreshing': isRefreshing }"
                    @click="refreshStats"
                    title="Segarkan data"
                >
                    <Loader2 :size="14" class="refresh-icon" />
                    <span class="refresh-label">Live</span>
                </button>
            </header>

            <!-- =========================================================
                 STATS GRID
                 ========================================================= -->
            <section class="stats-section reveal-on-scroll">
                <div class="stats-grid">
                    <Link
                        v-for="stat in stats"
                        :key="stat.label"
                        :href="stat.href"
                        class="stat-card group"
                    >
                        <div class="flex items-start justify-between">
                            <div :class="`stat-icon tone-${stat.color}`">
                                <component :is="stat.icon" :size="20" />
                            </div>
                            <ArrowUpRight
                                :size="16"
                                class="text-[var(--muted)] transition-all group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-[var(--primary)]"
                            />
                        </div>
                        <p class="mt-5 stat-label">{{ stat.label }}</p>
                        <p class="stat-value">{{ stat.value }}</p>
                        <div class="stat-footer">
                            <span class="stat-hint">{{ stat.hint }}</span>
                            <span v-if="stat.trend" class="stat-trend">
                                <TrendingUp :size="12" />
                                {{ stat.trend }}
                            </span>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- =========================================================
                 MAIN CONTENT GRID
                 ========================================================= -->
            <div class="main-content-grid">
                <!-- Quick Actions -->
                <section class="quick-actions-section reveal-on-scroll">
                    <div class="section-header">
                        <h2 class="section-title">Aksi Cepat</h2>
                        <p class="section-subtitle">Lompat langsung ke modul yang sering digunakan</p>
                    </div>

                    <div class="actions-grid">
                        <Link
                            v-for="action in actions"
                            :key="action.title"
                            :href="action.href"
                            class="action-card group"
                        >
                            <div class="flex items-start justify-between">
                                <div :class="`action-icon tone-${action.color}`">
                                    <component :is="action.icon" :size="20" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="action.badge && action.badge !== '—'" class="action-badge">{{ action.badge }}</span>
                                    <ArrowUpRight
                                        :size="16"
                                        class="text-[var(--muted)] transition-all group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-[var(--primary)]"
                                    />
                                </div>
                            </div>
                            <h3 class="action-title">{{ action.title }}</h3>
                            <p class="action-description">{{ action.body }}</p>
                        </Link>
                    </div>
                </section>

                <!-- Right Sidebar -->
                <aside class="dashboard-sidebar">
                    <!-- System Status -->
                    <div class="sidebar-card reveal-on-scroll">
                        <div class="flex items-center justify-between">
                            <h3 class="sidebar-card-title">Status Sistem</h3>
                            <span class="status-dot">
                                <span class="status-dot-ping"></span>
                            </span>
                        </div>
                        <div class="mt-4 status-list">
                            <div
                                v-for="(service, key) in systemStatus"
                                :key="key"
                                class="status-item"
                            >
                                <span class="status-label">{{ service.label }}</span>
                                <span class="status-value">
                                    <CheckCircle :size="14" />
                                    Online
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="sidebar-card reveal-on-scroll">
                        <div class="flex items-center justify-between">
                            <h3 class="sidebar-card-title">Aktivitas Terbaru</h3>
                            <Link
                                :href="route('admin.activity.index')"
                                class="view-all-link"
                            >
                                Lihat semua
                            </Link>
                        </div>
                        <div class="mt-4 activity-list">
                            <div
                                v-for="activity in recentActivities.slice(0, 4)"
                                :key="activity.id"
                                class="activity-item"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="activity-icon">
                                        <component :is="activity.icon" :size="14" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="activity-message">{{ activity.message }}</p>
                                        <p class="activity-time">{{ activity.time }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Tip -->
                    <div class="sidebar-card sidebar-tip reveal-on-scroll">
                        <div class="flex items-start gap-3">
                            <div class="tip-icon">
                                <Activity :size="16" />
                            </div>
                            <div>
                                <p class="tip-title">Tips</p>
                                <p class="tip-description">
                                    Verifikasi pendaftar baru segera untuk mempercepat proses seleksi beasiswa periode ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* =====================================================
   Dashboard Page Container
   ===================================================== */
.dashboard-page {
    max-width: 1400px;
    margin: 0 auto;
}

/* =====================================================
   Page Header
   ===================================================== */
.page-header {
    margin-bottom: 2rem;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.page-title {
    font-family: var(--font-display);
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 600;
    color: var(--ink);
    line-height: 1.2;
}

.page-subtitle {
    margin-top: 0.5rem;
    font-size: 15px;
    color: var(--muted);
}

/* =====================================================
   Live Refresh Button
   ===================================================== */
.refresh-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--muted);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.refresh-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.refresh-btn.is-refreshing .refresh-icon {
    animation: spin 0.8s linear infinite;
}

.refresh-label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.refresh-btn.is-refreshing .refresh-label {
    color: var(--primary);
}

.refresh-btn::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--success);
    flex-shrink: 0;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* =====================================================
   Stats Section
   ===================================================== */
.stats-section {
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
}

.stat-card {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    border-radius: var(--radius-card);
    background: var(--card);
    border: 1px solid var(--border);
    transition: all 0.2s ease;
    text-decoration: none;
}

.stat-card:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow-card);
    transform: translateY(-2px);
}

/* ---- Unified theme-aware tone palette (shared by stats & actions) ----
   Each `.tone-*` sets two CSS vars (`--tone` fg, `--tone-bg` bg) that the
   icon chip reads. Light uses soft pastel fills; dark uses translucent
   overlays + brighter foregrounds so chips keep contrast in both themes. */
.tone-primary { --tone: #2563eb; --tone-bg: #dbeafe; }
.tone-success { --tone: #059669; --tone-bg: #d1fae5; }
.tone-warning { --tone: #d97706; --tone-bg: #fef3c7; }
.tone-info    { --tone: #0891b2; --tone-bg: #cffafe; }
.tone-violet  { --tone: #7c3aed; --tone-bg: #ede9fe; }
.tone-rose    { --tone: #e11d48; --tone-bg: #ffe4e6; }

.dark .tone-primary { --tone: #93c5fd; --tone-bg: rgba(96, 165, 250, 0.16); }
.dark .tone-success { --tone: #4ade80; --tone-bg: rgba(74, 222, 128, 0.16); }
.dark .tone-warning { --tone: #fbbf24; --tone-bg: rgba(251, 191, 36, 0.16); }
.dark .tone-info    { --tone: #22d3ee; --tone-bg: rgba(34, 211, 238, 0.16); }
.dark .tone-violet  { --tone: #a78bfa; --tone-bg: rgba(167, 139, 250, 0.16); }
.dark .tone-rose    { --tone: #fb7185; --tone-bg: rgba(251, 113, 133, 0.16); }

/* Icon chip — same size & treatment for both stats and actions */
.stat-icon,
.action-icon {
    display: grid;
    height: 44px;
    width: 44px;
    place-items: center;
    border-radius: 12px;
    background: var(--tone-bg);
    color: var(--tone);
    transition: all 0.2s ease;
}

.stat-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
}

.stat-value {
    margin-top: 0.5rem;
    font-size: 2.4rem;
    font-weight: 600;
    line-height: 1;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
}

.stat-footer {
    margin-top: auto;
    padding-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stat-hint {
    font-size: 12px;
    color: var(--muted);
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 2px;
    font-size: 12px;
    font-weight: 600;
    color: var(--success);
}

/* =====================================================
   Main Content Grid
   ===================================================== */
.main-content-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
}

@media (max-width: 1024px) {
    .main-content-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-sidebar {
        order: 2;
    }
}

/* =====================================================
   Quick Actions Section
   ===================================================== */
.quick-actions-section {
    margin-bottom: 2rem;
}

.section-header {
    margin-bottom: 1rem;
}

.section-title {
    font-size: 1.15rem;
    font-weight: 600;
    color: var(--ink);
}

.section-subtitle {
    margin-top: 0.25rem;
    font-size: 14px;
    color: var(--muted);
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
}

.action-card {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    border-radius: var(--radius-card);
    background: var(--card);
    border: 1px solid var(--border);
    transition: all 0.2s ease;
    text-decoration: none;
}

.action-card:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow-card);
    transform: translateY(-2px);
}

.action-badge {
    display: inline-flex;
    height: 20px;
    padding: 0 6px;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    border-radius: 10px;
    background: var(--danger);
    color: white;
}

.action-title {
    margin-top: 1rem;
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
}

.action-description {
    margin-top: 0.375rem;
    font-size: 13px;
    line-height: 1.6;
    color: var(--muted);
}

/* =====================================================
   Sidebar Cards
   ===================================================== */
.dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.sidebar-card {
    padding: 1.25rem;
    border-radius: var(--radius-card);
    background: var(--card);
    border: 1px solid var(--border);
}

.sidebar-card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
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

.sidebar-tip {
    background: var(--primary-soft);
    border-color: color-mix(in oklab, var(--primary) 30%, var(--border));
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
    line-height: 1.5;
    color: var(--muted);
}

/* =====================================================
   Status List
   ===================================================== */
.status-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.status-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
}

.status-label {
    color: var(--muted);
}

.status-value {
    display: flex;
    align-items: center;
    gap: 4px;
    color: var(--success);
    font-weight: 500;
}

/* =====================================================
   Activity List
   ===================================================== */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    position: relative;
}

.activity-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 15px;
    top: 28px;
    bottom: -16px;
    width: 1px;
    background: var(--border);
}

.activity-icon {
    display: grid;
    height: 30px;
    width: 30px;
    place-items: center;
    border-radius: 8px;
    background: var(--surface-2);
    color: var(--muted);
    flex-shrink: 0;
}

.activity-message {
    font-size: 13px;
    color: var(--ink);
}

.activity-time {
    margin-top: 0.125rem;
    font-size: 11px;
    color: var(--muted);
}

/* =====================================================
   Status Dot Animation
   ===================================================== */
.status-dot {
    position: relative;
    display: inline-flex;
    height: 10px;
    width: 10px;
}

.status-dot::before {
    content: '';
    position: absolute;
    inset: 0;
    height: 10px;
    width: 10px;
    border-radius: 50%;
    background: var(--success);
}

.status-dot-ping {
    position: absolute;
    inset: 0;
    height: 10px;
    width: 10px;
    border-radius: 50%;
    background: var(--success);
    animation: status-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes status-ping {
    75%, 100% {
        transform: scale(2.5);
        opacity: 0;
    }
}

/* =====================================================
   Reveal Animation
   ===================================================== */
.reveal-on-scroll {
    animation: rise-in 0.5s ease-out forwards;
    opacity: 0;
    /* Override global blur — no IntersectionObserver on admin pages */
    filter: blur(0);
    transform: translateY(20px);
}

@keyframes rise-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* =====================================================
   Responsive
   ===================================================== */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .actions-grid {
        grid-template-columns: 1fr;
    }
}
</style>
