<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
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

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Activity Log" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10 space-y-4">
            <h1 class="font-display text-3xl font-semibold tracking-tight">Activity Log</h1>

            <Card variant="outline" class="p-4">
                <form class="grid grid-cols-1 gap-3 sm:grid-cols-4" @submit.prevent="applyFilters">
                    <div>
                        <label class="text-xs uppercase tracking-wide text-[var(--muted)]">Log name</label>
                        <select v-model="filterForm.log_name" class="mt-1 h-10 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm">
                            <option value="">Semua</option>
                            <option v-for="n in logNames" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-wide text-[var(--muted)]">User</label>
                        <select v-model="filterForm.user_id" class="mt-1 h-10 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm">
                            <option value="">Semua</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-wide text-[var(--muted)]">Dari</label>
                        <Input v-model="filterForm.from" type="date" class="mt-1" />
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-wide text-[var(--muted)]">Sampai</label>
                        <Input v-model="filterForm.to" type="date" class="mt-1" />
                    </div>
                    <div class="sm:col-span-4">
                        <Button type="submit" variant="secondary" size="sm">Filter</Button>
                    </div>
                </form>
            </Card>

            <Card variant="elevated" class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
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
                            <td class="px-4 py-3 text-[var(--muted)]">
                                {{ new Date(log.created_at).toLocaleString("id-ID") }}
                            </td>
                            <td class="px-4 py-3">{{ log.causer?.name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs uppercase">{{ log.log_name }}</td>
                            <td class="px-4 py-3">{{ log.description }}</td>
                            <td class="px-4 py-3 text-xs text-[var(--muted)]">
                                {{ log.subject_type ? `${log.subject_type.split('\\').pop()} #${log.subject_id}` : '—' }}
                            </td>
                        </tr>
                        <tr v-if="!logs.data.length">
                            <td colspan="5" class="px-4 py-10 text-center text-[var(--muted)]">Tidak ada log.</td>
                        </tr>
                    </tbody>
                </table>
            </Card>
        </main>
    </div>
</template>
