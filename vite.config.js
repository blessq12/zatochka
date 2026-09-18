import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";
import { fileURLToPath } from "url";
import { defineConfig } from "vite";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/site/css/site.css",
                "resources/site/js/site.js",
                "resources/css/apps.css",
                "resources/js/apps/client/main.js",
                "resources/js/apps/master/main.js",
                "resources/js/apps/manager/main.js",
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
            "@shared": path.resolve(__dirname, "resources/js/shared"),
            "@client": path.resolve(__dirname, "resources/js/apps/client"),
            "@master": path.resolve(__dirname, "resources/js/apps/master"),
        },
    },
});
