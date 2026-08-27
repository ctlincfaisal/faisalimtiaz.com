@extends('studio.app')

@section('title', 'Blog | Faisal Imtiaz')
@section('meta_description', 'Practical articles on websites, Laravel, React Native, and SEO that help startups and small businesses launch with more clarity.')

@php
    $schemaBase = 'https://faisalimtiaz.com';
    $websiteId = $schemaBase . '/#website';
    $blogUrl = $schemaBase . '/blog';
    $blogItems = collect($posts)->values()->map(function ($post, $index) use ($schemaBase) {
        $path = parse_url($post['canonical'], PHP_URL_PATH) ?: '/blog';

        return [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => $schemaBase . '/' . ltrim($path, '/'),
            'name' => $post['title'],
        ];
    })->all();
@endphp

@push('structured_data')
    @include('components.structured-data', ['graph' => [
        [
            '@type' => 'CollectionPage',
            '@id' => $blogUrl . '#collection',
            'url' => $blogUrl,
            'name' => 'Blog | Faisal Imtiaz',
            'isPartOf' => ['@id' => $websiteId],
            'mainEntity' => [
                '@type' => 'ItemList',
                '@id' => $blogUrl . '#itemlist',
                'itemListElement' => $blogItems,
            ],
            'inLanguage' => 'en',
        ],
    ]])
@endpush

@section('head')
<style>
    .hero-heading {
        font-size: clamp(2rem, 0.8rem + 3.4vw, 3.4rem);
        line-height: 1.05;
        letter-spacing: -0.035em;
        font-weight: 900;
    }
    .tech-chip {
        border: 1px solid rgba(var(--line), 0.12);
        background: rgb(var(--surface));
    }
</style>
@endsection

@section('content')
@php
    $coverImages = [
        'homepage-that-converts-visitors-into-leads' => 'https://images.unsplash.com/photo-1559028012-481c04fa702d?auto=format&fit=crop&w=1200&q=80',
        'website-vs-web-app' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
        'why-react-native-is-a-good-fit-for-startup-apps' => url('assets/faisalimtiaz/app-development.webp'),
        'when-laravel-is-the-right-backend-choice' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80',
        'seo-basics-for-service-businesses' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=80',
        'improve-website-speed-without-a-full-redesign' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1200&q=80',
        'what-to-include-on-a-service-page' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
        'react-native-launch-checklist-for-startups' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=1200&q=80',
        'react-native-app-cost' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
        'how-long-app-development-takes' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80',
        'seo-for-small-businesses' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=80',
        'website-vs-app-for-startups' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
        'maintenance-and-support-after-launch' => 'https://images.unsplash.com/photo-1551808525-51a94da548ce?auto=format&fit=crop&w=1200&q=80',
        'laravel-dashboard-features-small-businesses-need' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80',
        'internal-linking-for-service-business-blogs' => 'https://images.unsplash.com/photo-1516321165247-4aa89a48be28?auto=format&fit=crop&w=1200&q=80',
    ];
@endphp

<header class="site-header fixed inset-x-0 top-0 z-50">
    <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-5 sm:px-10" aria-label="Primary">
        <a href="{{ route('studio') }}" class="inline-flex items-center text-sm font-black tracking-tight text-paper">
            <img src="{{ url('assets/sign.png') }}" alt="Faisal Imtiaz" class="h-12 w-auto sm:h-20">
        </a>

        <ul class="hidden items-center gap-9 md:flex">
            <li><a href="{{ route('studio') }}" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">Home</a></li>
            <li><a href="{{ route('studio') }}#work" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">Work</a></li>
            <li><a href="{{ route('aboutme') }}" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">About</a></li>
            <li><a href="{{ route('aboutme') }}#contact" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">Contact</a></li>
        </ul>

        <div class="flex items-center gap-6">
            <button type="button"
                    id="theme-toggle"
                    class="magnetic-btn inline-flex h-10 w-10 items-center justify-center rounded-full border border-paper/20 text-paper transition-colors hover:border-accent hover:text-accent"
                    aria-label="Toggle dark mode" data-magnetic>
                <svg class="block h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                </svg>
                <svg class="hidden h-4 w-4 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
            <a href="{{ route('aboutme') }}#contact"
               class="magnetic-btn hidden items-center gap-1.5 rounded-full border border-paper/20 px-5 py-2.5 text-xs font-semibold text-paper transition-colors hover:border-accent hover:text-accent sm:inline-flex"
               data-magnetic>
                Let's Talk
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </nav>
</header>

<main>

    <!-- ========== BLOG HERO ========== -->
    <section class="px-5 pb-20 pt-32 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-4 text-[11px] font-semibold uppercase tracking-[0.3em] text-smoke">
                <a href="{{ url('/') }}" class="transition-colors hover:text-paper">Home</a>
                <span class="mx-2 text-paper/30">/</span>
                <span class="text-paper/60">Blog</span>
            </div>

            <span class="text-[11px] font-semibold uppercase tracking-[0.5em] text-accent">Blog</span>
            <h1 class="hero-heading mt-4 max-w-4xl text-paper" data-reveal>
                Notes on websites, apps, and SEO that support real projects.
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-relaxed text-smoke" data-reveal>
                The blog lives on faisalimtiaz.com so the articles, services, and case examples all strengthen the same domain. That keeps the authority in one place and makes it easier for readers to move from learning to enquiry.
            </p>
            <div class="mt-10 flex flex-wrap gap-4" data-reveal>
                <a href="{{ route('aboutme') }}#contact"
                   class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-7 py-3.5 text-sm font-bold text-ink transition-colors hover:bg-white hover:text-black"
                   data-magnetic>
                    Start a project
                    <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </a>
                <a href="{{ route('services.website-development') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-paper/20 px-7 py-3.5 text-sm font-semibold text-paper transition-colors hover:border-accent hover:text-accent">
                    Explore services
                </a>
            </div>
        </div>
    </section>

    <!-- ========== POSTS ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $slug => $post)
                    <article class="group flex flex-col overflow-hidden rounded-2xl bg-surface transition-colors duration-500 hover:shadow-[0_20px_50px_-20px_rgba(0,0,0,0.4)]" data-scroll-reveal>
                        <div class="relative aspect-[16/10] overflow-hidden">
                            <img src="{{ $coverImages[$slug] ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80' }}"
                                 alt="{{ $post['title'] }} illustration"
                                 loading="lazy"
                                 decoding="async"
                                 class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="rounded-full bg-accent/15 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.15em] text-accent">{{ $post['eyebrow'] }}</span>
                                <span class="text-xs text-smoke">{{ $post['reading_time'] }}</span>
                            </div>
                            <h2 class="text-lg font-black leading-tight tracking-tight text-paper">{{ $post['title'] }}</h2>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-smoke">{{ $post['summary'] }}</p>
                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach (array_slice($post['tags'], 0, 3) as $tag)
                                    <span class="tech-chip rounded-full px-3 py-1 text-[11px] font-medium text-paper/70">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <a href="{{ route('blog.post', $slug) }}"
                               class="mt-6 inline-flex items-center gap-2 self-start text-sm font-semibold text-paper transition-colors duration-300 group-hover:text-accent">
                                Read article
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== WHY KEEP BLOG ========== -->
    <section id="contact" class="px-5 pb-28 pt-4 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-surface p-8 ring-1 ring-line sm:p-12 lg:flex-row lg:items-center" data-scroll-reveal>
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">Why keep the blog here?</h2>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">When articles live under the main domain, they can reinforce the same topical authority as the service pages instead of splitting signals across another site.</p>
                </div>
                <a href="{{ route('aboutme') }}#contact"
                   class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-7 py-3.5 text-sm font-bold text-ink transition-colors hover:bg-white hover:text-black"
                   data-magnetic>
                    Contact me
                    <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer class="border-t border-line px-5 py-10 sm:px-10 lg:px-6">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 md:flex-row">
            <a href="{{ route('studio') }}" class="text-sm font-black tracking-tight text-paper">
                Faisal Imtiaz<span class="text-accent">.</span>
            </a>
            <div class="flex flex-wrap items-center gap-6 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">
                <a href="{{ route('studio') }}" class="transition-colors hover:text-paper">Home</a>
                <a href="{{ route('aboutme') }}" class="transition-colors hover:text-paper">About</a>
                <a href="{{ route('aboutme') }}#contact" class="transition-colors hover:text-paper">Contact</a>
            </div>
            <p class="text-[11px] text-smoke">© {{ date('Y') }} Faisal Imtiaz</p>
        </div>
    </footer>

</main>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var header = document.querySelector('.site-header');
    if (header) {
        var onHeaderScroll = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 24);
        };
        window.addEventListener('scroll', onHeaderScroll, { passive: true });
        onHeaderScroll();
    }

    var themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var html = document.documentElement;
            html.classList.toggle('dark');
            var isDark = html.classList.contains('dark');
            try { localStorage.setItem('studio-theme', isDark ? 'dark' : 'light'); } catch (e) {}
            var meta = document.getElementById('theme-color-meta');
            if (meta) meta.setAttribute('content', isDark ? '#090A0C' : '#EDEDE7');
        });
    }

    document.querySelectorAll('.magnetic-btn').forEach(function (btn) {
        if (!finePointer) return;
        btn.addEventListener('mousemove', function (e) {
            var rect = btn.getBoundingClientRect();
            gsap.to(btn, {
                x: (e.clientX - (rect.left + rect.width / 2)) * 0.15,
                y: (e.clientY - (rect.top + rect.height / 2)) * 0.15,
                duration: 0.4,
                ease: 'power2.out',
            });
        });
        btn.addEventListener('mouseleave', function () {
            gsap.to(btn, { x: 0, y: 0, duration: 0.6, ease: 'elastic.out(1, 0.4)' });
        });
    });

    if (!reducedMotion) {
        gsap.from('[data-reveal]', { y: 26, opacity: 0, duration: 0.9, stagger: 0.08, ease: 'power3.out', delay: 0.1 });
    } else {
        gsap.set('[data-reveal]', { opacity: 1 });
    }

    if (window.gsap && !reducedMotion) {
        gsap.utils.toArray('[data-scroll-reveal]').forEach(function (el) {
            gsap.from(el, { y: 24, opacity: 0, duration: 0.7, ease: 'power2.out' });
        });
    }
});
</script>
@endsection
