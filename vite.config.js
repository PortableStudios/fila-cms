import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";

export default defineConfig({
    build: {
        emptyOutDir: true,
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    let extType = assetInfo.name.split(".").pop();
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
                        extType = "images";
                    }
                    return `assets/${extType}/[name]-[hash][extname]`;
                },
                entryFileNames: "assets/js/[name]-[hash].js",
            },
        },
    },
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/tiptap/extensions.js",
                "resources/js/tiptap/eventHandler.js",
                "../../../../resources/js/tiptap/extensions.js",
                "../../../../resources/js/tiptap/eventHandler.js",
                "../../../../resources/css/filament/admin/theme.css",
                "../../../../resources/css/filacms.css",
            ],
            refresh: [
                ...refreshPaths,
                "../../../../resources/css/filacms.css",
                "app/Filament/**",
                "app/Forms/Components/**",
                "app/Livewire/**",
                "app/Infolists/Components/**",
                "app/Providers/Filament/**",
                "app/Tables/Columns/**",
            ],
        }),
    ],
});
