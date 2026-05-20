<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, reactive } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import MembershipChart from "@/components/admin/MembershipChart.vue";

const props = defineProps({
    criteria: { type: Array, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

// Local editable copy so the chart can preview before save.
const edits = reactive(
    Object.fromEntries(
        props.criteria.flatMap((c) =>
            c.sets.map((s) => [
                s.id,
                { a: s.a, b: s.b, c: s.c, errors: {}, processing: false },
            ])
        )
    )
);

function save(criterionDomain, set) {
    const local = edits[set.id];
    local.processing = true;
    local.errors = {};
    const form = useForm({ a: local.a, b: local.b, c: local.c });
    form.put(route("admin.criteria.update", { fuzzySet: set.id }), {
        preserveScroll: true,
        onError: (errs) => {
            local.errors = errs;
        },
        onFinish: () => {
            local.processing = false;
        },
    });
}

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Konfigurasi Kriteria" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10">
            <h1 class="font-display text-3xl font-semibold tracking-tight">
                Konfigurasi Kriteria Fuzzy
            </h1>
            <p class="mt-2 text-sm text-[var(--muted)]">
                Parameter himpunan (a, b, c) editable per kriteria. Validasi monotonik
                <code class="rounded bg-[var(--primary-soft)] px-1 text-xs">a &lt; b &lt; c</code>
                diberlakukan oleh server. Snapshot diambil saat batch dijalankan, sehingga
                ranking historis tidak akan berubah.
            </p>

            <p
                v-if="flash.success"
                role="status"
                class="mt-4 rounded-md border border-emerald-300/40 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200"
            >
                {{ flash.success }}
            </p>

            <div class="mt-8 space-y-6">
                <Card v-for="c in criteria" :key="c.id" variant="elevated" class="p-6">
                    <div class="flex items-baseline justify-between">
                        <h2 class="font-display text-xl font-semibold tracking-tight">
                            {{ c.name }}
                            <span class="ml-2 text-sm font-normal text-[var(--muted)]">
                                ({{ c.unit }} · domain {{ c.domain_min }}–{{ c.domain_max }})
                            </span>
                        </h2>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div
                            v-for="s in c.sets"
                            :key="s.id"
                            class="rounded-lg border border-[var(--border)] p-4"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium capitalize">{{ s.name }}</h3>
                                <span class="text-xs text-[var(--muted)]">{{ s.shape }}</span>
                            </div>

                            <div class="mt-3">
                                <MembershipChart
                                    :shape="s.shape"
                                    :a="Number(edits[s.id].a)"
                                    :b="Number(edits[s.id].b)"
                                    :c="edits[s.id].c !== null ? Number(edits[s.id].c) : null"
                                    :domain-min="c.domain_min"
                                    :domain-max="c.domain_max"
                                    :width="240"
                                    :height="80"
                                />
                            </div>

                            <form class="mt-4 space-y-2" @submit.prevent="save(c, s)">
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <Label :for="`a-${s.id}`">a</Label>
                                        <Input
                                            :id="`a-${s.id}`"
                                            v-model.number="edits[s.id].a"
                                            type="number"
                                            step="any"
                                            :invalid="!!edits[s.id].errors.a"
                                        />
                                    </div>
                                    <div>
                                        <Label :for="`b-${s.id}`">b</Label>
                                        <Input
                                            :id="`b-${s.id}`"
                                            v-model.number="edits[s.id].b"
                                            type="number"
                                            step="any"
                                            :invalid="!!edits[s.id].errors.b"
                                        />
                                    </div>
                                    <div>
                                        <Label :for="`c-${s.id}`">c</Label>
                                        <Input
                                            :id="`c-${s.id}`"
                                            v-model.number="edits[s.id].c"
                                            type="number"
                                            step="any"
                                            :disabled="s.shape !== 'segitiga'"
                                            :invalid="!!edits[s.id].errors.c"
                                        />
                                    </div>
                                </div>
                                <p
                                    v-if="edits[s.id].errors.a || edits[s.id].errors.b || edits[s.id].errors.c"
                                    class="text-xs text-red-600"
                                >
                                    {{ edits[s.id].errors.a || edits[s.id].errors.b || edits[s.id].errors.c }}
                                </p>
                                <Button
                                    type="submit"
                                    size="sm"
                                    class="w-full"
                                    :disabled="edits[s.id].processing"
                                >
                                    {{ edits[s.id].processing ? "Menyimpan…" : "Simpan" }}
                                </Button>
                            </form>
                        </div>
                    </div>
                </Card>
            </div>
        </main>
    </div>
</template>
