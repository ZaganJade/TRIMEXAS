<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";

const props = defineProps({
    student: { type: Object, required: true },
});

const form = useForm({
    nim: props.student.nim,
    full_name: props.student.full_name,
    semester: props.student.semester,
    status: props.student.status,
    ipk: props.student.ipk,
    penghasilan_ortu: props.student.penghasilan_ortu,
    tanggungan: props.student.tanggungan,
    phone: props.student.phone ?? "",
    address: props.student.address ?? "",
});

function submit() {
    form.put(route("admin.students.update", props.student.id), { preserveScroll: true });
}

function destroy() {
    if (!confirm(`Hapus mahasiswa ${props.student.full_name}?`)) return;
    useForm({}).delete(route("admin.students.destroy", props.student.id));
}

function verify(achievementId) {
    useForm({}).post(route("admin.achievements.verify", achievementId), { preserveScroll: true });
}

function unverify(achievementId) {
    useForm({}).post(route("admin.achievements.unverify", achievementId), { preserveScroll: true });
}

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head :title="`Mahasiswa ${student.full_name}`" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-10 space-y-6">
            <div class="flex items-baseline justify-between">
                <h1 class="font-display text-3xl font-semibold tracking-tight">
                    {{ student.full_name }}
                    <span class="ml-2 text-sm font-mono font-normal text-[var(--muted)]">{{ student.nim }}</span>
                </h1>
                <Button variant="danger" size="sm" @click="destroy">Hapus mahasiswa</Button>
            </div>

            <Card variant="elevated" class="p-6">
                <p class="font-medium">Edit data</p>
                <form class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2" @submit.prevent="submit">
                    <div class="space-y-1.5">
                        <Label for="nim" required>NIM</Label>
                        <Input id="nim" v-model="form.nim" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="full_name" required>Nama lengkap</Label>
                        <Input id="full_name" v-model="form.full_name" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="semester" required>Semester</Label>
                        <Input id="semester" v-model.number="form.semester" type="number" min="1" max="14" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="status" required>Status</Label>
                        <select id="status" v-model="form.status" class="h-10 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm">
                            <option value="aktif">Aktif</option>
                            <option value="cuti">Cuti</option>
                            <option value="lulus">Lulus</option>
                            <option value="keluar">Keluar</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="ipk" required>IPK</Label>
                        <Input id="ipk" v-model.number="form.ipk" type="number" step="0.01" min="0" max="4" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="penghasilan_ortu" required>Penghasilan ortu</Label>
                        <Input id="penghasilan_ortu" v-model.number="form.penghasilan_ortu" type="number" min="0" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="tanggungan" required>Tanggungan</Label>
                        <Input id="tanggungan" v-model.number="form.tanggungan" type="number" min="0" max="8" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="phone">Telepon</Label>
                        <Input id="phone" v-model="form.phone" />
                    </div>
                    <div class="sm:col-span-2 space-y-1.5">
                        <Label for="address">Alamat</Label>
                        <textarea id="address" v-model="form.address" rows="3" class="w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm" />
                    </div>
                    <div class="sm:col-span-2">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? "Menyimpan…" : "Simpan" }}
                        </Button>
                    </div>
                </form>
            </Card>

            <Card variant="elevated" class="overflow-hidden">
                <div class="border-b border-[var(--border)] bg-[var(--primary-soft)]/40 px-4 py-2 text-xs uppercase tracking-wide text-[var(--muted)]">
                    Prestasi (agregat akademis: {{ student.agregat_akademis }} · non-akademis: {{ student.agregat_non_akademis }})
                </div>
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Level</th>
                            <th class="px-4 py-3">Peringkat</th>
                            <th class="px-4 py-3 text-right">Skor</th>
                            <th class="px-4 py-3">Verified</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="a in student.achievements" :key="a.id">
                            <td class="px-4 py-3">{{ a.title }}</td>
                            <td class="px-4 py-3">{{ a.category }}</td>
                            <td class="px-4 py-3">{{ a.level }}</td>
                            <td class="px-4 py-3">{{ a.rank }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ a.score }}</td>
                            <td class="px-4 py-3">
                                <input type="checkbox" :checked="a.verified_by_admin" @change="a.verified_by_admin ? unverify(a.id) : verify(a.id)" />
                            </td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tbody>
                </table>
            </Card>
        </main>
    </div>
</template>
