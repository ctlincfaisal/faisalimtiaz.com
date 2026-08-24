/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: 'media',
    theme: {
        extend: {
            colors: {
                ink: '#000000',
                paper: '#ffffff',
                smoke: '#9ca3af',
                line: 'rgba(255, 255, 255, 0.12)',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
            },
            letterSpacing: {
                tightest: '-0.06em',
            },
            maxWidth: {
                '8xl': '88rem',
            },
        },
    },
    plugins: [],
};
