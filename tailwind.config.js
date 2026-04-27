import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Models/*.php', // For dynamic tailwind classes in models
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Lato', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', ...defaultTheme.fontFamily.serif],
                script: ['"Caveat"', 'cursive'],
            },
            colors: {
                journal: {
                    bg: '#FDFBF7',     // Main paper background
                    paper: '#F5F0E6',  // Card paper color
                    dark: '#2C2A29',   // Dark ink text
                    light: '#8B857F',  // Lighter text
                    border: '#E8E1D5', // Subtle borders
                    accent: '#D35400', // Terracotta accent (buttons, highlights)
                    olive: '#5A6E4D',  // Olive green secondary
                    gold: '#D4AF37',   // Gold accent
                }
            },
            backgroundImage: {
                'paper-texture': "url('/images/paper-texture.png')",
            },
            boxShadow: {
                'postcard': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(0,0,0,0.05)',
                'photo': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05), inset 0 0 0 8px #fff, inset 0 0 0 9px rgba(0,0,0,0.1)',
            }
        },
    },

    plugins: [forms],
};
