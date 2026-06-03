<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import { ArrowLeft } from "@lucide/vue";

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
</script>

<template>
    <Head title="Tambah Mahasiswa" />

    <AdminLayout active="students">
        <header class="reveal-stagger" style="--delay: 0ms">
            <Link
                :href="route('admin.students.index')"
                class="inline-flex items-center gap-1.5 text-sm text-[var(--muted)] hover:text-[var(--primary)]"
            >
                <ArrowLeft :size="14" />
                Kembali ke daftar
            </Link>
            <h1 class="display mt-4 text-[clamp(2rem,4vw,2.5rem)] text-[var(--ink)]">
                Tambah Mahasiswa
            </h1>
            <p class="mt-2 text-[14px] text-[var(--muted)]">
                Isi formulir di bawah untuk menambahkan mahasiswa baru ke sistem.
            </p>
        </header>

        <Card variant="elevated" class="reveal-stagger mt-8 p-6" style="--delay: 90ms">
            <form class="grid grid-cols-1 gap-5 sm:grid-cols-2" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label for="nim" required>NIM</Label>
                    <Input id="nim" v-model="form.nim" :invalid="!!form.errors.nim" />
                    <p v-if="form.errors.nim" class="text-xs text-[var(--danger)]">
                        {{ form.errors.nim }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label for="full_name" required>Nama lengkap</Label>
                    <Input id="full_name" v-model="form.full_name" :invalid="!!form.errors.full_name" />
                    <p v-if="form.errors.full_name" class="text-xs text-[var(--danger)]">
                        {{ form.errors.full_name }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label for="semester" required>Semester</Label>
                    <Input
                        id="semester"
                        v-model.number="form.semester"
                        type="number"
                        min="1"
                        max="14"
                        :invalid="!!form.errors.semester"
                    />
                    <p v-if="form.errors.semester" class="text-xs text-[var(--danger)]">
                        {{ form.errors.semester }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label for="status" required>Status</Label>
                    <select
                        id="status"
                        v-model="form.status"
                        class="h-10 w-full rounded-[var(--radius-input)] border border-[var(--border)] bg-[var(--surface)] px-3 text-sm text-[var(--foreground)] focus-visible:border-[var(--primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]/30"
                    >
                        <option value="aktif">Aktif</option>
                        <option value="cuti">Cuti</option>
                        <option value="lulus">Lulus</option>
                        <option value="keluar">Keluar</option>
                    </select>
                    <p v-if="form.errors.status" class="text-xs text-[var(--danger)]">
                        {{ form.errors.status }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label for="ipk" required>IPK</Label>
                    <Input
                        id="ipk"
                        v-model.number="form.ipk"
                        type="number"
                        step="0.01"
                        min="0"
                        max="4"
                        :invalid="!!form.errors.ipk"
                    />
                    <p v-if="form.errors.ipk" class="text-xs text-[var(--danger)]">
                        {{ form.errors.ipk }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label for="penghasilan_ortu" required>Penghasilan ortu</Label>
                    <Input
                        id="penghasilan_ortu"
                        v-model.number="form.penghasilan_ortu"
                        type="number"
                        min="0"
                        :invalid="!!form.errors.penghasilan_ortu"
                    />
                    <p v-if="form.errors.penghasilan_ortu" class="text-xs text-[var(--danger)]">
                        {{ form.errors.penghasilan_ortu }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label for="tanggungan" required>Tanggungan</Label>
                    <Input
                        id="tanggungan"
                        v-model.number="form.tanggungan"
                        type="number"
                        min="0"
                        max="8"
                        :invalid="!!form.errors.tanggungan"
                    />
                    <p v-if="form.errors.tanggungan" class="text-xs text-[var(--danger)]">
                        {{ form.errors.tanggungan }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label for="phone">Telepon</Label>
                    <Input id="phone" v-model="form.phone" :invalid="!!form.errors.phone" />
                    <p v-if="form.errors.phone" class="text-xs text-[var(--danger)]">
                        {{ form.errors.phone }}
                    </p>
                </div>

                <div class="space-y-1.5 sm:col-span-2">
                    <Label for="address">Alamat</Label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="w-full rounded-[var(--radius-input)] border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] placeholder:text-[var(--muted)] focus-visible:border-[var(--primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]/30"
                        :class="{ 'border-[var(--danger)]': form.errors.address }"
                    />
                    <p v-if="form.errors.address" class="text-xs text-[var(--danger)]">
                        {{ form.errors.address }}
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <Button type="submit" :disabled="form.processing" variant="primary">
                        {{ form.processing ? "Menyimpan…" : "Simpan Mahasiswa" }}
                    </Button>
                </div>
            </form>
        </Card>
    </AdminLayout>
</template>
