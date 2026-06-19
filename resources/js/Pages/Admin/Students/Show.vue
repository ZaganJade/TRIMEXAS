<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import { ArrowLeft, Trash2 } from "@lucide/vue";

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
    useForm({}).post(route("admin.achievements.verify", achievementId), {
        preserveScroll: true,
    });
}

function unverify(achievementId) {
    useForm({}).post(route("admin.achievements.unverify", achievementId), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Mahasiswa ${student.full_name}`" />

    <AdminLayout active="students">
        <header class="reveal-stagger" style="--delay: 0ms">
            <Link
                :href="route('admin.students.index')"
                class="inline-flex items-center gap-1.5 text-sm text-[var(--muted)] hover:text-[var(--primary)]"
            >
                <ArrowLeft :size="14" />
                Kembali ke daftar
            </Link>
            <div class="mt-4 flex flex-wrap items-baseline justify-between gap-3">
                <div>
                    <h1 class="display text-[clamp(2rem,4vw,2.5rem)] text-[var(--ink)]">
                        {{ student.full_name }}
                    </h1>
                    <p class="mono mt-1.5 text-sm text-[var(--muted)] tnum">
                        {{ student.nim }}
                    </p>
                </div>
                <Button variant="danger" size="sm" @click="destroy">
                    <Trash2 :size="14" class="mr-1.5" />
                    Hapus
                </Button>
            </div>
        </header>

        <Card variant="elevated" class="reveal-stagger mt-8 p-6" style="--delay: 90ms">
            <h2 class="display-md text-[1.2rem] text-[var(--ink)]">Edit Data Mahasiswa</h2>
            <form class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label for="nim" required>NIM</Label>
                    <Input id="nim" v-model="form.nim" :invalid="!!form.errors.nim" />
                    <p v-if="form.errors.nim" class="text-xs text-[var(--danger)]">
                        {{ form.errors.nim }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label for="full_name" required>Nama lengkap</Label>
                    <Input
                        id="full_name"
                        v-model="form.full_name"
                        :invalid="!!form.errors.full_name"
                    />
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
                        {{ form.processing ? "Menyimpan…" : "Simpan Perubahan" }}
                    </Button>
                </div>
            </form>
        </Card>

        <Card variant="elevated" class="reveal-stagger mt-6 overflow-hidden" style="--delay: 180ms">
            <div
                class="border-b border-[var(--border)] bg-[var(--primary-soft)]/40 px-5 py-3"
            >
                <h3 class="display-md text-[1rem] text-[var(--ink)]">Prestasi Mahasiswa</h3>
                <p class="mono mt-0.5 text-xs text-[var(--muted)] tnum">
                    Agregat akademis: {{ student.agregat_akademis }} · non-akademis:
                    {{ student.agregat_non_akademis }}
                </p>
            </div>
            <div class="table-scroll">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Level</th>
                            <th class="px-4 py-3">Peringkat</th>
                            <th class="px-4 py-3 text-right">Skor</th>
                            <th class="px-4 py-3 text-center">Verified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr
                            v-for="a in student.achievements"
                            :key="a.id"
                            class="hover:bg-[var(--surface-2)]"
                        >
                            <td class="px-4 py-3 font-medium">{{ a.title }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ a.category }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ a.level }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ a.rank }}</td>
                            <td class="px-4 py-3 text-right font-mono tnum">{{ a.score }}</td>
                            <td class="px-4 py-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="a.verified_by_admin"
                                    class="h-4 w-4 cursor-pointer accent-[var(--primary)]"
                                    @change="
                                        a.verified_by_admin ? unverify(a.id) : verify(a.id)
                                    "
                                />
                            </td>
                        </tr>
                        <tr v-if="!student.achievements || !student.achievements.length">
                            <td colspan="6" class="px-4 py-8 text-center text-[var(--muted)]">
                                Belum ada prestasi tercatat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </AdminLayout>
</template>
