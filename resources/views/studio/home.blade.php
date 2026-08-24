@extends('studio.app')

@section('title', 'Faisal Imtiaz — Website & Mobile App Developer')
@section('meta_description', 'Faisal Imtiaz is a Laravel and React Native developer helping startups and small businesses launch websites and mobile apps since 2013.')

@section('head')
<style>
    .hero-heading {
        font-size: clamp(2.25rem, 0.9rem + 3.8vw, 3.9rem);
        line-height: 1.05;
        letter-spacing: -0.035em;
        font-weight: 900;
    }
    .showcase {
        perspective: 1800px;
    }
    .showcase-glow {
        background: radial-gradient(ellipse at 50% 45%, rgba(200, 240, 62, 0.08), rgba(200, 240, 62, 0.02) 45%, transparent 65%);
    }
    .tech-tag {
        border: 1px solid rgba(var(--line), 0.10);
        background: rgb(var(--ink) / 0.6);
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
    }
</style>
@endsection

@section('content')

@php
    $navLinks = [
        ['label' => 'Work', 'href' => '#work'],
        ['label' => 'Services', 'href' => '#services'],
        ['label' => 'About', 'href' => '#about'],
        ['label' => 'Blog', 'href' => route('blog')],
    ];

    $skills = [
        'React Native', 'IONIC', 'NodeJS', 'Laravel', 'PHP', 'Angular', 'Wordpress',
        'Firebase', 'JavaScript', 'Tailwind CSS', 'MongoDB', 'MySQL', 'GitHub',
    ];

    $heroTags = [
        ['label' => 'React Native', 'class' => 'left-4 top-10'],
        ['label' => 'Laravel', 'class' => 'right-2 top-24'],
        ['label' => 'Expo', 'class' => 'bottom-40 left-2'],
        ['label' => 'Firebase', 'class' => 'bottom-24 right-2'],
    ];

    $projects = [
        [
            'name' => 'PhotoTrail',
            'tag' => 'Shared Event Album',
            'category' => 'React Native · Mobile',
            'desc' => 'A social event-album app where everyone at a wedding or party contributes photos and videos into one shared, private album.',
            'image' => url('assets/phototrail/featured-banner.png'),
            'href' => 'https://play.google.com/store/apps/details?id=com.phototrail',
        ],
        [
            'name' => 'Launch-Ready Business Website',
            'tag' => 'Web presence',
            'category' => 'Laravel · Web',
            'desc' => 'A fast, responsive company website designed to build trust and turn visitors into enquiries from day one.',
            'image' => url('assets/img/1200x900/img1.jpg'),
            'href' => 'mailto:ctlinc.faisal@gmail.com?subject=Website%20enquiry',
        ],
        [
            'name' => 'React Native Product Build',
            'tag' => 'Mobile app',
            'category' => 'React Native · Mobile',
            'desc' => 'A cross-platform mobile product built from scratch — polished UI, offline support, and a smooth native feel.',
            'image' => url('assets/portfolios/1.png'),
            'href' => 'mailto:ctlinc.faisal@gmail.com?subject=Mobile%20app%20enquiry',
        ],
        [
            'name' => 'SEO-Friendly Service Site',
            'tag' => 'Discoverability',
            'category' => 'Laravel · SEO',
            'desc' => 'A service site engineered for search — clean markup, fast loads, and content structured to rank and convert.',
            'image' => url('assets/img/1200x900/img2.jpg'),
            'href' => 'mailto:ctlinc.faisal@gmail.com?subject=SEO%20enquiry',
        ],
        [
            'name' => 'E-Commerce Store',
            'tag' => 'Online shop',
            'category' => 'Laravel · Web',
            'desc' => 'A complete online store with catalogue, cart, and checkout — built to sell and easy to manage.',
            'image' => url('assets/portfolios/4.png'),
            'href' => 'mailto:ctlinc.faisal@gmail.com?subject=E-commerce%20enquiry',
        ],
        [
            'name' => 'LMS Build',
            'tag' => 'Learning platform',
            'category' => 'Ionic · Education',
            'desc' => 'A learning management platform with courses, progress tracking, and a clean experience on any device.',
            'image' => url('assets/portfolios/6.png'),
            'href' => 'mailto:ctlinc.faisal@gmail.com?subject=LMS%20enquiry',
        ],
        [
            'name' => 'POS System',
            'tag' => 'Point of sale',
            'category' => 'React Native · Tools',
            'desc' => 'A point-of-sale app for managing sales, inventory, and receipts right from a mobile device.',
            'image' => url('assets/portfolios/8.png'),
            'href' => 'mailto:ctlinc.faisal@gmail.com?subject=POS%20enquiry',
        ],
    ];

    $services = [
        [
            'name' => 'Mobile app development',
            'desc' => 'I build Android and iOS apps that are designed around your product goals and user needs.',
            'image' => url('assets/faisalimtiaz/app-development.png'),
            'features' => [
                'App UI and UX design',
                'Frontend development',
                'Backend integration',
                'App Store and Play Store launch',
                'Maintenance and bug fixes for 1 month',
            ],
            'href' => route('services.mobile-app-development'),
        ],
        [
            'name' => 'Website development',
            'desc' => 'I create fast, responsive websites and web apps that help you launch and grow online.',
            'image' => url('assets/faisalimtiaz/website-development.png'),
            'features' => [
                'Website UI and UX design',
                'Frontend development',
                'Backend development',
                'Server deployment',
                'Maintenance and bug fixes for 1 month',
            ],
            'href' => route('services.website-development'),
        ],
        [
            'name' => 'SEO services',
            'desc' => 'I help your pages become easier to find, easier to understand, and easier to trust in search.',
            'image' => url('assets/faisalimtiaz/seo.png'),
            'features' => [
                'Speed improvements',
                'Technical SEO cleanup',
                'On-page optimization',
                'Search-friendly structure',
                'Post-launch tuning',
            ],
            'href' => route('services.seo-services'),
        ],
    ];

    $qualities = ['Performance', 'Usability', 'Compatibility', 'Accessibility', 'Scalability', 'Security'];

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
            'quote' => 'Very professional developer I ever meet on Fiverr. He is very cool gentle and know what is doing. The response is very fast. He respond even at midnight of his time. Keep it up bro. You will go far.',
            'name' => 'Abdul Rasheed',
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

    $homeTestimonials = array_slice(array_merge(
        [$testimonials[0]],
        array_filter(array_slice($testimonials, 1), function ($t) {
            return !empty($t['avatar']);
        })
    ), 0, 6);

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

    $faqs = [
        [
            'q' => 'What services do you offer?',
            'a' => 'I build websites, Laravel applications, React Native mobile apps, and SEO-friendly pages for startups and small businesses.',
        ],
        [
            'q' => 'Who are your services for?',
            'a' => 'They are for founders, startups, and small businesses that want one partner to design, build, launch, and support the work.',
        ],
        [
            'q' => 'How long does a project take?',
            'a' => 'It depends on the scope. Smaller pages can move quickly, while larger apps and custom systems take longer.',
        ],
        [
            'q' => 'How do you price projects?',
            'a' => 'I price based on scope, features, and complexity so the quote matches the actual work.',
        ],
        [
            'q' => 'Do you support work after launch?',
            'a' => 'Yes. I can help with fixes, updates, and improvements after the first release.',
        ],
        [
            'q' => 'What is your process?',
            'a' => 'We start with discovery, then design, build, launch, and post-launch support.',
        ],
    ];
@endphp

<!-- ========== NAVBAR ========== -->
<header class="site-header fixed inset-x-0 top-0 z-50">
    <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-5 sm:px-10" aria-label="Primary">
        <a href="#hero" class="text-sm font-black tracking-tight text-paper">
            Faisal Imtiaz<span class="text-accent">.</span>
        </a>

        <ul class="hidden items-center gap-9 md:flex">
            @foreach ($navLinks as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="nav-underline text-[13px] font-medium text-paper/70 transition-colors hover:text-paper">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-6">
            <div class="hidden items-center gap-2.5 text-xs font-medium text-paper/80 lg:flex">
                <span class="relative flex h-2 w-2" aria-hidden="true">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent/50"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-accent"></span>
                </span>
                Available for projects
            </div>
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
            <a href="mailto:ctlinc.faisal@gmail.com?subject=Project%20enquiry%20from%20faisalimtiaz.com"
               class="magnetic-btn inline-flex items-center gap-1.5 rounded-full border border-paper/20 px-5 py-2.5 text-xs font-semibold text-paper transition-colors hover:border-accent hover:text-accent"
               data-magnetic>
                Let's Talk
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </nav>
</header>

<main>

    <!-- ========== HERO ========== -->
    <section id="hero" class="relative flex min-h-[100svh] items-center overflow-hidden px-5 pt-24 pb-16 sm:px-10 lg:px-6">
        <div class="mx-auto grid w-full max-w-7xl items-center gap-16 lg:grid-cols-12 lg:gap-10">

            <!-- Left -->
            <div class="lg:col-span-5">
                <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.35em] text-accent" data-reveal>
                    Web &amp; System Apps Engineer <span class="text-paper/50">/</span> Multan, Pakistan
                </p>

                <h1 class="hero-heading text-paper" data-reveal>
                    I build digital products from idea to launch.
                </h1>

                <p class="mt-6 max-w-md text-base leading-relaxed text-smoke" data-reveal>
                    I design and develop websites, web applications, and mobile apps for startups and
                    businesses — from the first interface to deployment and ongoing support.
                </p>

                <div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-5" data-reveal>
                    <a href="#contact"
                       class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-8 py-4 text-sm font-bold text-ink transition-colors hover:bg-white"
                       data-magnetic>
                        Start a project
                        <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                    </a>
                    <a href="#work" class="nav-underline inline-flex items-center gap-2 text-sm font-semibold text-paper/80 transition-colors hover:text-paper">
                        View my work
                    </a>
                </div>

                <p class="mt-8 text-xs font-medium uppercase tracking-[0.18em] text-smoke" data-reveal>
                    10+ Years Experience
                    <span class="mx-2 text-accent" aria-hidden="true">·</span>
                    50+ Projects
                    <span class="mx-2 text-accent" aria-hidden="true">·</span>
                    Web + Mobile
                </p>

                <div class="mt-8 flex items-center gap-3 border-t border-line pt-6" data-reveal>
                    <img src="{{ url('assets/faisalimtiaz/faisalimtiaz.jpg') }}"
                         alt="Faisal Imtiaz"
                         class="h-11 w-11 rounded-full object-cover ring-1 ring-line">
                    <div class="leading-tight">
                        <p class="text-sm font-bold text-paper">Faisal Imtiaz</p>
                        <p class="mt-0.5 text-xs text-smoke">Based in Pakistan · Working worldwide</p>
                    </div>
                </div>
            </div>

            <!-- Right: product showcase -->
            <div class="relative lg:col-span-7" data-devices>
                <div class="showcase-glow pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>

                <div class="showcase relative px-6 py-12 sm:px-8">
                    <!-- Laptop -->
                    <div class="device-laptop relative z-10 mx-auto w-full max-w-2xl" data-parallax="laptop">
                        <div class="device-float">
                            <div class="overflow-hidden rounded-t-xl bg-surface shadow-[0_40px_90px_-30px_rgba(0,0,0,0.8)]">
                                <div class="flex items-center gap-2 border-b border-line bg-surface px-4 py-3">
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#E4A98B]" aria-hidden="true"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#E6C189]" aria-hidden="true"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-[#9FBFA2]" aria-hidden="true"></span>
                                    
                                </div>
                                <img src="{{ url('assets/phototrail/fsmobility.png') }}"
                                     alt="Launch-ready business website screenshot"
                                     class="aspect-[16/10] w-full object-cover">
                            </div>
                            <div class="mx-auto h-3 w-[104%] rounded-b-2xl border border-t-0 border-line bg-surface" aria-hidden="true"></div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="device-phone absolute bottom-0 left-0 z-20 w-36 sm:w-44 lg:-left-2" data-parallax="phone">
                        <div class="device-float">
                            <div class="rounded-[2.2rem] bg-ink shadow-[0_30px_70px_-20px_rgba(0,0,0,0.85)]">
                                <div class="relative overflow-hidden rounded-[1.9rem] bg-surface">
                                    <!-- <span class="absolute left-1/2 top-2.5 z-10 h-5 w-20 -translate-x-1/2 rounded-full bg-ink ring-1 ring-line" aria-hidden="true"></span> -->
                                    <img src="{{ url('assets/phototrail/home.png') }}"
                                         alt="PhotoTrail — shared event album mobile app"
                                         class="aspect-[9/18] w-full object-cover">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Currently building card -->
                    <a href="https://play.google.com/store/apps/details?id=com.phototrail"
                       target="_blank" rel="noopener"
                       class="group absolute -bottom-2 right-2 z-30 hidden items-center gap-3 rounded-xl bg-paper/5 px-4 py-3 text-left backdrop-blur-md transition-colors hover:bg-paper/10 sm:flex"
                       data-reveal>
                        <span class="relative flex h-2 w-2 shrink-0" aria-hidden="true">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent/50"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-accent"></span>
                        </span>
                        <span class="leading-tight">
                            <span class="block text-[10px] font-semibold uppercase tracking-[0.25em] text-smoke">Currently building</span>
                            <span class="mt-0.5 block text-sm font-bold text-paper">PhotoTrail</span>
                            <span class="mt-0.5 block text-[11px] text-accent">Shared event albums <span class="inline-block transition-transform duration-300 group-hover:translate-x-0.5">→</span></span>
                        </span>
                    </a>

                    <!-- Tech tags -->
                    @foreach ($heroTags as $tag)
                        <span class="tech-tag absolute {{ $tag['class'] }} z-30 hidden rounded-full px-3 py-1.5 text-[11px] font-medium tracking-wide text-paper/80 md:inline-flex" aria-hidden="true">
                            {{ $tag['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ========== SELECTED WORK ========== -->
    <section id="work" class="px-5 pb-32 pt-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-4 flex items-end justify-between" data-scroll-reveal>
                <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">Selected Work</h2>
                <span class="text-xs font-medium uppercase tracking-[0.3em] text-smoke">({{ count($projects) }})</span>
            </div>

            <p class="mb-16 max-w-md text-sm leading-relaxed text-smoke" data-scroll-reveal>
                A selection of app and web screens I've built for different businesses. Click "More" on any card to
                get details or start a conversation about a similar project.
            </p>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <article class="group flex flex-col overflow-hidden rounded-2xl bg-surface transition-colors duration-500 hover:shadow-[0_20px_50px_-20px_rgba(0,0,0,0.4)]" data-scroll-reveal>
                        <div class="relative aspect-[4/3] overflow-hidden">
                            <img src="{{ $project['image'] }}"
                                 alt="{{ $project['name'] }}"
                                 loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <span class="absolute left-3 top-3 rounded-full border border-line bg-ink/70 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-paper/80 backdrop-blur-md">
                                {{ $project['category'] }}
                            </span>
                        </div>

                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="text-xl font-black tracking-tight text-paper">{{ $project['name'] }}</h3>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-accent">{{ $project['tag'] }}</p>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-smoke">{{ $project['desc'] }}</p>

                            <a href="{{ $project['href'] }}"
                               @if (str_starts_with($project['href'], 'http')) target="_blank" rel="noopener" @endif
                               class="mt-6 inline-flex items-center gap-2 self-start rounded-full border border-paper/20 px-5 py-2.5 text-xs font-semibold text-paper transition-colors duration-300 group-hover:border-accent group-hover:text-accent">
                                More
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== TESTIMONIALS ========== -->
    <section id="testimonials" class="px-5 pb-32 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-14" data-scroll-reveal>
                <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">Client Feedback</p>
                <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">
                    Testimonials<span class="text-accent">.</span>
                </h2>
                <p class="mt-5 max-w-md text-sm leading-relaxed text-smoke">
                    What clients say about working with Faisal Imtiaz.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($homeTestimonials as $testimonial)
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
                                     loading="lazy"
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
                <a href="{{ route('studio.testimonials') }}"
                   class="magnetic-btn inline-flex items-center gap-2 rounded-full border border-paper/20 px-8 py-4 text-sm font-semibold text-paper transition-colors hover:border-accent hover:text-accent"
                   data-magnetic>
                    View more testimonials
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ========== ABOUT ME ========== -->
    <section id="about-me" class="px-5 pb-32 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid items-center gap-12 lg:grid-cols-12">
                <!-- Photo -->
                <div class="lg:col-span-5" data-scroll-reveal>
                    <div class="relative mx-auto max-w-sm">
                        <div class="showcase-glow pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>
                        <div class="overflow-hidden rounded-2xl bg-surface">
                            <img src="{{ url('assets/faisalimtiaz/faisalimtiaz.jpg') }}"
                                 alt="Faisal Imtiaz"
                                 class="aspect-[4/5] w-full object-cover">
                        </div>
                        <span class="tech-tag absolute -bottom-4 left-6 rounded-full px-4 py-2 text-xs font-semibold text-paper">
                            Faisal Imtiaz
                        </span>
                    </div>
                </div>

                <!-- Bio -->
                <div class="lg:col-span-7" data-scroll-reveal>
                    <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">About Me</p>
                    <h2 class="text-3xl font-black leading-tight tracking-tight text-paper sm:text-5xl">
                        Building products people love to <span class="text-outline">use</span> since 2013.
                    </h2>

                    <p class="mt-8 max-w-xl text-base leading-relaxed text-smoke">
                        I'm Faisal Imtiaz — a full-stack developer based in Multan, Pakistan, working with startups and
                        businesses worldwide. I design and build websites, web apps, and mobile apps from the first
                        interface to deployment and long-term support.
                    </p>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-smoke">
                        Since 2013 I've shipped 50+ projects across Laravel, React Native, Ionic, and beyond. I lead
                        TeckCreators, where I focus on clear communication, practical delivery, and work that is easy to
                        maintain after launch.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-5">
                        <a href="#contact"
                           class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-8 py-4 text-sm font-bold text-ink transition-colors hover:bg-white"
                           data-magnetic>
                            Work with me
                            <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                        </a>
                        <div class="flex items-center gap-6 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">
<a href="https://www.facebook.com/iamfaisalimtiaz/" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Facebook</a>
                    <a href="https://www.instagram.com/iamfaysalimtiaz/" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Instagram</a>
                    <a href="https://www.linkedin.com/in/faysalimtiaz/" target="_blank" rel="noopener" class="transition-colors hover:text-paper">LinkedIn</a>
                    <a href="https://www.youtube.com/@iamfaisalimtiaz" target="_blank" rel="noopener" class="transition-colors hover:text-paper">YouTube</a>
                    <a href="https://www.fiverr.com/faysal1994" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Fiverr</a>
                    <a href="https://www.upwork.com/freelancers/~01f4d63b18385cb19b?viewMode=1" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Upwork</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== SKILLS & TECHNOLOGIES ========== -->
    <section class="overflow-hidden border-y border-line bg-ink py-20 sm:py-24" aria-label="Skills and technologies">
        <div class="mb-8 flex flex-wrap items-baseline justify-between gap-4 px-5 sm:px-10 lg:px-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">
                Skills &amp; Technologies
            </p>
            <p class="text-xs text-smoke/80">The stack varies from project to project, depending on the product and goals.</p>
        </div>
        <div class="marquee overflow-hidden" data-marquee>
            <div class="flex w-max items-center whitespace-nowrap" data-marquee-track>
                @foreach (array_merge($skills, $skills) as $skill)
                    <span class="text-4xl font-black uppercase tracking-tight text-paper/85 sm:text-5xl">
                        {{ $skill }}
                        <span class="mx-6 align-middle text-accent/70 sm:mx-10" aria-hidden="true">✦</span>
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== ABOUT + SERVICES ========== -->
    <section id="about" class="px-5 pb-32 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">About</p>

            <div class="grid gap-10 lg:grid-cols-12" data-scroll-reveal>
                <h2 class="text-3xl font-black leading-tight tracking-tight text-paper sm:text-5xl lg:col-span-7">
                    Ten-plus years turning ideas into <span class="text-outline">launch-ready</span> products.
                </h2>
                <div class="lg:col-span-5 lg:pt-2">
                    <p class="text-base leading-relaxed text-smoke">
                        I'm a full-stack developer working with clients since 2013 and leading TeckCreators since 2018.
                        I build websites, mobile apps, LMS builds, POS systems, e-commerce stores, tools, and AI extensions.
                    </p>
                    <p class="mt-6 text-base leading-relaxed text-smoke">
                        I don't lean on inflated claims. I focus on clear communication, practical delivery, and work that
                        is easy to maintain after launch.
                    </p>
                </div>
            </div>

            <!-- Services -->
            <div id="services" class="mt-24 grid gap-8 lg:grid-cols-3" data-scroll-reveal>
                @foreach ($services as $service)
                    <article class="group flex flex-col overflow-hidden rounded-2xl bg-surface transition-shadow duration-500 hover:shadow-[0_20px_50px_-20px_rgba(0,0,0,0.4)]">
                        <div class="p-4">
                            <img src="{{ $service['image'] }}"
                                 alt="Illustration for {{ $service['name'] }} services"
                                 loading="lazy"
                                 class="w-full rounded-xl object-cover">
                        </div>
                        <div class="flex flex-1 flex-col p-6 pt-2">
                            <h3 class="text-xl font-black tracking-tight text-paper">{{ $service['name'] }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-smoke">{{ $service['desc'] }}</p>
                            <ul class="mt-5 flex-1 space-y-2.5">
                                @foreach ($service['features'] as $feature)
                                    <li class="flex items-start gap-2.5 text-sm text-smoke">
                                        <span class="mt-0.5 text-accent" aria-hidden="true">✓</span>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ $service['href'] }}"
                               class="mt-6 inline-flex items-center gap-2 self-start rounded-full border border-paper/20 px-5 py-2.5 text-xs font-semibold text-paper transition-colors duration-300 group-hover:border-accent group-hover:text-accent">
                                Learn more
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-16 flex flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between" data-scroll-reveal>
                <p class="max-w-md text-sm leading-relaxed text-smoke">
                    Need a React Native or Laravel-specific build? Explore the dedicated pages.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('services.react-native-development') }}"
                       class="magnetic-btn rounded-full border border-paper/20 px-5 py-2.5 text-xs font-semibold text-paper transition-colors hover:border-accent hover:text-accent" data-magnetic>
                        React Native development
                    </a>
                    <a href="{{ route('services.laravel-development') }}"
                       class="magnetic-btn rounded-full border border-paper/20 px-5 py-2.5 text-xs font-semibold text-paper transition-colors hover:border-accent hover:text-accent" data-magnetic>
                        Laravel development
                    </a>
                </div>
            </div>

            <!-- Qualities -->
            <div class="mt-16 flex flex-wrap items-center gap-3" data-scroll-reveal>
                @foreach ($qualities as $quality)
                    <span class="rounded-full border border-line px-5 py-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-smoke transition-colors duration-300 hover:border-paper hover:text-paper">
                        {{ $quality }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========== FAQ ========== -->
    <section id="faq" class="px-5 pb-32 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4" data-scroll-reveal>
                    <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">FAQ</p>
                    <h2 class="text-3xl font-black leading-tight tracking-tight text-paper sm:text-4xl">
                        Short answers to the questions people usually ask before they contact me.
                    </h2>
                </div>

                <div class="lg:col-span-8">
                    <dl class="divide-y divide-line">
                        @foreach ($faqs as $faq)
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

    <!-- ========== CONTACT ========== -->
    <section id="contact" class="px-5 pb-32 pt-20 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-16 lg:grid-cols-12">
                <!-- Left: info -->
                <div class="lg:col-span-5" data-scroll-reveal>
                    <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">Contact</p>
                    <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">
                        Get In Touch<span class="text-accent">.</span>
                    </h2>
                    <p class="mt-6 max-w-md text-sm leading-relaxed text-smoke">
                        I am open to Senior React Native roles, long-term mobile engineering opportunities and
                        technically challenging application projects.
                    </p>

                    <div class="mt-10 space-y-5">
                        <a href="mailto:ctlinc.faisal@gmail.com?subject=Project%20enquiry%20from%20faisalimtiaz.com"
                           class="group flex items-center gap-4" data-magnetic>
                            <span class="flex h-12 w-12 items-center justify-center rounded-full border border-paper/20 text-paper transition-colors duration-300 group-hover:border-accent group-hover:text-accent" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                            </span>
                            <span class="leading-tight">
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Email</span>
                                <span class="mt-0.5 block text-sm font-bold text-paper">ctlinc.faisal@gmail.com</span>
                            </span>
                        </a>
                        <a href="tel:+923006770770" class="group flex items-center gap-4" data-magnetic>
                            <span class="flex h-12 w-12 items-center justify-center rounded-full border border-paper/20 text-paper transition-colors duration-300 group-hover:border-accent group-hover:text-accent" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </span>
                            <span class="leading-tight">
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Phone · WhatsApp</span>
                                <span class="mt-0.5 block text-sm font-bold text-paper">+92 300 6770770</span>
                            </span>
                        </a>
                        <a href="tel:+923046770770" class="group flex items-center gap-4" data-magnetic>
                            <span class="flex h-12 w-12 items-center justify-center rounded-full border border-paper/20 text-paper transition-colors duration-300 group-hover:border-accent group-hover:text-accent" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                </svg>
                            </span>
                            <span class="leading-tight">
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Phone · WhatsApp</span>
                                <span class="mt-0.5 block text-sm font-bold text-paper">+92 304 6770770</span>
                            </span>
                        </a>
                    </div>

                    <div class="mt-10 flex items-center gap-6 border-t border-line pt-8 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">
                        <a href="https://www.linkedin.com/in/faysalimtiaz/" target="_blank" rel="noopener" class="transition-colors hover:text-paper">LinkedIn</a>
                        <a href="https://github.com/ctlincfaisal" target="_blank" rel="noopener" class="transition-colors hover:text-paper">GitHub</a>
                        <a href="https://www.behance.net/ficreations" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Behance</a>
                        <a href="https://www.youtube.com/@iamfaisalimtiaz" target="_blank" rel="noopener" class="transition-colors hover:text-paper">YouTube</a>
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
                                       class="w-full rounded-lg bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none">
                            </div>
                            <div>
                                <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">Email address</label>
                                <input type="email" id="email" name="email" placeholder="email@site.com" autocomplete="email" required
                                       class="w-full rounded-lg bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="budget" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">
                                Project type <span class="normal-case text-smoke/70">(optional)</span>
                            </label>
                            <select id="budget" name="budget" aria-label="Choose your project type"
                                    class="w-full rounded-lg bg-transparent px-4 py-3 text-sm text-paper outline-none">
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
                                      class="w-full resize-none rounded-lg bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none"></textarea>
                        </div>

                        <div class="mt-8">
                            <button type="submit"
                                    class="magnetic-btn group inline-flex w-full items-center justify-center gap-2 rounded-full bg-paper px-8 py-4 text-sm font-bold text-ink transition-colors hover:bg-white"
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

</main>

@endsection

@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isDesktop = window.innerWidth >= 1024;

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

    /* ---------- Contact form ---------- */
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
    if (!reducedMotion) {
        gsap.from('[data-reveal]', {
            y: 26,
            opacity: 0,
            duration: 0.9,
            stagger: 0.09,
            ease: 'power3.out',
            delay: 0.15,
        });
        gsap.from('[data-devices] .device-float', {
            y: 40,
            opacity: 0,
            duration: 1,
            stagger: 0.2,
            ease: 'power3.out',
            delay: 0.45,
        });
    } else {
        gsap.set('[data-reveal], [data-devices] .device-float', { opacity: 1 });
    }

    /* ---------- Device perspective + parallax ---------- */
    if (!reducedMotion) {
        var laptop = document.querySelector('.device-laptop');
        var phone = document.querySelector('.device-phone');

        if (laptop) {
            gsap.set(laptop, { rotateY: isDesktop ? -5 : 0, rotateX: isDesktop ? 2 : 0 });
        }
        if (phone) {
            gsap.set(phone, { rotateY: isDesktop ? 6 : 0, rotateX: isDesktop ? 4 : 0 });
        }

        // Gentle mouse parallax
        var hero = document.getElementById('hero');
        if (hero && finePointer && isDesktop) {
            hero.addEventListener('mousemove', function (e) {
                var rect = hero.getBoundingClientRect();
                var dx = ((e.clientX - rect.left) / rect.width) * 2 - 1;
                var dy = ((e.clientY - rect.top) / rect.height) * 2 - 1;
                if (laptop) gsap.to(laptop, { rotateY: -5 + dx * 2.5, rotateX: 2 - dy * 2.5, duration: 0.7, ease: 'power2.out' });
                if (phone) gsap.to(phone, { rotateY: 6 + dx * 3, rotateX: 4 + dy * 3, duration: 0.7, ease: 'power2.out' });
            });
            hero.addEventListener('mouseleave', function () {
                if (laptop) gsap.to(laptop, { rotateY: -5, rotateX: 2, duration: 0.8, ease: 'power2.out' });
                if (phone) gsap.to(phone, { rotateY: 6, rotateX: 4, duration: 0.8, ease: 'power2.out' });
            });
        }
    }

    /* ---------- Infinite marquee ---------- */
    var track = document.querySelector('[data-marquee-track]');
    if (track) {
        gsap.to(track, {
            xPercent: -50,
            ease: 'none',
            duration: 30,
            repeat: -1,
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