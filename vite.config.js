import { defineConfig } from 'vite'
import laravel, { refreshPaths } from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        react({
            jsxRuntime: 'automatic', // Explicitly enable automatic JSX runtime

        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                }
            }
        }),
        laravel({
            input: ['resources/css/app.css', 'resources/js/react/src/react.css','resources/js/results/App.css', 'resources/js/results/index.css', 'resources/js/result/index.css','resources/js/result/App.css','resources/js/result/App.jsx','resources/js/results/app.jsx','resources/js/react/app.jsx', 'resources/js/app.js', 'resources/css/filament/teacher/theme.css','resources/css/filament/app/theme.css','resources/css/filament/student/theme.css', 'resources/css/filament/ourstudent/theme.css' ],
            refresh: [
                ...refreshPaths,
                'app/Filament/**',
                'app/Forms/Components/**',
                'app/Livewire/**',
                'app/Infolists/Components/**',
                'app/Providers/Filament/**',
                'app/Tables/Columns/**',
            ],
        }),

    ],
})
