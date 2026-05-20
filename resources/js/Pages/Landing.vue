<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, onMounted, onBeforeUnmount, ref } from "vue";
import {
    ArrowUpRight,
    ArrowRight,
    ArrowDown,
    Sparkles,
    SunMedium,
    MoonStar,
    Zap,
    ShieldCheck,
    GitBranch,
    Activity,
    Lock,
    UsersRound,
    BookOpenCheck,
    Plus,
    Minus,
    LineChart,
    Cpu,
    FileCheck2,
} from "@lucide/vue";

defineProps({
    canRegister: { type: Boolean, default: true },
    appName: { type: String, default: "Trimexas" },
});

/* ===========================
   Theme toggle
   =========================== */
const isDark = ref(false);
onMounted(() => {
    isDark.value = document.documentElement.classList.contains("dark");
});
function toggleTheme() {
    isDark.value = !isDark.value;
    const next = isDark.value ? "dark" : "light";
    document.documentElement.classList.toggle("dark", isDark.value);
    try {
        localStorage.setItem("trimexas-theme", next);
    } catch (_) {}
}

/* ===========================
   Live fuzzy demo
   =========================== */
const ipk = ref(3.65);
const penghasilan = ref(3.5); // dalam juta
const prestasi = ref(22);

// Membership functions (matching mesin produksi)
const muRendahIpk = (x) => {
    if (x <= 3.25) return 1;
    if (x >= 3.6) return 0;
    return (3.6 - x) / 0.35;
};
const muSedangIpk = (x) => {
    if (x <= 3.25 || x >= 3.75) return 0;
    if (x <= 3.5) return (x - 3.25) / 0.25;
    return (3.75 - x) / 0.25;
};
const muTinggiIpk = (x) => {
    if (x <= 3.6) return 0;
    if (x >= 3.75) return 1;
    return (x - 3.6) / 0.15;
};

const muRendahHsl = (x) => {
    if (x <= 3) return 1;
    if (x >= 7) return 0;
    return (7 - x) / 4;
};
const muSedangHsl = (x) => {
    if (x <= 3 || x >= 10) return 0;
    if (x <= 5) return (x - 3) / 2;
    return (10 - x) / 5;
};
const muTinggiHsl = (x) => {
    if (x <= 7) return 0;
    if (x >= 10) return 1;
    return (x - 7) / 3;
};

const muSedikitPa = (x) => {
    if (x <= 5) return 1;
    if (x >= 15) return 0;
    return (15 - x) / 10;
};
const muSedangPa = (x) => {
    if (x <= 5 || x >= 25) return 0;
    if (x <= 15) return (x - 5) / 10;
    return (25 - x) / 10;
};
const muBanyakPa = (x) => {
    if (x <= 15) return 0;
    if (x >= 25) return 1;
    return (x - 15) / 10;
};

const memberships = computed(() => ({
    ipk: {
        rendah: muRendahIpk(ipk.value),
        sedang: muSedangIpk(ipk.value),
        tinggi: muTinggiIpk(ipk.value),
    },
    hsl: {
        rendah: muRendahHsl(penghasilan.value),
        sedang: muSedangHsl(penghasilan.value),
        tinggi: muTinggiHsl(penghasilan.value),
    },
    pa: {
        sedikit: muSedikitPa(prestasi.value),
        sedang: muSedangPa(prestasi.value),
        banyak: muBanyakPa(prestasi.value),
    },
}));

const result = computed(() => {
    const m = memberships.value;
    // R1: ipk=tinggi & hsl=rendah & pa=banyak → LAYAK
    const r1 = Math.min(m.ipk.tinggi, m.hsl.rendah, m.pa.banyak);
    // R2: ipk=sedang | hsl=sedang | pa=sedang → DIPERTIMBANGKAN
    const r2 = Math.max(m.ipk.sedang, m.hsl.sedang, m.pa.sedang);
    // R3: ipk=rendah | hsl=tinggi | pa=sedikit → TIDAK LAYAK
    const r3 = Math.max(m.ipk.rendah, m.hsl.tinggi, m.pa.sedikit);

    // Tsukamoto z-functions (threshold default 50/75)
    const z1 = 75 + r1 * 25;
    const z2 = 50 + r2 * 25;
    const z3 = 50 - r3 * 50;

    const num = r1 * z1 + r2 * z2 + r3 * z3;
    const den = r1 + r2 + r3;
    const score = den > 0 ? num / den : 0;

    let category = "Tidak Layak";
    let badgeBg = "var(--danger)";
    if (score >= 75) {
        category = "Layak";
        badgeBg = "var(--success)";
    } else if (score >= 50) {
        category = "Dipertimbangkan";
        badgeBg = "var(--primary)";
    }

    return {
        score: Math.round(score * 100) / 100,
        category,
        badgeBg,
        rules: [
            { code: "R1", label: "Layak", alpha: r1, z: z1, color: "var(--success)" },
            { code: "R2", label: "Dipertimbangkan", alpha: r2, z: z2, color: "var(--primary)" },
            { code: "R3", label: "Tidak Layak", alpha: r3, z: z3, color: "var(--danger)" },
        ],
    };
});

// Smooth animated counter for the score
const displayScore = ref(0);
let scoreRaf = null;
function animateScore(target) {
    cancelAnimationFrame(scoreRaf);
    const start = displayScore.value;
    const diff = target - start;
    const duration = 380;
    const t0 = performance.now();
    const step = (t) => {
        const p = Math.min(1, (t - t0) / duration);
        const ease = 1 - Math.pow(1 - p, 3);
        displayScore.value = start + diff * ease;
        if (p < 1) scoreRaf = requestAnimationFrame(step);
    };
    scoreRaf = requestAnimationFrame(step);
}

onMounted(() => {
    displayScore.value = result.value.score;
});
onBeforeUnmount(() => cancelAnimationFrame(scoreRaf));

// Watch result and animate
import { watch } from "vue";
watch(
    () => result.value.score,
    (s) => animateScore(s)
);

/* ===========================
   IPK chart points (animated SVG)
   For visualizing the membership functions live
   =========================== */
const chartW = 400;
const chartH = 140;

// Each membership rendered as polyline points
function ipkPathRendah() {
    const pts = [];
    for (let x = 3.0; x <= 4.0; x += 0.02) {
        const y = muRendahIpk(x);
        const px = ((x - 3.0) / 1.0) * chartW;
        const py = chartH - y * (chartH - 12) - 6;
        pts.push(`${px},${py}`);
    }
    return `M ${pts.join(" L ")}`;
}
function ipkPathSedang() {
    const pts = [];
    for (let x = 3.0; x <= 4.0; x += 0.02) {
        const y = muSedangIpk(x);
        const px = ((x - 3.0) / 1.0) * chartW;
        const py = chartH - y * (chartH - 12) - 6;
        pts.push(`${px},${py}`);
    }
    return `M ${pts.join(" L ")}`;
}
function ipkPathTinggi() {
    const pts = [];
    for (let x = 3.0; x <= 4.0; x += 0.02) {
        const y = muTinggiIpk(x);
        const px = ((x - 3.0) / 1.0) * chartW;
        const py = chartH - y * (chartH - 12) - 6;
        pts.push(`${px},${py}`);
    }
    return `M ${pts.join(" L ")}`;
}

const ipkMarkerX = computed(() => ((ipk.value - 3.0) / 1.0) * chartW);

/* ===========================
   Mouse-follow gradient on bento
   =========================== */
function bentoMouse(e) {
    const t = e.currentTarget;
    const rect = t.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    t.style.setProperty("--mouse-x", `${x}%`);
    t.style.setProperty("--mouse-y", `${y}%`);
}

/* ===========================
   Smooth scroll
   =========================== */
function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: "smooth", block: "start" });
}

/* ===========================
   FAQ accordion
   =========================== */
const openFaq = ref(0);
const faqs = [
    {
        q: "Apa bedanya dengan ranking biasa pakai bobot?",
        a: "Pembobotan linear butuh bobot tetap & threshold kaku — sulit menangani ketidakpastian. Tsukamoto menerima nilai berderajat (μ ∈ 0..1), menjalankan inferensi linguistik (IPK tinggi DAN penghasilan rendah → layak), dan menghasilkan skor yang traceable per rule.",
    },
    {
        q: "Bagaimana saya tahu hasilnya benar?",
        a: "Setiap kandidat menyimpan input crisp, derajat keanggotaan tiap himpunan, α-predikat per rule yang fire, dan z. Anda bisa rekonstruksi perhitungan manual dari halaman audit atau ekspor PDF.",
    },
    {
        q: "Apakah parameter bisa diubah?",
        a: "Bisa, lewat halaman /admin/criteria — dengan validasi a < b < c dan domain range. Setiap batch men-snapshot konfigurasi saat itu juga ke JSONB, sehingga ranking historis tidak berubah retroaktif.",
    },
    {
        q: "Berapa lama untuk 1.000 kandidat?",
        a: "Baseline pengukuran kami: 1,29 detik dengan worker self-spawn dan chunking 50 kandidat per job. Target NFR ≤ 5 menit. Selisih ~230× cadangan, jadi aman walau di hardware lemah.",
    },
];

/* ===========================
   Stats data
   =========================== */
const stats = [
    { value: "75", unit: "rule", label: "Knowledge base aktif", icon: BookOpenCheck },
    { value: "5", unit: "var", label: "Variabel input fuzzy", icon: Cpu },
    { value: "≤0,01", unit: "Δ", label: "vs perhitungan manual", icon: FileCheck2 },
    { value: "1,29", unit: "s", label: "untuk 1.000 kandidat", icon: Zap },
];

const team = [
    "Muhammad Ikhsanudin Arsalan",
    "Ahmad Irsyad Zahrani Nur Abdullah",
    "Muhammad Javier Rakha Abhista",
    "Muhammad Rizki Ibrahim",
];

const tickerItems = [
    "Fuzzy Tsukamoto",
    "Audit-trail lengkap",
    "Privacy by design",
    "Reproducible per-batch",
    "75 rule × 5 variabel",
    "Eligibility 4-gate",
    "1.000 kandidat / 1,29 s",
    "Open source friendly",
];
</script>

<template>
    <Head title="Trimexas — SPK Beasiswa Fuzzy Tsukamoto" />

    <main class="relative min-h-screen overflow-x-clip bg-[var(--background)] text-[var(--foreground)]">
        <!-- =========================================================
             ATMOSPHERE — gradient mesh + dot grid + soft halos
             ========================================================= -->
        <div class="pointer-events-none absolute inset-0 -z-10 bg-hero-mesh"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-grid-line"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-noise"></div>

        <!-- =========================================================
             NAV — minimal, glass on scroll
             ========================================================= -->
        <header class="sticky top-0 z-30">
            <div class="mx-auto mt-4 max-w-7xl px-6 lg:px-10">
                <nav
                    class="card-glass flex items-center justify-between rounded-full px-5 py-2.5"
                    aria-label="Navigasi utama"
                >
                    <Link href="/" class="group flex items-center gap-2.5">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-[var(--primary)] text-[var(--primary-foreground)] shadow-[0_4px_12px_rgba(49,137,198,0.4)] transition-transform group-hover:scale-110">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 17 L10 7 L13 13 L19 5" />
                            </svg>
                        </span>
                        <span class="font-display text-base font-semibold tracking-tight">Trimexas</span>
                        <span class="hidden text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--muted)] sm:inline">v1.0</span>
                    </Link>

                    <div class="hidden items-center gap-7 text-sm md:flex">
                        <button class="link-draw text-[var(--muted)] hover:text-[var(--foreground)]" @click="scrollTo('demo')">Demo</button>
                        <button class="link-draw text-[var(--muted)] hover:text-[var(--foreground)]" @click="scrollTo('how')">Cara kerja</button>
                        <button class="link-draw text-[var(--muted)] hover:text-[var(--foreground)]" @click="scrollTo('method')">Metode</button>
                        <button class="link-draw text-[var(--muted)] hover:text-[var(--foreground)]" @click="scrollTo('faq')">FAQ</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] bg-[var(--surface)] text-[var(--muted)] transition-colors hover:border-[var(--primary)] hover:text-[var(--primary)]"
                            :aria-label="isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap'"
                            @click="toggleTheme"
                        >
                            <SunMedium v-if="isDark" :size="15" />
                            <MoonStar v-else :size="15" />
                        </button>
                        <Link
                            :href="route('login')"
                            class="hidden rounded-full px-4 py-2 text-sm font-medium text-[var(--foreground)] transition-colors hover:text-[var(--primary)] sm:inline-block"
                        >
                            Masuk
                        </Link>
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center gap-1.5 rounded-full bg-[var(--primary)] px-4 py-2 text-sm font-medium text-[var(--primary-foreground)] transition-colors hover:bg-[var(--primary-dark)]"
                        >
                            Daftar
                            <ArrowUpRight :size="14" class="transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                        </Link>
                    </div>
                </nav>
            </div>
        </header>

        <!-- =========================================================
             HERO — kinetic display + live demo card side-by-side
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-7xl px-6 pt-20 pb-24 lg:px-10 lg:pt-32">
            <!-- Pre-headline status pill -->
            <div class="reveal-fade flex items-center gap-2 text-xs font-medium">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inset-0 rounded-full bg-[var(--primary)] pulse-ring"></span>
                    <span class="relative h-2.5 w-2.5 rounded-full bg-[var(--primary)]"></span>
                </span>
                <span class="mono text-[10px] uppercase tracking-[0.25em] text-[var(--muted)]">
                    LIVE · v1.0 · Triv × MEXC Foundation
                </span>
            </div>

            <div class="mt-8 grid grid-cols-1 items-end gap-12 lg:grid-cols-12 lg:gap-10">
                <!-- LEFT: massive headline + lead + CTAs -->
                <div class="lg:col-span-7">
                    <h1 class="display-tight reveal text-[clamp(2.5rem,8vw,6.5rem)] text-[var(--ink)]">
                        Beasiswa berbasis
                        <span class="text-gradient-shimmer">angka,</span>
                        bukan
                        <span class="relative inline-block">
                            tebakan.
                            <svg
                                class="pointer-events-none absolute -bottom-2 left-0 h-3 w-full text-[var(--primary)]"
                                viewBox="0 0 200 12"
                                fill="none"
                                preserveAspectRatio="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M2 8 Q 50 2, 100 6 T 198 4"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    class="draw-path"
                                    style="--length: 220"
                                />
                            </svg>
                        </span>
                    </h1>

                    <p class="reveal mt-8 max-w-xl text-lg leading-relaxed text-[var(--muted)] md:text-xl" style="animation-delay: 120ms;">
                        Sistem pendukung keputusan untuk seleksi beasiswa Triv × MEXC, dibangun di atas
                        <strong class="text-[var(--foreground)]">metode Fuzzy Tsukamoto</strong> —
                        bukan bobot tetap. Setiap skor punya jejak. Setiap keputusan dapat
                        dipertanggungjawabkan.
                    </p>

                    <div class="reveal mt-9 flex flex-wrap items-center gap-3" style="animation-delay: 220ms;">
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center gap-2 rounded-full bg-[var(--primary)] px-7 py-4 text-sm font-medium text-[var(--primary-foreground)] transition-all hover:bg-[var(--primary-dark)] glow-blue"
                        >
                            Daftar gratis sebagai mahasiswa
                            <ArrowRight :size="16" class="transition-transform group-hover:translate-x-1" />
                        </Link>

                        <button
                            type="button"
                            class="group inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--surface)] px-6 py-4 text-sm font-medium text-[var(--foreground)] transition-all hover:border-[var(--primary)] hover:text-[var(--primary)]"
                            @click="scrollTo('demo')"
                        >
                            <Sparkles :size="14" />
                            Coba demo interaktif
                            <ArrowDown :size="14" class="transition-transform group-hover:translate-y-0.5" />
                        </button>
                    </div>

                    <!-- trust strip -->
                    <div class="reveal mt-12 flex flex-wrap items-center gap-x-8 gap-y-3 text-xs text-[var(--muted)]" style="animation-delay: 320ms;">
                        <div class="flex items-center gap-1.5">
                            <ShieldCheck :size="14" class="text-[var(--success)]" />
                            <span>Audit-trail lengkap</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <Lock :size="14" class="text-[var(--primary)]" />
                            <span>Privacy by design</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <GitBranch :size="14" class="text-[var(--primary)]" />
                            <span>Reproducible per-batch</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <Activity :size="14" class="text-[var(--primary)]" />
                            <span>≤ 5 menit / 1.000 kandidat</span>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: floating "live result" card -->
                <aside class="reveal-scale lg:col-span-5" style="animation-delay: 200ms;">
                    <div class="card-modern relative overflow-hidden p-7 shadow-floating">
                        <div class="pointer-events-none absolute -right-20 -top-20 h-48 w-48 rounded-full bg-[var(--primary-light)] opacity-60 blur-2xl"></div>

                        <div class="relative flex items-center justify-between">
                            <span class="mono text-[10px] uppercase tracking-[0.22em] text-[var(--muted)]">
                                LIVE_PREVIEW · MOCK
                            </span>
                            <span class="flex h-2 w-2 rounded-full bg-[var(--success)] pulse-ring"></span>
                        </div>

                        <p class="mt-5 text-sm text-[var(--muted)]">Skor kelayakan</p>
                        <div class="flex items-baseline gap-2">
                            <span class="display-tight stat-num text-7xl text-gradient-blue">
                                {{ displayScore.toFixed(2) }}
                            </span>
                            <span class="text-base text-[var(--muted)]">/ 100</span>
                        </div>

                        <div
                            class="cat-badge mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium text-white"
                            :style="{ backgroundColor: result.badgeBg }"
                        >
                            <span class="flex h-1.5 w-1.5 rounded-full bg-white/80"></span>
                            {{ result.category }}
                        </div>

                        <div class="mt-6 space-y-2.5 border-t border-[var(--border-subtle)] pt-5">
                            <div
                                v-for="rule in result.rules"
                                :key="rule.code"
                                class="grid grid-cols-[36px_1fr_44px] items-center gap-3 text-xs"
                            >
                                <span class="mono font-medium" :style="{ color: rule.color }">
                                    {{ rule.code }}
                                </span>
                                <div class="relative h-1.5 overflow-hidden rounded-full bg-[var(--border)]/50">
                                    <div
                                        class="absolute inset-y-0 left-0 rounded-full transition-all duration-500"
                                        :style="{
                                            width: `${Math.max(rule.alpha * 100, rule.alpha > 0 ? 6 : 0)}%`,
                                            backgroundColor: rule.color,
                                        }"
                                    ></div>
                                </div>
                                <span class="mono text-right tabular-nums text-[var(--muted)]">
                                    α={{ rule.alpha.toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <p class="mt-5 text-[11px] leading-relaxed text-[var(--muted)]">
                            <Sparkles class="-mt-0.5 mr-0.5 inline-block h-3 w-3 text-[var(--primary)]" />
                            Card ini live — geser slider di bawah untuk melihat skor & α berubah real-time.
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <!-- =========================================================
             MARQUEE TICKER
             ========================================================= -->
        <section class="relative z-10 border-y border-[var(--border)] bg-[var(--surface)] py-4 overflow-hidden">
            <div class="marquee-track" aria-hidden="true">
                <div
                    v-for="rep in 2"
                    :key="rep"
                    class="flex items-center gap-10 pr-10"
                >
                    <template v-for="(item, idx) in tickerItems" :key="`${rep}-${idx}`">
                        <span class="font-display whitespace-nowrap text-2xl font-medium text-[var(--ink)]">
                            {{ item }}
                        </span>
                        <span class="font-mono text-2xl text-[var(--primary)]">/</span>
                    </template>
                </div>
            </div>
        </section>

        <!-- =========================================================
             STATS — bento grid with mouse-follow glow
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-7xl px-6 py-24 lg:px-10">
            <div class="mb-10 flex items-baseline justify-between">
                <div>
                    <span class="mono text-[10px] uppercase tracking-[0.25em] text-[var(--primary)]">/ 01 · DALAM ANGKA</span>
                    <h2 class="display-tight mt-2 text-4xl text-[var(--ink)] md:text-5xl">
                        Bukan klaim. <span class="text-[var(--primary)]">Pengukuran.</span>
                    </h2>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div
                    v-for="(s, i) in stats"
                    :key="s.label"
                    class="bento group p-6"
                    :style="{ animationDelay: `${i * 80}ms` }"
                    @mousemove="bentoMouse"
                >
                    <component :is="s.icon" class="h-6 w-6 text-[var(--primary)] transition-transform group-hover:scale-110" :stroke-width="1.6" />
                    <div class="mt-8 flex items-baseline gap-1">
                        <span class="display-tight stat-num text-5xl text-[var(--ink)]">{{ s.value }}</span>
                        <span class="text-sm text-[var(--muted)]">{{ s.unit }}</span>
                    </div>
                    <p class="mt-2 text-sm text-[var(--foreground)]">{{ s.label }}</p>
                </div>
            </div>
        </section>

        <!-- =========================================================
             INTERACTIVE DEMO — sliders + live SVG chart + result
             ========================================================= -->
        <section id="demo" class="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-10">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-16">
                <header class="lg:col-span-5">
                    <span class="mono text-[10px] uppercase tracking-[0.25em] text-[var(--primary)]">/ 02 · DEMO</span>
                    <h2 class="display-tight mt-2 text-5xl text-[var(--ink)] md:text-6xl">
                        Geser. Mesin
                        <span class="text-gradient-blue">menghitung.</span>
                    </h2>
                    <p class="mt-6 text-base leading-relaxed text-[var(--muted)]">
                        Versi mini dari mesin produksi: 3 input → 3 rule → defuzzifikasi
                        weighted-average. Mesin asli memakai 5 input × 75 rule, threshold dapat
                        dikonfigurasi, dan setiap evaluasi dicatat untuk audit.
                    </p>

                    <!-- Live IPK chart -->
                    <div class="card-modern mt-8 p-5">
                        <div class="flex items-center justify-between">
                            <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">μ IPK</span>
                            <span class="mono text-xs text-[var(--foreground)]">x = {{ ipk.toFixed(2) }}</span>
                        </div>
                        <svg
                            :viewBox="`0 0 ${chartW} ${chartH}`"
                            class="mt-3 h-32 w-full"
                            role="img"
                            aria-label="Kurva membership IPK"
                        >
                            <!-- Grid lines -->
                            <g stroke="var(--border)" stroke-width="0.5" opacity="0.5">
                                <line x1="0" :y1="chartH * 0.5" :x2="chartW" :y2="chartH * 0.5" stroke-dasharray="2 4" />
                                <line :x1="chartW * 0.25" y1="0" :x2="chartW * 0.25" :y2="chartH" stroke-dasharray="2 4" />
                                <line :x1="chartW * 0.5" y1="0" :x2="chartW * 0.5" :y2="chartH" stroke-dasharray="2 4" />
                                <line :x1="chartW * 0.75" y1="0" :x2="chartW * 0.75" :y2="chartH" stroke-dasharray="2 4" />
                            </g>
                            <!-- Membership curves -->
                            <path :d="ipkPathRendah()" stroke="var(--danger)" stroke-width="2" fill="none" stroke-linecap="round" />
                            <path :d="ipkPathSedang()" stroke="var(--primary)" stroke-width="2" fill="none" stroke-linecap="round" />
                            <path :d="ipkPathTinggi()" stroke="var(--success)" stroke-width="2" fill="none" stroke-linecap="round" />
                            <!-- Vertical marker at current IPK -->
                            <line
                                :x1="ipkMarkerX"
                                y1="0"
                                :x2="ipkMarkerX"
                                :y2="chartH"
                                stroke="var(--ink)"
                                stroke-width="1.5"
                                stroke-dasharray="3 3"
                                opacity="0.6"
                            />
                            <circle :cx="ipkMarkerX" :cy="chartH - memberships.ipk.rendah * (chartH - 12) - 6" r="4" fill="var(--danger)" />
                            <circle :cx="ipkMarkerX" :cy="chartH - memberships.ipk.sedang * (chartH - 12) - 6" r="4" fill="var(--primary)" />
                            <circle :cx="ipkMarkerX" :cy="chartH - memberships.ipk.tinggi * (chartH - 12) - 6" r="4" fill="var(--success)" />
                        </svg>
                        <div class="mono mt-2 flex justify-between text-[10px] uppercase tracking-wider text-[var(--muted)]">
                            <span>3,00</span><span>3,25</span><span>3,50</span><span>3,75</span><span>4,00</span>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px]">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-[var(--danger)]"></span>
                                <span class="mono text-[var(--muted)]">rendah · {{ memberships.ipk.rendah.toFixed(2) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-[var(--primary)]"></span>
                                <span class="mono text-[var(--muted)]">sedang · {{ memberships.ipk.sedang.toFixed(2) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-[var(--success)]"></span>
                                <span class="mono text-[var(--muted)]">tinggi · {{ memberships.ipk.tinggi.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="lg:col-span-7">
                    <div class="card-modern overflow-hidden p-6 shadow-floating lg:p-8">
                        <!-- Sliders -->
                        <div>
                            <div class="flex items-baseline justify-between">
                                <label for="d-ipk" class="text-sm font-medium text-[var(--foreground)]">IPK</label>
                                <span class="mono stat-num text-2xl font-semibold text-[var(--primary)]">{{ ipk.toFixed(2) }}</span>
                            </div>
                            <input
                                id="d-ipk"
                                v-model.number="ipk"
                                type="range"
                                min="3"
                                max="4"
                                step="0.01"
                                class="fuzzy-slider mt-3"
                                aria-label="IPK"
                                :style="{ '--filled': `${((ipk - 3) / 1) * 100}%` }"
                            />
                            <div class="mono mt-2 flex justify-between text-[10px] uppercase tracking-wider text-[var(--muted)]">
                                <span>3,00</span><span>3,50</span><span>4,00</span>
                            </div>
                        </div>

                        <div class="mt-7">
                            <div class="flex items-baseline justify-between">
                                <label for="d-hsl" class="text-sm font-medium text-[var(--foreground)]">
                                    Penghasilan ortu <span class="text-xs text-[var(--muted)]">(juta/bulan)</span>
                                </label>
                                <span class="mono stat-num text-2xl font-semibold text-[var(--primary)]">{{ penghasilan.toFixed(1) }} jt</span>
                            </div>
                            <input
                                id="d-hsl"
                                v-model.number="penghasilan"
                                type="range"
                                min="1"
                                max="15"
                                step="0.1"
                                class="fuzzy-slider mt-3"
                                aria-label="Penghasilan orang tua"
                                :style="{ '--filled': `${((penghasilan - 1) / 14) * 100}%` }"
                            />
                            <div class="mono mt-2 flex justify-between text-[10px] uppercase tracking-wider text-[var(--muted)]">
                                <span>1 jt</span><span>8 jt</span><span>15 jt</span>
                            </div>
                        </div>

                        <div class="mt-7">
                            <div class="flex items-baseline justify-between">
                                <label for="d-pa" class="text-sm font-medium text-[var(--foreground)]">
                                    Prestasi akademis <span class="text-xs text-[var(--muted)]">(poin)</span>
                                </label>
                                <span class="mono stat-num text-2xl font-semibold text-[var(--primary)]">{{ prestasi }}</span>
                            </div>
                            <input
                                id="d-pa"
                                v-model.number="prestasi"
                                type="range"
                                min="0"
                                max="50"
                                step="1"
                                class="fuzzy-slider mt-3"
                                aria-label="Prestasi akademis"
                                :style="{ '--filled': `${(prestasi / 50) * 100}%` }"
                            />
                            <div class="mono mt-2 flex justify-between text-[10px] uppercase tracking-wider text-[var(--muted)]">
                                <span>0</span><span>25</span><span>50</span>
                            </div>
                        </div>

                        <!-- Result strip -->
                        <div class="mt-8 grid grid-cols-1 gap-3 rounded-2xl border border-[var(--border)] bg-[var(--primary-light)] p-5 sm:grid-cols-[1fr_auto] sm:items-center">
                            <div>
                                <p class="mono text-[10px] uppercase tracking-[0.22em] text-[var(--primary-dark)]">SKOR DEFUZZIFIKASI</p>
                                <p class="display-tight stat-num mt-1 text-5xl text-[var(--ink)]">
                                    {{ displayScore.toFixed(2) }}
                                </p>
                            </div>
                            <div
                                class="cat-badge inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-white"
                                :style="{ backgroundColor: result.badgeBg }"
                            >
                                <span class="flex h-1.5 w-1.5 rounded-full bg-white/80"></span>
                                {{ result.category }}
                            </div>
                        </div>

                        <!-- Rule eval table mini -->
                        <details class="mt-4 group">
                            <summary class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-xs text-[var(--muted)] hover:bg-[var(--primary-light)] hover:text-[var(--primary)]">
                                <span class="mono uppercase tracking-wider">Show rule evaluations</span>
                                <Plus :size="14" class="group-open:hidden" />
                                <Minus :size="14" class="hidden group-open:block" />
                            </summary>
                            <div class="mt-3 space-y-2 px-3 text-xs">
                                <div
                                    v-for="r in result.rules"
                                    :key="r.code"
                                    class="grid grid-cols-[40px_1fr_60px_60px] items-center gap-3"
                                >
                                    <span class="mono font-medium" :style="{ color: r.color }">{{ r.code }}</span>
                                    <span class="text-[var(--muted)]">{{ r.label }}</span>
                                    <span class="mono text-right text-[var(--foreground)]">α {{ r.alpha.toFixed(3) }}</span>
                                    <span class="mono text-right text-[var(--foreground)]">z {{ r.z.toFixed(2) }}</span>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             HOW IT WORKS — bento steps with arrows
             ========================================================= -->
        <section id="how" class="relative z-10 mx-auto max-w-7xl px-6 py-24 lg:px-10">
            <div class="flex items-baseline justify-between">
                <div>
                    <span class="mono text-[10px] uppercase tracking-[0.25em] text-[var(--primary)]">/ 03 · ALUR</span>
                    <h2 class="display-tight mt-2 text-5xl text-[var(--ink)] md:text-6xl">
                        Empat fase. <span class="text-gradient-blue">Deterministik.</span>
                    </h2>
                </div>
                <span class="mono hidden text-[10px] uppercase tracking-[0.2em] text-[var(--muted)] md:inline">~ 5 menit / batch</span>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <article
                    v-for="(step, i) in [
                        { n: '01', title: 'Daftar', body: 'Mahasiswa isi profil & maks 5 prestasi. Admin approve / reject dengan alasan tertulis.', icon: UsersRound },
                        { n: '02', title: 'Konfigurasi', body: 'Admin atur parameter himpunan (a, b, c) per kriteria. Validasi monotonik aktif.', icon: BookOpenCheck },
                        { n: '03', title: 'Run', body: 'Worker auto-spawn, eligibility 4-gate, fuzzifikasi, inferensi 75-rule, defuzzifikasi.', icon: Cpu },
                        { n: '04', title: 'Audit', body: 'Skor + α tiap rule + z tersimpan. Export CSV / PDF dengan footer reproducibility.', icon: ShieldCheck },
                    ]"
                    :key="step.n"
                    class="bento group p-6"
                    @mousemove="bentoMouse"
                >
                    <div class="flex items-center justify-between">
                        <span class="mono text-3xl font-semibold text-[var(--primary)]">{{ step.n }}</span>
                        <component :is="step.icon" class="h-5 w-5 text-[var(--muted)] transition-colors group-hover:text-[var(--primary)]" :stroke-width="1.6" />
                    </div>
                    <h3 class="display-clean mt-7 text-2xl font-semibold text-[var(--ink)]">{{ step.title }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-[var(--muted)]">{{ step.body }}</p>
                    <div class="mt-6 flex items-center gap-1.5 text-xs font-medium text-[var(--primary)] opacity-0 transition-all group-hover:opacity-100 group-hover:translate-x-1">
                        Detail <ArrowRight :size="12" />
                    </div>
                </article>
            </div>
        </section>

        <!-- =========================================================
             METHODOLOGY — dark inverted block + formula
             ========================================================= -->
        <section id="method" class="relative z-10 my-12">
            <div class="mx-auto max-w-7xl px-6 lg:px-10">
                <div class="relative overflow-hidden rounded-[var(--radius-card)] bg-[var(--ink)] p-10 text-white shadow-floating md:p-16">
                    <div class="pointer-events-none absolute inset-0 bg-grid-dot opacity-20" aria-hidden="true"></div>
                    <div
                        class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full opacity-30 blur-3xl"
                        :style="{ background: 'radial-gradient(closest-side, var(--primary), transparent)' }"
                        aria-hidden="true"
                    ></div>

                    <div class="relative">
                        <span class="mono text-[10px] uppercase tracking-[0.25em] text-[var(--accent)]">/ METODE INFERENSI</span>
                        <h2 class="display-tight mt-3 max-w-4xl text-5xl md:text-7xl">
                            <span class="text-gradient-blue">Tsukamoto,</span>
                            bukan Mamdani.
                        </h2>
                        <p class="mt-6 max-w-2xl text-lg leading-relaxed text-white/70">
                            Setiap rule punya <em class="not-italic font-medium text-white">consequent monotonik</em>.
                            Output bukan distribusi tapi nilai z yang dapat dihitung balik. Defuzzifikasi
                            weighted-average:
                        </p>

                        <!-- Formula card -->
                        <div class="mt-8 inline-flex items-baseline gap-3 rounded-2xl border border-white/15 bg-white/5 px-7 py-5 backdrop-blur">
                            <span class="display-tight text-3xl text-white md:text-4xl">Z</span>
                            <span class="display-tight text-3xl text-white/40 md:text-4xl">=</span>
                            <div class="flex flex-col items-center">
                                <span class="mono text-base text-white md:text-lg">
                                    Σ(αᵢ × zᵢ)
                                </span>
                                <span class="block h-px w-full bg-white/40"></span>
                                <span class="mono text-base text-white md:text-lg">
                                    Σ(αᵢ)
                                </span>
                            </div>
                        </div>

                        <!-- Two columns -->
                        <div class="mt-12 grid grid-cols-1 gap-10 md:grid-cols-2">
                            <div>
                                <h3 class="display-clean text-xl font-medium text-white">5 variabel input</h3>
                                <ul class="mono mt-4 space-y-2 text-sm text-white/70">
                                    <li>· IPK <span class="text-white">(3,00–4,00)</span></li>
                                    <li>· Penghasilan ortu <span class="text-white">(0–15 jt)</span></li>
                                    <li>· Prestasi akademis <span class="text-white">(0–50)</span></li>
                                    <li>· Prestasi non-akademis <span class="text-white">(0–50)</span></li>
                                    <li>· Tanggungan keluarga <span class="text-white">(0–8)</span></li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="display-clean text-xl font-medium text-white">3 kategori output</h3>
                                <ul class="mt-4 space-y-2 text-sm text-white/70">
                                    <li>
                                        <span class="inline-flex h-2 w-2 -translate-y-0.5 rounded-full bg-[var(--success)] mr-2"></span>
                                        <span class="text-white">Layak</span> — z ≥ threshold₂ (default 75)
                                    </li>
                                    <li>
                                        <span class="inline-flex h-2 w-2 -translate-y-0.5 rounded-full bg-[var(--primary)] mr-2"></span>
                                        <span class="text-white">Dipertimbangkan</span> — threshold₁ ≤ z &lt; threshold₂
                                    </li>
                                    <li>
                                        <span class="inline-flex h-2 w-2 -translate-y-0.5 rounded-full bg-[var(--danger)] mr-2"></span>
                                        <span class="text-white">Tidak Layak</span> — z &lt; threshold₁ (default 50)
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             FAQ
             ========================================================= -->
        <section id="faq" class="relative z-10 mx-auto max-w-4xl px-6 py-24 lg:px-10">
            <span class="mono text-[10px] uppercase tracking-[0.25em] text-[var(--primary)]">/ 04 · TANYA</span>
            <h2 class="display-tight mt-2 text-5xl text-[var(--ink)] md:text-6xl">
                Mungkin kamu <span class="text-[var(--primary)]">bertanya-tanya.</span>
            </h2>

            <div class="mt-10 divide-y divide-[var(--border)] border-y border-[var(--border)]">
                <div v-for="(item, i) in faqs" :key="i">
                    <button
                        type="button"
                        class="group flex w-full items-baseline justify-between gap-6 py-6 text-left transition-colors hover:text-[var(--primary)]"
                        :aria-expanded="openFaq === i"
                        @click="openFaq = openFaq === i ? -1 : i"
                    >
                        <span class="display-clean text-2xl font-medium md:text-3xl">{{ item.q }}</span>
                        <span class="mt-1 grid h-9 w-9 flex-shrink-0 place-items-center rounded-full border border-[var(--border)] transition-all group-hover:border-[var(--primary)] group-hover:bg-[var(--primary)] group-hover:text-white">
                            <Plus v-if="openFaq !== i" :size="16" />
                            <Minus v-else :size="16" />
                        </span>
                    </button>
                    <Transition
                        enter-active-class="transition-all duration-400 ease-out overflow-hidden"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-96"
                        leave-active-class="transition-all duration-300 ease-in overflow-hidden"
                        leave-from-class="opacity-100 max-h-96"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div v-if="openFaq === i" class="overflow-hidden">
                            <p class="max-w-3xl pb-6 pr-12 text-base leading-relaxed text-[var(--muted)]">
                                {{ item.a }}
                            </p>
                        </div>
                    </Transition>
                </div>
            </div>
        </section>

        <!-- =========================================================
             FINAL CTA — hero blue gradient
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-10">
            <div class="relative overflow-hidden rounded-[var(--radius-card)] p-10 md:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-[var(--primary)] to-[var(--primary-dark)]"></div>
                <div class="pointer-events-none absolute inset-0 bg-grid-dot opacity-20" aria-hidden="true"></div>
                <div
                    class="pointer-events-none absolute -right-20 -top-20 h-96 w-96 rounded-full opacity-40 blur-3xl"
                    :style="{ background: 'radial-gradient(closest-side, var(--accent), transparent)' }"
                    aria-hidden="true"
                ></div>

                <div class="relative">
                    <span class="mono text-[10px] uppercase tracking-[0.25em] text-white/80">/ 05 · MULAI</span>
                    <h2 class="display-tight mt-3 max-w-3xl text-5xl text-white md:text-7xl">
                        Daftar sekarang.<br />
                        <span class="opacity-90">Verifikasi dalam 24 jam.</span>
                    </h2>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/85">
                        Setelah disetujui admin, kamu bisa mengisi prestasi & data, lalu menunggu
                        hasil seleksi terbaru. Status & ranking publik tersedia di dashboard.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-3">
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center gap-2 rounded-full bg-white px-7 py-4 text-sm font-medium text-[var(--primary-dark)] transition-all hover:bg-[var(--ink)] hover:text-white"
                        >
                            Daftar gratis
                            <ArrowUpRight :size="16" class="transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                        </Link>
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-white underline-offset-4 hover:underline"
                        >
                            Sudah punya akun? Masuk →
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             FOOTER
             ========================================================= -->
        <footer class="relative z-10 mx-auto max-w-7xl px-6 pb-16 pt-10 lg:px-10">
            <div class="flex flex-col gap-10 border-t border-[var(--border)] pt-12 md:flex-row md:items-start md:justify-between">
                <div class="max-w-md">
                    <div class="flex items-center gap-2.5">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-[var(--primary)] text-white">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 17 L10 7 L13 13 L19 5" />
                            </svg>
                        </span>
                        <span class="font-display text-lg font-semibold tracking-tight">Trimexas</span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-[var(--muted)]">
                        Sistem Pendukung Keputusan beasiswa berbasis Fuzzy Tsukamoto.
                        Dibangun untuk Praktikum AI Semester 4 — Kelompok 4.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-x-12 gap-y-2 text-sm">
                    <div>
                        <p class="mono mb-3 text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Tim</p>
                        <ul class="space-y-1">
                            <li v-for="m in team" :key="m" class="text-[var(--foreground)]">{{ m }}</li>
                        </ul>
                    </div>
                    <div>
                        <p class="mono mb-3 text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Aksi</p>
                        <ul class="space-y-1">
                            <li><Link :href="route('register')" class="link-draw text-[var(--foreground)]">Daftar</Link></li>
                            <li><Link :href="route('login')" class="link-draw text-[var(--foreground)]">Masuk</Link></li>
                            <li><button type="button" class="link-draw text-[var(--foreground)]" @click="scrollTo('demo')">Demo</button></li>
                            <li><button type="button" class="link-draw text-[var(--foreground)]" @click="scrollTo('faq')">FAQ</button></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mono mt-10 flex flex-col items-start justify-between gap-3 text-[10px] uppercase tracking-[0.2em] text-[var(--muted)] md:flex-row">
                <span>© 2026 Trimexas — All rights observed</span>
                <span>v1.0 · Built with deliberate precision</span>
            </div>
        </footer>
    </main>
</template>
