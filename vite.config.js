const { defineConfig } = require('vite');
const laravel = require('laravel-vite-plugin');
const vue = require('@vitejs/plugin-vue');
const path = require('path');

module.exports = defineConfig({
    plugins: [
        laravel.default({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue.default({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        cors: {
            origin: ['http://counsel-wise.test', 'https://counsel-wise.test'],
            credentials: true,
        },
    },
});
