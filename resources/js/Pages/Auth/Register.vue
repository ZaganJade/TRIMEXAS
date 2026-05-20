<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
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

    <div class="min-h-screen flex items-center justify-center bg-[var(--background)] px-4 py-10">
        <div class="w-full max-w-lg space-y-6">
            <div class="text-center space-y-2">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 text-sm text-[var(--muted)] hover:text-[var(--foreground)]"
                >
                    ← Kembali ke beranda
                </Link>
                <h1 class="display-tight text-5xl tracking-tight text-[var(--ink)]">
                    Daftar sebagai <span class="text-gradient-blue">Mahasiswa</span>
                </h1>
                <p class="mt-2 text-sm text-[var(--muted)]">
                    Akun baru menunggu verifikasi admin sebelum dapat masuk.
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
                                autocomplete="name"
                                :invalid="!!form.errors.name"
                            />
                            <p v-if="form.errors.name" class="text-xs text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="nim" required>NIM</Label>
                            <Input
                                id="nim"
                                v-model="form.nim"
                                :invalid="!!form.errors.nim"
                            />
                            <p v-if="form.errors.nim" class="text-xs text-red-600">
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
                            :invalid="!!form.errors.email"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-600">
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
                            :invalid="!!form.errors.semester"
                        />
                        <p v-if="form.errors.semester" class="text-xs text-red-600">
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
                            <p v-if="form.errors.password" class="text-xs text-red-600">
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

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        {{ form.processing ? "Memproses..." : "Daftar" }}
                    </Button>
                </form>
            </Card>

            <p class="text-center text-sm text-[var(--muted)]">
                Sudah punya akun?
                <Link :href="route('login')" class="text-[var(--primary)] hover:underline">
                    Masuk
                </Link>
            </p>
        </div>
    </div>
</template>
