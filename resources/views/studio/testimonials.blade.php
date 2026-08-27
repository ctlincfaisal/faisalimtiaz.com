@extends('studio.app')

@section('title', 'Testimonials — Faisal Imtiaz')
@section('meta_description', 'Client reviews and feedback shared on Fiverr about working with Faisal Imtiaz.')

@php
    $schemaBase = 'https://faisalimtiaz.com';
    $websiteId = $schemaBase . '/#website';
    $testimonialsUrl = $schemaBase . '/testimonials';
@endphp

@push('structured_data')
    @include('components.structured-data', ['graph' => [
        [
            '@type' => 'CollectionPage',
            '@id' => $testimonialsUrl . '#collection',
            'url' => $testimonialsUrl,
            'name' => 'Testimonials — Faisal Imtiaz',
            'description' => 'Client reviews and feedback shared on Fiverr about working with Faisal Imtiaz.',
            'isPartOf' => ['@id' => $websiteId],
            'inLanguage' => 'en',
        ],
    ]])
@endpush

@php
$testimonials = [
    [
        'quote' => 'It was a pleasure working with Faisal. He understood the brief with minimal explanations other than the documentation provided. Delivery was 4 days ahead of schedule. I am looking forward to the next milestone!',
        'name' => 'stellataboola',
        'country' => 'South Africa',
        'date' => 'Aug 10, 2026',
        'avatar' => null,
    ],
    [
        'quote' => 'I have been regularly hiring Faysal for the last 2 years. Highly skilled professional whom I can trust with my confidential projects. Delivers above and beyond what is required. He is a Honest, truthful and Highly Skilled Individual. Already working on my next project.. thanks Faysal, I highly recommend your services to others...',
        'name' => 'Harry (logonto)',
        'country' => 'Australia',
        'date' => 'Jul 24, 2026',
        'avatar' => 'https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto,t_profile_small/v1/attachments/profile/photo/34a0b6625b8428aa6b0f1f0a8e0d1b04-1681913364511/d8f96290-c151-4235-b72c-7c4e3f40c020.jpg',
    ],
    [
        'quote' => 'This job we just made some smaller changes on some headers and layout. But I have been working with Faisal before and will keep working with him whenever I need this kind of expertise. We have had a blast working together and the result was even better than I expected it to be. So he made my ideas even better. Top notch and obviously 5/5 stars!',
        'name' => 'Eric Johansson',
        'country' => 'Sweden',
        'date' => 'Jun 24, 2026',
        'avatar' => null,
    ],
    [
        'quote' => 'Repeat customer, so this was my second coding task with Faisal on my new website build. To implement the reviews section on my webpage and code for receiving customer reviews, ironic I am writing a customer review :) Exceptional communication and implementation of what I required, and exceeding the brief by adding formatted reviews results, very pleased with the work done.',
        'name' => 'mikeywebcsp',
        'country' => 'UK',
        'date' => 'Aug 24, 2025',
        'avatar' => 'https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto,t_profile_small/v1/attachments/profile/photo/ac965fab2ea46afc1cd11276da1cbd30-1741946830167/cde7e171-769f-4a84-bea1-98a93426a050.jpg',
    ],
    [
        'quote' => 'Awesome work! Faisal fixed my push notification issue super fast and was very easy to work with.',
        'name' => 'jesse_terry1',
        'country' => 'USA',
        'date' => 'Aug 24, 2025',
        'avatar' => null,
    ],
    [
        'quote' => 'Exactly who I was looking for, fast and accurate. I am very impressed with the amount of knowledge he has. Expert in mobile app development in my opinion. Going to give him a lot of work from now. Glad that I found you Faisal.',
        'name' => 'bertha_hallie',
        'country' => 'UK',
        'date' => 'Aug 24, 2025',
        'avatar' => 'https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto,t_profile_small/v1/attachments/profile/photo/187312b11be2db9895c2442ab6cdf33e-1731068993181/52d4ab34-1033-4634-a6b8-d1635af9b9c6.jpeg',
    ],
    [
        'quote' => 'Wow. Amazing service. Faisal did exceptional work. Very quick, professional and exactly what I needed. Giving a tip as well. Very happy.',
        'name' => 'jacob_amy',
        'country' => 'USA',
        'date' => 'Aug 24, 2025',
        'avatar' => null,
    ],
    [
        'quote' => 'Very professional developer I ever meet on Fiverr. He is very cool gentle and know what is doing. The response is very fast. He respond even at midnight of his time. Keep it up bro. You will go far.',
        'name' => 'Abdul Rasheed',
        'country' => 'Pakistan',
        'date' => 'Aug 04, 2023',
        'avatar' => 'https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto,t_profile_small/v1/attachments/profile/photo/9720f4f678e744cacf474b7f0177b4c4-927878881639672047710/JPEG_20211216_172723_640842739563614055.jpg',
    ],
    [
        'quote' => 'Faysal offers unrivaled professionalism and commitment to excellence. I have been working with him for over a year now and I highly recommend his work.',
        'name' => 'Whizkidrepair',
        'date' => 'Feb 23, 2022',
        'avatar' => 'https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto,t_profile_small/v1/attachments/profile/photo/e2ef94b346cc74217181e6eb88d3f42b-1706164084989/58ae2472-4d16-4285-bbdb-88cf8f8b95ec.jpg',
    ],
    [
        'quote' => 'Great person to work with. He has much patience for fussy people like me. He did an excellent job and even offered extra. Highly recommended.',
        'name' => 'Olivera',
        'date' => 'Dec 14, 2022',
        'avatar' => 'https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto,t_profile_small/v1/attachments/profile/photo/d6f7b8e330c94b04e8177fbe302b0942-1705884720213/efbb9adc-9200-4430-a10d-c09a077cdb32.png',
    ],
    [
        'quote' => 'He went above and beyond to help me with my code. Will use his service again!',
        'name' => 'Nick Meyer',
        'date' => 'Jan 20, 2021',
        'avatar' => 'https://fiverr-res.cloudinary.com/image/upload/f_auto,q_auto,t_profile_original/v1/attachments/profile/photo/7b5a7ba33fa2544734e80b1a7625640a-1551019777553/42487649-9e72-4b38-9ab2-fdfc8dbbdd4f.jpeg',
    ],
    [
        'quote' => 'This was the last gig prior to publication of the app and @faisal delivered as expected. I received updates regularly through to the end. I am satisfied with the final delivery.',
        'name' => 'David McDermott',
        'date' => 'Aug 04, 2023',
        'avatar' => 'https://i.pinimg.com/736x/50/f2/26/50f2261e4e3a97bf5a5f58af5ec2f845.jpg',
    ],
    [
        'quote' => 'His consistency in providing quality service is the reason why I keep on getting him for my projects. His time and dedication to completing the tasks are remarkable.',
        'name' => 'Ryan Paul',
        'date' => 'Apr 18, 2021',
        'avatar' => 'https://i.pinimg.com/564x/9f/6f/0d/9f6f0df5b26ddab4258cc55d2f3529c1.jpg',
    ],
];

function initials($name)
{
    $parts = preg_split('/[\s_(]+/', $name);
    $chars = '';
    foreach ($parts as $part) {
        if ($part !== '' && strlen($chars) < 2) {
            $chars .= strtoupper(substr($part, 0, 1));
        }
    }
    return $chars !== '' ? $chars : 'FI';
}
@endphp

@section('head')
<style>
    .hero-heading {
        font-size: clamp(2.25rem, 0.9rem + 3.8vw, 3.9rem);
        line-height: 1.05;
        letter-spacing: -0.035em;
        font-weight: 900;
    }
</style>
@endsection

@section('content')

<!-- ========== NAVBAR ========== -->
<header class="site-header fixed inset-x-0 top-0 z-50">
    <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-5 sm:px-10" aria-label="Primary">
        <a href="{{ route('studio') }}#hero" class="inline-flex items-center text-sm font-black tracking-tight text-paper">
            <img src="{{ url('assets/sign.png') }}" alt="Faisal Imtiaz" width="2493" height="1098" decoding="async" class="h-12 w-auto sm:h-20">
        </a>

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
    </nav>
</header>

<main>
    <!-- ========== HERO ========== -->
    <section class="relative flex min-h-[60svh] items-center overflow-hidden px-5 pt-24 pb-16 sm:px-10 lg:px-6">
        <div class="mx-auto w-full max-w-7xl">
            <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.35em] text-accent" data-reveal>
                Client Feedback
            </p>
            <h1 class="hero-heading text-paper" data-reveal>
                Testimonials<span class="text-accent">.</span>
            </h1>
            <p class="mt-6 max-w-md text-base leading-relaxed text-smoke" data-reveal>
                Reviews shared by clients I've worked with on Fiverr. Want the full picture? Read them
                <a href="https://www.fiverr.com/faysal1994" target="_blank" rel="noopener" class="text-paper underline decoration-accent underline-offset-4 transition-colors hover:text-accent">on my Fiverr profile</a>.
            </p>
        </div>
    </section>

    <!-- ========== ALL TESTIMONIALS ========== -->
    <section class="px-5 pb-32 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <figure class="flex flex-col rounded-2xl bg-surface p-6 transition-shadow duration-500 hover:shadow-[0_20px_50px_-20px_rgba(0,0,0,0.4)]" data-scroll-reveal>
                        <div class="flex items-center justify-between">
                            <span class="text-accent" aria-hidden="true">★★★★★</span>
                            <a href="https://www.fiverr.com/faysal1994"
                               target="_blank" rel="noopener"
                               class="text-[11px] font-semibold uppercase tracking-[0.2em] text-smoke transition-colors hover:text-paper">
                                View on Fiverr ↗
                            </a>
                        </div>
                        <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-smoke">
                            "{{ $testimonial['quote'] }}"
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-3 border-t border-line pt-5">
                            @if (!empty($testimonial['avatar']))
                                <img src="{{ $testimonial['avatar'] }}"
                                     alt="Portrait of {{ $testimonial['name'] }}"
                                     width="44"
                                     height="44"
                                     loading="lazy"
                                     decoding="async"
                                     class="h-11 w-11 rounded-full object-cover ring-1 ring-line">
                            @else
                                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-accent/15 text-xs font-black text-accent ring-1 ring-line" aria-hidden="true">
                                    {{ initials($testimonial['name']) }}
                                </span>
                            @endif
                            <div class="leading-tight">
                                <p class="text-sm font-bold text-paper">{{ $testimonial['name'] }}</p>
                                <p class="mt-0.5 text-[11px] text-smoke">
                                    @if (!empty($testimonial['country'])){{ $testimonial['country'] }} · @endif{{ $testimonial['date'] }}
                                </p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="mt-14 flex justify-center" data-scroll-reveal>
                <a href="{{ route('studio') }}#testimonials"
                   class="magnetic-btn inline-flex items-center gap-2 rounded-full border border-paper/20 px-8 py-4 text-sm font-semibold text-paper transition-colors hover:border-accent hover:text-accent"
                   data-magnetic>
                    Back to home
                    <span aria-hidden="true">←</span>
                </a>
            </div>
        </div>
    </section>
</main>

@endsection

@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Navbar scroll state ---------- */
    var header = document.querySelector('.site-header');
    if (header) {
        var onHeaderScroll = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 24);
        };
        window.addEventListener('scroll', onHeaderScroll, { passive: true });
        onHeaderScroll();
    }

    /* ---------- Theme toggle ---------- */
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

    /* ---------- Magnetic buttons (subtle) ---------- */
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

    /* ---------- Subtle load entrance ---------- */
    if (!reducedMotion && window.gsap) {
        gsap.from('[data-reveal]', {
            y: 26,
            opacity: 0,
            duration: 0.9,
            stagger: 0.09,
            ease: 'power3.out',
            delay: 0.15,
        });
    }

    /* ---------- Gentle section reveals (kept very simple) ---------- */
    if (window.gsap && !reducedMotion) {
        gsap.utils.toArray('[data-scroll-reveal]').forEach(function (el) {
            gsap.from(el, {
                y: 24,
                opacity: 0,
                duration: 0.7,
                ease: 'power2.out',
            });
        });
    }

});
</script>

@endsection
