import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/sass/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: [
            {
                // this is required for the SCSS modules
                find: /^~(.*)$/,
                replacement: '$1',
            },
        ],
    },
    build: {
        // 啟用 Subresource Integrity (SRI)
        // 產生 integrity hash 以驗證資源完整性
        rollupOptions: {
            output: {
                // 確保產生的檔案名稱包含 hash
                entryFileNames: 'assets/[name].[hash].js',
                chunkFileNames: 'assets/[name].[hash].js',
                assetFileNames: 'assets/[name].[hash].[ext]'
            }
        },
        // 產生 manifest 檔案,Laravel 會使用它來產生帶有 integrity 的標籤
        manifest: true,
    },
});
