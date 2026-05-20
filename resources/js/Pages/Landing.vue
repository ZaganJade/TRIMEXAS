<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, onMounted, ref, watch } from "vue";
import {
    ArrowUpRight,
    ArrowRight,
    ArrowDown,
    Sparkles,
    SunMedium,
    MoonStar,
    Asterisk,
    Plus,
    Minus,
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
   Live fuzzy demo (interactive)
   IPK + Penghasilan + Prestasi → Skor
   =========================== */
const ipk = ref(3.65);
const penghasilan = ref(3.5); // dalam juta
const prestasi = ref(22);

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
const muTinggiHsl = (x) => {
    if (x <= 7) return 0;
    if (x >= 10) return 1;
    return (x - 7) / 3;
};
const muBanyakPrestasi = (x) => {
    if (x <= 15) return 0;
    if (x >= 25) return 1;
    return (x - 15) / 10;
};

// Mini-Tsukamoto — tiga rule sederhana untuk demo
const result = computed(() => {
    const a = muTinggiIpk(ipk.value);
    const b = muRendahHsl(penghasilan.value);
    const c = muBanyakPrestasi(prestasi.value);

    // R1: ipk=tinggi & hsl=rendah & prestasi=banyak → LAYAK (z = 75 + α*25)
    // R2: ipk=sedang OR hsl=sedang → DIPERTIMBANGKAN (z = 50 + α*25)
    // R3: ipk=rendah → TIDAK (z = 50 - α*50)
    const r1 = Math.min(a, b, c);
    const r2 = Math.min(muSedangIpk(ipk.value), muTinggiHsl(penghasilan.value));
    const r3 = muRendahIpk(ipk.value);

    const num = r1 * (75 + r1 * 25) + r2 * (50 + r2 * 25) + r3 * (50 - r3 * 50);
    const den = r1 + r2 + r3;
    const score = den > 0 ? num / den : 0;

    let category = "Tidak Layak";
    let color = "var(--secondary)";
    if (score >= 75) {
        category = "Layak";
        color = "var(--accent)";
    } else if (score >= 50) {
        category = "Dipertimbangkan";
        color = "var(--tertiary)";
    }

    return {
        score: Math.round(score * 100) / 100,
        category,
        color,
        rules: [
            { label: "R1 · LAYAK", alpha: r1, fired: r1 > 0 },
            { label: "R2 · DIPERTIMBANGKAN", alpha: r2, fired: r2 > 0 },
            { label: "R3 · TIDAK LAYAK", alpha: r3, fired: r3 > 0 },
        ],
    };
});

/* ===========================
   Smooth scroll helper
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
        a: "Pembobotan linear membutuhkan bobot tetap dan threshold kaku — sulit menangani ketidakpastian. Tsukamoto menerima nilai berderajat (μ ∈ 0..1), menjalankan inferensi linguistik (IPK tinggi DAN penghasilan rendah → layak), dan menghasilkan skor yang traceable per rule.",
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
        a: "Baseline pengukuran kami: 1,29 detik dengan worker self-spawn dan chunking 50 kandidat per job. Target NFR ≤ 5 menit. Selisih ~230x cadangan, jadi aman walau di hardware lemah.",
    },
];

/* ===========================
   Stats data
   =========================== */
const stats = [
    { value: "75", unit: "rule", label: "Knowledge base aktif" },
    { value: "5", unit: "var", label: "Variabel input fuzzy" },
    { value: "≤0,01", unit: "selisih", label: "vs perhitungan manual" },
    { value: "1,29", unit: "detik", label: "untuk 1.000 kandidat" },
];

const team = [
    "Muhammad Ikhsanudin Arsalan",
    "Ahmad Irsyad Zahrani Nur Abdullah",
    "Muhammad Javier Rakha Abhista",
    "Muhammad Rizki Ibrahim",
];

/* ===========================
   Marquee items (editorial ticker)
   =========================== */
const tickerItems = [
    "Fuzzy Tsukamoto",
    "Audit-trail lengkap",
    "Privacy by design",
    "Reproducible per-batch",
    "Open-source friendly",
    "Built for AI Praktikum",
    "75 rule × 5 variabel",
    "Eligibility 4-gate",
];
</script>

<template>
    <Head title="Trimexas — SPK Beasiswa Fuzzy Tsukamoto" />

    <main class="relative min-h-screen overflow-x-clip bg-[var(--background)] bg-noise text-[var(--foreground)]">
        <!-- =========================================================
             ATMOSPHERE — paper texture + dotted grid + floating glyphs
             ========================================================= -->
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute inset-0 bg-grid-dot opacity-50"></div>
            <div
                class="absolute -left-32 top-32 h-80 w-80 rounded-full opacity-40 blur-[100px]"
                :style="{ background: 'radial-gradient(closest-side, var(--accent-soft), transparent)' }"
                aria-hidden="true"
            ></div>
            <div
                class="absolute -right-20 top-[600px] h-96 w-96 rounded-full opacity-30 blur-[120px]"
                :style="{ background: 'radial-gradient(closest-side, var(--secondary-soft), transparent)' }"
                aria-hidden="true"
            ></div>
        </div>

        <!-- Decorative floating asterisks -->
        <Asterisk
            class="absolute right-[10%] top-32 h-12 w-12 text-[var(--accent)] float-slow opacity-50"
            aria-hidden="true"
            :stroke-width="1"
        />
        <Asterisk
            class="absolute left-[8%] top-[55vh] h-8 w-8 text-[var(--secondary)] spin-slow opacity-40"
            aria-hidden="true"
            :stroke-width="1.2"
        />

        <!-- =========================================================
             NAV — minimal editorial masthead
             ========================================================= -->
        <header class="relative z-30">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-10" aria-label="Navigasi utama">
                <Link href="/" class="group flex items-baseline gap-2">
                    <span class="display-italic text-2xl font-semibold tracking-tight text-[var(--accent)]">
                        Trimexas<sup class="text-xs">®</sup>
                    </span>
                    <span class="hidden text-[10px] uppercase tracking-[0.2em] text-[var(--muted)] sm:inline">
                        Est. 2026 · Kelompok 4
                    </span>
                </Link>

                <div class="flex items-center gap-6">
                    <button
                        type="button"
                        class="hidden text-sm link-draw text-[var(--muted)] hover:text-[var(--foreground)] md:inline-block"
                        @click="scrollTo('demo')"
                    >
                        Coba demo
                    </button>
                    <button
                        type="button"
                        class="hidden text-sm link-draw text-[var(--muted)] hover:text-[var(--foreground)] md:inline-block"
                        @click="scrollTo('how')"
                    >
                        Cara kerja
                    </button>
                    <button
                        type="button"
                        class="hidden text-sm link-draw text-[var(--muted)] hover:text-[var(--foreground)] md:inline-block"
                        @click="scrollTo('faq')"
                    >
                        FAQ
                    </button>

                    <button
                        type="button"
                        class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] bg-[var(--paper)] text-[var(--foreground)] transition-colors hover:border-[var(--accent)] hover:text-[var(--accent)]"
                        :aria-label="isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap'"
                        @click="toggleTheme"
                    >
                        <SunMedium v-if="isDark" :size="16" />
                        <MoonStar v-else :size="16" />
                    </button>

                    <Link
                        :href="route('login')"
                        class="group inline-flex items-center gap-1.5 rounded-full border border-[var(--ink)] bg-[var(--ink)] px-4 py-2 text-sm font-medium text-[var(--paper)] transition-all hover:bg-[var(--accent)] hover:border-[var(--accent)]"
                    >
                        Masuk
                        <ArrowUpRight :size="14" class="transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                    </Link>
                </div>
            </nav>
        </header>

        <!-- =========================================================
             HERO — kinetic editorial spread
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-7xl px-6 pt-12 pb-20 lg:px-10">
            <!-- Issue tag -->
            <div class="reveal-fade flex items-center gap-3 text-[11px] font-medium uppercase tracking-[0.25em] text-[var(--muted)]">
                <span class="inline-block h-2 w-2 rounded-full bg-[var(--accent)] ticker-pulse"></span>
                <span>Vol. 01 / Issue MVP / 2026</span>
                <span class="hidden h-px flex-1 bg-[var(--border)] sm:inline-block"></span>
                <span class="hidden text-[var(--muted)] sm:inline">Triv × MEXC Foundation</span>
            </div>

            <!-- Massive kinetic display -->
            <h1 class="display-tight reveal-wonk mt-8 text-[clamp(3rem,11vw,9rem)] font-light text-[var(--ink)]">
                <span class="block">Beasiswa</span>
                <span class="block">tanpa
                    <span class="display-italic font-light text-[var(--accent)]">tebak</span>—
                    <span class="display-italic font-light text-[var(--accent)]">tebakan</span>.
                </span>
            </h1>

            <!-- Lead paragraph + CTA in editorial 2-col grid -->
            <div class="reveal mt-10 grid grid-cols-1 items-end gap-10 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <p class="max-w-2xl text-lg leading-relaxed text-[var(--muted)] md:text-xl">
                        Sebuah sistem pendukung keputusan untuk seleksi beasiswa Triv × MEXC
                        Foundation. Dibangun di atas <strong class="text-[var(--foreground)]">metode Fuzzy
                        Tsukamoto</strong> — bukan bobot tetap. Setiap skor punya jejak. Setiap
                        keputusan dapat dipertanggungjawabkan.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center gap-2 rounded-full bg-[var(--accent)] px-6 py-3.5 text-sm font-medium text-[var(--accent-foreground)] transition-all hover:bg-[var(--accent-deep)] vermillion-glow"
                        >
                            Daftar sebagai mahasiswa
                            <ArrowRight :size="16" class="transition-transform group-hover:translate-x-1" />
                        </Link>

                        <button
                            type="button"
                            class="group inline-flex items-center gap-2 rounded-full border border-[var(--ink)] px-6 py-3.5 text-sm font-medium text-[var(--ink)] transition-all hover:bg-[var(--ink)] hover:text-[var(--paper)]"
                            @click="scrollTo('demo')"
                        >
                            <span class="kinetic-word">
                                <span>C</span><span>o</span><span>b</span><span>a</span>
                            </span>
                            demo interaktif
                            <ArrowDown :size="14" class="transition-transform group-hover:translate-y-0.5" />
                        </button>
                    </div>
                </div>

                <!-- Hero card: a "live result" mock -->
                <aside class="lg:col-span-5">
                    <div class="paper-card relative overflow-hidden rounded-[var(--radius-card)] p-6 ink-shadow">
                        <div class="flex items-center justify-between text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--muted)]">
                            <span>Hasil seleksi</span>
                            <span class="mono">SNAPSHOT #2026.05.20</span>
                        </div>

                        <div class="mt-4 flex items-baseline gap-3">
                            <span class="display-tight text-7xl font-semibold text-[var(--ink)]">{{ result.score.toFixed(2) }}</span>
                            <span class="text-sm text-[var(--muted)]">/ 100</span>
                        </div>
                        <p class="display-italic mt-1 text-2xl text-[var(--accent)]">{{ result.category }}</p>

                        <div class="mt-6 space-y-2">
                            <div
                                v-for="rule in result.rules"
                                :key="rule.label"
                                class="flex items-center gap-3 text-xs"
                            >
                                <span class="mono w-44 truncate text-[var(--muted)]">{{ rule.label }}</span>
                                <div class="relative h-1.5 flex-1 overflow-hidden rounded-full bg-[var(--paper-deeper)]">
                                    <div
                                        class="absolute inset-y-0 left-0 rounded-full transition-all duration-500"
                                        :class="rule.fired ? 'bg-[var(--accent)]' : 'bg-[var(--border)]'"
                                        :style="{ width: `${Math.max(rule.alpha * 100, rule.fired ? 8 : 0)}%` }"
                                    ></div>
                                </div>
                                <span class="mono w-12 text-right text-[var(--foreground)]">α={{ rule.alpha.toFixed(2) }}</span>
                            </div>
                        </div>

                        <p class="mt-6 border-t border-[var(--border-subtle)] pt-4 text-[11px] leading-relaxed text-[var(--muted)]">
                            Hasil di atas <strong>live</strong> — geser slider di bagian
                            <button class="text-[var(--accent)] underline" @click="scrollTo('demo')">demo</button>
                            untuk melihat skor berubah real-time.
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <!-- =========================================================
             MARQUEE — editorial ticker
             ========================================================= -->
        <section class="relative z-10 border-y border-[var(--border)] bg-[var(--paper)] py-5 overflow-hidden">
            <div class="marquee-track">
                <div
                    v-for="rep in 2"
                    :key="rep"
                    class="flex items-center gap-12 pr-12 text-2xl text-[var(--ink)]"
                    aria-hidden="true"
                >
                    <template v-for="(item, idx) in tickerItems" :key="`${rep}-${idx}`">
                        <span class="display-italic whitespace-nowrap font-light">{{ item }}</span>
                        <Asterisk class="h-5 w-5 flex-shrink-0 text-[var(--accent)]" :stroke-width="1.5" />
                    </template>
                </div>
            </div>
        </section>

        <!-- =========================================================
             STATS — bento number tiles
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-10">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div
                    v-for="(s, i) in stats"
                    :key="s.label"
                    class="stat-tile reveal-slide"
                    :style="{ animationDelay: `${i * 80}ms` }"
                >
                    <div class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">
                        / {{ String(i + 1).padStart(2, "0") }}
                    </div>
                    <div class="mt-3 flex items-baseline gap-1">
                        <span class="display-tight text-5xl font-semibold text-[var(--ink)]">{{ s.value }}</span>
                        <span class="text-sm text-[var(--muted)]">{{ s.unit }}</span>
                    </div>
                    <p class="mt-2 text-sm text-[var(--foreground)]">{{ s.label }}</p>
                </div>
            </div>
        </section>

        <!-- =========================================================
             INTERACTIVE DEMO — geser slider untuk lihat skor live
             ========================================================= -->
        <section id="demo" class="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-10">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-20">
                <div class="lg:col-span-5">
                    <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">/ 02 — DEMO</span>
                    <h2 class="display-tight mt-3 text-5xl font-semibold text-[var(--ink)] md:text-6xl">
                        Geser. Lihat
                        <span class="display-italic text-[var(--accent)]">angkanya</span>
                        bekerja.
                    </h2>
                    <p class="mt-6 text-base leading-relaxed text-[var(--muted)]">
                        Versi mini dari mesin asli. Tiga input, tiga rule simbolik. Skor akhir hasil
                        defuzzifikasi weighted-average dari α-predikat per rule. Mesin produksi memakai
                        5 variabel × 75 rule.
                    </p>
                </div>

                <div class="lg:col-span-7">
                    <div class="paper-card rounded-[var(--radius-card)] p-6 lg:p-8 ink-shadow">
                        <!-- IPK -->
                        <div>
                            <div class="flex items-baseline justify-between">
                                <label class="text-sm font-medium text-[var(--foreground)]" for="d-ipk">IPK</label>
                                <span class="mono text-2xl font-semibold text-[var(--accent)]">{{ ipk.toFixed(2) }}</span>
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
                            />
                            <div class="mono mt-2 flex justify-between text-[10px] uppercase tracking-wider text-[var(--muted)]">
                                <span>3,00</span><span>3,50</span><span>4,00</span>
                            </div>
                        </div>

                        <!-- Penghasilan -->
                        <div class="mt-7">
                            <div class="flex items-baseline justify-between">
                                <label class="text-sm font-medium text-[var(--foreground)]" for="d-hsl">
                                    Penghasilan ortu <span class="text-xs text-[var(--muted)]">(juta/bulan)</span>
                                </label>
                                <span class="mono text-2xl font-semibold text-[var(--accent)]">{{ penghasilan.toFixed(1) }} jt</span>
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
                            />
                            <div class="mono mt-2 flex justify-between text-[10px] uppercase tracking-wider text-[var(--muted)]">
                                <span>1 jt</span><span>8 jt</span><span>15 jt</span>
                            </div>
                        </div>

                        <!-- Prestasi -->
                        <div class="mt-7">
                            <div class="flex items-baseline justify-between">
                                <label class="text-sm font-medium text-[var(--foreground)]" for="d-pa">
                                    Prestasi akademis <span class="text-xs text-[var(--muted)]">(poin)</span>
                                </label>
                                <span class="mono text-2xl font-semibold text-[var(--accent)]">{{ prestasi }}</span>
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
                            />
                            <div class="mono mt-2 flex justify-between text-[10px] uppercase tracking-wider text-[var(--muted)]">
                                <span>0</span><span>25</span><span>50</span>
                            </div>
                        </div>

                        <!-- Live result strip -->
                        <div class="mt-8 flex items-center justify-between rounded-[var(--radius-card)] border border-[var(--border)] bg-[var(--paper-deeper)] px-5 py-4">
                            <div>
                                <p class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Skor akhir</p>
                                <p class="display-tight mt-1 text-4xl font-semibold text-[var(--ink)]">
                                    {{ result.score.toFixed(2) }}
                                </p>
                            </div>
                            <div
                                class="display-italic rounded-full px-4 py-2 text-sm font-medium transition-colors"
                                :style="{ background: result.color, color: 'var(--paper)' }"
                            >
                                {{ result.category }}
                            </div>
                        </div>

                        <p class="mt-3 text-xs text-[var(--muted)]">
                            <Sparkles class="-mt-0.5 mr-1 inline-block h-3 w-3" />
                            Demo memakai 3 dari 75 rule asli. Mesin produksi punya 5 input.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             HOW IT WORKS — editorial step bento
             ========================================================= -->
        <section id="how" class="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-10">
            <div class="flex items-baseline justify-between">
                <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">/ 03 — CARA KERJA</span>
                <span class="mono hidden text-[10px] uppercase tracking-[0.2em] text-[var(--muted)] sm:inline">4 fase · deterministik</span>
            </div>

            <h2 class="display-tight mt-3 max-w-4xl text-5xl font-semibold text-[var(--ink)] md:text-6xl">
                Dari pendaftaran sampai
                <span class="display-italic text-[var(--accent)]">audit-trail</span>.
            </h2>

            <div class="mt-12 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                <article
                    v-for="(step, i) in [
                        { n: '01', title: 'Daftar', body: 'Mahasiswa isi profil & maks 5 prestasi. Admin approve / reject dengan alasan.' },
                        { n: '02', title: 'Konfigurasi', body: 'Admin atur parameter himpunan (a, b, c) — disnapshot saat batch dijalankan.' },
                        { n: '03', title: 'Run', body: 'Worker auto-spawn, eligibility 4-gate, fuzzifikasi, inferensi 75-rule, defuzzifikasi.' },
                        { n: '04', title: 'Audit', body: 'Skor + α tiap rule + z tersimpan. Ekspor CSV / PDF dengan footer reproducibility.' }
                    ]"
                    :key="step.n"
                    class="bento-card group p-6"
                    :style="{ animationDelay: `${i * 60}ms` }"
                >
                    <div class="flex items-center justify-between">
                        <span class="mono text-3xl font-semibold text-[var(--accent)]">{{ step.n }}</span>
                        <ArrowUpRight :size="18" class="text-[var(--muted)] transition-all group-hover:text-[var(--accent)] group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                    </div>
                    <h3 class="display-tight mt-6 text-3xl font-semibold text-[var(--ink)]">{{ step.title }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-[var(--muted)]">{{ step.body }}</p>
                </article>
            </div>
        </section>

        <!-- =========================================================
             METHODOLOGY — vermillion break + offset card
             ========================================================= -->
        <section class="relative z-10 my-12 overflow-hidden bg-[var(--ink)] py-24 text-[var(--paper)]">
            <div
                class="pointer-events-none absolute inset-0 bg-grid-dot opacity-10"
                aria-hidden="true"
            ></div>

            <div class="relative mx-auto max-w-7xl px-6 lg:px-10">
                <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--accent)]">/ METODE</span>
                <h2 class="display-tight mt-3 max-w-4xl text-5xl font-light md:text-7xl">
                    <span class="display-italic font-light text-[var(--accent)]">Tsukamoto</span>,
                    bukan Mamdani.
                </h2>
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-[var(--paper-deeper)] opacity-90">
                    Setiap rule punya <em class="display-italic text-[var(--paper)]">consequent monotonik</em>.
                    Output bukan distribusi tapi nilai z yang dapat dihitung balik. Defuzzifikasi
                    weighted-average:
                </p>

                <!-- Formula -->
                <div class="mono mt-8 inline-block rounded-[var(--radius-card)] border border-[var(--paper-deeper)] bg-transparent px-6 py-5 text-2xl text-[var(--paper)]">
                    Z = Σ(αᵢ × zᵢ) / Σ(αᵢ)
                </div>

                <!-- Two columns of detail -->
                <div class="mt-12 grid grid-cols-1 gap-10 md:grid-cols-2">
                    <div>
                        <h3 class="display-tight text-2xl font-medium text-[var(--paper)]">5 variabel input</h3>
                        <ul class="mono mt-4 space-y-2 text-sm text-[var(--paper-deeper)] opacity-80">
                            <li>· IPK <span class="text-[var(--paper)]">(3,00–4,00)</span></li>
                            <li>· Penghasilan ortu <span class="text-[var(--paper)]">(0–15 jt)</span></li>
                            <li>· Prestasi akademis <span class="text-[var(--paper)]">(0–50)</span></li>
                            <li>· Prestasi non-akademis <span class="text-[var(--paper)]">(0–50)</span></li>
                            <li>· Tanggungan keluarga <span class="text-[var(--paper)]">(0–8)</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="display-tight text-2xl font-medium text-[var(--paper)]">3 kategori output</h3>
                        <ul class="mt-4 space-y-2 text-sm text-[var(--paper-deeper)] opacity-80">
                            <li><span class="display-italic text-[var(--accent)]">Layak</span> — z ≥ threshold₂ (default 75)</li>
                            <li><span class="display-italic text-[var(--paper)]">Dipertimbangkan</span> — threshold₁ ≤ z &lt; threshold₂</li>
                            <li><span class="display-italic text-[var(--paper-deeper)]">Tidak Layak</span> — z &lt; threshold₁ (default 50)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             FAQ — accordion editorial
             ========================================================= -->
        <section id="faq" class="relative z-10 mx-auto max-w-4xl px-6 py-20 lg:px-10">
            <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">/ 04 — TANYA JAWAB</span>
            <h2 class="display-tight mt-3 text-5xl font-semibold text-[var(--ink)] md:text-6xl">
                Mungkin kamu
                <span class="display-italic text-[var(--accent)]">bertanya-tanya</span>.
            </h2>

            <div class="mt-10 divide-y divide-[var(--border)] border-y border-[var(--border)]">
                <div v-for="(item, i) in faqs" :key="i">
                    <button
                        type="button"
                        class="group flex w-full items-baseline justify-between gap-6 py-6 text-left transition-colors hover:text-[var(--accent)]"
                        :aria-expanded="openFaq === i"
                        @click="openFaq = openFaq === i ? -1 : i"
                    >
                        <span class="display-tight text-2xl font-medium md:text-3xl">{{ item.q }}</span>
                        <span class="mt-1 grid h-9 w-9 flex-shrink-0 place-items-center rounded-full border border-[var(--border)] transition-all group-hover:border-[var(--accent)] group-hover:bg-[var(--accent)] group-hover:text-[var(--paper)]">
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
             FINAL CTA — bold vermillion editorial banner
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-7xl px-6 py-20 lg:px-10">
            <div class="relative overflow-hidden rounded-[var(--radius-card)] bg-[var(--accent)] p-10 md:p-16 vermillion-glow">
                <div class="pointer-events-none absolute inset-0 bg-grid-dot opacity-15" aria-hidden="true"></div>

                <Asterisk class="absolute right-12 top-12 h-16 w-16 text-[var(--accent-deep)] spin-slow" :stroke-width="1.5" />

                <div class="relative">
                    <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--accent-foreground)] opacity-80">
                        / 05 — MULAI
                    </span>
                    <h2 class="display-tight mt-3 max-w-3xl text-5xl font-semibold text-[var(--accent-foreground)] md:text-7xl">
                        Daftar sekarang.
                        <span class="display-italic block opacity-90">Verifikasi dalam 24 jam.</span>
                    </h2>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-[var(--accent-foreground)] opacity-85">
                        Setelah disetujui admin, kamu bisa mengisi prestasi & data, lalu menunggu hasil
                        seleksi terbaru. Status & ranking publik tersedia di dashboard.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-3">
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center gap-2 rounded-full bg-[var(--ink)] px-7 py-4 text-sm font-medium text-[var(--paper)] transition-all hover:bg-[var(--paper)] hover:text-[var(--ink)]"
                        >
                            Daftar gratis
                            <ArrowUpRight :size="16" class="transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                        </Link>
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-[var(--accent-foreground)] underline-offset-4 hover:underline"
                        >
                            Sudah punya akun? Masuk →
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             FOOTER — masthead-style colophon
             ========================================================= -->
        <footer class="relative z-10 mx-auto max-w-7xl px-6 pb-16 pt-10 lg:px-10">
            <div class="flex flex-col gap-10 border-t border-[var(--border)] pt-10 md:flex-row md:items-start md:justify-between">
                <div class="max-w-md">
                    <p class="display-italic text-2xl font-medium text-[var(--accent)]">
                        Trimexas<sup class="text-xs">®</sup>
                    </p>
                    <p class="mt-3 text-sm leading-relaxed text-[var(--muted)]">
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
                <span>Made with deliberate intent · Vol.01 / MVP / 2026</span>
            </div>
        </footer>
    </main>
</template>
