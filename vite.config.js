import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: true, 
        port: 5174,
        strictPort: true,
        hmr: { host: process.env.VITE_HMR_HOST || 'localhost' },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
