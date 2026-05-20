<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";

const form = useForm({
    nim: "",
    full_name: "",
    semester: 1,
    status: "aktif",
    ipk: 0,
    penghasilan_ortu: 0,
    tanggungan: 0,
    phone: "",
    address: "",
});

function submit() {
    form.post(route("admin.students.store"));
}

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Tambah Mahasiswa" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6 py-10">
            <h1 class="font-display text-3xl font-semibold tracking-tight">Tambah Mahasiswa</h1>

            <Card variant="elevated" class="mt-6 p-6">
                <form class="grid grid-cols-1 gap-4 sm:grid-cols-2" @submit.prevent="submit">
                    <div class="space-y-1.5">
                        <Label for="nim" required>NIM</Label>
                        <Input id="nim" v-model="form.nim" :invalid="!!form.errors.nim" />
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
                    <div class="space-y-1.5 sm:col-span-2">
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
        </main>
    </div>
</template>
