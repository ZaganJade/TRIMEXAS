<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";

const props = defineProps({
    student: { type: Object, required: true },
});

const form = useForm({
    full_name: props.student.full_name,
    semester: props.student.semester,
    ipk: props.student.ipk,
    penghasilan_ortu: props.student.penghasilan_ortu,
    tanggungan: props.student.tanggungan,
    phone: props.student.phone ?? "",
    address: props.student.address ?? "",
});

const error409 = ref(null);

function submit() {
    error409.value = null;
    form.put(route("mahasiswa.profile.update"), {
        onError: (errs) => {
            if (errs.message) error409.value = errs.message;
        },
    });
}

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Profil Mahasiswa" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
                <Link :href="route('mahasiswa.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Mahasiswa</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-2xl px-6 py-10">
            <h1 class="font-display text-3xl font-semibold tracking-tight">Profil</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">
                NIM: <span class="font-mono">{{ student.nim }}</span>. Profil terkunci selama batch seleksi berjalan.
            </p>

            <p
                v-if="error409"
                role="alert"
                class="mt-4 rounded-md border border-amber-300/40 bg-amber-50 px-3 py-2 text-sm text-amber-700"
            >
                {{ error409 }}
            </p>

            <Card variant="elevated" class="mt-6 p-6">
                <form class="grid grid-cols-1 gap-4 sm:grid-cols-2" @submit.prevent="submit">
                    <div class="space-y-1.5 sm:col-span-2">
                        <Label for="full_name" required>Nama lengkap</Label>
                        <Input id="full_name" v-model="form.full_name" :invalid="!!form.errors.full_name" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="semester" required>Semester</Label>
                        <Input id="semester" v-model.number="form.semester" type="number" min="1" max="14" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="ipk" required>IPK</Label>
                        <Input id="ipk" v-model.number="form.ipk" type="number" step="0.01" min="0" max="4" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="penghasilan_ortu" required>Penghasilan ortu (Rp)</Label>
                        <Input id="penghasilan_ortu" v-model.number="form.penghasilan_ortu" type="number" min="0" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="tanggungan" required>Tanggungan</Label>
                        <Input id="tanggungan" v-model.number="form.tanggungan" type="number" min="0" max="8" />
                    </div>

                    <div class="space-y-1.5 sm:col-span-2">
                        <Label for="phone">Telepon</Label>
                        <Input id="phone" v-model="form.phone" />
                    </div>

                    <div class="space-y-1.5 sm:col-span-2">
                        <Label for="address">Alamat</Label>
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="3"
                            class="w-full rounded-[var(--radius-input)] border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? "Menyimpan…" : "Simpan profil" }}
                        </Button>
                    </div>
                </form>
            </Card>
        </main>
    </div>
</template>
