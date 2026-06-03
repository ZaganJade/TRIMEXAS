<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { ShieldCheck, ArrowRight } from "@lucide/vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
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

    <GuestLayout>
        <div class="reveal-stagger space-y-6" style="--delay: 60ms">
            <div class="space-y-3 text-center">
                <span class="eyebrow mx-auto" style="--delay: 0ms">
                    <span class="dot"></span>
                    <span class="mono uppercase tracking-[0.14em]">Portal Pengelola &amp; Mahasiswa</span>
                </span>
                <h1 class="display-tight text-[clamp(2.4rem,7vw,3.25rem)] text-[var(--ink)]">
                    Masuk ke <span class="text-gradient">Trimexas</span>
                </h1>
                <p class="text-sm leading-relaxed text-[var(--muted)]">
                    Sistem Pendukung Keputusan Beasiswa Triv × MEXC
                </p>
            </div>

            <Card variant="elevated" class="p-6 sm:p-8">
                <div
                    v-if="flash.success"
                    role="status"
                    class="mb-5 flex items-start gap-2.5 rounded-[var(--radius-input)] border border-[color-mix(in_oklab,var(--success)_36%,transparent)] bg-[color-mix(in_oklab,var(--success)_12%,transparent)] px-3.5 py-2.5 text-sm text-[var(--success)]"
                >
                    <ShieldCheck :size="16" class="mt-0.5 shrink-0" />
                    <span>{{ flash.success }}</span>
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
                        <p v-if="form.errors.email" class="text-xs text-[var(--danger)]">
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
                        <p v-if="form.errors.password" class="text-xs text-[var(--danger)]">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-[var(--muted)]">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-[var(--border)] bg-[var(--surface)] text-[var(--primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)]/30"
                        />
                        Ingat saya
                    </label>

                    <Button type="submit" size="lg" class="group w-full" :disabled="form.processing">
                        {{ form.processing ? "Memproses..." : "Masuk" }}
                        <ArrowRight
                            v-if="!form.processing"
                            :size="16"
                            class="transition-transform group-hover:translate-x-0.5"
                        />
                    </Button>
                </form>
            </Card>

            <p v-if="canRegister" class="text-center text-sm text-[var(--muted)]">
                Belum punya akun?
                <Link :href="route('register')" class="font-medium text-[var(--primary)] hover:underline">
                    Daftar sebagai mahasiswa
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
