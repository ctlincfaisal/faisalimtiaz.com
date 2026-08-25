<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Faisal Imtiaz — Full-Stack Developer')</title>
    <meta name="description" content="@yield('meta_description', 'Faisal Imtiaz is a full-stack developer focused on SaaS platforms and AI-powered products.')">
    <meta name="theme-color" id="theme-color-meta" content="#EDEDE7">
    <meta name="color-scheme" content="light dark">

    <script>
        // Keep the theme light by default on every load.
        (function () {
            try {
                localStorage.removeItem('studio-theme');
            } catch (e) {}
        })();
    </script>

    <!-- Tailwind CSS (Play CDN — swap for a compiled build in production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
                },
            },
        };
    </script>

    <!-- Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- GSAP -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <style>
        /* ---------- Theme tokens (light is default, .dark overrides) ---------- */
        :root {
            --ink: 241 241 237;
            --paper: 9 10 12;
            --smoke: 82 88 96;
            --surface: 255 255 255;
            --line: 9 10 12;
            --accent: 77 124 15;
        }
        .dark {
            --ink: 9 10 12;
            --paper: 237 237 231;
            --smoke: 138 143 152;
            --surface: 13 15 18;
            --line: 237 237 231;
            --accent: 200 240 62;
        }

        /* ---------- Anchor scroll offset (fixed header) ---------- */
        [id] {
            scroll-margin-top: 5rem;
        }

        /* ---------- Clamp-based responsive type ---------- */
        .hero-title {
            font-size: clamp(2.5rem, 1rem + 6vw, 6rem);
            line-height: 0.95;
            letter-spacing: -0.05em;
            font-weight: 900;
        }
        .display-title {
            font-size: clamp(2.25rem, 1.25rem + 2.2vw, 4rem);
            line-height: 1.02;
            letter-spacing: -0.04em;
            font-weight: 900;
        }
        .project-title {
            font-size: clamp(1.5rem, 0.5rem + 3vw, 3rem);
            letter-spacing: -0.03em;
            font-weight: 900;
        }
        .text-outline {
            color: transparent;
            -webkit-text-stroke: 1.5px rgb(var(--paper));
        }

        /* ---------- Header ---------- */
        .site-header {
            transition: background-color .3s ease, border-color .3s ease, box-shadow .3s ease;
            border-bottom: 1px solid transparent;
            background-color: transparent;
        }
        .site-header.is-scrolled {
            background-color: rgb(var(--ink) / 0.82);
            -webkit-backdrop-filter: blur(12px);
            backdrop-filter: blur(12px);
            border-bottom-color: rgba(var(--line), 0.10);
        }
        .nav-underline {
            position: relative;
        }
        .nav-underline::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -4px;
            height: 1px;
            width: 100%;
            background: rgb(var(--accent));
            transform: scaleX(0);
            transform-origin: right;
            transition: transform .3s ease;
        }
        .nav-underline:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* ---------- Marquee edge fade ---------- */
        .marquee {
            -webkit-mask-image: linear-gradient(90deg, transparent, rgb(var(--ink)) 8%, rgb(var(--ink)) 92%, transparent);
            mask-image: linear-gradient(90deg, transparent, rgb(var(--ink)) 8%, rgb(var(--ink)) 92%, transparent);
        }

        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>

    @yield('head')

    @stack('structured_data')
</head>
<body class="bg-ink text-paper font-sans antialiased selection:bg-paper selection:text-ink overflow-x-hidden">

    @yield('content')

    @yield('scripts')

</body>
</html>