<script setup>
import { Head, Link } from "@inertiajs/vue3";
import {
    User,
    Trophy,
    ListOrdered,
    Clock,
    XCircle,
    CheckCircle2,
    ArrowUpRight,
    LineChart,
} from "@lucide/vue";
import MahasiswaLayout from "@/Layouts/MahasiswaLayout.vue";

defineProps({
    profile: { type: Object, required: true },
    latestBatch: { type: Object, default: null },
    myResult: { type: Object, default: null },
});

const quickLinks = [
    {
        label: "Edit profil",
        desc: "Perbarui data diri & ekonomi",
        href: "mahasiswa.profile.show",
        icon: User,
    },
    {
        label: "Kelola prestasi",
        desc: "Tambah & susun capaianmu",
        href: "mahasiswa.achievements.index",
        icon: Trophy,
    },
    {
        label: "Ranking publik",
        desc: "Lihat hasil seleksi terbuka",
        href: "mahasiswa.ranking.index",
        icon: ListOrdered,
    },
];
</script>

<template>
    <Head title="Mahasiswa · Dashboard" />

    <MahasiswaLayout active="dashboard">
        <div class="space-y-7">
            <!-- Heading -->
            <header class="reveal-stagger" style="--delay: 0ms">
                <span class="section-label">Beranda Mahasiswa</span>
                <h1 class="display mt-4 text-[clamp(2rem,5vw,3rem)] text-[var(--ink)]">
                    Halo, <span class="text-gradient">{{ profile.name }}</span>
                </h1>
                <p class="mt-2 text-[15px] text-[var(--muted)]">
                    Pantau status akun dan hasil seleksimu dari satu tempat.
                </p>
            </header>

            <!-- Status card -->
            <section
                class="reveal-stagger"
                style="--delay: 80ms"
                aria-label="Status akun"
            >
                <!-- Pending -->
                <article
                    v-if="profile.approval_status === 'pending'"
                    class="bento col-6"
                >
                    <div class="flex items-start gap-4">
                        <span class="bento-icon shrink-0" style="color: var(--warning); background: color-mix(in oklab, var(--warning) 14%, transparent); border-color: color-mix(in oklab, var(--warning) 28%, transparent)">
                            <Clock :size="20" />
                        </span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="display-md text-[1.2rem] text-[var(--ink)]">
                                    Akun menunggu verifikasi
                                </h2>
                                <span class="tag tag-warning">Pending</span>
                            </div>
                            <p class="mt-1.5 text-[14.5px] leading-relaxed text-[var(--muted)]">
                                Pengelola sedang meninjau pendaftaranmu. Notifikasi email
                                akan dikirim saat akun disetujui atau ditolak.
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Rejected -->
                <article
                    v-else-if="profile.approval_status === 'rejected'"
                    class="bento col-6"
                    style="border-color: color-mix(in oklab, var(--danger) 38%, var(--border))"
                >
                    <div class="flex items-start gap-4">
                        <span class="bento-icon shrink-0" style="color: var(--danger); background: color-mix(in oklab, var(--danger) 14%, transparent); border-color: color-mix(in oklab, var(--danger) 30%, transparent)">
                            <XCircle :size="20" />
                        </span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="display-md text-[1.2rem]" style="color: var(--danger)">
                                    Akun ditolak
                                </h2>
                                <span
                                    class="tag"
                                    style="color: var(--danger); border-color: color-mix(in oklab, var(--danger) 36%, transparent); background: color-mix(in oklab, var(--danger) 14%, transparent)"
                                >
                                    Ditolak
                                </span>
                            </div>
                            <p class="mt-1.5 text-[14.5px] leading-relaxed" style="color: var(--danger)">
                                Silakan hubungi admin beasiswa untuk informasi lebih lanjut.
                            </p>
                        </div>
                    </div>
                </article>

                <!-- Approved -->
                <article v-else class="bento col-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <span class="bento-icon shrink-0" style="color: var(--success); background: color-mix(in oklab, var(--success) 14%, transparent); border-color: color-mix(in oklab, var(--success) 28%, transparent)">
                                <CheckCircle2 :size="20" />
                            </span>
                            <div>
                                <span class="mono block text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">
                                    Status akun
                                </span>
                                <div class="mt-1 flex items-center gap-2">
                                    <h2 class="display-md text-[1.3rem] text-[var(--ink)]">Aktif</h2>
                                    <span class="tag tag-success">Approved</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <!-- Quick links -->
            <section
                v-if="profile.approval_status === 'approved'"
                class="reveal-stagger grid grid-cols-1 gap-4 sm:grid-cols-3"
                style="--delay: 160ms"
                aria-label="Pintasan"
            >
                <Link
                    v-for="link in quickLinks"
                    :key="link.label"
                    :href="route(link.href)"
                    class="bento group"
                >
                    <div class="flex items-center justify-between">
                        <span class="bento-icon"><component :is="link.icon" :size="18" /></span>
                        <ArrowUpRight
                            :size="16"
                            class="text-[var(--muted)] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-[var(--primary)]"
                        />
                    </div>
                    <h3 class="display-md mt-4 text-[1.05rem] text-[var(--ink)]">{{ link.label }}</h3>
                    <p class="mt-1 text-[13.5px] leading-relaxed text-[var(--muted)]">{{ link.desc }}</p>
                </Link>
            </section>

            <!-- Result card -->
            <section class="reveal-stagger" style="--delay: 240ms" aria-label="Hasil seleksi">
                <article v-if="latestBatch && myResult" class="window">
                    <div class="window-bar">
                        <span class="window-dot" style="background:#fb7185"></span>
                        <span class="window-dot" style="background:#fbbf24"></span>
                        <span class="window-dot" style="background:#34d399"></span>
                        <span class="window-title">hasil-seleksi — {{ latestBatch.label }}</span>
                    </div>
                    <div class="window-body !p-6">
                        <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">
                            Hasil seleksi terakhir
                        </span>

                        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-[auto_1fr] sm:items-center">
                            <!-- Score dial -->
                            <div class="flex flex-col items-center justify-center rounded-2xl border border-[var(--border)] bg-[var(--surface-2)] px-7 py-6 text-center sm:w-[180px]">
                                <span class="mono text-[9px] uppercase tracking-[0.18em] text-[var(--muted)]">
                                    Skor Anda
                                </span>
                                <span class="score-dial mt-2 text-[2.8rem] leading-none text-[var(--ink)] tnum">
                                    {{ myResult.eligible ? Number(myResult.score).toFixed(2) : "—" }}
                                </span>
                                <span
                                    class="tag mt-3"
                                    :class="myResult.eligible ? 'tag-success' : 'tag-warning'"
                                >
                                    {{ myResult.eligible ? myResult.status : "Tidak memenuhi syarat" }}
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-[14px] text-[var(--foreground)]">
                                    <LineChart :size="15" class="text-[var(--accent)]" />
                                    <span>Batch <span class="font-medium text-[var(--ink)]">{{ latestBatch.label }}</span></span>
                                </div>
                                <div
                                    v-if="!myResult.eligible && myResult.reasons"
                                    class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4"
                                >
                                    <p class="mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">
                                        Alasan
                                    </p>
                                    <ul class="mt-2 space-y-1.5">
                                        <li
                                            v-for="r in myResult.reasons"
                                            :key="r"
                                            class="flex gap-2 text-[13.5px] leading-relaxed text-[var(--muted)]"
                                        >
                                            <span class="mt-2 h-1 w-1 shrink-0 rounded-full bg-[var(--warning)]"></span>
                                            <span>{{ r }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <p v-else-if="myResult.eligible" class="text-[14px] leading-relaxed text-[var(--muted)]">
                                    Selamat, kamu memenuhi syarat pada batch ini.
                                    Lihat posisi lengkap di
                                    <Link :href="route('mahasiswa.ranking.index')" class="text-[var(--primary)] hover:underline">
                                        halaman ranking
                                    </Link>.
                                </p>
                            </div>
                        </div>
                    </div>
                </article>

                <article v-else-if="latestBatch && !myResult" class="bento col-6">
                    <div class="flex items-start gap-4">
                        <span class="bento-icon shrink-0"><LineChart :size="20" /></span>
                        <div>
                            <h3 class="display-md text-[1.1rem] text-[var(--ink)]">Belum diikutkan</h3>
                            <p class="mt-1.5 text-[14.5px] leading-relaxed text-[var(--muted)]">
                                Kamu belum diikutkan dalam batch terbaru ({{ latestBatch.label }}).
                            </p>
                        </div>
                    </div>
                </article>

                <article
                    v-else-if="profile.approval_status === 'approved'"
                    class="bento col-6"
                >
                    <div class="flex items-start gap-4">
                        <span class="bento-icon shrink-0"><Clock :size="20" /></span>
                        <div>
                            <h3 class="display-md text-[1.1rem] text-[var(--ink)]">Menunggu seleksi berikutnya</h3>
                            <p class="mt-1.5 text-[14.5px] leading-relaxed text-[var(--muted)]">
                                Akun aktif. Pastikan profil dan prestasimu sudah lengkap
                                sebelum batch berikutnya dibuka.
                            </p>
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </MahasiswaLayout>
</template>
