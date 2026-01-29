import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: true, 
        port: 5174,
        strictPort: true,
        hmr: { host: '192.168.56.1' },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
