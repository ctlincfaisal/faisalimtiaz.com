@extends('studio.app')

@section('title', $page['title'])
@section('meta_description', $page['meta_description'])

@php
    $schemaBase = 'https://faisalimtiaz.com';
    $serviceCanonical = $schemaBase . '/' . ltrim(parse_url($page['canonical'], PHP_URL_PATH) ?: '', '/');
@endphp

@push('structured_data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $serviceCanonical.'#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $schemaBase,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $page['eyebrow'],
                    'item' => $serviceCanonical,
                ],
            ],
        ],
        [
            '@type' => 'Service',
            '@id' => $serviceCanonical.'#service',
            'name' => $page['h1'],
            'serviceType' => $page['primaryKeyword'] ?? $page['eyebrow'],
            'description' => $page['meta_description'],
            'provider' => [
                '@type' => 'Person',
                '@id' => $schemaBase.'/#person',
                'name' => 'Faisal Imtiaz',
                'url' => $schemaBase,
            ],
            'areaServed' => [
                '@type' => 'Place',
                'name' => 'Worldwide',
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $serviceCanonical.'#faq',
            'mainEntity' => collect($page['faqs'])->map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['a'],
                    ],
                ];
            })->values()->all(),
        ],
    ],
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
</style>
@endsection

@section('content')

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

    <!-- ========== SERVICE HERO ========== -->
    <section class="px-5 pb-16 pt-32 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-4 text-[11px] font-semibold uppercase tracking-[0.3em] text-smoke">
                <a href="{{ url('/') }}" class="transition-colors hover:text-paper">Home</a>
                <span class="mx-2 text-paper/30">/</span>
                <span class="text-paper/60">{{ $page['eyebrow'] }}</span>
            </div>

            <div class="grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-8" data-reveal>
                    <span class="text-[11px] font-semibold uppercase tracking-[0.5em] text-accent">{{ $page['eyebrow'] }}</span>
                    <h1 class="hero-heading mt-4 text-paper">{{ $page['h1'] }}</h1>
                    <p class="mt-6 max-w-2xl text-base leading-relaxed text-smoke">{{ $page['intro'] }}</p>

                    <div class="mt-8 flex flex-wrap gap-2">
                        @foreach ($page['supporting_keywords'] as $keyword)
                            <span class="tech-chip rounded-full px-4 py-2 text-xs font-semibold text-paper/70">{{ $keyword }}</span>
                        @endforeach
                    </div>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        <a href="{{ route('aboutme') }}#contact"
                           class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-7 py-3.5 text-sm font-bold text-ink transition-colors hover:bg-white hover:text-black"
                           data-magnetic>
                            Start a project
                            <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                        </a>
                        <a href="mailto:ctlinc.faisal@gmail.com?subject={{ rawurlencode($page['h1'] . ' enquiry') }}"
                           class="inline-flex items-center gap-2 rounded-full border border-paper/20 px-7 py-3.5 text-sm font-semibold text-paper transition-colors hover:border-accent hover:text-accent">
                            Email me
                        </a>
                        <a href="{{ route('studio') }}" class="nav-underline text-sm font-semibold text-paper/80 transition-colors hover:text-paper">Back to homepage</a>
                    </div>
                    <p class="mt-4 text-xs text-smoke">Prefer email? I usually reply within 3-4 business hours.</p>
                </div>

                <div class="lg:col-span-4" data-reveal>
                    <div class="rounded-2xl bg-surface p-8 ring-1 ring-line">
                        <h2 class="text-xl font-black tracking-tight text-paper">Who this is for</h2>
                        <p class="mt-3 text-sm leading-relaxed text-smoke">{{ $page['audience'] }}</p>
                        <p class="mt-6 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Main outcome</p>
                        <p class="mt-2 text-sm leading-relaxed text-smoke">{{ $page['outcome'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== QUICK ANSWERS ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 text-center" data-scroll-reveal>
                <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">Quick answers</h2>
                <p class="mt-2 text-sm text-smoke">Short answers to the questions clients ask before they book a project.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-scroll-reveal>
                <div class="rounded-2xl bg-surface p-6 ring-1 ring-line">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-accent">Services offered</p>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">{{ $page['eyebrow'] }} built around your project goals and launch needs.</p>
                </div>
                <div class="rounded-2xl bg-surface p-6 ring-1 ring-line">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-accent">Who it is for</p>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">{{ $page['audience'] }}</p>
                </div>
                <div class="rounded-2xl bg-surface p-6 ring-1 ring-line">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-accent">Typical timeline</p>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">{{ $page['timeline'] ?? 'Timeline depends on the scope and complexity of the project.' }}</p>
                </div>
                <div class="rounded-2xl bg-surface p-6 ring-1 ring-line">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-accent">Pricing and support</p>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">{{ $page['pricing'] ?? 'Pricing is based on scope and complexity.' }} {{ $page['support'] ?? 'Support after launch is available when needed.' }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== PROCESS ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 text-center" data-scroll-reveal>
                <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">Process</h2>
                <p class="mt-2 text-sm text-smoke">How I take the work from idea to launch.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-scroll-reveal>
                @foreach ($page['process'] as $step)
                    <div class="rounded-2xl bg-surface p-6 ring-1 ring-line">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-accent/15 text-sm font-black text-accent">{{ $loop->iteration }}</span>
                        <h3 class="mt-4 text-base font-black tracking-tight text-paper">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-smoke">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== BENEFITS ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 text-center" data-scroll-reveal>
                <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">Benefits</h2>
                <p class="mt-2 text-sm text-smoke">Why this service helps your business move faster.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-scroll-reveal>
                @foreach ($page['benefits'] as $benefit)
                    <div class="flex items-start gap-3 rounded-2xl bg-surface p-6 ring-1 ring-line">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                        <p class="text-sm leading-relaxed text-smoke">{{ $benefit }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== FAQ ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 text-center" data-scroll-reveal>
                <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">FAQ</h2>
                <p class="mt-2 text-sm text-smoke">Quick answers to common questions.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-scroll-reveal>
                @foreach ($page['faqs'] as $faq)
                    <div class="rounded-2xl bg-surface p-6 ring-1 ring-line">
                        <h3 class="text-base font-black tracking-tight text-paper">{{ $faq['q'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-smoke">{{ $faq['a'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== RELATED LINKS ========== -->
    <section class="px-5 pb-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-10 text-center" data-scroll-reveal>
                <h2 class="text-2xl font-black tracking-tight text-paper sm:text-3xl">Related links</h2>
                <p class="mt-2 text-sm text-smoke">Explore related services or head back to the homepage.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-scroll-reveal>
                @foreach ($page['related'] as $link)
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
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-paper p-8 sm:p-12 lg:flex-row lg:items-center" data-scroll-reveal>
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-black tracking-tight text-ink sm:text-3xl">Ready to talk about your project?</h2>
                    <p class="mt-3 text-sm leading-relaxed text-ink/70">Send me a message and I'll help you decide the right next step.</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-3">
                    <a href="{{ route('aboutme') }}#contact"
                       class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-ink px-7 py-3.5 text-sm font-bold text-paper transition-colors hover:bg-surface"
                       data-magnetic>
                        Contact me
                        <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                    </a>
                    <a href="mailto:ctlinc.faisal@gmail.com?subject={{ rawurlencode($page['h1'] . ' enquiry') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-ink/20 px-7 py-3.5 text-sm font-semibold text-ink transition-colors hover:border-ink">
                        Email me
                    </a>
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
