<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";

defineProps({
    profile: { type: Object, required: true },
    latestBatch: { type: Object, default: null },
    myResult: { type: Object, default: null },
});

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Mahasiswa · Dashboard" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
                <Link href="/" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Mahasiswa</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6 py-10 space-y-6">
            <h1 class="font-display text-3xl font-semibold tracking-tight">Halo, {{ profile.name }}</h1>

            <Card v-if="profile.approval_status === 'pending'" variant="outline" class="p-5">
                <p class="font-medium">Akun menunggu verifikasi admin</p>
                <p class="mt-1 text-sm text-[var(--muted)]">
                    Anda akan menerima notifikasi email saat akun disetujui atau ditolak.
                </p>
            </Card>

            <Card v-else-if="profile.approval_status === 'rejected'" variant="outline" class="p-5 border-red-300/40 bg-red-50/40">
                <p class="font-medium text-red-700">Akun ditolak</p>
                <p class="mt-1 text-sm text-red-700">Silakan hubungi admin beasiswa.</p>
            </Card>

            <Card v-else variant="elevated" class="p-5">
                <p class="text-xs uppercase tracking-wide text-[var(--muted)]">Status akun</p>
                <p class="mt-1 font-display text-lg font-semibold text-emerald-600">Aktif (Approved)</p>
                <div class="mt-3 flex flex-wrap gap-3 text-sm">
                    <Link :href="route('mahasiswa.profile.show')" class="text-[var(--primary)] hover:underline">Edit profil</Link>
                    <Link :href="route('mahasiswa.achievements.index')" class="text-[var(--primary)] hover:underline">Kelola prestasi</Link>
                    <Link :href="route('mahasiswa.ranking.index')" class="text-[var(--primary)] hover:underline">Lihat ranking publik</Link>
                </div>
            </Card>

            <Card v-if="latestBatch && myResult" variant="elevated" class="p-6">
                <p class="text-xs uppercase tracking-wide text-[var(--muted)]">Hasil seleksi terakhir</p>
                <p class="mt-1 font-display text-lg font-semibold">{{ latestBatch.label }}</p>
                <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-[var(--muted)]">Skor Anda</p>
                        <p class="mt-1 font-display text-2xl font-semibold">
                            {{ myResult.eligible ? Number(myResult.score).toFixed(2) : "—" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[var(--muted)]">Status</p>
                        <p class="mt-1 font-display text-lg capitalize">
                            {{ myResult.eligible ? myResult.status : "Tidak memenuhi syarat" }}
                        </p>
                    </div>
                </div>
                <div v-if="!myResult.eligible && myResult.reasons" class="mt-3 text-sm text-[var(--muted)]">
                    <p>Alasan:</p>
                    <ul class="mt-1 list-disc pl-5">
                        <li v-for="r in myResult.reasons" :key="r">{{ r }}</li>
                    </ul>
                </div>
            </Card>

            <Card v-else-if="latestBatch && !myResult" variant="outline" class="p-5">
                <p class="text-sm">Anda belum diikutkan dalam batch terbaru ({{ latestBatch.label }}).</p>
            </Card>

            <Card v-else-if="profile.approval_status === 'approved'" variant="outline" class="p-5">
                <p class="text-sm">Akun aktif, menunggu seleksi berikutnya.</p>
            </Card>
        </main>
    </div>
</template>
