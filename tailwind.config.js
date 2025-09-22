import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            // Brand Colors
            colors: {
                // Primary Brand (Sage Green/Teal)
                primary: {
                    50: '#f0f9f7',
                    100: '#dbf0ed',
                    200: '#b9e2dc',
                    300: '#8bccc4',
                    400: '#6aa49c',
                    500: '#4f9288', // Brand Primary
                    600: '#3f7a70',
                    700: '#35635c',
                    800: '#2d504a',
                    900: '#28433f',
                },
                // Secondary Brand (Blue Gray)
                secondary: {
                    50: '#f8fafb',
                    100: '#f1f4f6',
                    200: '#e0e7eb',
                    300: '#c4d1d8',
                    400: '#9fb1bb',
                    500: '#7a8e9a',
                    600: '#637580',
                    700: '#546268',
                    800: '#49565c',
                    900: '#2f4a56', // Brand Secondary
                },
                // Legacy support - map to primary colors
                brand: {
                    50: '#f0f9f7',
                    100: '#dbf0ed',
                    200: '#b9e2dc',
                    300: '#8bccc4',
                    400: '#6aa49c',
                    500: '#4f9288', // Primary brand color
                    600: '#3f7a70',
                    700: '#35635c',
                    800: '#2d504a',
                    900: '#28433f',
                },
                teal: {
                    50: '#f0f9f7',
                    100: '#dbf0ed',
                    200: '#b9e2dc',
                    300: '#8bccc4',
                    400: '#6aa49c',
                    500: '#4f9288',
                    600: '#3f7a70',
                    700: '#35635c',
                    800: '#2d504a',
                    900: '#28433f',
                },
                slate: {
                    50: '#f8fafb',
                    100: '#f1f4f6',
                    200: '#e0e7eb',
                    300: '#c4d1d8',
                    400: '#9fb1bb',
                    500: '#7a8e9a',
                    600: '#637580',
                    700: '#546268',
                    800: '#49565c',
                    900: '#2f4a56',
                },
            },

            // Typography
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                heading: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },

            // Spacing
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
                '128': '32rem',
            },

            // Border Radius
            borderRadius: {
                'xl': '1rem',
                '2xl': '1.5rem',
                '3xl': '2rem',
            },

            // Box Shadow
            boxShadow: {
                'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                'medium': '0 4px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                'strong': '0 10px 40px -10px rgba(0, 0, 0, 0.15), 0 20px 25px -5px rgba(0, 0, 0, 0.1)',
            },

            // Animation
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'slide-down': 'slideDown 0.3s ease-out',
                'pulse-soft': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },

            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(-10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
            },

            // Screen sizes for responsive design
            screens: {
                'xs': '475px',
                '3xl': '1680px',
            },
        },
    },

    plugins: [
        forms({
            strategy: 'class', // Use form classes instead of global styles
        }),
    ],
};
