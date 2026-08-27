<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @php
        $canonicalBase = 'https://faisalimtiaz.com';
        $canonicalOverride = trim($__env->yieldContent('canonical', ''));
        $canonicalPath = $canonicalOverride !== ''
            ? (parse_url($canonicalOverride, PHP_URL_PATH) ?: '/')
            : request()->getPathInfo();
        $canonicalPath = '/' . ltrim($canonicalPath ?: '/', '/');
        $seoCanonical = $canonicalPath === '/'
            ? $canonicalBase
            : $canonicalBase . rtrim($canonicalPath, '/');
        $seoTitle = trim($__env->yieldContent('title', 'Faisal Imtiaz — Full-Stack Developer'));
        $seoDescription = trim($__env->yieldContent('meta_description', 'Faisal Imtiaz is a full-stack developer focused on SaaS platforms and AI-powered products.'));
        $seoType = trim($__env->yieldContent('og_type', 'website')) ?: 'website';
        $socialImageOverride = trim($__env->yieldContent('og_image', ''));
        $seoImage = $socialImageOverride !== ''
            ? (filter_var($socialImageOverride, FILTER_VALIDATE_URL)
                ? $socialImageOverride
                : $canonicalBase . '/' . ltrim(parse_url($socialImageOverride, PHP_URL_PATH) ?: $socialImageOverride, '/'))
            : $canonicalBase . '/assets/logo.png';
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:site_name" content="Faisal Imtiaz">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <meta name="theme-color" id="theme-color-meta" content="#EDEDE7">
    <meta name="color-scheme" content="light dark">

    <script>
        // Default to light theme, but restore the user's previously selected theme.
        (function () {
            try {
                var saved = localStorage.getItem('studio-theme');
                if (saved === 'dark') {
                    document.documentElement.classList.add('dark');
                    var meta = document.getElementById('theme-color-meta');
                    if (meta) meta.setAttribute('content', '#090A0C');
                }
            } catch (e) {}
        })();
    </script>

    @vite('resources/css/app.css')

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

    <!-- ========== FOOTER ========== -->
    <footer class="bg-white px-5 py-5 text-neutral-900 sm:px-10 dark:bg-black dark:text-neutral-100 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-6 pb-4 md:grid-cols-12">
                <div class="md:col-span-5">
                    <a href="{{ route('studio') }}" class="text-base font-black tracking-tight">
                        Faisal Imtiaz<span class="text-accent">.</span>
                    </a>
                    <p class="mt-1.5 max-w-sm text-[11px] leading-relaxed opacity-70">
                        Senior React Native Engineer building and shipping production mobile applications and websites.
                    </p>
                </div>

                <nav class="md:col-span-3" aria-label="Apps">
                    <h3 class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.25em] opacity-60">Apps</h3>
                    <ul class="space-y-0.5">
                        <li><a href="{{ route('aboutme') }}" class="text-[11px] transition-colors hover:text-accent">About</a></li>
                        <li><a href="{{ route('aboutme') }}#skills" class="text-[11px] transition-colors hover:text-accent">Skills</a></li>
                        <li><a href="{{ route('aboutme') }}#contact" class="text-[11px] transition-colors hover:text-accent">Contact</a></li>
                    </ul>
                </nav>

                <nav class="md:col-span-4" aria-label="Resources">
                    <h3 class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.25em] opacity-60">Resources</h3>
                    <ul class="space-y-0.5">
                        <li><a href="{{ route('blog') }}" class="text-[11px] transition-colors hover:text-accent">Blog</a></li>
                        <li><a href="{{ route('studio.testimonials') }}" class="text-[11px] transition-colors hover:text-accent">Testimonials</a></li>
                        <li><a href="mailto:ctlinc.faisal@gmail.com?subject=Project%20enquiry%20from%20faisalimtiaz.com" class="text-[11px] transition-colors hover:text-accent">Email</a></li>
                        <li><a href="{{ route('aboutme') }}#contact" class="text-[11px] transition-colors hover:text-accent">Contact</a></li>
                    </ul>
                </nav>
            </div>

            <div class="flex flex-col items-center justify-between gap-1.5 border-t border-black/10 pt-3 sm:flex-row dark:border-white/10">
                <p class="text-[10px] opacity-60">© {{ date('Y') }} Faisal Imtiaz. All rights reserved.</p>
                <p class="text-[10px] font-semibold uppercase tracking-[0.25em] opacity-60">Senior React Native Engineer</p>
            </div>
        </div>
    </footer>

    @yield('scripts')

</body>
</html>
