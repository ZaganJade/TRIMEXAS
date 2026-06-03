<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ArrowRight } from "@lucide/vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";

const form = useForm({
    name: "",
    nim: "",
    email: "",
    semester: 1,
    password: "",
    password_confirmation: "",
});

function submit() {
    form.post(route("register.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
}
</script>

<template>
    <Head title="Daftar" />

    <GuestLayout maxWidth="max-w-lg">
        <div class="reveal-stagger space-y-6" style="--delay: 60ms">
            <div class="space-y-3 text-center">
                <span class="eyebrow mx-auto" style="--delay: 0ms">
                    <span class="dot"></span>
                    <span class="mono uppercase tracking-[0.14em]">Pendaftaran Mahasiswa</span>
                </span>
                <h1 class="display-tight text-[clamp(2.4rem,7vw,3.25rem)] text-[var(--ink)]">
                    Daftar sebagai <span class="text-gradient">Mahasiswa</span>
                </h1>
                <p class="text-sm leading-relaxed text-[var(--muted)]">
                    Akun baru menunggu verifikasi admin sebelum dapat masuk
                </p>
            </div>

            <Card variant="elevated" class="p-6 sm:p-8">
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="name" required>Nama lengkap</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                autocomplete="name"
                                placeholder="Nama lengkap"
                                :invalid="!!form.errors.name"
                            />
                            <p v-if="form.errors.name" class="text-xs text-[var(--danger)]">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="nim" required>NIM</Label>
                            <Input
                                id="nim"
                                v-model="form.nim"
                                type="text"
                                placeholder="Nomor Induk Mahasiswa"
                                :invalid="!!form.errors.nim"
                            />
                            <p v-if="form.errors.nim" class="text-xs text-[var(--danger)]">
                                {{ form.errors.nim }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="email" required>Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="email@kampus.ac.id"
                            :invalid="!!form.errors.email"
                        />
                        <p v-if="form.errors.email" class="text-xs text-[var(--danger)]">
                            {{ form.errors.email }}
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
                            placeholder="1-14"
                            :invalid="!!form.errors.semester"
                        />
                        <p v-if="form.errors.semester" class="text-xs text-[var(--danger)]">
                            {{ form.errors.semester }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="password" required>Password</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                autocomplete="new-password"
                                :invalid="!!form.errors.password"
                            />
                            <p v-if="form.errors.password" class="text-xs text-[var(--danger)]">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="password_confirmation" required>Konfirmasi password</Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>

                    <Button type="submit" size="lg" class="group w-full" :disabled="form.processing">
                        {{ form.processing ? "Memproses..." : "Daftar" }}
                        <ArrowRight
                            v-if="!form.processing"
                            :size="16"
                            class="transition-transform group-hover:translate-x-0.5"
                        />
                    </Button>
                </form>
            </Card>

            <p class="text-center text-sm text-[var(--muted)]">
                Sudah punya akun?
                <Link :href="route('login')" class="font-medium text-[var(--primary)] hover:underline">
                    Masuk
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
