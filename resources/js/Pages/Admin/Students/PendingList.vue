<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Dialog from "@/components/ui/Dialog.vue";
import Label from "@/components/ui/Label.vue";
import { UserCheck, UserX, Clock } from "@lucide/vue";

const props = defineProps({
    pending: { type: Object, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

const approveForm = useForm({});
function approve(userId) {
    if (!confirm("Setujui akun mahasiswa ini?")) return;
    approveForm.post(route("admin.students.approve", { user: userId }), {
        preserveScroll: true,
    });
}

const rejectForm = useForm({ reason: "" });
const rejectingUser = ref(null);
const rejectOpen = ref(false);
function openReject(user) {
    rejectingUser.value = user;
    rejectForm.reset();
    rejectForm.clearErrors();
    rejectOpen.value = true;
}
function submitReject() {
    if (!rejectingUser.value) return;
    rejectForm.post(route("admin.students.reject", { user: rejectingUser.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            rejectOpen.value = false;
            rejectingUser.value = null;
        },
    });
}
</script>

<template>
    <Head title="Verifikasi Akun Mahasiswa" />

    <AdminLayout active="pending">
        <header class="reveal-stagger" style="--delay: 0ms">
            <span class="section-label">Verifikasi Pendaftar</span>
            <h1 class="display mt-4 text-[clamp(2rem,4vw,2.5rem)] text-[var(--ink)]">
                Akun Menunggu Persetujuan
            </h1>
            <p class="mt-2 text-[14px] text-[var(--muted)]">
                <Clock :size="14" class="mr-1 inline" />
                {{ pending.total }} akun menunggu verifikasi manual.
            </p>
        </header>

        <div
            v-if="flash.success"
            role="status"
            class="reveal-stagger mt-6 rounded-md border border-emerald-300/40 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-950/40 dark:text-emerald-200"
            style="--delay: 90ms"
        >
            {{ flash.success }}
        </div>

        <div class="window reveal-stagger mt-6 overflow-hidden" style="--delay: 180ms">
            <div class="window-bar">
                <div class="flex items-center gap-1.5">
                    <span class="window-dot bg-red-500"></span>
                    <span class="window-dot bg-yellow-500"></span>
                    <span class="window-dot bg-green-500"></span>
                </div>
                <span class="window-title">pending_verifications.db</span>
            </div>
            <div class="window-body overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Semester</th>
                            <th class="px-4 py-3">Mendaftar</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr
                            v-for="row in pending.data"
                            :key="row.id"
                            class="hover:bg-[var(--surface-2)]"
                        >
                            <td class="px-4 py-3 font-medium">{{ row.name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-[var(--muted)] tnum">
                                {{ row.nim ?? "—" }}
                            </td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ row.email }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">
                                {{ row.semester ?? "—" }}
                            </td>
                            <td class="px-4 py-3 text-[var(--muted)]">
                                {{ new Date(row.created_at).toLocaleDateString("id-ID") }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <Button size="sm" variant="primary" @click="approve(row.id)">
                                        <UserCheck :size="14" class="mr-1" />
                                        Approve
                                    </Button>
                                    <Button size="sm" variant="danger" @click="openReject(row)">
                                        <UserX :size="14" class="mr-1" />
                                        Reject
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!pending.data.length">
                            <td colspan="6" class="px-4 py-10 text-center text-[var(--muted)]">
                                Tidak ada akun yang menunggu verifikasi.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog
            :open="rejectOpen"
            title="Tolak Akun Mahasiswa"
            :description="`Akun ${rejectingUser?.name ?? ''} akan ditolak. Alasan akan dikirim ke mahasiswa.`"
            @update:open="rejectOpen = $event"
        >
            <form class="space-y-3" @submit.prevent="submitReject">
                <div class="space-y-1.5">
                    <Label for="reason" required>Alasan penolakan (min. 10 karakter)</Label>
                    <textarea
                        id="reason"
                        v-model="rejectForm.reason"
                        rows="4"
                        class="w-full rounded-[var(--radius-input)] border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] placeholder:text-[var(--muted)] focus-visible:border-[var(--primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]/30"
                        :class="{ 'border-[var(--danger)]': rejectForm.errors.reason }"
                    />
                    <p v-if="rejectForm.errors.reason" class="text-xs text-[var(--danger)]">
                        {{ rejectForm.errors.reason }}
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button variant="ghost" type="button" @click="rejectOpen = false">
                        Batal
                    </Button>
                    <Button
                        variant="danger"
                        type="submit"
                        :disabled="rejectForm.processing"
                    >
                        {{ rejectForm.processing ? "Memproses..." : "Tolak Akun" }}
                    </Button>
                </div>
            </form>
        </Dialog>
    </AdminLayout>
</template>
