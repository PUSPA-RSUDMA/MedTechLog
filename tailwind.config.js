import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // 60% Latar Utama (#FAF5F6), 30% Kartu/Sekunder (#F2E3E7), Teks Utama (#331F24)
                slate: {
                    50: '#FAF5F6',
                    100: '#F2E3E7',
                    200: '#E5CDD3',
                    300: '#D4B4BD',
                    400: '#9A7B83',
                    500: '#75565E',
                    600: '#5A3E46',
                    700: '#462C33',
                    800: '#331F24',
                    900: '#331F24',
                    950: '#241418',
                },
                // 10% Aksen / Tombol (#CB4D6D)
                teal: {
                    50: '#FDF4F6',
                    100: '#F2E3E7',
                    200: '#E8C5CE',
                    300: '#DDA0B0',
                    400: '#D6758E',
                    500: '#D15D7B',
                    600: '#CB4D6D',
                    700: '#CB4D6D',
                    800: '#B03C5A',
                    900: '#8F2D47',
                    950: '#331F24',
                },
            },
        },
    },

    plugins: [forms],
};
