import vue from 'eslint-plugin-vue';

export default [
    ...vue.configs['flat/essential'],
    {
        ignores: ['vendor', 'node_modules', 'public', 'bootstrap/ssr', 'tailwind.config.js'],
    },
    {
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
];
