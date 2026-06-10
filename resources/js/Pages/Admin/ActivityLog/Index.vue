<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Input from "@/components/ui/Input.vue";

const props = defineProps({
    logs: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    logNames: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

const filterForm = ref({
    log_name: props.filters.log_name ?? "",
    user_id: props.filters.user_id ?? "",
    from: props.filters.from ?? "",
    to: props.filters.to ?? "",
});

function applyFilters() {
    const payload = Object.fromEntries(
        Object.entries(filterForm.value).filter(([, v]) => v !== "" && v !== null)
    );
    router.get(route("admin.activity.index"), payload, { preserveState: true });
}
</script>

<template>
    <Head title="Activity Log" />
    <AdminLayout active="activity">
        <div class="window">
            <div class="window-bar">
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-title">Activity Log</span>
            </div>
            <div class="window-body">
                <form class="bento-grid" @submit.prevent="applyFilters">
                    <div class="bento col-2">
                        <label class="eyebrow block mb-1">Log name</label>
                        <select v-model="filterForm.log_name" class="h-9 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm mono">
                            <option value="">Semua</option>
                            <option v-for="n in logNames" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                    <div class="bento col-2">
                        <label class="eyebrow block mb-1">User</label>
                        <select v-model="filterForm.user_id" class="h-9 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm">
                            <option value="">Semua</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div class="bento col-2">
                        <label class="eyebrow block mb-1">Dari</label>
                        <Input v-model="filterForm.from" type="date" />
                    </div>
                    <div class="bento col-2">
                        <label class="eyebrow block mb-1">Sampai</label>
                        <Input v-model="filterForm.to" type="date" />
                    </div>
                    <div class="bento col-8">
                        <Button type="submit" variant="secondary" size="sm">Filter</Button>
                    </div>
                </form>
            </div>
        </div>

        <div class="window mt-6">
            <div class="window-body p-0 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)] bg-[var(--surface)]">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Log name</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Subject</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="log in logs.data" :key="log.id">
                            <td class="px-4 py-3 text-[var(--muted)] text-xs mono">
                                {{ new Date(log.created_at).toLocaleString("id-ID") }}
                            </td>
                            <td class="px-4 py-3">{{ log.causer?.name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="tag mono uppercase text-xs">{{ log.log_name }}</span>
                            </td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ log.description }}</td>
                            <td class="px-4 py-3 text-xs text-[var(--muted)] mono">
                                {{ log.subject_type ? `${log.subject_type.split('\\').pop()} #${log.subject_id}` : '—' }}
                            </td>
                        </tr>
                        <tr v-if="!logs.data.length">
                            <td colspan="5" class="px-4 py-10 text-center text-[var(--muted)]">Tidak ada log.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
