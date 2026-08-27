/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './config/**/*.php',
        './app/**/*.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                ink: 'rgb(var(--ink) / <alpha-value>)',
                paper: 'rgb(var(--paper) / <alpha-value>)',
                smoke: 'rgb(var(--smoke) / <alpha-value>)',
                surface: 'rgb(var(--surface) / <alpha-value>)',
                line: 'rgba(var(--line), 0.10)',
                accent: 'rgb(var(--accent) / <alpha-value>)',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
            },
            letterSpacing: {
                tightest: '-0.05em',
            },
            maxWidth: {
                '8xl': '88rem',
            },
        },
    },
    plugins: [],
};
