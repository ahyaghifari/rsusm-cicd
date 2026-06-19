import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                // "resources/css/filament/admin/theme.css",
                "resources/js/app.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: "0.0.0.0",
        // Mengizinkan domain Ngrok agar tidak terkena error "Block/Invalid Host"
        allowedHosts: [".ngrok-free.app", ".ngrok-free.dev"],
        hmr: {
            host: "localhost",
        },
    },
});
