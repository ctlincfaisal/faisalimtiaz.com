@extends('studio.app')

@section('title', $post['title'] . ' | Faisal Imtiaz')
@section('meta_description', $post['meta_description'])

@php
    $breadcrumb = [
        ['label' => 'Home', 'href' => url('/')],
        ['label' => 'Blog', 'href' => route('blog')],
        ['label' => $post['title'], 'href' => $post['canonical']],
    ];

    $faqItems = $post['faqs'] ?? [];
    $structuredGraph = [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $post['canonical'].'#breadcrumb',
            'itemListElement' => collect($breadcrumb)->map(function ($crumb, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $crumb['label'],
                    'item' => $crumb['href'],
                ];
            })->values()->all(),
        ],
        [
            '@type' => 'BlogPosting',
            '@id' => $post['canonical'].'#article',
            'headline' => $post['title'],
            'description' => $post['meta_description'],
            'mainEntityOfPage' => $post['canonical'],
            'url' => $post['canonical'],
            'datePublished' => $post['published_at'],
            'dateModified' => $post['published_at'],
            'author' => [
                '@type' => 'Person',
                'name' => 'Faisal Imtiaz',
                'url' => url('/'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Faisal Imtiaz',
                'url' => url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => url('assets/logo.png'),
                ],
            ],
            'about' => $post['primaryKeyword'],
        ],
    ];

    if (count($faqItems)) {
        $structuredGraph[] = [
            '@type' => 'FAQPage',
            '@id' => $post['canonical'].'#faq',
            'mainEntity' => collect($faqItems)->map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['a'],
                    ],
                ];
            })->values()->all(),
        ];
    }
@endphp

@push('structured_data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => $structuredGraph,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
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
    .article-body {
        line-height: 1.75;
    }
</style>
@endsection

@section('content')

<header class="site-header fixed inset-x-0 top-0 z-50">
    <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-5 sm:px-10" aria-label="Primary">
        <a href="{{ route('studio') }}" class="text-sm font-black tracking-tight text-paper">
            Faisal Imtiaz<span class="text-accent">.</span>
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

    <!-- ========== ARTICLE HERO ========== -->
    <section class="px-5 pb-16 pt-32 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-4xl">
            <div class="mb-4 text-[11px] font-semibold uppercase tracking-[0.3em] text-smoke">
                <a href="{{ url('/') }}" class="transition-colors hover:text-paper">Home</a>
                <span class="mx-2 text-paper/30">/</span>
                <a href="{{ route('blog') }}" class="transition-colors hover:text-paper">Blog</a>
                <span class="mx-2 text-paper/30">/</span>
                <span class="text-paper/60">{{ $post['eyebrow'] }}</span>
            </div>

            <span class="text-[11px] font-semibold uppercase tracking-[0.5em] text-accent">{{ $post['eyebrow'] }}</span>
            <h1 class="hero-heading mt-4 text-paper" data-reveal>{{ $post['h1'] }}</h1>
            <p class="mt-6 max-w-3xl text-base leading-relaxed text-smoke" data-reveal>{{ $post['intro'] }}</p>

            <div class="mt-8 flex flex-wrap gap-2" data-reveal>
                <span class="tech-chip rounded-full px-4 py-2 text-xs font-semibold text-paper">{{ $post['reading_time'] }}</span>
                @foreach ($post['tags'] as $tag)
                    <span class="tech-chip rounded-full px-4 py-2 text-xs font-semibold text-paper/70">{{ $tag }}</span>
                @endforeach
            </div>

            <div class="mt-10 flex flex-wrap gap-4" data-reveal>
                <a href="{{ route('aboutme') }}#contact"
                   class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-7 py-3.5 text-sm font-bold text-ink transition-colors hover:bg-white"
                   data-magnetic>
                    Start a project
                    <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </a>
                @php($primaryRelated = $post['related'][0] ?? null)
                <a href="{{ $primaryRelated ? ($primaryRelated['href'] ?? route($primaryRelated['route'])) : route('blog') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-paper/20 px-7 py-3.5 text-sm font-semibold text-paper transition-colors hover:border-accent hover:text-accent">
                    Related service
                </a>
            </div>
        </div>
    </section>

    <!-- ========== ARTICLE BODY ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-4xl">
            <div class="space-y-8">
                @foreach ($post['sections'] as $section)
                    <div class="rounded-2xl bg-surface p-8 ring-1 ring-line sm:p-10" data-scroll-reveal>
                        <h2 class="text-2xl font-black tracking-tight text-paper">{{ $section['title'] }}</h2>
                        <p class="article-body mt-4 text-sm leading-relaxed text-smoke">{{ $section['text'] }}</p>
                        @if (!empty($section['bullets']))
                            <ul class="mt-5 space-y-3">
                                @foreach ($section['bullets'] as $bullet)
                                    <li class="flex items-start gap-3 text-sm leading-relaxed text-smoke">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                        <span>{{ $bullet }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== WHAT TO DO NEXT ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-4xl">
            <div class="rounded-3xl bg-surface p-8 ring-1 ring-line sm:p-12" data-scroll-reveal>
                <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">What to do next</h2>
                <p class="mt-4 max-w-2xl text-sm leading-relaxed text-smoke">Use this article as a planning guide, then move into the service page that matches your current need. If you already know the project is ready, the contact form is the fastest next step.</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('aboutme') }}#contact"
                       class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-7 py-3.5 text-sm font-bold text-ink transition-colors hover:bg-white"
                       data-magnetic>
                        Contact me
                        <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('blog') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-paper/20 px-7 py-3.5 text-sm font-semibold text-paper transition-colors hover:border-accent hover:text-accent">
                        Back to blog
                    </a>
                </div>
            </div>
        </div>
    </section>

    @if (count($faqItems))
    <!-- ========== FAQ ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-4xl">
            <div class="mb-10" data-scroll-reveal>
                <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">FAQ</h2>
                <p class="mt-2 text-sm text-smoke">Short answers to common questions.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($faqItems as $faq)
                    <div class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                        <h3 class="text-base font-black tracking-tight text-paper">{{ $faq['q'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-smoke">{{ $faq['a'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ========== RELATED LINKS ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-4xl">
            <div class="mb-10" data-scroll-reveal>
                <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">Related links</h2>
                <p class="mt-2 text-sm text-smoke">Keep moving toward the page that fits your next step.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($post['related'] as $link)
                    <a href="{{ $link['href'] ?? route($link['route']) }}"
                       class="group flex items-center justify-between rounded-2xl bg-surface p-6 ring-1 ring-line transition-colors duration-300 hover:border-accent hover:text-accent">
                        <span class="text-sm font-semibold text-paper">{{ $link['label'] }}</span>
                        <span class="text-paper/40 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:text-accent" aria-hidden="true">→</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== CTA ========== -->
    <section id="contact" class="px-5 pb-28 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-4xl">
            <div class="rounded-3xl bg-paper p-8 sm:p-12" data-scroll-reveal>
                <div class="flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
                    <div class="max-w-2xl">
                        <h2 class="text-2xl font-black tracking-tight text-ink sm:text-3xl">Want help applying this to your project?</h2>
                        <p class="mt-3 text-sm leading-relaxed text-ink/70">I can help turn the ideas in this article into a real homepage, service page, or product plan.</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-3">
                        <a href="{{ route('aboutme') }}#contact"
                           class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-ink px-7 py-3.5 text-sm font-bold text-paper transition-colors hover:bg-surface"
                           data-magnetic>
                            Contact me
                            <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('services.website-development') }}"
                           class="inline-flex items-center gap-2 rounded-full border border-ink/20 px-7 py-3.5 text-sm font-semibold text-ink transition-colors hover:border-ink">
                            Services
                        </a>
                    </div>
                </div>
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
                <a href="{{ route('blog') }}" class="transition-colors hover:text-paper">Blog</a>
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
