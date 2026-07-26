import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sources/main/style.scss',
                'resources/sources/superdong.scss',
                'resources/sources/main/home-v2.scss',
                'resources/js/superdong.js',
                'resources/js/home-v2.js',
                'resources/sources/admin/style.scss',
                'resources/sources/main/print.scss',
                'resources/sources/admin/print.scss',
            ],
            refresh: [
                'resources/views/**',
                'resources/sources/**',
                'resources/js/**',
                'app/Http/Controllers/**',
                'routes/**',
            ],
            detectTls: process.env.VITE_DETECT_TLS || false,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: process.env.VITE_HMR_HOST || '127.0.0.1',
        },
        watch: {
            usePolling: true,
            interval: 100,
        },
    },
    build: {
        outDir: 'public/build',
        assetsDir: 'assets',
        manifest: 'manifest.json',
        rollupOptions: {
            output: {
                // Thêm [hash] để browser cache tự hết hiệu lực khi deploy
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: ({ name }) => {
                    if (/\.css$/.test(name ?? '')) {
                        return 'assets/[name]-[hash].css';
                    }
                    return 'assets/[name]-[hash].[ext]';
                },
            },
        },
    },
});