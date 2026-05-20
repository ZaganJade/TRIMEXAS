<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import {
    ArrowUpRight,
    Sparkles,
    BookOpenCheck,
    GitBranch,
    Lock,
    Activity,
    UsersRound,
    SunMedium,
    MoonStar,
    ArrowRight,
} from "lucide-vue-next";
import Button from "@/components/ui/Button.vue";

defineProps({
    canRegister: { type: Boolean, default: true },
    appName: { type: String, default: "Trimexas" },
});

// Theme toggle
const isDark = ref(false);
onMounted(() => {
    isDark.value = document.documentElement.classList.contains("dark");
});
function toggleTheme() {
    isDark.value = !isDark.value;
    window.__setTheme?.(isDark.value ? "dark" : "light");
}

// Scroll-link helper
function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: "smooth", block: "start" });
}

// Live "fuzzy demo" — moves the IPK input across membership functions
const ipkInput = ref(3.55);

const muRendah = (x) => {
    if (x <= 3.25) return 1;
    if (x >= 3.6) return 0;
    return (3.6 - x) / (3.6 - 3.25);
};
const muSedang = (x) => {
    if (x <= 3.25 || x >= 3.75) return 0;
    if (x <= 3.5) return (x - 3.25) / (3.5 - 3.25);
    return (3.75 - x) / (3.75 - 3.5);
};
const muTinggi = (x) => {
    if (x <= 3.6) return 0;
    if (x >= 3.75) return 1;
    return (x - 3.6) / (3.75 - 3.6);
};

const features = [
    {
        icon: BookOpenCheck,
        title: "Metodologi Tertelusur",
        body: "Setiap kandidat menyimpan input crisp, derajat keanggotaan, α-predikat, z, dan skor final di tabel audit yang dapat diekspor.",
    },
    {
        icon: GitBranch,
        title: "Snapshot Per Batch",
        body: "Konfigurasi himpunan dan rule disnapshot ke JSONB saat batch dijalankan. Edit parameter besok, ranking lama tetap reproducible.",
    },
    {
        icon: Lock,
        title: "Privacy By Design",
        body: "Ranking publik mahasiswa hanya membuka Nama, Skor, Status. Penghasilan dan NIM tidak pernah meninggalkan controller.",
    },
    {
        icon: Activity,
        title: "Worker Self-Spawn",
        body: "Eksekusi 1.000 kandidat ≤ 5 menit lewat queue async. Worker dispawn on-demand dan auto-exit saat queue kosong.",
    },
    {
        icon: UsersRound,
        title: "Dua Peran, Satu Sistem",
        body: "Admin meng-orkestrasi, mahasiswa mendaftar mandiri & memantau status. Approval workflow Pending → Approved/Rejected dengan jejak.",
    },
    {
        icon: Sparkles,
        title: "Aesthetic, Bukan Dekoratif",
        body: "Setiap pixel di sisi atas funnel adalah investasi pada kepercayaan dosen, yayasan, dan calon penerima.",
    },
];

const steps = [
    {
        n: "01",
        title: "Daftar",
        body: "Mahasiswa mengisi profil + maks 5 prestasi. Admin verifikasi akun & entri.",
    },
    {
        n: "02",
        title: "Run Selection",
        body: "Admin trigger batch. Snapshot diambil, worker bekerja, progress live.",
    },
    {
        n: "03",
        title: "Audit",
        body: "Ranking + audit trail per kandidat siap diekspor CSV / PDF dengan jejak metode.",
    },
];

const team = [
    "Muhammad Ikhsanudin Arsalan",
    "Ahmad Irsyad Zahrani Nur Abdullah",
    "Muhammad Javier Rakha Abhista",
    "Muhammad Rizki Ibrahim",
];
</script>

<template>
    <Head title="Sistem Pendukung Keputusan Beasiswa Fuzzy Tsukamoto" />

    <div class="relative min-h-screen overflow-x-clip bg-[var(--background)]">
        <!-- ATMOSPHERE: grid + noise + radial glow -->
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute inset-0 bg-grid"></div>
            <div
                class="absolute left-1/2 top-[-280px] h-[640px] w-[920px] -translate-x-1/2 rounded-full opacity-70 blur-[120px]"
                :style="{ background: 'radial-gradient(closest-side, var(--primary-soft), transparent)' }"
                aria-hidden="true"
            ></div>
            <div class="absolute inset-0 bg-noise"></div>
        </div>

        <!-- TOP NAV -->
        <header class="relative z-20">
            <nav
                class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-10"
                aria-label="Navigasi utama"
            >
                <Link
                    href="/"
                    class="group inline-flex items-center gap-2 font-display text-base font-semibold tracking-tight"
                >
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-[var(--primary)] text-[var(--primary-foreground)] transition-transform group-hover:rotate-[-6deg]"
                        aria-hidden="true"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                            <path d="M3 18 L8 6 L13 14 L17 8 L21 18" />
                        </svg>
                    </span>
                    {{ appName }}
                </Link>

                <ul class="hidden items-center gap-7 text-sm text-[var(--muted)] lg:flex" role="menubar">
                    <li><button class="hover:text-[var(--foreground)] transition-colors" @click="scrollTo('how')">Cara Kerja</button></li>
                    <li><button class="hover:text-[var(--foreground)] transition-colors" @click="scrollTo('features')">Fitur</button></li>
                    <li><button class="hover:text-[var(--foreground)] transition-colors" @click="scrollTo('method')">Metodologi</button></li>
                    <li><button class="hover:text-[var(--foreground)] transition-colors" @click="scrollTo('about')">Tim</button></li>
                </ul>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        :aria-label="isDark ? 'Aktifkan light mode' : 'Aktifkan dark mode'"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-[var(--radius-card)] border border-[var(--border)] bg-[var(--surface)] text-[var(--muted)] transition hover:text-[var(--primary)] hover:border-[var(--primary)]/40"
                        @click="toggleTheme"
                    >
                        <component :is="isDark ? SunMedium : MoonStar" :size="16" />
                    </button>
                    <Button href="/login" variant="ghost" size="sm" class="hidden sm:inline-flex">
                        Login Admin
                    </Button>
                    <Button v-if="canRegister" href="/register" variant="primary" size="sm">
                        Daftar
                        <ArrowRight :size="14" />
                    </Button>
                </div>
            </nav>
        </header>

        <!-- HERO -->
        <section class="relative">
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-14 px-6 py-12 lg:grid-cols-12 lg:gap-10 lg:px-10 lg:py-24">
                <div class="lg:col-span-7 reveal">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--surface)] px-3 py-1 text-xs font-medium uppercase tracking-[0.16em] text-[var(--muted)]"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--primary)]" aria-hidden="true"></span>
                        Triv × MEXC Foundation
                    </span>

                    <h1 class="mt-6 font-display text-[44px] font-semibold leading-[1.05] tracking-[-0.03em] text-[var(--foreground)] sm:text-6xl lg:text-7xl">
                        Penilaian beasiswa
                        <span class="relative inline-block whitespace-nowrap">
                            <span class="relative z-10">tanpa tebak-tebakan</span>
                            <span class="absolute inset-x-0 bottom-2 z-0 h-3 bg-[var(--primary-soft)]" aria-hidden="true"></span>
                        </span>
                        — hanya angka, jejak, dan keputusan yang bisa kamu pertanggungjawabkan.
                    </h1>

                    <p class="mt-7 max-w-xl text-base leading-relaxed text-[var(--muted)] sm:text-lg">
                        Trimexas memproses lima variabel non-deterministik dengan mesin Fuzzy
                        Tsukamoto, menghasilkan skor 0–100 yang dapat diaudit dan diulang
                        — termasuk batch yang dijalankan minggu lalu.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        <Button v-if="canRegister" href="/register" variant="primary" size="lg">
                            Daftar Sebagai Mahasiswa
                            <ArrowUpRight :size="18" />
                        </Button>
                        <Button href="/login" variant="outline" size="lg">
                            Login Admin
                        </Button>
                        <button
                            type="button"
                            class="text-sm text-[var(--muted)] underline-offset-4 hover:text-[var(--primary)] hover:underline"
                            @click="scrollTo('method')"
                        >
                            Lihat metodologi →
                        </button>
                    </div>

                    <!-- Trust bar -->
                    <dl class="mt-14 grid grid-cols-3 gap-8 border-t border-[var(--border)] pt-8 text-left">
                        <div>
                            <dt class="text-xs uppercase tracking-[0.18em] text-[var(--muted)]">Variabel</dt>
                            <dd class="mt-2 font-display text-3xl font-semibold tracking-tight">5</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.18em] text-[var(--muted)]">Rule Base</dt>
                            <dd class="mt-2 font-display text-3xl font-semibold tracking-tight">75</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.18em] text-[var(--muted)]">Kandidat / 5 menit</dt>
                            <dd class="mt-2 font-display text-3xl font-semibold tracking-tight">1.000</dd>
                        </div>
                    </dl>
                </div>

                <!-- Hero side: live fuzzy preview -->
                <aside class="lg:col-span-5 reveal" style="animation-delay: 180ms">
                    <div class="relative">
                        <div class="absolute -inset-6 -z-10 rotate-[-3deg] rounded-[1.5rem] bg-[var(--primary-soft)] opacity-60 blur-2xl" aria-hidden="true"></div>

                        <div class="rounded-[var(--radius-card)] border border-[var(--border)] bg-[var(--surface)] p-6 shadow-[var(--shadow-card)]">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-[var(--muted)]">
                                    Live Fuzzifikasi · IPK
                                </p>
                                <span class="flex items-center gap-1.5 text-xs text-[var(--primary)]">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[var(--primary)]" aria-hidden="true"></span>
                                    realtime
                                </span>
                            </div>

                            <div class="mt-6 flex items-baseline gap-3">
                                <span class="font-display text-5xl font-semibold tracking-tight tabular-nums">
                                    {{ ipkInput.toFixed(2) }}
                                </span>
                                <span class="text-sm text-[var(--muted)]">/ 4.00</span>
                            </div>

                            <input
                                type="range"
                                min="3.0"
                                max="4.0"
                                step="0.01"
                                v-model.number="ipkInput"
                                aria-label="Geser IPK"
                                class="mt-4 w-full accent-[var(--primary)]"
                            />

                            <!-- Three membership bars -->
                            <div class="mt-6 space-y-3">
                                <div v-for="row in [
                                    { label: 'Rendah', mu: muRendah(ipkInput), tone: 'rgba(239, 68, 68, 0.85)' },
                                    { label: 'Sedang', mu: muSedang(ipkInput), tone: 'var(--primary)' },
                                    { label: 'Tinggi', mu: muTinggi(ipkInput), tone: '#22C55E' },
                                ]" :key="row.label" class="text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[var(--muted)]">μ {{ row.label }}</span>
                                        <span class="font-mono text-xs tabular-nums text-[var(--foreground)]">
                                            {{ row.mu.toFixed(3) }}
                                        </span>
                                    </div>
                                    <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-[var(--border)]/60">
                                        <div
                                            class="h-full rounded-full transition-[width] duration-300 ease-[var(--ease-soft)]"
                                            :style="{ width: (row.mu * 100).toFixed(1) + '%', background: row.tone }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <p class="mt-6 text-xs text-[var(--muted)]">
                                Geser slider untuk melihat fuzzifikasi linear-turun, segitiga, dan linear-naik bekerja seperti yang
                                dikalkulasi oleh <code class="rounded bg-[var(--primary-soft)] px-1 py-0.5 text-[var(--primary)]">FuzzyEngine::run()</code>.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <!-- HOW IT WORKS -->
        <section id="how" class="relative">
            <div class="mx-auto max-w-7xl px-6 py-20 lg:px-10">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--primary)]">
                            Cara Kerja
                        </p>
                        <h2 class="mt-3 font-display text-4xl font-semibold tracking-tight">
                            Tiga langkah, audit di setiap titik henti.
                        </h2>
                        <p class="mt-4 text-[var(--muted)]">
                            Tidak ada kotak hitam. Setiap pengambilan keputusan
                            menyimpan jejak yang dapat diperiksa kembali oleh dosen,
                            yayasan, atau auditor independen.
                        </p>
                    </div>

                    <ol class="lg:col-span-8 grid grid-cols-1 gap-px rounded-[var(--radius-card)] bg-[var(--border)] sm:grid-cols-3">
                        <li
                            v-for="(s, i) in steps"
                            :key="s.n"
                            class="group relative bg-[var(--surface)] p-7 first:rounded-t-[var(--radius-card)] last:rounded-b-[var(--radius-card)] sm:first:rounded-l-[var(--radius-card)] sm:first:rounded-tr-none sm:last:rounded-r-[var(--radius-card)] sm:last:rounded-bl-none transition hover:bg-[var(--primary-soft)]/40"
                            :style="{ animationDelay: 80 * (i + 1) + 'ms' }"
                        >
                            <span class="font-display text-xs font-medium tracking-[0.2em] text-[var(--primary)]">{{ s.n }}</span>
                            <h3 class="mt-3 font-display text-2xl font-semibold tracking-tight">{{ s.title }}</h3>
                            <p class="mt-3 text-sm text-[var(--muted)]">{{ s.body }}</p>
                            <ArrowRight v-if="i < steps.length - 1" :size="14" class="absolute right-3 top-3 text-[var(--muted)] opacity-0 transition group-hover:opacity-100" />
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section id="features" class="relative">
            <div class="mx-auto max-w-7xl px-6 py-20 lg:px-10">
                <div class="flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-end">
                    <div class="max-w-2xl">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--primary)]">
                            Fitur Inti
                        </p>
                        <h2 class="mt-3 font-display text-4xl font-semibold tracking-tight">
                            Dirancang untuk dipakai, dipertanggungjawabkan, dan diulang.
                        </h2>
                    </div>
                    <p class="max-w-md text-sm text-[var(--muted)]">
                        Bukan dashboard yang penuh ornamen. Setiap modul punya alasan
                        kuat untuk hadir, dan ada ujinya.
                    </p>
                </div>

                <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(f, i) in features"
                        :key="f.title"
                        class="group relative overflow-hidden rounded-[var(--radius-card)] border border-[var(--border)] bg-[var(--surface)] p-6 transition hover:-translate-y-0.5 hover:border-[var(--primary)]/50 hover:shadow-[var(--shadow-card)]"
                        :style="{ animationDelay: 60 * i + 'ms' }"
                    >
                        <div
                            class="inline-flex h-10 w-10 items-center justify-center rounded-[var(--radius-input)] bg-[var(--primary-soft)] text-[var(--primary)] transition group-hover:rotate-[-4deg]"
                            aria-hidden="true"
                        >
                            <component :is="f.icon" :size="18" />
                        </div>
                        <h3 class="mt-5 font-display text-lg font-semibold tracking-tight">{{ f.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[var(--muted)]">{{ f.body }}</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- METHODOLOGY HIGHLIGHT -->
        <section id="method" class="relative">
            <div class="mx-auto max-w-7xl px-6 py-24 lg:px-10">
                <div class="rounded-[1.25rem] border border-[var(--border)] bg-[var(--surface)] p-8 sm:p-12 lg:p-16 shadow-[var(--shadow-card)]">
                    <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                        <div class="lg:col-span-5">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--primary)]">Metodologi</p>
                            <h2 class="mt-3 font-display text-4xl font-semibold tracking-tight">
                                Eligibility → Fuzzifikasi → Inferensi → Defuzz.
                            </h2>
                            <p class="mt-5 text-[var(--muted)]">
                                Empat gerbang boolean menyaring lebih dulu (status aktif,
                                semester, IPK, akun approved). Lalu mesin fuzzy bergerak
                                deterministik: AND = MIN, defuzzifikasi
                                <code class="rounded bg-[var(--primary-soft)] px-1 py-0.5 font-mono text-[var(--primary)]">Z = Σ(αᵢ · zᵢ) / Σαᵢ</code>.
                            </p>
                            <p class="mt-4 text-sm text-[var(--muted)]">
                                Selisih ≤ 0,01 vs perhitungan manual pada 5 kasus uji
                                — bukan klaim, terverifikasi lewat unit test.
                            </p>
                        </div>

                        <!-- Membership chart -->
                        <figure class="lg:col-span-7">
                            <svg viewBox="0 0 480 220" class="w-full" role="img" aria-label="Kurva fungsi keanggotaan IPK: rendah (turun), sedang (segitiga), tinggi (naik)">
                                <defs>
                                    <linearGradient id="grad-l" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0" stop-color="#EF4444" stop-opacity="0.18" />
                                        <stop offset="1" stop-color="#EF4444" stop-opacity="0" />
                                    </linearGradient>
                                    <linearGradient id="grad-m" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0" stop-color="#3189C6" stop-opacity="0.28" />
                                        <stop offset="1" stop-color="#3189C6" stop-opacity="0" />
                                    </linearGradient>
                                    <linearGradient id="grad-h" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0" stop-color="#22C55E" stop-opacity="0.18" />
                                        <stop offset="1" stop-color="#22C55E" stop-opacity="0" />
                                    </linearGradient>
                                </defs>

                                <!-- Axes -->
                                <line x1="40" y1="180" x2="460" y2="180" stroke="var(--border)" stroke-width="1" />
                                <line x1="40" y1="20" x2="40" y2="180" stroke="var(--border)" stroke-width="1" />

                                <!-- Rendah area + line -->
                                <path d="M 40,30 L 200,30 L 280,170 L 40,170 Z" fill="url(#grad-l)" />
                                <path d="M 40,30 L 200,30 L 280,170" stroke="#EF4444" stroke-width="2" fill="none" />

                                <!-- Sedang -->
                                <path d="M 200,170 L 280,30 L 360,170 Z" fill="url(#grad-m)" />
                                <path d="M 200,170 L 280,30 L 360,170" stroke="#3189C6" stroke-width="2.4" fill="none" />

                                <!-- Tinggi -->
                                <path d="M 280,170 L 360,30 L 460,30 L 460,170 Z" fill="url(#grad-h)" />
                                <path d="M 280,170 L 360,30 L 460,30" stroke="#22C55E" stroke-width="2" fill="none" />

                                <!-- Marker -->
                                <line x1="280" y1="20" x2="280" y2="180" stroke="var(--primary)" stroke-dasharray="3 3" stroke-width="1" />
                                <circle cx="280" cy="30" r="4" fill="var(--primary)" />

                                <!-- X labels -->
                                <text x="40" y="200" font-size="10" fill="var(--muted-foreground, var(--muted))">3.00</text>
                                <text x="200" y="200" font-size="10" fill="var(--muted-foreground, var(--muted))">3.25</text>
                                <text x="280" y="200" font-size="10" fill="var(--muted-foreground, var(--muted))" font-weight="600">3.50</text>
                                <text x="360" y="200" font-size="10" fill="var(--muted-foreground, var(--muted))">3.75</text>
                                <text x="446" y="200" font-size="10" fill="var(--muted-foreground, var(--muted))">4.00</text>

                                <!-- Legend -->
                                <g transform="translate(50, 30)" font-size="11" fill="var(--muted-foreground, var(--muted))">
                                    <circle cx="0" cy="0" r="3" fill="#EF4444" /><text x="8" y="3">μ rendah</text>
                                    <circle cx="80" cy="0" r="3" fill="#3189C6" /><text x="88" y="3">μ sedang</text>
                                    <circle cx="160" cy="0" r="3" fill="#22C55E" /><text x="168" y="3">μ tinggi</text>
                                </g>
                            </svg>
                            <figcaption class="mt-2 text-xs text-[var(--muted)]">
                                Kurva keanggotaan kriteria IPK. Boundaries (3,25 / 3,5 / 3,75) ikut di-snapshot per batch.
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>

        <!-- ABOUT / TEAM -->
        <section id="about" class="relative">
            <div class="mx-auto max-w-7xl px-6 py-20 lg:px-10">
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--primary)]">Tentang</p>
                        <h2 class="mt-3 font-display text-4xl font-semibold tracking-tight">
                            Praktikum AI · Kelompok 4 · Semester 4.
                        </h2>
                        <p class="mt-5 text-[var(--muted)]">
                            Trimexas adalah karya akademik dengan rigor produk —
                            bukan slide, melainkan kode yang berjalan. Snapshot
                            metodologi disimpan agar setiap perhitungan dapat
                            diverifikasi ulang oleh siapa pun di kemudian hari.
                        </p>
                    </div>
                    <ul class="lg:col-span-7 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <li
                            v-for="m in team"
                            :key="m"
                            class="rounded-[var(--radius-card)] border border-[var(--border)] bg-[var(--surface)] px-4 py-4 transition hover:-translate-y-px hover:border-[var(--primary)]/40"
                        >
                            <span class="font-display text-base font-medium tracking-tight">{{ m }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- CTA STRIP -->
        <section class="relative">
            <div class="mx-auto max-w-7xl px-6 pb-24 lg:px-10">
                <div class="overflow-hidden rounded-[1.25rem] border border-[var(--primary)]/30 bg-gradient-to-br from-[var(--primary-soft)] to-transparent p-10 sm:p-14">
                    <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                        <div class="max-w-xl">
                            <h3 class="font-display text-3xl font-semibold tracking-tight">
                                Siap diuji secara metodologis?
                            </h3>
                            <p class="mt-3 text-sm text-[var(--muted)]">
                                Mulai dengan akun mahasiswa atau buka dashboard admin.
                                Snapshot pertama selalu cuma satu klik.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <Button v-if="canRegister" href="/register" variant="primary" size="lg">
                                Daftar
                                <ArrowUpRight :size="16" />
                            </Button>
                            <Button href="/login" variant="secondary" size="lg">
                                Login Admin
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-[var(--border)]">
            <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-6 py-10 sm:flex-row sm:items-center lg:px-10">
                <p class="text-sm text-[var(--muted)]">
                    © {{ new Date().getFullYear() }} Trimexas — Praktikum Artificial Intelligence, Kelompok 4.
                </p>
                <p class="text-xs uppercase tracking-[0.2em] text-[var(--muted)]">
                    Triv Foundation × MEXC Foundation
                </p>
            </div>
        </footer>
    </div>
</template>
