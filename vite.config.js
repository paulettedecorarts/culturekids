import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/heritage-client.css',
                'resources/js/heritage-client.js',
            ],
            refresh: true,
        }),
    ],
});
