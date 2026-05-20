import vuePlugin from "eslint-plugin-vue";
import prettierConfig from "@vue/eslint-config-prettier";

export default [
    ...vuePlugin.configs["flat/recommended"],
    prettierConfig,
    {
        ignores: [
            "node_modules/**",
            "vendor/**",
            "public/build/**",
            "storage/**",
            "bootstrap/cache/**",
        ],
        rules: {
            "vue/multi-word-component-names": "off",
            "vue/singleline-html-element-content-newline": "off",
            "vue/html-self-closing": ["warn", { html: { void: "always" } }],
            "no-console": ["warn", { allow: ["warn", "error"] }],
            "no-unused-vars": ["warn", { argsIgnorePattern: "^_" }],
        },
    },
];
