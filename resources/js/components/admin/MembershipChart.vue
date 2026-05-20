<script setup>
import { computed } from "vue";

const props = defineProps({
    shape: { type: String, required: true }, // linear_turun | segitiga | linear_naik
    a: { type: Number, required: true },
    b: { type: Number, required: true },
    c: { type: Number, default: null },
    domainMin: { type: Number, required: true },
    domainMax: { type: Number, required: true },
    color: { type: String, default: "#3189C6" },
    height: { type: Number, default: 80 },
    width: { type: Number, default: 240 },
});

const path = computed(() => {
    const samples = 64;
    const span = props.domainMax - props.domainMin;
    if (span <= 0) return "";

    const points = [];
    for (let i = 0; i <= samples; i++) {
        const x = props.domainMin + (span * i) / samples;
        let mu = 0;
        if (props.shape === "linear_turun") {
            if (x <= props.a) mu = 1;
            else if (x >= props.b) mu = 0;
            else mu = (props.b - x) / (props.b - props.a);
        } else if (props.shape === "linear_naik") {
            if (x <= props.a) mu = 0;
            else if (x >= props.b) mu = 1;
            else mu = (x - props.a) / (props.b - props.a);
        } else {
            // segitiga
            const c = props.c ?? props.b;
            if (x <= props.a || x >= c) mu = 0;
            else if (x < props.b) mu = (x - props.a) / (props.b - props.a);
            else if (x === props.b) mu = 1;
            else mu = (c - x) / (c - props.b);
        }

        const px = ((x - props.domainMin) / span) * props.width;
        const py = props.height - mu * props.height;
        points.push(`${i === 0 ? "M" : "L"}${px.toFixed(2)},${py.toFixed(2)}`);
    }
    return points.join(" ");
});
</script>

<template>
    <svg
        :width="width"
        :height="height"
        :viewBox="`0 0 ${width} ${height}`"
        role="img"
        :aria-label="`Kurva membership ${shape}`"
        class="overflow-visible"
    >
        <line
            :x1="0"
            :y1="height"
            :x2="width"
            :y2="height"
            stroke="var(--border)"
            stroke-width="1"
        />
        <path
            :d="path"
            :stroke="color"
            stroke-width="2"
            fill="none"
            stroke-linejoin="round"
        />
    </svg>
</template>
