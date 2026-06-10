<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { onMounted, onBeforeUnmount, ref, computed, reactive } from "vue";
import Lenis from "lenis";
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
    Plus,
    Check,
    ListChecks,
} from "@lucide/vue";

defineProps({
    canRegister: { type: Boolean, default: true },
    appName: { type: String, default: "Trimexas" },
});

const prefersReduced = () =>
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/* ===========================================================
   Theme + UI state
   =========================================================== */
const isDark = ref(false);
const scrollProgress = ref(0);
const scrolled = ref(false);
const loaded = ref(false);

/* Intro preloader */
const reducedAtMount = typeof window !== "undefined" ? prefersReduced() : false;
const introSeen = (() => {
    try {
        return sessionStorage.getItem("trimexas-intro") === "1";
    } catch (_) {
        return false;
    }
})();
const showIntro = ref(!reducedAtMount && !introSeen);
const introCount = ref(0);
const introLeaving = ref(false);

/* Custom cursor */
const cursorOn = ref(false);
const cursorHover = ref(false);
const cursorDot = ref(null);
const cursorRing = ref(null);

/* Marquee velocity skew + active method step */
const mqSkew = ref(0);
const activeStep = ref(0);

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle("dark", isDark.value);
    try {
        localStorage.setItem("trimexas-theme", isDark.value ? "dark" : "light");
    } catch (_) {}
}

/* ===========================================================
   Lenis momentum scroll
   =========================================================== */
let lenis = null;
let lenisRaf = null;

function initLenis() {
    if (reducedAtMount) return;
    lenis = new Lenis({
        duration: 1.1,
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        smoothWheel: true,
        wheelMultiplier: 1,
        touchMultiplier: 1.4,
    });
    lenis.on("scroll", (e) => {
        const v = e.velocity || 0;
        mqSkew.value = Math.max(-6, Math.min(6, v * 0.35));
    });
    const raf = (t) => {
        if (lenis) lenis.raf(t);
        lenisRaf = requestAnimationFrame(raf);
    };
    lenisRaf = requestAnimationFrame(raf);
    if (showIntro.value) lenis.stop();
}

function scrollTo(id) {
    const target = "#" + id;
    if (lenis) {
        lenis.scrollTo(target, { offset: -80, duration: 1.2 });
    } else {
        document.getElementById(id)?.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

/* ===========================================================
   Scroll-driven: progress, nav, parallax, method step
   =========================================================== */
let parallaxEls = [];
let stepCards = [];
let scrollHandler = null;

function collectParallax() {
    parallaxEls = Array.from(document.querySelectorAll("[data-parallax]"));
    stepCards = Array.from(document.querySelectorAll("#metode .step-card"));
}

function updateScrollState() {
    const h = document.documentElement.scrollHeight - window.innerHeight;
    const y = window.scrollY;
    scrollProgress.value = h > 0 ? y / h : 0;
    scrolled.value = y > 24;

    if (!reducedAtMount) {
        const vh = window.innerHeight;
        for (const el of parallaxEls) {
            const r = el.getBoundingClientRect();
            const center = r.top + r.height / 2;
            const offset = center - vh / 2;
            const speed = parseFloat(el.dataset.parallax) || 0.1;
            el.style.transform = `translate3d(0, ${(-offset * speed).toFixed(1)}px, 0)`;
        }
    }

    if (stepCards.length) {
        const targetY = window.innerHeight * 0.42;
        let best = 0;
        let bestDist = Infinity;
        stepCards.forEach((c, i) => {
            const r = c.getBoundingClientRect();
            const dist = Math.abs(r.top + r.height / 2 - targetY);
            if (dist < bestDist) {
                bestDist = dist;
                best = i;
            }
        });
        activeStep.value = best;
    }
}

/* ===========================================================
   Custom cursor (fine pointers only)
   =========================================================== */
let cursorRaf = null;
const ptr = { tx: -100, ty: -100, dx: -100, dy: -100, rx: -100, ry: -100 };

function onCursorMove(e) {
    ptr.tx = e.clientX;
    ptr.ty = e.clientY;
}
function onCursorOver(e) {
    cursorHover.value = !!e.target.closest(
        "a, button, input, label, [data-cursor], .faq-trigger, .fuzzy-slider"
    );
}
function cursorLoop() {
    ptr.dx += (ptr.tx - ptr.dx) * 0.32;
    ptr.dy += (ptr.ty - ptr.dy) * 0.32;
    ptr.rx += (ptr.tx - ptr.rx) * 0.16;
    ptr.ry += (ptr.ty - ptr.ry) * 0.16;
    if (cursorDot.value)
        cursorDot.value.style.transform = `translate3d(${ptr.dx}px, ${ptr.dy}px, 0) translate(-50%, -50%)`;
    if (cursorRing.value)
        cursorRing.value.style.transform = `translate3d(${ptr.rx}px, ${ptr.ry}px, 0) translate(-50%, -50%)`;
    cursorRaf = requestAnimationFrame(cursorLoop);
}

function initCursor() {
    if (reducedAtMount) return;
    if (!window.matchMedia("(pointer: fine)").matches) return;
    if (!window.matchMedia("(hover: hover)").matches) return;
    cursorOn.value = true;
    document.documentElement.classList.add("cursor-none");
    window.addEventListener("pointermove", onCursorMove, { passive: true });
    document.addEventListener("pointerover", onCursorOver, { passive: true });
    cursorRaf = requestAnimationFrame(cursorLoop);
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
                    entry.target.setAttribute("data-visible", "true");
                    io.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: "0px 0px -8% 0px" }
    );
    document.querySelectorAll(".reveal-on-scroll").forEach((el) => io.observe(el));
}

/* ===========================================================
   Magnetic buttons + card glow
   =========================================================== */
function magnetic(e) {
    if (prefersReduced()) return;
    const r = e.currentTarget.getBoundingClientRect();
    const x = e.clientX - (r.left + r.width / 2);
    const y = e.clientY - (r.top + r.height / 2);
    e.currentTarget.style.transform = `translate(${x * 0.22}px, ${y * 0.32}px)`;
}
function magneticReset(e) {
    e.currentTarget.style.transform = "";
}
function bentoGlow(e) {
    const r = e.currentTarget.getBoundingClientRect();
    e.currentTarget.style.setProperty("--mx", `${e.clientX - r.left}px`);
    e.currentTarget.style.setProperty("--my", `${e.clientY - r.top}px`);
}

/* ===========================================================
   Interactive particle field (lightweight canvas)
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
        ink: styles.getPropertyValue("--canvas-ink").trim() || "49, 137, 198",
        accent: styles.getPropertyValue("--canvas-accent").trim() || "99, 179, 237",
        alpha: parseFloat(styles.getPropertyValue("--canvas-alpha")) || 0.5,
    };
}

function initField() {
    const canvas = fieldCanvas.value;
    if (!canvas) return;
    if (prefersReduced()) return;
    ctx = canvas.getContext("2d");

    const resize = () => {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = window.innerWidth * dpr;
        canvas.height = window.innerHeight * dpr;
        canvas.style.width = window.innerWidth + "px";
        canvas.style.height = window.innerHeight + "px";

        const area = window.innerWidth * window.innerHeight;
        const count = Math.max(26, Math.min(64, Math.floor(area / 28000)));
        nodes = Array.from({ length: count }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            vx: (Math.random() - 0.5) * 0.3 * dpr,
            vy: (Math.random() - 0.5) * 0.3 * dpr,
            r: (Math.random() * 1.5 + 0.6) * dpr,
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
   Live Fuzzy Tsukamoto calculator
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
   Hero stat count-up
   =========================================================== */
const counts = reactive({ kriteria: 0, telusur: 0 });
function countTo(key, target, dur = 1300) {
    if (prefersReduced()) {
        counts[key] = target;
        return;
    }
    const t0 = performance.now();
    const step = (now) => {
        const p = Math.min(1, (now - t0) / dur);
        counts[key] = Math.round(target * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}
function startCounters() {
    countTo("kriteria", 5);
    countTo("telusur", 100);
}

/* ===========================================================
   Hero headline (word-split for kinetic reveal)
   =========================================================== */
let wordIndex = 0;
const heroLines = [
    [{ t: "Keputusan" }],
    [{ t: "beasiswa" }, { t: "yang" }],
    [{ t: "terukur" }, { t: "&" }, { t: "transparan.", grad: true }],
].map((line) => line.map((w) => ({ ...w, d: wordIndex++ })));

/* ===========================================================
   Static content
   =========================================================== */
const features = [
    {
        icon: ShieldCheck,
        title: "Multi-peran & aman",
        body: "Akses terpisah untuk administrator dan mahasiswa, lengkap dengan verifikasi.",
    },
    {
        icon: BellRing,
        title: "Notifikasi real-time",
        body: "Pengumuman status dan hasil langsung sampai ke setiap kandidat.",
    },
    {
        icon: Gauge,
        title: "Mesin Fuzzy Tsukamoto",
        body: "Lima kriteria diolah jadi satu skor kelayakan yang konsisten dan bisa dijelaskan.",
    },
];

const methodSteps = [
    {
        n: "01",
        title: "Fuzzifikasi",
        body: "Setiap nilai mentah IPK, penghasilan, prestasi diterjemahkan jadi derajat keanggotaan antara 0 dan 1.",
    },
    {
        n: "02",
        title: "Inferensi IF–THEN",
        body: "Aturan Tsukamoto menghitung kekuatan tiap rule dan menghasilkan nilai keluaran yang monoton.",
    },
    {
        n: "03",
        title: "Defuzzifikasi",
        body: "Rata-rata terbobot menyatukan seluruh rule menjadi satu skor kelayakan akhir yang bisa diperingkat.",
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
        body: "Lima kriteria - akademik, prestasi, kondisi keluarga dirangkum jadi satu skor.",
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

const methodWords = [
    "Fuzzifikasi",
    "Inferensi",
    "Defuzzifikasi",
    "Rata-rata terbobot",
    "Akuntabel",
    "Dapat ditelusuri",
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
        a: "Bisa. Setiap mahasiswa punya halaman ranking publik berisi nama, skor, dan status tanpa membocorkan data sensitif lainnya.",
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

/* ===========================================================
   Lifecycle
   =========================================================== */
function finishIntro() {
    introLeaving.value = true;
    window.setTimeout(() => {
        showIntro.value = false;
        document.body.style.overflow = "";
        try {
            sessionStorage.setItem("trimexas-intro", "1");
        } catch (_) {}
        if (lenis) lenis.start();
        loaded.value = true;
        window.setTimeout(startCounters, 500);
    }, 820);
}

function runIntro() {
    document.body.style.overflow = "hidden";
    const t0 = performance.now();
    const dur = 1150;
    const tick = (now) => {
        const p = Math.min(1, (now - t0) / dur);
        introCount.value = Math.round(p * 100);
        if (p < 1) requestAnimationFrame(tick);
        else finishIntro();
    };
    requestAnimationFrame(tick);
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
    initLenis();
    initCursor();
    collectParallax();

    window.addEventListener("pointermove", onPointerMove, { passive: true });
    window.addEventListener("pointerleave", onPointerLeave, { passive: true });

    scrollHandler = () => updateScrollState();
    updateScrollState();
    window.addEventListener("scroll", scrollHandler, { passive: true });
    window.addEventListener("resize", scrollHandler, { passive: true });

    if (showIntro.value) {
        runIntro();
    } else {
        loaded.value = true;
        window.setTimeout(startCounters, 400);
    }
});

onBeforeUnmount(() => {
    if (scrollHandler) {
        window.removeEventListener("scroll", scrollHandler);
        window.removeEventListener("resize", scrollHandler);
    }
    window.removeEventListener("pointermove", onPointerMove);
    window.removeEventListener("pointerleave", onPointerLeave);
    window.removeEventListener("pointermove", onCursorMove);
    document.removeEventListener("pointerover", onCursorOver);
    document.documentElement.classList.remove("cursor-none");
    document.body.style.overflow = "";
    if (io) io.disconnect();
    if (raf) cancelAnimationFrame(raf);
    if (cursorRaf) cancelAnimationFrame(cursorRaf);
    if (lenisRaf) cancelAnimationFrame(lenisRaf);
    if (lenis) {
        lenis.destroy();
        lenis = null;
    }
    running = false;
    const c = fieldCanvas.value;
    if (c) {
        if (c._resize) window.removeEventListener("resize", c._resize);
        if (c._visHandler) document.removeEventListener("visibilitychange", c._visHandler);
    }
});
</script>

<template>
    <Head title="Trimexas - SPK Beasiswa Fuzzy Tsukamoto" />

    <!-- Intro preloader -->
    <div v-if="showIntro" class="preloader" :class="{ 'is-leaving': introLeaving }">
        <div class="preloader-inner">
            <span class="preloader-brand display">Trimexas</span>
            <span class="preloader-tag mono">SPK Beasiswa · Fuzzy Tsukamoto</span>
        </div>
        <span class="preloader-count mono">{{ introCount }}</span>
        <span class="preloader-bar"><i :style="{ transform: `scaleX(${introCount / 100})` }"></i></span>
    </div>

    <!-- Custom cursor -->
    <template v-if="cursorOn">
        <div ref="cursorRing" class="cursor-ring" :class="{ 'is-hover': cursorHover }" aria-hidden="true"></div>
        <div ref="cursorDot" class="cursor-dot" :class="{ 'is-hover': cursorHover }" aria-hidden="true"></div>
    </template>

    <main
        class="relative min-h-screen overflow-x-clip bg-[var(--background)] text-[var(--foreground)]"
        :class="{ 'is-loaded': loaded }"
    >
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
            <div class="mx-auto max-w-[1240px] px-5 pt-4 lg:px-8">
                <nav
                    class="nav-glass flex items-center justify-between px-4 py-2.5 pl-5"
                    :class="{ 'nav-scrolled': scrolled }"
                    aria-label="Navigasi utama"
                >
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
                        <button class="nav-link" @click="scrollTo('metode')">Kalkulator</button>
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
             HERO — kinetic type-forward
             ========================================================= -->
        <section class="hero-shell relative z-10 mx-auto max-w-[1240px] px-5 pb-16 lg:px-8">
            <div class="hero-glow" data-parallax="0.16" aria-hidden="true"></div>

            <!-- Top meta row -->
            <div class="fade-load hero-meta pt-6" style="--i: 0">
                <span>SPK · Fuzzy Tsukamoto</span>
                <span class="hidden sm:inline">Beasiswa 2026 / Kelompok&nbsp;04</span>
            </div>

            <div class="flex flex-1 flex-col justify-center py-12 lg:py-16">
                <!-- Eyebrow -->
                <div class="fade-load mb-8" style="--i: 1">
                    <span class="eyebrow">
                        <span class="dot"></span>
                        <span>Triv Foundation × MEXC Foundation</span>
                    </span>
                </div>

                <!-- Kinetic headline (word-split) -->
                <h1 class="hero-title max-w-[16ch]">
                    <span v-for="(line, li) in heroLines" :key="li" class="hero-line">
                        <span v-for="(w, wi) in line" :key="wi" class="word-mask">
                            <span
                                class="word"
                                :class="{ 'text-gradient': w.grad }"
                                :style="{ '--wd': w.d }"
                            >{{ w.t }}</span>
                        </span>
                    </span>
                </h1>

                <!-- Subheading + CTAs -->
                <div class="mt-9 grid gap-10 lg:grid-cols-[1.1fr_auto] lg:items-end">
                    <p class="fade-load max-w-2xl text-[17px] leading-[1.75] text-[var(--muted)]" style="--i: 5">
                        Trimexas adalah Sistem Pendukung Keputusan berbasis
                        <span class="font-medium text-[var(--ink)]">Logika Fuzzy Tsukamoto</span> 
                        mengubah lima kriteria menjadi satu skor kelayakan yang konsisten,
                        objektif, dan bisa dipertanggungjawabkan.
                    </p>

                    <div class="fade-load flex flex-wrap items-center gap-3" style="--i: 6">
                        <Link :href="route('register')" class="btn-primary btn-magnetic group !px-7 !py-4" @mousemove="magnetic" @mouseleave="magneticReset">
                            Mulai pendaftaran
                            <ArrowRight :size="16" class="transition-transform group-hover:translate-x-0.5" />
                        </Link>
                        <button type="button" class="btn-secondary !px-7 !py-4" @click="scrollTo('metode')">
                            Coba kalkulatornya
                        </button>
                    </div>
                </div>

                <!-- Stat strip -->
                <dl class="fade-load mt-16 grid max-w-2xl grid-cols-3 gap-6 border-t border-[var(--border)] pt-8" style="--i: 7">
                    <div>
                        <dt class="display text-[2.4rem] leading-none text-[var(--ink)] tnum">{{ counts.kriteria }}</dt>
                        <dd class="mt-2 text-[13px] text-[var(--muted)]">Kriteria penilaian</dd>
                    </div>
                    <div>
                        <dt class="display text-[2.4rem] leading-none text-[var(--ink)] tnum">&lt;&#8202;3<span class="text-[1.2rem]">&nbsp;mnt</span></dt>
                        <dd class="mt-2 text-[13px] text-[var(--muted)]">Per batch seleksi</dd>
                    </div>
                    <div>
                        <dt class="display text-[2.4rem] leading-none text-[var(--ink)] tnum">{{ counts.telusur }}%</dt>
                        <dd class="mt-2 text-[13px] text-[var(--muted)]">Bisa ditelusuri</dd>
                    </div>
                </dl>
            </div>

            <!-- Scroll cue -->
            <div class="fade-load flex justify-center pb-2 lg:justify-start" style="--i: 8">
                <button type="button" class="scroll-cue" @click="scrollTo('produk')" aria-label="Gulir ke bawah">
                    <span class="track"></span>
                    <span>Scroll</span>
                </button>
            </div>
        </section>

        <!-- =========================================================
             MARQUEE — two-row kinetic band (velocity-skewed)
             ========================================================= -->
        <section class="relative z-10 border-y border-[var(--border)] py-6">
            <div class="marquee-skew" :style="{ '--mq-skew': mqSkew + 'deg' }">
                <div class="marquee-mask overflow-hidden">
                    <div class="marquee-track">
                        <div v-for="loop in 2" :key="`a-${loop}`" class="marquee-xl flex items-center gap-x-10 pr-10">
                            <span
                                v-for="p in partners"
                                :key="`${loop}-${p}`"
                                class="flex items-center gap-3 whitespace-nowrap text-xl text-[var(--muted)] sm:text-2xl"
                            >
                                <Sparkles :size="14" class="text-[var(--primary)]" />
                                {{ p }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="marquee-mask mt-4 overflow-hidden">
                    <div class="marquee-track-reverse">
                        <div v-for="loop in 2" :key="`b-${loop}`" class="marquee-xl flex items-center gap-x-10 pr-10">
                            <span
                                v-for="word in methodWords"
                                :key="`${loop}-${word}`"
                                class="flex items-center gap-3 whitespace-nowrap text-xl text-[var(--border-strong)] sm:text-2xl"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-[var(--primary)]"></span>
                                {{ word }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             PRODUK — Bento grid
             ========================================================= -->
        <section id="produk" class="relative z-10 mx-auto max-w-[1240px] px-5 py-28 lg:px-8">
            <div class="reveal-on-scroll flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                <div class="max-w-2xl">
                    <span class="sec-index">(01) - Produk</span>
                    <h2 class="display mt-4 text-[clamp(2rem,4.6vw,3.4rem)] leading-[1.05] text-[var(--ink)]">
                        Satu ruang kerja untuk
                        <span class="text-gradient">seluruh proses seleksi.</span>
                    </h2>
                </div>
                <p class="max-w-sm text-[15px] leading-[1.7] text-[var(--muted)]">
                    Dari pendaftaran sampai pengumuman verifikasi, penilaian fuzzy, ranking,
                    dan laporan, semuanya rapi dalam satu sistem.
                </p>
            </div>

            <div class="bento-grid mt-14">
                <!-- Big card: live ranking surface -->
                <article class="bento b-4 reveal-on-scroll" data-cursor @pointermove="bentoGlow">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="bento-icon mb-3"><ListChecks :size="20" /></span>
                            <h3 class="display-md text-[1.3rem] text-[var(--ink)]">Ranking otomatis</h3>
                            <p class="mt-1.5 max-w-md text-[14.5px] leading-relaxed text-[var(--muted)]">
                                Setiap kandidat diberi skor dan peringkat secara langsung siap dibawa ke rapat.
                            </p>
                        </div>
                        <span class="tag tag-primary hidden sm:inline-flex">Batch #07</span>
                    </div>

                    <div class="window mt-6">
                        <div class="window-bar">
                            <span class="window-dot" style="background:#ef4444"></span>
                            <span class="window-dot" style="background:#f59e0b"></span>
                            <span class="window-dot" style="background:#22c55e"></span>
                            <span class="window-title">trimexas - hasil seleksi</span>
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
                <div class="b-2 flex flex-col gap-[1.1rem]">
                    <article
                        v-for="(f, fi) in features"
                        :key="f.title"
                        class="bento flex-1 reveal-on-scroll"
                        :style="{ '--delay': `${fi * 90}ms` }"
                        data-cursor
                        @pointermove="bentoGlow"
                    >
                        <span class="bento-icon mb-3"><component :is="f.icon" :size="20" /></span>
                        <h3 class="display-md text-[1.08rem] text-[var(--ink)]">{{ f.title }}</h3>
                        <p class="mt-1.5 text-[13.5px] leading-relaxed text-[var(--muted)]">{{ f.body }}</p>
                    </article>
                </div>

                <!-- Wide: report export -->
                <article class="bento b-3 reveal-on-scroll" data-cursor @pointermove="bentoGlow">
                    <span class="bento-icon mb-3"><FileSpreadsheet :size="20" /></span>
                    <h3 class="display-md text-[1.15rem] text-[var(--ink)]">Laporan siap kirim</h3>
                    <p class="mt-1.5 text-[14px] leading-relaxed text-[var(--muted)]">
                        Ekspor hasil ke PDF &amp; Excel dengan rincian skor tiap kriteria akuntabel untuk yayasan.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="tag">.pdf</span>
                        <span class="tag">.xlsx</span>
                        <span class="tag tag-primary">rincian kriteria</span>
                    </div>
                </article>

                <!-- Wide: workflow -->
                <article class="bento b-3 reveal-on-scroll" data-cursor @pointermove="bentoGlow">
                    <span class="bento-icon mb-3"><Workflow :size="20" /></span>
                    <h3 class="display-md text-[1.15rem] text-[var(--ink)]">Alur kerja terpandu</h3>
                    <p class="mt-1.5 text-[14px] leading-relaxed text-[var(--muted)]">
                        Daftar → verifikasi → penilaian batch → pengumuman. Setiap langkah punya status yang jelas.
                    </p>
                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <span v-for="(step, i) in ['Daftar','Verifikasi','Nilai','Hasil']" :key="step" class="flex items-center gap-2">
                            <span class="tag" :class="i === 0 ? 'tag-success' : ''">{{ step }}</span>
                            <ArrowRight v-if="i < 3" :size="13" class="text-[var(--muted)]" />
                        </span>
                    </div>
                </article>
            </div>
        </section>

        <!-- =========================================================
             METODE — Sticky interactive calculator
             ========================================================= -->
        <section id="metode" class="relative z-10 mx-auto max-w-[1240px] px-5 py-28 lg:px-8">
            <div class="reveal-on-scroll max-w-2xl">
                <span class="sec-index">(02) - Kalkulator</span>
                <h2 class="display mt-4 text-[clamp(2rem,4.6vw,3.4rem)] leading-[1.05] text-[var(--ink)]">
                    Geser parameternya,
                    <span class="text-gradient">lihat skornya hidup.</span>
                </h2>
                <p class="mt-5 text-[16px] leading-[1.7] text-[var(--muted)]">
                    Logika fuzzy menerjemahkan nilai mentah jadi tingkat keanggotaan, lalu
                    menyatukannya jadi satu skor. Tiga langkah yang sama dipakai mesin produksi.
                </p>
            </div>

            <div class="method-grid mt-16">
                <!-- Left: scrolling steps with scroll-driven highlight -->
                <ol class="method-steps">
                    <li
                        v-for="(s, si) in methodSteps"
                        :key="s.n"
                        class="step-card reveal-on-scroll"
                        :class="{ 'is-active': activeStep === si }"
                        :style="{ '--delay': `${si * 80}ms` }"
                    >
                        <span class="step-num">{{ s.n }}</span>
                        <h3 class="display-md text-[1.2rem] text-[var(--ink)]">{{ s.title }}</h3>
                        <p class="mt-2 text-[14.5px] leading-[1.65] text-[var(--muted)]">{{ s.body }}</p>
                    </li>
                    <li class="reveal-on-scroll flex items-start gap-3 px-1 pt-2">
                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[var(--primary-light)] text-[var(--primary)]"><Check :size="13" /></span>
                        <p class="mono text-[11px] leading-relaxed text-[var(--muted)]">
                            Demo ilustratif memvisualkan 3 kriteria. Mesin produksi memakai 5 kriteria penuh.
                        </p>
                    </li>
                </ol>

                <!-- Right: sticky live calculator -->
                <div class="method-sticky reveal-on-scroll">
                    <div class="window" data-cursor @pointermove="bentoGlow">
                        <div class="window-bar">
                            <span class="window-dot" style="background:#ef4444"></span>
                            <span class="window-dot" style="background:#f59e0b"></span>
                            <span class="window-dot" style="background:#22c55e"></span>
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
        <section id="alur" class="relative z-10 mx-auto max-w-[1000px] px-5 py-28 lg:px-8">
            <div class="reveal-on-scroll max-w-2xl">
                <span class="sec-index">(03) - Alur</span>
                <h2 class="display mt-4 text-[clamp(2rem,4.4vw,3.2rem)] leading-[1.05] text-[var(--ink)]">
                    Empat langkah, dari daftar
                    <span class="text-gradient">sampai pengumuman.</span>
                </h2>
            </div>

            <ol class="journey-line reveal-on-scroll mt-20">
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
        <section id="audience" class="relative z-10 mx-auto max-w-[1240px] px-5 py-28 lg:px-8">
            <div class="reveal-on-scroll max-w-2xl">
                <span class="sec-index">(04) - Untuk siapa</span>
                <h2 class="display mt-4 text-[clamp(2rem,4.4vw,3.2rem)] leading-[1.05] text-[var(--ink)]">
                    Dua peran, satu ruang yang
                    <span class="text-gradient">saling memahami.</span>
                </h2>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-7">
                <article
                    v-for="(a, ai) in audience"
                    :key="a.kicker"
                    class="audience-card reveal-on-scroll"
                    :class="`is-${a.tone}`"
                    :style="{ '--delay': `${ai * 110}ms` }"
                    data-cursor
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
        <section class="relative z-10 mx-auto max-w-[1240px] px-5 py-20 lg:px-8">
            <div class="reveal-on-scroll mx-auto max-w-4xl text-center">
                <span class="sec-index">(05) - Prinsip</span>
                <blockquote class="display mx-auto mt-6 max-w-4xl text-[clamp(1.7rem,4vw,3rem)] leading-[1.2] text-[var(--ink)]">
                    Keputusan yang baik bukan yang paling cepat melainkan yang
                    <span class="text-gradient">bisa diceritakan kembali.</span>
                </blockquote>
                <footer class="mt-8 flex items-center justify-center gap-3">
                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--primary)]"></span>
                    <span class="mono text-[11px] uppercase tracking-[0.24em] text-[var(--muted)]">Kelompok 4 · Praktikum AI 2026</span>
                </footer>
            </div>
        </section>

        <!-- =========================================================
             FAQ
             ========================================================= -->
        <section id="faq" class="relative z-10 mx-auto max-w-[920px] px-5 py-20 lg:px-8">
            <div class="reveal-on-scroll max-w-2xl">
                <span class="sec-index">(06) - Pertanyaan</span>
                <h2 class="display mt-4 text-[clamp(2rem,4.4vw,3rem)] leading-[1.05] text-[var(--ink)]">
                    Hal yang <span class="text-gradient">sering ditanyakan.</span>
                </h2>
            </div>

            <ul class="faq-list reveal-on-scroll mt-12">
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
        <section id="team" class="relative z-10 mx-auto max-w-[1240px] px-5 py-20 lg:px-8">
            <div class="reveal-on-scroll max-w-2xl">
                <span class="sec-index">(07) - Tim</span>
                <h2 class="display mt-4 text-[clamp(2rem,4.4vw,3rem)] leading-[1.05] text-[var(--ink)]">
                    Empat mahasiswa, <span class="text-gradient">satu kelas.</span>
                </h2>
                <p class="mt-5 text-[16px] leading-[1.7] text-[var(--muted)]">
                    Praktikum Artificial Intelligence - Semester 4, Kelompok 4.
                </p>
            </div>

            <div class="team-grid mt-12">
                <article
                    v-for="(m, i) in teamMembers"
                    :key="m.name"
                    class="team-card reveal-on-scroll"
                    :style="{ '--delay': `${i * 80}ms` }"
                    data-cursor
                >
                    <div class="team-mono">{{ m.mono }}</div>
                    <h3 class="mt-4 text-[13.5px] font-medium leading-snug text-[var(--ink)]">{{ m.name }}</h3>
                </article>
            </div>
        </section>

        <!-- =========================================================
             CTA STRIP
             ========================================================= -->
        <section class="relative z-10 mx-auto max-w-[1240px] px-5 pb-20 pt-10 lg:px-8">
            <div class="cta-strip reveal-on-scroll">
                <div class="cta-dots"></div>
                <div class="grid grid-cols-1 items-center gap-8 md:grid-cols-[1.4fr_1fr]">
                    <div>
                        <span class="mono text-[10px] uppercase tracking-[0.22em] text-[var(--muted)]">Sebelum batch berikutnya</span>
                        <h2 class="display mt-4 text-[clamp(2rem,4.4vw,3.2rem)] leading-[1.05] text-[var(--ink)]">
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
             FOOTER — giant wordmark
             ========================================================= -->
        <footer class="relative z-10 mx-auto max-w-[1240px] px-5 pb-10 lg:px-8">
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
                    <button type="button" class="nav-link" @click="scrollTo('metode')">Kalkulator</button>
                    <button type="button" class="nav-link" @click="scrollTo('alur')">Alur</button>
                    <button type="button" class="nav-link" @click="scrollTo('faq')">FAQ</button>
                </div>

                <span class="mono text-[10px] uppercase tracking-[0.22em] text-[var(--muted)]">© 2026 · Kelompok 4</span>
            </div>

            <div class="footer-wordmark mt-12 select-none" data-parallax="-0.08" aria-hidden="true">Trimexas</div>
        </footer>
    </main>
</template>
