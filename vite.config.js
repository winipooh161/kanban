import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: {
            origin: ['http://kanban', 'http://localhost', 'http://127.0.0.1'],
            credentials: true,
        },
        hmr: {
            host: 'localhost',
        },
    },
});
