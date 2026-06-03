<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { computed, reactive } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
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

function save(_criterion, set) {
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
</script>

<template>
    <Head title="Konfigurasi Kriteria" />
    <AdminLayout active="criteria">
        <div class="window">
            <div class="window-bar">
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-title">Konfigurasi Kriteria Fuzzy</span>
            </div>
            <div class="window-body space-y-2">
                <p class="text-sm text-[var(--muted)]">
                    Parameter himpunan (a, b, c) editable per kriteria. Validasi monotonik
                    <code class="mono rounded bg-[var(--primary-soft)] px-1 text-xs">a &lt; b &lt; c</code>
                    diberlakukan oleh server. Snapshot diambil saat batch dijalankan.
                </p>

                <p
                    v-if="flash.success"
                    role="status"
                    class="tag tag-success"
                >
                    {{ flash.success }}
                </p>
            </div>
        </div>

        <div class="mt-6 space-y-6">
            <Card v-for="c in criteria" :key="c.id" variant="elevated" class="p-6">
                <div class="flex items-baseline justify-between">
                    <h2 class="display-md">
                        {{ c.name }}
                        <span class="ml-2 text-sm font-normal text-[var(--muted)]">
                            ({{ c.unit }} · domain {{ c.domain_min }}–{{ c.domain_max }})
                        </span>
                    </h2>
                </div>

                <div class="mt-6 bento-grid col-3">
                    <div
                        v-for="s in c.sets"
                        :key="s.id"
                        class="bento"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium capitalize">{{ s.name }}</h3>
                            <span class="tag mono text-xs">{{ s.shape }}</span>
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
    </AdminLayout>
</template>
