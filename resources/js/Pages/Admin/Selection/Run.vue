<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";

defineProps({
    lastBatches: { type: Array, default: () => [] },
});

const form = useForm({ label: "" });

function submit() {
    form.post(route("admin.selection.run"));
}
</script>

<template>
    <Head title="Jalankan Seleksi" />
    <AdminLayout active="selection">
        <div class="window">
            <div class="window-bar">
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-title">Jalankan Seleksi</span>
            </div>
            <div class="window-body">
                <p class="text-sm text-[var(--muted)]">
                    Snapshot parameter himpunan, rules, dan thresholds akan dibekukan saat batch dimulai.
                </p>
            </div>
        </div>

        <Card variant="elevated" class="mt-6 p-6">
            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label for="label" required>Label batch</Label>
                    <Input
                        id="label"
                        v-model="form.label"
                        placeholder="contoh: Semester Genap 2026"
                        :invalid="!!form.errors.label"
                    />
                    <p v-if="form.errors.label" class="text-xs text-red-600">
                        {{ form.errors.label }}
                    </p>
                </div>
                <Button type="submit" :disabled="form.processing" class="btn-primary">
                    {{ form.processing ? "Memproses…" : "Run Selection" }}
                </Button>
            </form>
        </Card>

        <Card v-if="lastBatches.length" variant="outline" class="mt-6 overflow-hidden">
            <div class="window-bar">
                <span class="window-title text-xs">Riwayat batch terbaru</span>
            </div>
            <ul class="divide-y divide-[var(--border)]">
                <li
                    v-for="b in lastBatches"
                    :key="b.id"
                    class="flex items-center justify-between px-4 py-3 text-sm"
                >
                    <div>
                        <p class="font-medium">{{ b.label }}</p>
                        <p class="text-xs text-[var(--muted)] mono">
                            {{ new Date(b.created_at).toLocaleString("id-ID") }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="tag tag-primary mono text-xs uppercase">{{ b.status }}</span>
                        <Link
                            :href="route('admin.selection.show', b.id)"
                            class="text-sm text-[var(--primary)] hover:underline"
                        >
                            Lihat
                        </Link>
                    </div>
                </li>
            </ul>
        </Card>
    </AdminLayout>
</template>
