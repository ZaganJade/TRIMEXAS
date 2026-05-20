<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Dialog from "@/components/ui/Dialog.vue";
import Label from "@/components/ui/Label.vue";

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

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Verifikasi Akun Mahasiswa" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="font-display text-3xl font-semibold tracking-tight">
                        Verifikasi Akun Mahasiswa
                    </h1>
                    <p class="mt-2 text-sm text-[var(--muted)]">
                        {{ pending.total }} akun menunggu verifikasi.
                    </p>
                </div>
            </div>

            <p
                v-if="flash.success"
                role="status"
                class="mt-4 rounded-md border border-emerald-300/40 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200"
            >
                {{ flash.success }}
            </p>

            <Card variant="elevated" class="mt-6 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-[var(--primary-soft)]/40 text-left text-xs uppercase tracking-wide text-[var(--muted)]">
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
                        <tr v-for="row in pending.data" :key="row.id">
                            <td class="px-4 py-3 font-medium">{{ row.name }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ row.nim ?? "—" }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ row.email }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ row.semester ?? "—" }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">
                                {{ new Date(row.created_at).toLocaleDateString("id-ID") }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <Button size="sm" variant="primary" @click="approve(row.id)">
                                        Approve
                                    </Button>
                                    <Button size="sm" variant="danger" @click="openReject(row)">
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
            </Card>
        </main>

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
                        class="w-full rounded-[var(--radius-input)] border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] placeholder:text-[var(--muted)] focus-visible:outline-none focus-visible:border-[var(--primary)] focus-visible:ring-2 focus-visible:ring-[var(--ring)]/30"
                    />
                    <p v-if="rejectForm.errors.reason" class="text-xs text-red-600">
                        {{ rejectForm.errors.reason }}
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button variant="ghost" type="button" @click="rejectOpen = false">
                        Batal
                    </Button>
                    <Button variant="danger" type="submit" :disabled="rejectForm.processing">
                        {{ rejectForm.processing ? "Memproses..." : "Tolak Akun" }}
                    </Button>
                </div>
            </form>
        </Dialog>
    </div>
</template>
