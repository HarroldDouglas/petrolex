import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";
import fs from "fs";

// Créer une fonction pour générer un fichier .watchignore si nécessaire
const createWatchIgnore = () => {
    const watchIgnorePath = path.resolve(__dirname, ".watchignore");
    if (!fs.existsSync(watchIgnorePath)) {
        fs.writeFileSync(watchIgnorePath, "resources/lang/**/*\n");
    }
};

// Exécuter la fonction
createWatchIgnore();

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "public/assets/scss/style.scss",
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: [
                "**/node_modules/**",
                "**/vendor/**",
                "**/resources/lang/**",
                "**/.git/**",
            ],
            usePolling: false, // Désactiver le polling peut aider dans certains cas
        },
        hmr: {
            // Exclure les fichiers spécifiques du HMR
            exclude: ["resources/lang/**/*.json", "resources/lang/**/*.php"],
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: [
                    "mixed-decls",
                    "color-functions",
                    "global-builtin",
                    "import",
                ],
            },
        },
    },
    resolve: {
        alias: {
            "@scss": path.resolve(__dirname, "public/assets/scss/app"),
        },
    },
    optimizeDeps: {
        exclude: ["resources/lang"],
    },
    // Configurations de build améliorées
    build: {
        // Ignorer les fichiers de langues lors de la construction
        rollupOptions: {
            external: [/resources\/lang\/.*/],
        },
    },
});
