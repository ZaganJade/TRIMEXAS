<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import MahasiswaLayout from "@/Layouts/MahasiswaLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import { User } from "@lucide/vue";

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
</script>

<template>
    <Head title="Profil Mahasiswa" />

    <MahasiswaLayout active="profile">
        <div class="space-y-7">
            <!-- Heading -->
            <header class="reveal-stagger" style="--delay: 0ms">
                <div class="flex items-center gap-3">
                    <span class="bento-icon" style="background: var(--primary-soft); color: var(--primary); border-color: color-mix(in oklab, var(--primary) 28%, transparent)">
                        <User :size="20" />
                    </span>
                    <div>
                        <span class="section-label">Data Diri</span>
                        <h1 class="display mt-1 text-[clamp(1.8rem,4vw,2.4rem)] text-[var(--ink)]">
                            Profil Mahasiswa
                        </h1>
                    </div>
                </div>
                <p class="mt-3 text-[14.5px] text-[var(--muted)]">
                    NIM: <span class="mono tnum text-[var(--foreground)]">{{ student.nim }}</span>. Profil terkunci selama batch seleksi berjalan.
                </p>
            </header>

            <!-- Error 409 -->
            <div
                v-if="error409"
                role="alert"
                class="bento col-8"
                style="border-color: color-mix(in oklab, var(--warning) 38%, var(--border)); background: color-mix(in oklab, var(--warning) 6%, var(--surface))"
            >
                <p class="text-[14px] leading-relaxed" style="color: var(--warning)">
                    {{ error409 }}
                </p>
            </div>

            <!-- Form -->
            <section class="reveal-stagger" style="--delay: 80ms">
                <Card variant="elevated" class="p-6">
                    <form class="grid grid-cols-1 gap-5 sm:grid-cols-2" @submit.prevent="submit">
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="full_name" required>Nama lengkap</Label>
                            <Input id="full_name" v-model="form.full_name" :invalid="!!form.errors.full_name" />
                            <p v-if="form.errors.full_name" class="text-[13px]" style="color: var(--danger)">{{ form.errors.full_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="semester" required>Semester</Label>
                            <Input id="semester" v-model.number="form.semester" type="number" min="1" max="14" :invalid="!!form.errors.semester" />
                            <p v-if="form.errors.semester" class="text-[13px]" style="color: var(--danger)">{{ form.errors.semester }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="ipk" required>IPK</Label>
                            <Input id="ipk" v-model.number="form.ipk" type="number" step="0.01" min="0" max="4" :invalid="!!form.errors.ipk" />
                            <p v-if="form.errors.ipk" class="text-[13px]" style="color: var(--danger)">{{ form.errors.ipk }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="penghasilan_ortu" required>Penghasilan orang tua (Rp)</Label>
                            <Input id="penghasilan_ortu" v-model.number="form.penghasilan_ortu" type="number" min="0" :invalid="!!form.errors.penghasilan_ortu" />
                            <p v-if="form.errors.penghasilan_ortu" class="text-[13px]" style="color: var(--danger)">{{ form.errors.penghasilan_ortu }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="tanggungan" required>Tanggungan</Label>
                            <Input id="tanggungan" v-model.number="form.tanggungan" type="number" min="0" max="8" :invalid="!!form.errors.tanggungan" />
                            <p v-if="form.errors.tanggungan" class="text-[13px]" style="color: var(--danger)">{{ form.errors.tanggungan }}</p>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <Label for="phone">Nomor telepon</Label>
                            <Input id="phone" v-model="form.phone" placeholder="+62" />
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <Label for="address">Alamat</Label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                placeholder="Masukkan alamat lengkap…"
                                class="w-full rounded-[var(--radius-input)] border border-[var(--border)] bg-[var(--surface)] px-3 py-2.5 text-[14px] text-[var(--foreground)] placeholder:text-[var(--muted)] focus:border-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary-soft)]"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <Button type="submit" :disabled="form.processing" variant="primary">
                                {{ form.processing ? "Menyimpan…" : "Simpan profil" }}
                            </Button>
                        </div>
                    </form>
                </Card>
            </section>
        </div>
    </MahasiswaLayout>
</template>
