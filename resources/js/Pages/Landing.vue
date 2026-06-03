<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { onMounted, onBeforeUnmount, ref, computed, reactive } from "vue";
import {
    ArrowRight,
    ArrowUpRight,
    SunMedium,
    MoonStar,
    ShieldCheck,
    Workflow,
    Gauge,
    Sparkles,
    Users,
    FileSpreadsheet,
    LayoutDashboard,
    BellRing,
    Quote,
    Plus,
    Check,
    ListChecks,
} from "@lucide/vue";

defineProps({
    canRegister: { type: Boolean, default: true },
    appName: { type: String, default: "Trimexas" },
});

/* ===========================================================
   Theme + scroll progress
   =========================================================== */
const isDark = ref(false);
const scrollProgress = ref(0);
let scrollHandler = null;

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle("dark", isDark.value);
    try {
        localStorage.setItem("trimexas-theme", isDark.value ? "dark" : "light");
    } catch (_) {}
}

function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: "smooth", block: "start" });
}

/* ===========================================================
   Magnetic button effect
   =========================================================== */
function magnetic(e) {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    const r = e.currentTarget.getBoundingClientRect();
    const x = e.clientX - (r.left + r.width / 2);
    const y = e.clientY - (r.top + r.height / 2);
    e.currentTarget.style.transform = `translate(${x * 0.18}px, ${y * 0.28}px)`;
}
function magneticReset(e) {
    e.currentTarget.style.transform = "";
}

/* ===========================================================
   Bento mouse-follow glow
   =========================================================== */
function bentoGlow(e) {
    const r = e.currentTarget.getBoundingClientRect();
    e.currentTarget.style.setProperty("--mx", `${e.clientX - r.left}px`);
    e.currentTarget.style.setProperty("--my", `${e.clientY - r.top}px`);
}

/* ===========================================================
   Intersection-observer reveals
   =========================================================== */
let io = null;
function setupReveal() {
    if (typeof IntersectionObserver === "undefined") return;
    io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    io.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: "0px 0px -8% 0px" }
    );
    document.querySelectorAll(".reveal-on-scroll").forEach((el) => io.observe(el));
}

/* ===========================================================
   Devin-style interactive particle field (lightweight canvas)
   =========================================================== */
const fieldCanvas = ref(null);
let raf = null;
let ctx = null;
let nodes = [];
let dpr = 1;
const pointer = { x: -9999, y: -9999, active: false };
let running = false;

function readCanvasColor() {
    const styles = getComputedStyle(document.documentElement);
    return {
        ink: styles.getPropertyValue("--canvas-ink").trim() || "77, 166, 255",
        accent: styles.getPropertyValue("--canvas-accent").trim() || "56, 214, 245",
        alpha: parseFloat(styles.getPropertyValue("--canvas-alpha")) || 0.85,
    };
}

function initField() {
    const canvas = fieldCanvas.value;
    if (!canvas) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    ctx = canvas.getContext("2d");

    const resize = () => {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = window.innerWidth * dpr;
        canvas.height = window.innerHeight * dpr;
        canvas.style.width = window.innerWidth + "px";
        canvas.style.height = window.innerHeight + "px";

        const area = window.innerWidth * window.innerHeight;
        const count = Math.max(28, Math.min(72, Math.floor(area / 26000)));
        nodes = Array.from({ length: count }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            vx: (Math.random() - 0.5) * 0.32 * dpr,
            vy: (Math.random() - 0.5) * 0.32 * dpr,
            r: (Math.random() * 1.6 + 0.6) * dpr,
        }));
    };
    resize();
    window.addEventListener("resize", resize, { passive: true });
    canvas._resize = resize;

    let palette = readCanvasColor();
    canvas._refreshPalette = () => (palette = readCanvasColor());

    const linkDist = 150 * dpr;
    const draw = () => {
        if (!running) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const px = pointer.x * dpr;
        const py = pointer.y * dpr;

        for (const n of nodes) {
            n.x += n.vx;
            n.y += n.vy;
            if (n.x < 0 || n.x > canvas.width) n.vx *= -1;
            if (n.y < 0 || n.y > canvas.height) n.vy *= -1;

            if (pointer.active) {
                const dx = px - n.x;
                const dy = py - n.y;
                const d2 = dx * dx + dy * dy;
                const rad = 200 * dpr;
                if (d2 < rad * rad) {
                    const f = (1 - Math.sqrt(d2) / rad) * 0.6;
                    n.x += (dx / (Math.sqrt(d2) || 1)) * f;
                    n.y += (dy / (Math.sqrt(d2) || 1)) * f;
                }
            }
        }

        for (let i = 0; i < nodes.length; i++) {
            for (let j = i + 1; j < nodes.length; j++) {
                const a = nodes[i];
                const b = nodes[j];
                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const dist = Math.hypot(dx, dy);
                if (dist < linkDist) {
                    const o = (1 - dist / linkDist) * 0.5 * palette.alpha;
                    ctx.strokeStyle = `rgba(${palette.ink}, ${o})`;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }
        }

        for (const n of nodes) {
            let near = false;
            if (pointer.active) {
                const dist = Math.hypot(px - n.x, py - n.y);
                if (dist < linkDist * 1.4) {
                    near = true;
                    const o = (1 - dist / (linkDist * 1.4)) * 0.8 * palette.alpha;
                    ctx.strokeStyle = `rgba(${palette.accent}, ${o})`;
                    ctx.lineWidth = 1.1;
                    ctx.beginPath();
                    ctx.moveTo(px, py);
                    ctx.lineTo(n.x, n.y);
                    ctx.stroke();
                }
            }
            ctx.fillStyle = `rgba(${near ? palette.accent : palette.ink}, ${palette.alpha})`;
            ctx.beginPath();
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fill();
        }
        raf = requestAnimationFrame(draw);
    };

    running = true;
    raf = requestAnimationFrame(draw);

    canvas._visHandler = () => {
        if (document.hidden) {
            running = false;
            if (raf) cancelAnimationFrame(raf);
        } else if (!running) {
            running = true;
            raf = requestAnimationFrame(draw);
        }
    };
    document.addEventListener("visibilitychange", canvas._visHandler);
}

function onPointerMove(e) {
    pointer.x = e.clientX;
    pointer.y = e.clientY;
    pointer.active = true;
}
function onPointerLeave() {
    pointer.active = false;
}

/* ===========================================================
   Live Fuzzy Tsukamoto demo
   =========================================================== */
const fuzzy = reactive({ ipk: 3.4, penghasilan: 2.5, prestasi: 6 });

const clamp01 = (v) => Math.max(0, Math.min(1, v));
const muIpkTinggi = (v) => clamp01((v - 2.5) / (3.8 - 2.5));
const muIpkRendah = (v) => clamp01((3.5 - v) / (3.5 - 2.5));
const muButuh = (v) => clamp01((6 - v) / (6 - 1));
const muMampu = (v) => clamp01((v - 3) / (8 - 3));
const muPrestasiBanyak = (v) => clamp01((v - 2) / (9 - 2));

const fuzzyScore = computed(() => {
    const a1 = Math.min(muIpkTinggi(fuzzy.ipk), muButuh(fuzzy.penghasilan));
    const z1 = 90;
    const a2 = muPrestasiBanyak(fuzzy.prestasi);
    const z2 = 75;
    const a3 = Math.min(muIpkRendah(fuzzy.ipk), muMampu(fuzzy.penghasilan));
    const z3 = 45;
    const num = a1 * z1 + a2 * z2 + a3 * z3;
    const den = a1 + a2 + a3;
    const z = den === 0 ? 60 : num / den;
    return Math.round(z * 10) / 10;
});

const fuzzyVerdict = computed(() => {
    const s = fuzzyScore.value;
    if (s >= 78) return { label: "Sangat Layak", tone: "success" };
    if (s >= 62) return { label: "Layak", tone: "primary" };
    return { label: "Dipertimbangkan", tone: "warning" };
});

const ipkFill = computed(() => `${((fuzzy.ipk - 2) / (4 - 2)) * 100}%`);
const penghasilanFill = computed(() => `${(fuzzy.penghasilan / 10) * 100}%`);
const prestasiFill = computed(() => `${(fuzzy.prestasi / 10) * 100}%`);

/* ===========================================================
   Static content
   =========================================================== */
const stats = [
    { value: "5", label: "Kriteria penilaian" },
    { value: "< 3 mnt", label: "Per batch seleksi" },
    { value: "100%", label: "Hasil dapat ditelusuri" },
];

const features = [
    {
        icon: Gauge,
        title: "Mesin Fuzzy Tsukamoto",
        body: "Lima kriteria diolah jadi satu skor kelayakan yang konsisten dan bisa dijelaskan.",
        span: "col-2",
    },
    {
        icon: ShieldCheck,
        title: "Multi-peran & aman",
        body: "Akses terpisah untuk administrator dan mahasiswa, lengkap dengan verifikasi.",
        span: "col-2",
    },
    {
        icon: BellRing,
        title: "Notifikasi real-time",
        body: "Pengumuman status dan hasil langsung sampai ke setiap kandidat.",
        span: "col-2",
    },
];

const journey = [
    {
        n: "01",
        title: "Mahasiswa mendaftar",
        body: "Mengisi data diri dan prestasi secara mandiri, lalu menunggu verifikasi pengelola.",
    },
    {
        n: "02",
        title: "Pengelola memverifikasi",
        body: "Memeriksa kelengkapan data, lalu menyetujui atau memberi catatan perbaikan.",
    },
    {
        n: "03",
        title: "Sistem menilai",
        body: "Lima kriteria — akademik, prestasi, kondisi keluarga — dirangkum jadi satu skor.",
    },
    {
        n: "04",
        title: "Hasil terbuka",
        body: "Mahasiswa melihat status. Pengelola membawa laporan rapi yang akuntabel.",
    },
];

const audience = [
    {
        kicker: "untuk pengelola",
        title: "Selesaikan seleksi tanpa lembur.",
        bullets: [
            "Verifikasi pendaftar dengan satu klik",
            "Jalankan penilaian satu batch sekaligus",
            "Unduh laporan resmi siap kirim",
        ],
        cta: { label: "Masuk sebagai pengelola", href: "login" },
        tone: "admin",
        icon: LayoutDashboard,
    },
    {
        kicker: "untuk mahasiswa",
        title: "Daftar sekali, pantau kapan saja.",
        bullets: [
            "Isi data dan prestasi secara mandiri",
            "Pantau status verifikasi dari dashboard",
            "Lihat pengumuman hasil saat dibuka",
        ],
        cta: { label: "Mulai pendaftaran", href: "register" },
        tone: "student",
        icon: Users,
    },
];

const partners = [
    "Triv Foundation",
    "MEXC Foundation",
    "Praktikum AI",
    "Kelompok 4",
    "Beasiswa 2026",
    "Fuzzy Tsukamoto",
];

const ranking = [
    { pos: 1, name: "Adelia Putri", mono: "AP", score: 88.4, verdict: "Sangat Layak", tone: "success", w: "92%" },
    { pos: 2, name: "Bagus Pradana", mono: "BP", score: 82.1, verdict: "Layak", tone: "success", w: "84%" },
    { pos: 3, name: "Citra Anindya", mono: "CA", score: 74.6, verdict: "Layak", tone: "primary", w: "75%" },
    { pos: 4, name: "Damar Wijaya", mono: "DW", score: 61.2, verdict: "Dipertimbangkan", tone: "warning", w: "61%" },
];

const faqs = [
    {
        q: "Apakah saya bisa mendaftar sendiri?",
        a: "Ya. Halaman pendaftaran terbuka untuk mahasiswa dengan IPK minimal 3,0 dan masih aktif kuliah. Setelah daftar, akun menunggu verifikasi pengelola.",
    },
    {
        q: "Berapa lama proses verifikasi akun?",
        a: "Pengelola memeriksa pendaftaran setiap hari kerja. Notifikasi disetujui atau ditolak dikirim langsung ke email kamu.",
    },
    {
        q: "Apakah hasilnya bisa saya lihat?",
        a: "Bisa. Setiap mahasiswa punya halaman ranking publik berisi nama, skor, dan status — tanpa membocorkan data sensitif lainnya.",
    },
    {
        q: "Siapa yang membuat sistem ini?",
        a: "Empat mahasiswa Praktikum AI Semester 4, Kelompok 4. Dirancang khusus untuk Triv Foundation × MEXC Foundation.",
    },
];

const teamMembers = [
    { name: "Muhammad Ikhsanudin Arsalan", mono: "MIA" },
    { name: "Ahmad Irsyad Zahrani Nur Abdullah", mono: "AIZ" },
    { name: "Muhammad Javier Rakha Abhista", mono: "MJR" },
    { name: "Muhammad Rizki Ibrahim", mono: "MRI" },
];

const openFaq = ref(0);
function toggleFaq(i) {
    openFaq.value = openFaq.value === i ? -1 : i;
}

function toneClass(tone) {
    return tone === "success"
        ? "tag-success"
        : tone === "warning"
          ? "tag-warning"
          : "tag-primary";
}

onMounted(() => {
    try {
        const stored = localStorage.getItem("trimexas-theme");
        if (stored === null || stored === "light") {
            isDark.value = false;
            document.documentElement.classList.remove("dark");
        } else {
            isDark.value = true;
            document.documentElement.classList.add("dark");
        }
    } catch (_) {
        isDark.value = false;
        document.documentElement.classList.remove("dark");
    }

    setupReveal();
    initField();
    window.addEventListener("pointermove", onPointerMove, { passive: true });
    window.addEventListener("pointerleave", onPointerLeave, { passive: true });

    scrollHandler = () => {
        const h = document.documentElement.scrollHeight - window.innerHeight;
        scrollProgress.value = h > 0 ? window.scrollY / h : 0;
    };
    scrollHandler();
    window.addEventListener("scroll", scrollHandler, { passive: true });
    window.addEventListener("resize", scrollHandler, { passive: true });
});

onBeforeUnmount(() => {
    if (scrollHandler) {
        window.removeEventListener("scroll", scrollHandler);
        window.removeEventListener("resize", scrollHandler);
    }
    window.removeEventListener("pointermove", onPointerMove);
    window.removeEventListener("pointerleave", onPointerLeave);
    if (io) io.disconnect();
    if (raf) cancelAnimationFrame(raf);
    running = false;
    const c = fieldCanvas.value;
    if (c) {
        if (c._resize) window.removeEventListener("resize", c._resize);
        if (c._visHandler) document.removeEventListener("visibilitychange", c._visHandler);
    }
});
</script>

<template>
    <Head title="Trimexas — SPK Beasiswa Fuzzy Tsukamoto" />

    <main class="relative min-h-screen overflow-x-clip bg-[var(--background)] text-[var(--foreground)]">
        <!-- Scroll progress indicator -->
        <span class="scroll-progress-h" :style="{ transform: `scaleX(${scrollProgress})` }" aria-hidden="true" />

        <!-- Interactive canvas backdrop -->
        <canvas ref="fieldCanvas" class="field-canvas -z-20" aria-hidden="true"></canvas>

        <!-- Aurora + grid atmosphere -->
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden bg-noise">
            <div class="aurora aurora-a"></div>
            <div class="aurora aurora-b"></div>
            <div class="aurora aurora-c"></div>
            <div class="absolute inset-0 bg-grid-fade"></div>
        </div>

        <!-- =========================================================
             NAVIGATION
             ========================================================= -->
        <header class="sticky top-0 z-30">
            <div class="mx-auto max-w-[1200px] px-5 pt-4 lg:px-8">
                <nav class="nav-glass flex items-center justify-between px-4 py-2.5 pl-5" aria-label="Navigasi utama">
                    <Link href="/" class="group flex items-center gap-2.5">
                        <span class="brand-mark">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 16 L9 7 L13 12 L20 4" />
                            </svg>
                        </span>
                        <span class="display text-[18px] tracking-tight text-[var(--ink)]">{{ appName }}</span>
                    </Link>

                    <div class="hidden items-center gap-8 text-sm font-medium md:flex">
                        <button class="nav-link" @click="scrollTo('produk')">Produk</button>
                        <button class="nav-link" @click="scrollTo('metode')">Metode</button>
                        <button class="nav-link" @click="scrollTo('alur')">Alur</button>
                        <button class="nav-link" @click="scrollTo('faq')">FAQ</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] bg-[var(--surface)] text-[var(--muted)] transition-colors hover:border-[var(--primary)] hover:text-[var(--primary)]"
                            :aria-label="isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap'"
                            @click="toggleTheme"
                        >
                            <SunMedium v-if="isDark" :size="15" class="theme-icon is-dark" />
                            <MoonStar v-else :size="15" class="theme-icon" />
                        </button>
                        <Link :href="route('login')" class="btn-ghost hidden text-[0.85rem] sm:inline-flex">Masuk</Link>
                        <Link :href="route('register')" class="btn-primary text-[0.85rem]">Daftar</Link>
                    </div>
                </nav>
            </div>
        </header>

        <!-- =========================================================
             HERO — Text centered, diagram removed
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-[1200px] px-5 pt-24 pb-20 text-center lg:pt-32 lg:pb-28">
            <!-- Eyebrow -->
            <div class="mb-8 reveal-on-scroll">
                <span class="eyebrow mx-auto">
                    <span class="dot"></span>
                    <span>Triv Foundation × MEXC Foundation</span>
                </span>
            </div>

            <!-- Headline -->
            <h1 class="display reveal-on-scroll text-[clamp(2.2rem,5.5vw,4rem)] text-[var(--ink)] leading-[1.15]">
                Seleksi beasiswa yang
                <span class="text-gradient">terukur</span> dan
                <span class="text-gradient">transparan</span>.
            </h1>

            <!-- Subheading -->
            <p class="mx-auto mt-8 max-w-2xl reveal-on-scroll text-[17px] leading-[1.75] text-[var(--muted)]">
                Trimexas adalah Sistem Pendukung Keputusan berbasis
                <span class="font-medium text-[var(--ink)]">Logika Fuzzy Tsukamoto</span> —
                mengubah lima kriteria menjadi satu skor kelayakan yang konsisten,
                objektif, dan bisa dipertanggungjawabkan.
            </p>

            <!-- CTA Buttons -->
            <div class="mx-auto mt-10 reveal-on-scroll flex flex-wrap items-center justify-center gap-4">
                <Link
                    :href="route('register')"
                    class="btn-primary group !px-8 !py-4"
                >
                    Daftar sebagai mahasiswa
                    <ArrowRight :size="16" class="transition-transform group-hover:translate-x-0.5" />
                </Link>
                <Link :href="route('login')" class="btn-secondary !px-8 !py-4">Login admin</Link>
            </div>

            <!-- Stats -->
            <dl class="mx-auto mt-16 reveal-on-scroll grid max-w-lg grid-cols-3 gap-8 text-center">
                <div v-for="s in stats" :key="s.label">
                    <dt class="display text-[2rem] text-[var(--ink)] tnum">{{ s.value }}</dt>
                    <dd class="mt-2 text-[13px] text-[var(--muted)]">{{ s.label }}</dd>
                </div>
            </dl>
        </section>

        <!-- =========================================================
             PARTNERS MARQUEE
             ========================================================= -->
        <section class="relative z-10 border-y border-[var(--border)] py-7">
            <div class="marquee-mask overflow-hidden">
                <div class="marquee-track-reverse">
                    <div v-for="loop in 2" :key="loop" class="flex items-center gap-x-12 pr-12">
                        <span
                            v-for="p in partners"
                            :key="`${loop}-${p}`"
                            class="display-md flex items-center gap-2 whitespace-nowrap text-lg text-[var(--muted)]"
                        >
                            <Sparkles :size="13" class="text-[var(--primary)]" />
                            {{ p }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             PRODUK — Bento grid
             ========================================================= -->
        <section id="produk" class="relative z-10 mx-auto max-w-[1200px] px-5 py-24 lg:px-8">
            <div class="reveal-on-scroll mx-auto max-w-2xl text-center">
                <span class="section-label">Produk</span>
                <h2 class="display mt-5 text-[clamp(2rem,4.6vw,3.4rem)] text-[var(--ink)]">
                    Satu ruang kerja untuk
                    <span class="text-gradient">seluruh proses seleksi.</span>
                </h2>
                <p class="mt-5 text-[16px] leading-[1.7] text-[var(--muted)]">
                    Dari pendaftaran sampai pengumuman — verifikasi, penilaian fuzzy, ranking,
                    dan laporan, semuanya rapi dalam satu sistem.
                </p>
            </div>

            <div class="bento-grid reveal-on-scroll mt-14">
                <!-- Big card: live ranking surface -->
                <article class="bento col-4" @pointermove="bentoGlow">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="bento-icon mb-3"><ListChecks :size="20" /></span>
                            <h3 class="display-md text-[1.3rem] text-[var(--ink)]">Ranking otomatis</h3>
                            <p class="mt-1.5 max-w-md text-[14.5px] leading-relaxed text-[var(--muted)]">
                                Setiap kandidat diberi skor dan peringkat secara langsung — siap dibawa ke rapat.
                            </p>
                        </div>
                        <span class="tag tag-primary hidden sm:inline-flex">Batch #07</span>
                    </div>

                    <div class="window mt-6">
                        <div class="window-bar">
                            <span class="window-dot" style="background:#fb7185"></span>
                            <span class="window-dot" style="background:#fbbf24"></span>
                            <span class="window-dot" style="background:#34d399"></span>
                            <span class="window-title">trimexas — hasil seleksi</span>
                        </div>
                        <div class="window-body">
                            <div v-for="r in ranking" :key="r.pos" class="rank-row">
                                <span class="rank-pos">{{ r.pos }}</span>
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="avatar">{{ r.mono }}</span>
                                    <span class="truncate text-[13.5px] font-medium text-[var(--ink)]">{{ r.name }}</span>
                                </div>
                                <div class="rank-meter meter"><i :style="{ width: r.w }"></i></div>
                                <span class="rank-score mono text-[13px] font-medium text-[var(--ink)] tnum">{{ r.score }}</span>
                                <span class="rank-verdict tag" :class="toneClass(r.tone)">{{ r.verdict }}</span>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Side stack -->
                <div class="col-2 flex flex-col gap-[1.1rem]">
                    <article
                        v-for="f in features"
                        :key="f.title"
                        class="bento flex-1"
                        @pointermove="bentoGlow"
                    >
                        <span class="bento-icon mb-3"><component :is="f.icon" :size="20" /></span>
                        <h3 class="display-md text-[1.08rem] text-[var(--ink)]">{{ f.title }}</h3>
                        <p class="mt-1.5 text-[13.5px] leading-relaxed text-[var(--muted)]">{{ f.body }}</p>
                    </article>
                </div>

                <!-- Wide: report export -->
                <article class="bento col-3" @pointermove="bentoGlow">
                    <span class="bento-icon mb-3"><FileSpreadsheet :size="20" /></span>
                    <h3 class="display-md text-[1.15rem] text-[var(--ink)]">Laporan siap kirim</h3>
                    <p class="mt-1.5 text-[14px] leading-relaxed text-[var(--muted)]">
                        Ekspor hasil ke PDF & Excel dengan rincian skor tiap kriteria — akuntabel untuk yayasan.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="tag">.pdf</span>
                        <span class="tag">.xlsx</span>
                        <span class="tag tag-primary">rincian kriteria</span>
                    </div>
                </article>

                <!-- Wide: dashboard -->
                <article class="bento col-3" @pointermove="bentoGlow">
                    <span class="bento-icon mb-3"><Workflow :size="20" /></span>
                    <h3 class="display-md text-[1.15rem] text-[var(--ink)]">Alur kerja terpandu</h3>
                    <p class="mt-1.5 text-[14px] leading-relaxed text-[var(--muted)]">
                        Daftar → verifikasi → penilaian batch → pengumuman. Setiap langkah punya status yang jelas.
                    </p>
                    <div class="mt-5 flex items-center gap-2">
                        <span v-for="(step, i) in ['Daftar','Verifikasi','Nilai','Hasil']" :key="step" class="flex items-center gap-2">
                            <span class="tag" :class="i === 0 ? 'tag-success' : ''">{{ step }}</span>
                            <ArrowRight v-if="i < 3" :size="13" class="text-[var(--muted)]" />
                        </span>
                    </div>
                </article>
            </div>
        </section>

        <!-- =========================================================
             METODE — Interactive Fuzzy Tsukamoto demo
             ========================================================= -->
        <section id="metode" class="relative z-10 mx-auto max-w-[1200px] px-5 py-24 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div class="reveal-on-scroll">
                    <span class="section-label">Metode Fuzzy Tsukamoto</span>
                    <h2 class="display mt-5 text-[clamp(2rem,4.4vw,3.2rem)] text-[var(--ink)]">
                        Coba sendiri.
                        <span class="text-gradient">Geser, lihat skornya.</span>
                    </h2>
                    <p class="mt-5 max-w-lg text-[16px] leading-[1.7] text-[var(--muted)]">
                        Logika fuzzy menerjemahkan nilai mentah jadi tingkat keanggotaan, lalu
                        menyatukannya jadi satu skor. Ubah parameter di samping dan saksikan
                        keputusan diperbarui secara langsung.
                    </p>
                    <ul class="mt-7 space-y-3">
                        <li v-for="t in ['Fuzzifikasi tiap kriteria','Aturan IF–THEN Tsukamoto','Defuzzifikasi rata-rata terbobot']" :key="t" class="flex items-center gap-3 text-[15px] text-[var(--foreground)]">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-[var(--primary-light)] text-[var(--primary)]"><Check :size="13" /></span>
                            {{ t }}
                        </li>
                    </ul>
                    <p class="mono mt-7 text-[11px] leading-relaxed text-[var(--muted)]">
                        * Demo ilustratif untuk memvisualkan metode. Mesin produksi memakai 5 kriteria penuh.
                    </p>
                </div>

                <!-- Live demo window -->
                <div class="reveal-on-scroll">
                    <div class="window" @pointermove="bentoGlow">
                        <div class="window-bar">
                            <span class="window-dot" style="background:#fb7185"></span>
                            <span class="window-dot" style="background:#fbbf24"></span>
                            <span class="window-dot" style="background:#34d399"></span>
                            <span class="window-title">fuzzy-tsukamoto.demo</span>
                        </div>
                        <div class="window-body !p-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-[1fr_auto] sm:items-center">
                                <!-- sliders -->
                                <div class="space-y-5">
                                    <div>
                                        <div class="flex items-baseline justify-between">
                                            <label class="text-[13px] font-medium text-[var(--foreground)]">IPK</label>
                                            <span class="mono text-[13px] text-[var(--primary)] tnum">{{ fuzzy.ipk.toFixed(2) }}</span>
                                        </div>
                                        <input type="range" class="fuzzy-slider mt-2" min="2" max="4" step="0.01"
                                            v-model.number="fuzzy.ipk" :style="{ '--filled': ipkFill }" aria-label="IPK" />
                                    </div>
                                    <div>
                                        <div class="flex items-baseline justify-between">
                                            <label class="text-[13px] font-medium text-[var(--foreground)]">Penghasilan keluarga</label>
                                            <span class="mono text-[13px] text-[var(--primary)] tnum">{{ fuzzy.penghasilan.toFixed(1) }} jt</span>
                                        </div>
                                        <input type="range" class="fuzzy-slider mt-2" min="0" max="10" step="0.1"
                                            v-model.number="fuzzy.penghasilan" :style="{ '--filled': penghasilanFill }" aria-label="Penghasilan keluarga" />
                                    </div>
                                    <div>
                                        <div class="flex items-baseline justify-between">
                                            <label class="text-[13px] font-medium text-[var(--foreground)]">Poin prestasi</label>
                                            <span class="mono text-[13px] text-[var(--primary)] tnum">{{ fuzzy.prestasi.toFixed(0) }}</span>
                                        </div>
                                        <input type="range" class="fuzzy-slider mt-2" min="0" max="10" step="1"
                                            v-model.number="fuzzy.prestasi" :style="{ '--filled': prestasiFill }" aria-label="Poin prestasi" />
                                    </div>
                                </div>

                                <!-- result -->
                                <div class="flex flex-col items-center justify-center rounded-2xl border border-[var(--border)] bg-[var(--surface-2)] px-6 py-7 text-center sm:w-[170px]">
                                    <span class="mono text-[9px] uppercase tracking-[0.18em] text-[var(--muted)]">Skor kelayakan</span>
                                    <span class="score-dial mt-2 text-[3rem] leading-none text-[var(--ink)] tnum">{{ fuzzyScore.toFixed(1) }}</span>
                                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-[var(--border)]">
                                        <div class="h-full rounded-full transition-all duration-500"
                                            :style="{ width: `${fuzzyScore}%`, background: 'var(--gradient-brand)' }"></div>
                                    </div>
                                    <span class="tag mt-3" :class="toneClass(fuzzyVerdict.tone)">{{ fuzzyVerdict.label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             ALUR — Journey timeline
             ========================================================= -->
        <section id="alur" class="relative z-10 mx-auto max-w-[1000px] px-5 py-24 lg:px-8">
            <div class="reveal-on-scroll mx-auto max-w-2xl text-center">
                <span class="section-label">Alur</span>
                <h2 class="display mt-5 text-[clamp(2rem,4.4vw,3.2rem)] text-[var(--ink)]">
                    Empat langkah, dari daftar
                    <span class="text-gradient">sampai pengumuman.</span>
                </h2>
            </div>

            <ol class="journey-line reveal-on-scroll mt-28 sm:mt-36">
                <li
                    v-for="(j, i) in journey"
                    :key="j.n"
                    class="journey-card reveal-on-scroll"
                    :style="{ '--delay': `${i * 90}ms` }"
                >
                    <span class="journey-num">{{ j.n }}</span>
                    <div>
                        <h3 class="display-md text-[1.3rem] text-[var(--ink)]">{{ j.title }}</h3>
                        <p class="mt-2 text-[15px] leading-[1.65] text-[var(--muted)]">{{ j.body }}</p>
                    </div>
                </li>
            </ol>
        </section>

        <!-- =========================================================
             AUDIENCE — Two cards
             ========================================================= -->
        <section id="audience" class="relative z-10 mx-auto max-w-[1200px] px-5 py-24 lg:px-8">
            <div class="reveal-on-scroll mx-auto max-w-2xl text-center">
                <span class="section-label">Untuk siapa</span>
                <h2 class="display mt-5 text-[clamp(2rem,4.4vw,3.2rem)] text-[var(--ink)]">
                    Dua peran, satu ruang yang
                    <span class="text-gradient">saling memahami.</span>
                </h2>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-5 reveal-on-scroll md:grid-cols-2 md:gap-7">
                <article
                    v-for="a in audience"
                    :key="a.kicker"
                    class="audience-card"
                    :class="`is-${a.tone}`"
                >
                    <span class="bento-icon mb-4" style="color: currentColor; background: color-mix(in oklab, currentColor 14%, transparent); border-color: color-mix(in oklab, currentColor 28%, transparent)">
                        <component :is="a.icon" :size="20" />
                    </span>
                    <span class="mono block text-[10px] uppercase tracking-[0.22em] text-[var(--muted)]">{{ a.kicker }}</span>
                    <h3 class="display-md mt-2 text-[1.7rem] text-[var(--ink)]">{{ a.title }}</h3>
                    <ul class="mt-6 space-y-3.5">
                        <li v-for="b in a.bullets" :key="b" class="flex gap-3 text-[15px] leading-[1.5] text-[var(--foreground)]">
                            <span class="audience-bullet" aria-hidden="true">
                                <Check :size="12" />
                            </span>
                            <span>{{ b }}</span>
                        </li>
                    </ul>
                    <Link :href="route(a.cta.href)" class="audience-cta group">
                        <span>{{ a.cta.label }}</span>
                        <ArrowUpRight :size="14" class="transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                    </Link>
                </article>
            </div>
        </section>

        <!-- =========================================================
             QUOTE
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-[1200px] px-5 py-20 lg:px-8">
            <div class="quote-card reveal-on-scroll">
                <Quote :size="34" :stroke-width="1.4" class="mx-auto text-[var(--primary)]" />
                <blockquote class="display-md mx-auto mt-6 max-w-3xl text-[clamp(1.5rem,3vw,2.3rem)] leading-[1.3] text-[var(--ink)]">
                    Keputusan yang baik bukan yang paling cepat — melainkan yang
                    <span class="text-gradient">bisa diceritakan kembali.</span>
                </blockquote>
                <footer class="mt-7 flex items-center justify-center gap-3">
                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--primary)]"></span>
                    <span class="mono text-[11px] uppercase tracking-[0.24em] text-[var(--muted)]">Kelompok 4 · Praktikum AI 2026</span>
                </footer>
            </div>
        </section>

        <!-- =========================================================
             FAQ
             ========================================================= -->
        <section id="faq" class="relative z-10 mx-auto max-w-[860px] px-5 py-20 lg:px-8">
            <div class="reveal-on-scroll text-center">
                <span class="section-label">Pertanyaan</span>
                <h2 class="display mt-5 text-[clamp(2rem,4.4vw,3rem)] text-[var(--ink)]">
                    Hal yang <span class="text-gradient">sering ditanyakan.</span>
                </h2>
            </div>

            <ul class="faq-list reveal-on-scroll mt-28 sm:mt-32">
                <li v-for="(f, i) in faqs" :key="f.q" class="faq-item" :class="{ 'is-open': openFaq === i }">
                    <button type="button" class="faq-trigger" @click="toggleFaq(i)" :aria-expanded="openFaq === i">
                        <span class="faq-question">{{ f.q }}</span>
                        <span class="faq-toggle" aria-hidden="true"><Plus :size="16" /></span>
                    </button>
                    <div class="faq-answer">
                        <div><p>{{ f.a }}</p></div>
                    </div>
                </li>
            </ul>
        </section>

        <!-- =========================================================
             TEAM
             ========================================================= -->
        <section id="team" class="relative z-10 mx-auto max-w-[1200px] px-5 py-20 lg:px-8">
            <div class="reveal-on-scroll mx-auto max-w-2xl text-center">
                <span class="section-label">Tim</span>
                <h2 class="display mt-5 text-[clamp(2rem,4.4vw,3rem)] text-[var(--ink)]">
                    Empat mahasiswa, <span class="text-gradient">satu kelas.</span>
                </h2>
                <p class="mt-5 text-[16px] leading-[1.7] text-[var(--muted)]">
                    Praktikum Artificial Intelligence — Semester 4, Kelompok 4.
                </p>
            </div>

            <div class="team-grid reveal-on-scroll mt-12">
                <article v-for="(m, i) in teamMembers" :key="m.name" class="team-card" :style="{ '--delay': `${i * 70}ms` }">
                    <div class="team-mono">{{ m.mono }}</div>
                    <h3 class="mt-4 text-[13.5px] font-medium leading-snug text-[var(--ink)]">{{ m.name }}</h3>
                </article>
            </div>
        </section>

        <!-- =========================================================
             CTA STRIP
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-[1200px] px-5 pb-24 pt-10 lg:px-8">
            <div class="cta-strip reveal-on-scroll">
                <div class="cta-dots"></div>
                <div class="grid grid-cols-1 items-center gap-8 md:grid-cols-[1.4fr_1fr]">
                    <div>
                        <span class="mono text-[10px] uppercase tracking-[0.22em] text-[var(--muted)]">Sebelum batch berikutnya</span>
                        <h2 class="display mt-4 text-[clamp(2rem,4.4vw,3.2rem)] text-[var(--ink)]">
                            Mulai dari satu langkah
                            <span class="text-gradient">yang terukur.</span>
                        </h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 md:justify-end">
                        <Link
                            :href="route('register')"
                            class="btn-primary btn-magnetic group !px-7 !py-3.5"
                            @mousemove="magnetic"
                            @mouseleave="magneticReset"
                        >
                            Mulai pendaftaran
                            <ArrowRight :size="16" class="transition-transform group-hover:translate-x-0.5" />
                        </Link>
                        <Link :href="route('login')" class="btn-ghost !px-6 !py-3.5">Masuk pengelola</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             FOOTER
             ========================================================= -->
        <footer class="relative z-10 mx-auto max-w-[1200px] px-5 pb-12 pt-4 lg:px-8">
            <div class="flex flex-col gap-6 border-t border-[var(--border)] pt-10 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="brand-mark !h-8 !w-8">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 16 L9 7 L13 12 L20 4" />
                        </svg>
                    </span>
                    <span class="display text-base tracking-tight text-[var(--ink)]">Trimexas</span>
                </div>

                <div class="mono flex flex-wrap items-center gap-x-7 gap-y-2 text-[10px] uppercase tracking-[0.22em] text-[var(--muted)]">
                    <button type="button" class="nav-link" @click="scrollTo('produk')">Produk</button>
                    <button type="button" class="nav-link" @click="scrollTo('metode')">Metode</button>
                    <button type="button" class="nav-link" @click="scrollTo('alur')">Alur</button>
                    <button type="button" class="nav-link" @click="scrollTo('faq')">FAQ</button>
                </div>

                <span class="mono text-[10px] uppercase tracking-[0.22em] text-[var(--muted)]">© 2026 · Kelompok 4</span>
            </div>
        </footer>
    </main>
</template>
