<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";

defineProps({
    canRegister: { type: Boolean, default: true },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

function submit() {
    form.post(route("login.store"), {
        onFinish: () => form.reset("password"),
    });
}
</script>

<template>
    <Head title="Masuk" />

    <div class="min-h-screen flex items-center justify-center bg-[var(--background)] px-4 py-10">
        <div class="w-full max-w-md space-y-6">
            <div class="text-center space-y-2">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 text-sm text-[var(--muted)] hover:text-[var(--foreground)]"
                >
                    ← Kembali ke beranda
                </Link>
                <h1 class="display-tight text-5xl tracking-tight text-[var(--ink)]">
                    Masuk ke <span class="text-gradient-blue">Trimexas</span>
                </h1>
                <p class="mt-2 text-sm text-[var(--muted)]">
                    Sistem Pendukung Keputusan Beasiswa Triv × MEXC
                </p>
            </div>

            <Card variant="elevated" class="p-6 sm:p-8">
                <div
                    v-if="flash.success"
                    role="status"
                    class="mb-4 rounded-md border border-emerald-300/40 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200"
                >
                    {{ flash.success }}
                </div>

                <form class="space-y-4" @submit.prevent="submit">
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
                        <p v-if="form.errors.email" class="text-xs text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="password" required>Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            :invalid="!!form.errors.password"
                        />
                        <p v-if="form.errors.password" class="text-xs text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-[var(--muted)]">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded border-[var(--border)] text-[var(--primary)] focus:ring-[var(--primary)]/30"
                        />
                        Ingat saya
                    </label>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        {{ form.processing ? "Memproses..." : "Masuk" }}
                    </Button>
                </form>
            </Card>

            <p v-if="canRegister" class="text-center text-sm text-[var(--muted)]">
                Belum punya akun?
                <Link :href="route('register')" class="text-[var(--primary)] hover:underline">
                    Daftar sebagai mahasiswa
                </Link>
            </p>
        </div>
    </div>
</template>
