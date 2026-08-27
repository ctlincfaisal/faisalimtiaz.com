@extends('studio.app')

@section('title', 'About Faisal Imtiaz | Laravel & Mobile Application Developer')
@section('meta_description', 'Learn more about Faisal Imtiaz, a Laravel and mobile application developer experienced in React Native, Ionic, Firebase, MySQL, and MongoDB.')

@php
    $schemaBase = 'https://faisalimtiaz.com';
    $personId = $schemaBase . '/#person';
    $websiteId = $schemaBase . '/#website';
    $aboutUrl = $schemaBase . '/aboutme';
@endphp

@push('structured_data')
    @include('components.structured-data', ['graph' => [
        [
            '@type' => 'ProfilePage',
            '@id' => $aboutUrl . '#profile',
            'url' => $aboutUrl,
            'name' => 'About Faisal Imtiaz',
            'isPartOf' => ['@id' => $websiteId],
            'mainEntity' => ['@id' => $personId],
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
    .showcase-glow {
        background: radial-gradient(ellipse at 50% 45%, rgba(200, 240, 62, 0.10), rgba(200, 240, 62, 0.02) 45%, transparent 65%);
    }
    .tech-chip {
        border: 1px solid rgba(var(--line), 0.12);
        background: rgb(var(--surface));
    }
    .timeline-line {
        background: linear-gradient(to bottom, rgba(var(--line), 0.35), rgba(var(--line), 0.05));
    }
</style>
@endsection

@section('content')

@php
    $aboutFaqs = [
        ['q' => 'What services do you offer?', 'a' => 'I build websites, Laravel applications, React Native mobile apps, and SEO-friendly pages.'],
        ['q' => 'Who are your services for?', 'a' => 'They are for startups and small businesses that want a practical partner for web and mobile work.'],
        ['q' => 'How long have you been doing this?', 'a' => 'I have been working with clients since 2013 and leading TeckCreators since 2018.'],
        ['q' => 'How do you price projects?', 'a' => 'I price based on scope, features, and complexity so the quote fits the actual work.'],
        ['q' => 'Do you support work after launch?', 'a' => 'Yes. I can help with fixes, updates, and maintenance after the project goes live.'],
        ['q' => 'What is your process?', 'a' => 'I usually start with discovery, then design, build, launch, and support.'],
    ];

    $education = [
        [
            'degree' => 'M. Phil. degree of information technology (MSIT)',
            'school' => 'Superior University Lahore',
            'years' => '2019-2021',
            'body' => 'Completed my M. Phil. degree in the year 2021 with major of computer science and information technology. Here I read in detail about the latest technologies, growth of the digital world, major programming languages with hands on practices and much more.',
            'tech' => 'Learnt various important programming languages like C, Python, MATLAB, Artificial Intelligence, Image processing, Data analysis, Crypto currency, Blockchain, Data mining, Web3 and Javascript. My research work and thesis was about Blockchain technology and the effects of blockchain in the supply chain industry.',
        ],
        [
            'degree' => 'Masters of information technology (MIT)',
            'school' => 'Bahauddin Zakariya University Multan',
            'years' => '2011-2013',
            'body' => 'Completed my Masters degree in the year 2013 with major of computer science and information technology. Here I read in detail about the latest technologies, growth of the digital world, major programming languages with hands on practices and much more.',
            'tech' => 'Learnt various important programming languages like C, C#, JAVA, HTML and Algorithms, Data Sciences and CSS.',
        ],
        [
            'degree' => 'BSC',
            'school' => 'Gov. Alamdar College Multan',
            'years' => '2009-2011',
            'body' => 'Completed my Bachelor degree in the year 2011 with major of science and mathematics. Read majorly about maths, physics and statistics.',
            'tech' => null,
        ],
        [
            'degree' => 'FSC',
            'school' => 'Central College Multan',
            'years' => '2007-2009',
            'body' => 'Completed my Intermediate degree in the year 2009 with major of computer sciences. Read majorly about computers, how they are built, how to use them and some basic programming languages there like C# and C.',
            'tech' => null,
        ],
        [
            'degree' => 'Matriculation',
            'school' => 'Noukhez Public High School Multan',
            'years' => '2006-2007',
            'body' => 'Completed my matriculation degree from Noukhez Public High School Multan, Pakistan in the year 2007 with major subject of computer sciences. Read majorly about computers, how they are built, how to use them and some basic programming languages there like GW-Basic.',
            'tech' => null,
        ],
    ];

    $experience = [
        [
            'role' => 'CEO and Founder at Phototrail',
            'years' => '2025 - present',
            'body' => 'Leading Phototrail as CEO, a shared event-album app that lets everyone at a wedding, party, or gathering contribute photos and videos into one private album. Overseeing the product from strategy to launch, focused on building a platform people love to use every day.',
            'tech' => 'React Native, Supabase, Expo, and more',
        ],
        [
            'role' => 'MERN Stack Trainer at Erozgaar Multan',
            'years' => '2024 - 2025',
            'body' => 'Trained aspiring developers in full MERN Stack development at Erozgaar Multan. Led hands-on sessions covering MongoDB, Express, React, and Node.js, with a focus on building complete, production-ready web applications and preparing students for real client work.',
            'tech' => 'MongoDB, Express, React, Node.js, and more',
        ],
        [
            'role' => 'Technical Trainer at National Freelance Training Program',
            'years' => '2021 - 2024',
            'body' => 'Worked as a technical trainer at the National Freelance Training Program, mainly teaching web development with the latest technologies. Guided students through modern, industry-relevant skills so they could build real websites and launch freelance careers with confidence.',
            'tech' => 'HTML, CSS, Javascript, php, Laravel, freelancing and more',
        ],
        [
            'role' => 'CEO and owner of TeckCreators',
            'years' => 'From 2018 - present',
            'body' => 'Since 2018 I have led TeckCreators as a delivery-focused team for small to mid-size projects. The work usually starts with scope and design decisions, then moves through development, deployment, and maintenance. We have completed 50+ projects for clients around the world, including mobile applications, websites, LMS platforms, POS systems, blogs, e-commerce stores, tools, and AI extensions.',
            'tech' => 'HTML, CSS, PHP, Javascript, Laravel, IONIC, React Native, Mysql, MongoDB, Firebase, Nodejs',
        ],
        [
            'role' => 'Fiverr',
            'years' => 'From 2013 - present',
            'body' => 'Completing 50+ projects around the world. I have worked on Fiverr since 2013 as a website developer and mobile application developer, with a focus on clear communication and dependable delivery.',
            'tech' => 'HTML, CSS, PHP, Javascript, Laravel, IONIC, React Native, Mysql, MongoDB, Firebase, Nodejs',
        ],
        [
            'role' => 'CTL Cables Ltd.',
            'years' => 'From 2014 - 2017',
            'body' => 'Worked in a USA based networking company as a senior website developer and virtual assistant, supporting web projects and day-to-day client operations.',
            'tech' => 'HTML, CSS, PHP, Javascript, Mysql, Nodejs, firebase, Wordpress',
        ],
        [
            'role' => 'Z-Index',
            'years' => 'From 2014 - 2015',
            'body' => 'Worked in a Multan based software house as a senior PHP and WordPress developer, mostly on existing WordPress sites, while helping lead daily client work.',
            'tech' => 'HTML, CSS, PHP, Javascript, Mysql, Wordpress',
        ],
        [
            'role' => 'Virtual Base',
            'years' => 'From 2013 - 2014',
            'body' => 'Worked in a Multan based software house as a senior PHP and WordPress developer, contributing to web projects for clients around the world.',
            'tech' => 'HTML, CSS, PHP, Javascript, Mysql, Wordpress',
        ],
    ];

    $skills = [
        ['name' => 'React JS / React Native', 'icon' => 'assets/brands/react.svg', 'level' => 100, 'href' => route('services.react-native-development')],
        ['name' => 'Ionic Framework', 'icon' => 'assets/brands/ionic.svg', 'level' => 100, 'href' => route('services.mobile-app-development')],
        ['name' => 'Laravel', 'icon' => 'assets/brands/laravel.svg', 'level' => 85, 'href' => route('services.laravel-development')],
        ['name' => 'PHP', 'icon' => 'assets/brands/php.svg', 'level' => 90, 'href' => route('services.laravel-development')],
        ['name' => 'HTML5', 'icon' => 'assets/brands/html5.svg', 'level' => 100, 'href' => route('services.website-development')],
        ['name' => 'CSS3', 'icon' => 'assets/brands/css3.svg', 'level' => 100, 'href' => route('services.website-development')],
        ['name' => 'MySQL', 'icon' => 'assets/brands/mysql.svg', 'level' => 70, 'href' => route('services.laravel-development')],
        ['name' => 'Firebase', 'icon' => 'assets/brands/firebase.svg', 'level' => 80, 'href' => route('services.mobile-app-development')],
        ['name' => 'Javascript', 'icon' => 'assets/brands/javascript.svg', 'level' => 100, 'href' => route('services.website-development')],
    ];

    $socials = [
        ['label' => 'YouTube', 'href' => 'https://www.youtube.com/@iamfaisalimtiaz'],
        ['label' => 'LinkedIn', 'href' => 'https://www.linkedin.com/in/faysalimtiaz/'],
        ['label' => 'Instagram', 'href' => 'https://www.instagram.com/iamfaysalimtiaz/'],
        ['label' => 'Facebook', 'href' => 'https://www.facebook.com/iamfaisalimtiaz/'],
        ['label' => 'Behance', 'href' => 'https://www.behance.net/ficreations'],
        ['label' => 'GitHub', 'href' => 'https://github.com/ctlincfaisal'],
    ];
@endphp

<header class="site-header fixed inset-x-0 top-0 z-50">
    <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-5 sm:px-10" aria-label="Primary">
        <a href="{{ route('studio') }}" class="inline-flex items-center text-sm font-black tracking-tight text-paper">
            <img src="{{ url('assets/sign.png') }}" alt="Faisal Imtiaz" width="2493" height="1098" decoding="async" class="h-12 w-auto sm:h-20">
        </a>

        <ul class="hidden items-center gap-9 md:flex">
            <li><a href="{{ route('studio') }}" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">Home</a></li>
            <li><a href="#about" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">About</a></li>
            <li><a href="#education" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">Education</a></li>
            <li><a href="#experience" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">Experience</a></li>
            <li><a href="#skills" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">Skills</a></li>
            <li><a href="#contact" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">Contact</a></li>
            <li><a href="#faq" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">FAQ</a></li>
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

    <!-- ========== ABOUT / INTRO ========== -->
    <section id="about" class="px-5 pb-24 pt-32 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid items-center gap-14 lg:grid-cols-12">
                <!-- Portrait -->
                <div class="lg:col-span-5" data-reveal>
                    <div class="relative mx-auto max-w-sm">
                        <div class="showcase-glow pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>
                        <div class="overflow-hidden rounded-2xl bg-surface">
                            <img src="{{ url('assets/faisalimtiaz/faisalimtiaz.webp') }}"
                                 alt="Portrait of Faisal Imtiaz"
                                 width="768"
                                 height="682"
                                 decoding="async"
                                 class="aspect-[4/5] w-full object-cover">
                        </div>
                        <span class="tech-tag absolute -bottom-4 left-6 rounded-full px-4 py-2 text-xs font-semibold text-paper">
                            Faisal Imtiaz
                        </span>
                    </div>
                </div>

                <!-- Bio -->
                <div class="lg:col-span-7" data-reveal>
                    <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">About Me</p>
                    <h1 class="hero-heading text-paper">
                        Faisal <span class="text-outline">Imtiaz</span>
                    </h1>
                    <p class="mt-2 text-sm font-semibold uppercase tracking-[0.2em] text-accent">Laravel &amp; Mobile Application Developer</p>

                    <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-sm text-smoke">
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            Pakistan, PK
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                            ctlinc.faisal@gmail.com
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            +923006770770
                        </span>
                    </div>

                    <p class="mt-8 max-w-xl text-base leading-relaxed text-smoke">
                        I have been building web and mobile products since 2013. Over the years, my work has moved from freelance delivery into leading client projects through TeckCreators, where I help startups and small businesses launch <a href="{{ route('services.website-development') }}" class="text-accent underline decoration-accent/40 underline-offset-4 transition-colors hover:text-paper">websites</a>, <a href="{{ route('services.mobile-app-development') }}" class="text-accent underline decoration-accent/40 underline-offset-4 transition-colors hover:text-paper">apps</a>, and technical systems that are easier to maintain after handoff.
                    </p>
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-smoke">
                        My usual scope includes discovery, interface design, <a href="{{ route('services.laravel-development') }}" class="text-accent underline decoration-accent/40 underline-offset-4 transition-colors hover:text-paper">Laravel backend work</a>, <a href="{{ route('services.react-native-development') }}" class="text-accent underline decoration-accent/40 underline-offset-4 transition-colors hover:text-paper">React Native app development</a>, API integration, launch support, and maintenance. I also work on <a href="{{ route('services.seo-services') }}" class="text-accent underline decoration-accent/40 underline-offset-4 transition-colors hover:text-paper">SEO-friendly site structure</a> when a project needs better search clarity from day one.
                    </p>

                    <div class="mt-8 rounded-2xl bg-surface p-6 ring-1 ring-line">
                        <p class="mb-3 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Focus areas</p>
                        <ul class="grid gap-2.5 sm:grid-cols-2">
                            @foreach (['Freelancing since 2013', 'Udemy instructor since 2015', 'Technical trainer for NFTP', 'YouTube content since 2019'] as $focus)
                                <li class="flex items-center gap-2 text-sm text-paper/80">
                                    <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                                    {{ $focus }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @foreach ($socials as $social)
                            <a href="{{ $social['href'] }}" target="_blank" rel="noopener"
                               class="tech-chip rounded-full px-4 py-2 text-xs font-semibold text-paper/80 transition-colors hover:border-accent hover:text-accent">
                                {{ $social['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== EDUCATION ========== -->
    <section id="education" class="px-5 pb-28 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-14" data-scroll-reveal>
                <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">Education</p>
                <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">Educational <span class="text-outline">Background</span></h2>
            </div>

            <div class="relative border-l border-line pl-8 sm:pl-12" data-scroll-reveal>
                @foreach ($education as $item)
                    <div class="relative mb-12 last:mb-0">
                        <span class="absolute -left-[41px] top-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-accent bg-ink sm:-left-[57px]" aria-hidden="true"></span>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-accent">{{ $item['years'] }}</span>
                        <h3 class="mt-1 text-xl font-black tracking-tight text-paper">{{ $item['degree'] }}</h3>
                        <p class="mt-1 text-sm font-semibold text-paper/70">{{ $item['school'] }}</p>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-smoke">{{ $item['body'] }}</p>
                        @if (!empty($item['tech']))
                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-smoke"><span class="font-semibold text-paper">Technologies</span> - {{ $item['tech'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== EXPERIENCE ========== -->
    <section id="experience" class="px-5 pb-28 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-14" data-scroll-reveal>
                <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">Experience</p>
                <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">Professional Work <span class="text-outline">Experience</span></h2>
            </div>

            <div class="relative border-l border-line pl-8 sm:pl-12" data-scroll-reveal>
                @foreach ($experience as $item)
                    <div class="relative mb-12 last:mb-0">
                        <span class="absolute -left-[41px] top-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-accent bg-ink sm:-left-[57px]" aria-hidden="true"></span>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-accent">{{ $item['years'] }}</span>
                        <h3 class="mt-1 text-xl font-black tracking-tight text-paper">{{ $item['role'] }}</h3>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-smoke">{{ $item['body'] }}</p>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-smoke"><span class="font-semibold text-paper">Technologies Used</span> - {{ $item['tech'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== SKILLS ========== -->
    <section id="skills" class="px-5 pb-28 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-14" data-scroll-reveal>
                <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">Skills</p>
                <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">Professional <span class="text-outline">Technical Skills</span></h2>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3" data-scroll-reveal>
                @foreach ($skills as $skill)
                    <a href="{{ $skill['href'] }}"
                       class="group rounded-2xl bg-surface p-6 transition-colors duration-500 hover:shadow-[0_20px_50px_-20px_rgba(0,0,0,0.4)]">
                        <div class="flex items-center gap-4">
                            <img src="{{ url($skill['icon']) }}" alt="{{ $skill['name'] }} logo" loading="lazy"
                                 decoding="async" width="44" height="44"
                                 class="h-11 w-11 rounded-full bg-paper/5 p-1.5 ring-1 ring-line">
                            <div class="flex-1">
                                <h3 class="text-sm font-black tracking-tight text-paper">{{ $skill['name'] }}</h3>
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-ink ring-1 ring-line">
                                        <div class="h-full rounded-full bg-accent" style="width: {{ $skill['level'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-accent">{{ $skill['level'] }}%</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== CONTACT ========== -->
    <section id="contact" class="px-5 pb-28 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-16 lg:grid-cols-12">
                <!-- Left: info -->
                <div class="lg:col-span-5" data-scroll-reveal>
                    <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">Contact</p>
                    <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">Get In Touch<span class="text-accent">.</span></h2>
                    <p class="mt-6 max-w-md text-sm leading-relaxed text-smoke">
                        I'm available for websites, mobile apps, SEO-led builds, and project support.
                    </p>

                    <div class="mt-10 space-y-5">
                        <a href="mailto:ctlinc.faisal@gmail.com?subject=Project%20enquiry%20from%20about%20page"
                           class="group flex items-center gap-4" data-magnetic>
                            <span class="flex h-12 w-12 items-center justify-center rounded-full border border-paper/20 text-paper transition-colors duration-300 group-hover:border-accent group-hover:text-accent" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                            </span>
                            <span class="leading-tight">
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Email</span>
                                <span class="mt-0.5 block text-sm font-bold text-paper">ctlinc.faisal@gmail.com</span>
                            </span>
                        </a>
                        <a href="tel:+923006770770" class="group flex items-center gap-4" data-magnetic>
                            <span class="flex h-12 w-12 items-center justify-center rounded-full border border-paper/20 text-paper transition-colors duration-300 group-hover:border-accent group-hover:text-accent" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            </span>
                            <span class="leading-tight">
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Phone · WhatsApp</span>
                                <span class="mt-0.5 block text-sm font-bold text-paper">+92 300 6770770</span>
                            </span>
                        </a>
                    </div>

                    <div class="mt-10 rounded-2xl bg-surface p-6 ring-1 ring-line">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Work style</p>
                        <p class="text-sm leading-relaxed text-smoke">
                            Clear scope, regular updates, and a handoff that includes the details needed for ongoing support.
                        </p>
                    </div>
                </div>

                <!-- Right: form -->
                <div class="lg:col-span-7" data-scroll-reveal>
                    <form id="studio_contact_form" method="POST" action="{{ url('contactus') }}"
                          class="rounded-2xl bg-surface p-8 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] sm:p-10">
                        @csrf

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="firstname" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">Name</label>
                                <input type="text" id="firstname" name="firstname" placeholder="Your name" autocomplete="name" required
                                       class="w-full rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none transition-colors focus:border-accent">
                            </div>
                            <div>
                                <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">Email address</label>
                                <input type="email" id="email" name="email" placeholder="email@site.com" autocomplete="email" required
                                       class="w-full rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none transition-colors focus:border-accent">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="budget" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">
                                Project type <span class="normal-case text-smoke/70">(optional)</span>
                            </label>
                            <select id="budget" name="budget" aria-label="Choose your project type"
                                    class="w-full rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper outline-none transition-colors focus:border-accent">
                                <option value="" class="bg-ink">Choose a project type</option>
                                <option value="Website development" class="bg-ink">Website development</option>
                                <option value="Mobile app development" class="bg-ink">Mobile app development</option>
                                <option value="SEO services" class="bg-ink">SEO services</option>
                                <option value="Not sure yet" class="bg-ink">Not sure yet</option>
                            </select>
                        </div>

                        <div class="mt-6">
                            <label for="details" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">Project details</label>
                            <textarea id="details" name="details" rows="5" required
                                      placeholder="Tell me what you need, your timeline, and the best way to help"
                                      class="w-full resize-none rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none transition-colors focus:border-accent"></textarea>
                        </div>

                        <div class="mt-8">
                            <button type="submit"
                                    class="magnetic-btn group inline-flex w-full items-center justify-center gap-2 rounded-full bg-paper px-8 py-4 text-sm font-bold text-ink transition-colors hover:bg-white hover:text-black"
                                    data-magnetic>
                                Send project details
                                <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                            </button>
                            <div id="studio_form_success" class="mt-4 hidden rounded-lg bg-accent/15 px-4 py-3 text-center text-sm font-medium text-accent">
                                Thanks. I've received your message and will reply soon.
                            </div>
                            <p class="mt-4 text-center text-xs text-smoke">I'll get back to you in 3-4 business hours.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== FAQ ========== -->
    <section id="faq" class="px-5 pb-28 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4" data-scroll-reveal>
                    <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">FAQ</p>
                    <h2 class="text-3xl font-black leading-tight tracking-tight text-paper sm:text-4xl">
                        A few short answers about experience, process, pricing, and support.
                    </h2>
                </div>

                <div class="lg:col-span-8">
                    <dl class="divide-y divide-line">
                        @foreach ($aboutFaqs as $faq)
                            <div class="group py-6" data-scroll-reveal>
                                <dt class="flex items-center justify-between gap-4">
                                    <h3 class="text-lg font-bold tracking-tight text-paper">{{ $faq['q'] }}</h3>
                                    <span class="shrink-0 text-sm font-semibold text-smoke" aria-hidden="true">+</span>
                                </dt>
                                <dd class="mt-3 max-w-2xl text-sm leading-relaxed text-smoke">{{ $faq['a'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== FOOTER ========== -->

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

    var contactForm = document.getElementById('studio_contact_form');
    if (contactForm) {
        var markInvalid = function (el, invalid) {
            if (!el) return;
            el.classList.toggle('border-red-500', invalid);
        };
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var firstname = document.getElementById('firstname');
            var email = document.getElementById('email');
            var details = document.getElementById('details');
            var success = document.getElementById('studio_form_success');
            var submitBtn = contactForm.querySelector('button[type="submit"]');
            markInvalid(firstname, false);
            markInvalid(email, false);
            markInvalid(details, false);
            if (success) success.classList.add('hidden');
            if (firstname.value.trim() === '' || email.value.trim() === '' || details.value.trim() === '') {
                markInvalid(firstname, firstname.value.trim() === '');
                markInvalid(email, email.value.trim() === '');
                markInvalid(details, details.value.trim() === '');
                return;
            }
            var formData = new FormData(contactForm);
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            fetch(contactForm.getAttribute('action'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            }).then(function (res) {
                return res.json().catch(function () { return {}; });
            }).then(function (data) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send project details';
                if (data && data.msg === 'success') {
                    contactForm.reset();
                    if (success) success.classList.remove('hidden');
                }
            }).catch(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send project details';
            });
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
