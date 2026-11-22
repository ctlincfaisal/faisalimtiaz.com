@extends('studio.app')

@section('title', 'Faisal Imtiaz | Freelance Mobile & Web App Developer')
@section('meta_description', 'Faisal Imtiaz is a freelance mobile and web app developer for startups and businesses, building Android, iOS, and web applications with React Native and Laravel.')

@php
    $schemaBase = 'https://faisalimtiaz.com';
    $personId = $schemaBase . '/#person';
    $websiteId = $schemaBase . '/#website';
    $professionalServiceId = $schemaBase . '/#professional-service';
@endphp

@push('structured_data')
    @include('components.structured-data', ['graph' => [
        [
            '@type' => 'Person',
            '@id' => $personId,
            'name' => 'Faisal Imtiaz',
            'url' => $schemaBase,
            'image' => $schemaBase . '/assets/faisalimtiaz/faisalimtiaz-about.jpeg',
            'jobTitle' => 'Senior React Native Engineer',
            'email' => 'ctlinc.faisal@gmail.com',
            'sameAs' => [
                'https://www.youtube.com/@iamfaisalimtiaz',
                'https://www.linkedin.com/in/faysalimtiaz/',
                'https://www.instagram.com/iamfaysalimtiaz/',
                'https://www.facebook.com/iamfaisalimtiaz/',
                'https://www.behance.net/ficreations',
                'https://github.com/ctlincfaisal',
            ],
            'knowsAbout' => [
                'React Native', 'Ionic', 'Node.js', 'Laravel', 'PHP', 'Angular',
                'WordPress', 'Firebase', 'JavaScript', 'Tailwind CSS', 'MongoDB', 'MySQL',
            ],
            'areaServed' => [
                '@type' => 'Place',
                'name' => 'Worldwide',
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => $schemaBase,
            'name' => 'Faisal Imtiaz',
            'description' => 'Faisal Imtiaz is a Laravel and React Native developer helping startups and small businesses launch websites and mobile apps since 2013.',
            'publisher' => ['@id' => $personId],
            'inLanguage' => 'en',
        ],
        [
            '@type' => 'ProfessionalService',
            '@id' => $professionalServiceId,
            'name' => 'Faisal Imtiaz — Web and Mobile App Development',
            'url' => $schemaBase,
            'description' => 'Web, Laravel, and React Native development for startups and small businesses.',
            'provider' => ['@id' => $personId],
            'areaServed' => [
                '@type' => 'Place',
                'name' => 'Worldwide',
            ],
            'serviceType' => [
                'Website development',
                'Web application development',
                'Laravel development',
                'React Native development',
                'Mobile app development',
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $schemaBase . '/#webpage',
            'url' => $schemaBase,
            'name' => 'Faisal Imtiaz — Website & Mobile App Developer',
            'isPartOf' => ['@id' => $websiteId],
            'about' => ['@id' => $personId],
            'mainEntity' => ['@id' => $professionalServiceId],
            'inLanguage' => 'en',
        ],
    ]])
@endpush

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
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection

@section('content')

@php
    $navLinks = [
        ['label' => 'Home', 'href' => '#hero'],
        ['label' => 'Work', 'href' => '#work'],
        ['label' => 'Services', 'href' => '#services'],
        ['label' => 'Process', 'href' => '#process'],
        ['label' => 'About', 'href' => '#about-me'],
        ['label' => 'Testimonials', 'href' => '#testimonials'],
        ['label' => 'FAQ', 'href' => '#faq'],
        ['label' => 'Contact', 'href' => '#contact'],
    ];

    $skills = [
        'React Native', 'IONIC', 'NodeJS', 'Laravel', 'PHP', 'Angular', 'Wordpress',
        'Firebase', 'JavaScript', 'Tailwind CSS', 'MongoDB', 'MySQL', 'GitHub',
    ];

    $projects = config('projects');
    $featuredProjects = collect($projects);

    $services = [
        [
            'name' => 'Android & iOS App Development',
            'desc' => 'Turn a product idea into a focused mobile experience for the platforms your users need.',
            'href' => route('services.mobile-app-development'),
        ],
        [
            'name' => 'React Native Development',
            'desc' => 'Build and improve Android and iOS apps from one maintainable cross-platform codebase.',
            'href' => route('services.react-native-development'),
        ],
        [
            'name' => 'Website & Web App Development',
            'desc' => 'Create a responsive website or web application that is clear for customers and practical for your team.',
            'href' => route('services.web-application-development'),
        ],
        [
            'name' => 'Laravel Backend & API Development',
            'desc' => 'Connect your product to a dependable backend, admin workflow, database, and API foundation.',
            'href' => route('services.laravel-development'),
        ],
        [
            'name' => 'MVP Development for Startups',
            'desc' => 'Shape the first release around the core user flow so you can learn from a working product.',
            'href' => route('services.web-application-development'),
        ],
        [
            'name' => 'Existing App Improvements & Maintenance',
            'desc' => 'Get focused help with features, fixes, integrations, release work, and the next practical improvement.',
            'href' => route('services.mobile-app-development'),
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

    $initials = function ($name) {
        $parts = preg_split('/[\s_(]+/', $name);
        $chars = '';
        foreach ($parts as $part) {
            if ($part !== '' && strlen($chars) < 2) {
                $chars .= strtoupper(substr($part, 0, 1));
            }
        }
        return $chars !== '' ? $chars : 'FI';
    };

    $faqs = [
        [
            'q' => 'Can you build Android and iOS applications?',
            'a' => 'Yes. I build Android and iOS applications, including React Native products and the backend/API connections they need.',
        ],
        [
            'q' => 'Can you work with an existing codebase?',
            'a' => 'Yes. I can review an existing application, clarify the next priority, and take on focused features, fixes, or maintenance.',
        ],
        [
            'q' => 'Can you help with MVP development?',
            'a' => 'Yes. We can define the smallest useful first release, its key user flows, and the integrations needed to build it responsibly.',
        ],
        [
            'q' => 'Can you improve or maintain an existing app?',
            'a' => 'Yes. I can help with bug fixes, updates, new features, maintenance, and improvements after the first release.',
        ],
        [
            'q' => 'How is the cost estimated?',
            'a' => 'I estimate from the users, workflows, screens, integrations, existing code, and first-release scope rather than a generic package.',
        ],
        [
            'q' => 'How does the development process work?',
            'a' => 'We discuss the product, plan the scope and estimate, design and develop the agreed work, then prepare the launch and support that is in scope.',
        ],
        [
            'q' => 'Do you provide post-launch support?',
            'a' => 'Yes. I can stay involved for fixes, maintenance, updates, and future product changes after launch.',
        ],
    ];
@endphp

<!-- ========== NAVBAR ========== -->
<header class="site-header fixed inset-x-0 top-0 z-50 border-b border-line bg-ink/85 backdrop-blur-md">
    <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-5 sm:px-10" aria-label="Primary">
        <a href="#hero" class="inline-flex items-center text-sm font-black tracking-tight text-paper">
            <img src="{{ url('assets/sign.png') }}" alt="Faisal Imtiaz" width="2493" height="1098" decoding="async" class="h-12 w-auto sm:h-20">
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

        <div class="flex items-center gap-4">
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
               class="magnetic-btn hidden items-center gap-1.5 rounded-full border border-paper/20 px-5 py-2.5 text-xs font-semibold text-paper transition-colors hover:border-accent hover:text-accent sm:inline-flex"
               data-magnetic>
                Let's Talk
                <span aria-hidden="true">↗</span>
            </a>
            <button type="button"
                    id="nav-toggle"
                    aria-label="Toggle navigation menu"
                    aria-expanded="false"
                    class="magnetic-btn inline-flex h-10 w-10 items-center justify-center rounded-full border border-paper/20 text-paper transition-colors hover:border-accent hover:text-accent md:hidden"
                    data-magnetic>
                <svg id="nav-open-icon" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg id="nav-close-icon" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden border-t border-line bg-ink/95 backdrop-blur-md md:hidden">
        <ul class="mx-auto flex max-w-7xl flex-col gap-1 px-5 py-4">
            @foreach ($navLinks as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="block rounded-lg px-4 py-3 text-sm font-semibold text-paper/80 transition-colors hover:bg-surface hover:text-paper">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</header>

<main>

    <!-- ========== HERO ========== -->
    <section id="hero" class="relative flex min-h-[100svh] items-center overflow-hidden px-5 pt-24 pb-16 sm:px-10 lg:px-6">
        <div class="mx-auto grid w-full max-w-7xl items-center gap-16 lg:grid-cols-12 lg:gap-10">

            <!-- Left -->
            <div class="lg:col-span-5">
                <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.35em] text-accent">
                    Freelance mobile &amp; web developer <span class="text-paper/50">/</span> Working worldwide
                </p>

                <h1 class="hero-heading text-paper">
                    I Build Android, iOS &amp; Web Apps That Turn Your Ideas Into Real Products.
                </h1>

                <p class="mt-6 max-w-xl text-base leading-relaxed text-smoke">
                    From first concept to launch, I help startups and businesses design, develop, and improve custom
                    mobile applications, websites, and backend systems.
                </p>

                <div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-5">
                    <a href="#contact"
                       class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-8 py-4 text-sm font-bold text-ink transition-colors hover:bg-white hover:text-black"
                       data-magnetic>
                        Get a Free Project Estimate
                        <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                    </a>
                    <a href="#work" class="nav-underline inline-flex items-center gap-2 text-sm font-semibold text-paper/80 transition-colors hover:text-paper">
                        View My Work
                    </a>
                </div>

                <p class="mt-8 text-xs font-medium uppercase tracking-[0.18em] text-smoke" data-reveal>
                    Since 2013
                    <span class="mx-2 text-accent" aria-hidden="true">·</span>
                    50+ Projects
                    <span class="mx-2 text-accent" aria-hidden="true">·</span>
                    React Native + Laravel
                </p>

                <div class="mt-8 flex items-center gap-3 border-t border-line pt-6" data-reveal>
                    <img src="{{ url('assets/faisalimtiaz/faisalimtiaz.webp') }}"
                         alt="Faisal Imtiaz"
                         width="768"
                         height="682"
                         decoding="async"
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
                    <div class="device-laptop relative z-10 mx-auto w-full max-w-sm" data-parallax="laptop">
                        <div class="device-float">
                            <div class="overflow-hidden rounded-t-xl">
                                <img src="{{ url('assets/phototrail/home.webp') }}"
                                     alt="PhotoTrail product interface"
                                     width="739"
                                     height="976"
                                     fetchpriority="high"
                                     decoding="async"
                                     class="block w-full">
                            </div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <!-- <div class="device-phone absolute bottom-0 left-0 z-20 w-36 sm:w-44 lg:-left-2" data-parallax="phone">
                        <div class="device-float">
                            <div class="rounded-[2.2rem] bg-ink shadow-[0_30px_70px_-20px_rgba(0,0,0,0.85)]">
                                <div class="relative overflow-hidden rounded-[1.9rem] bg-surface">
                                    <img src="{{ url('assets/phototrail/home.png') }}"
                                         alt="PhotoTrail — shared event album mobile app"
                                         class="aspect-[9/18] w-full object-cover">
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <!-- Published project card -->
                    <a href="https://play.google.com/store/apps/details?id=com.phototrail"
                       target="_blank" rel="noopener"
                       class="group absolute -bottom-2 right-2 z-30 hidden items-center gap-3 rounded-xl bg-paper/5 px-4 py-3 text-left backdrop-blur-md transition-colors hover:bg-paper/10 sm:flex"
                       data-reveal>
                        <span class="relative flex h-2 w-2 shrink-0" aria-hidden="true">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent/50"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-accent"></span>
                        </span>
                        <span class="leading-tight">
                            <span class="block text-[10px] font-semibold uppercase tracking-[0.25em] text-smoke">Published app</span>
                            <span class="mt-0.5 block text-sm font-bold text-paper">PhotoTrail</span>
                            <span class="mt-0.5 block text-[11px] text-accent">View on Google Play <span class="inline-block transition-transform duration-300 group-hover:translate-x-0.5">↗</span></span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== TRUST SIGNALS ========== -->
    <section class="border-y border-line bg-surface/40 px-5 py-8 sm:px-10 lg:px-6" aria-label="Experience and proof">
        <div class="mx-auto grid max-w-7xl gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:gap-0">
            <div class="border-line lg:border-r lg:px-8 lg:first:pl-0" data-scroll-reveal>
                <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Experience</p>
                <p class="mt-2 text-lg font-black tracking-tight text-paper">Since 2013</p>
                <p class="mt-1 text-xs leading-relaxed text-smoke">Building websites, apps, and backend systems.</p>
            </div>
            <div class="border-line sm:border-r lg:px-8" data-scroll-reveal>
                <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Portfolio</p>
                <p class="mt-2 text-lg font-black tracking-tight text-paper">50+ projects</p>
                <p class="mt-1 text-xs leading-relaxed text-smoke">Across mobile, web, commerce, and business tools.</p>
            </div>
            <div class="border-line lg:border-r lg:px-8" data-scroll-reveal>
                <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Published app</p>
                <p class="mt-2 text-lg font-black tracking-tight text-paper">PhotoTrail</p>
                <p class="mt-1 text-xs leading-relaxed text-smoke">A shared event album app available on Google Play.</p>
            </div>
            <div class="lg:px-8 lg:pr-0" data-scroll-reveal>
                <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Client feedback</p>
                <p class="mt-2 text-lg font-black tracking-tight text-paper">Authentic reviews</p>
                <a href="#testimonials" class="mt-1 inline-flex text-xs font-semibold text-accent transition-colors hover:text-paper">
                    Read client feedback <span class="ml-1" aria-hidden="true">↓</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ========== FEATURED PROJECTS ========== -->
    <section id="work" class="px-5 pb-32 pt-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-4 flex items-end justify-between" data-scroll-reveal>
                <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">Featured Projects</h2>
                <span class="text-xs font-medium uppercase tracking-[0.3em] text-smoke">({{ $featuredProjects->count() }})</span>
            </div>

            <p class="mb-16 max-w-xl text-sm leading-relaxed text-smoke" data-scroll-reveal>
                Real products and interfaces from the portfolio. Explore a case study, or tell me what you want to build next.
            </p>

            <div class="relative">
                <div id="work-scroller" class="no-scrollbar -mx-4 flex snap-x snap-mandatory overflow-x-auto scroll-smooth px-4 pb-2">
                    @foreach ($featuredProjects as $project)
                        <div class="w-full shrink-0 px-4 sm:w-1/2 lg:w-1/4" data-work-card>
                            <article class="group flex h-full flex-col overflow-hidden rounded-2xl bg-surface transition-colors duration-500 hover:shadow-[0_20px_50px_-20px_rgba(0,0,0,0.4)]" data-scroll-reveal>
                                <div class="relative aspect-[4/3] overflow-hidden">
                                    <img src="{{ url($project['image_thumb'] ?? $project['image']) }}"
                                         alt="{{ $project['name'] }}"
                                         width="{{ $project['image_thumb_width'] ?? $project['image_width'] }}"
                                         height="{{ $project['image_thumb_height'] ?? $project['image_height'] }}"
                                         loading="lazy"
                                         decoding="async"
                                         class="h-full w-full {{ ($project['image_fit'] ?? 'cover') === 'contain' ? 'object-contain' : 'object-cover' }} transition-transform duration-700 group-hover:scale-105">
                                    <span class="absolute left-3 top-3 rounded-full border border-line bg-ink/70 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-paper/80 backdrop-blur-md">
                                        {{ $project['category'] }}
                                    </span>
                                </div>

                                <div class="flex flex-1 flex-col p-6">
                                    <h3 class="text-xl font-black tracking-tight text-paper">{{ $project['name'] }}</h3>
                                    <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-accent">{{ $project['tag'] }}</p>
                                    <p class="mt-3 flex-1 text-sm leading-relaxed text-smoke">{{ $project['desc'] }}</p>

                                    @if (!empty($project['tech']))
                                        <div class="mt-5 flex flex-wrap gap-1.5">
                                            @foreach (array_slice($project['tech'], 0, 3) as $tech)
                                                <span class="rounded-full border border-line px-2.5 py-1 text-[10px] font-semibold text-smoke">{{ $tech }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-6 flex flex-wrap gap-2">
                                        <a href="{{ route('studio.work', $project['slug']) }}"
                                           class="inline-flex items-center gap-2 rounded-full border border-paper/20 px-4 py-2.5 text-xs font-semibold text-paper transition-colors duration-300 hover:border-accent hover:text-accent">
                                            View Case Study <span aria-hidden="true">→</span>
                                        </a>
                                        <a href="{{ route('aboutme', ['project' => $project['name']]) }}#contact"
                                           class="inline-flex items-center gap-2 rounded-full bg-paper/10 px-4 py-2.5 text-xs font-semibold text-paper transition-colors duration-300 hover:bg-accent hover:text-ink">
                                            Build Something Similar <span aria-hidden="true">↗</span>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="work-prev" aria-label="Previous featured projects"
                        class="magnetic-btn absolute left-0 top-1/2 z-20 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-line bg-ink/70 text-paper shadow-lg backdrop-blur-md transition-colors hover:border-accent hover:text-accent sm:h-12 sm:w-12" data-magnetic>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"></path></svg>
                </button>
                <button type="button" id="work-next" aria-label="Next featured projects"
                        class="magnetic-btn absolute right-0 top-1/2 z-20 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-line bg-ink/70 text-paper shadow-lg backdrop-blur-md transition-colors hover:border-accent hover:text-accent sm:h-12 sm:w-12" data-magnetic>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"></path></svg>
                </button>
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
                            <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-smoke">Shared on Fiverr</span>
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
                                    {{ $initials($testimonial['name']) }}
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
                            <img src="{{ url('assets/faisalimtiaz/faisalimtiaz-about.jpeg') }}"
                                 alt="Faisal Imtiaz"
                                 width="419"
                                 height="559"
                                 loading="lazy"
                                 decoding="async"
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
                        I'm Faisal Imtiaz, a freelance mobile and web app developer based in Multan, Pakistan. I help
                        startups and businesses worldwide build Android, iOS, and web applications from the first
                        interface through deployment and long-term support.
                    </p>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-smoke">
                        Since 2013 I've shipped 50+ projects across Laravel, React Native, Ionic, and beyond. I lead
                        TeckCreators, where I focus on clear communication, practical delivery, and work that is easy to
                        maintain after launch.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-5">
                        <a href="#contact"
                           class="magnetic-btn group inline-flex items-center gap-2 rounded-full bg-paper px-8 py-4 text-sm font-bold text-ink transition-colors hover:bg-white hover:text-black"
                           data-magnetic>
                            Discuss Your Project
                            <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('aboutme') }}"
                           class="magnetic-btn inline-flex items-center gap-2 rounded-full border border-paper/20 px-8 py-4 text-sm font-semibold text-paper transition-colors hover:border-accent hover:text-accent"
                           data-magnetic>
                            My Resume
                            <span aria-hidden="true">↗</span>
                        </a>
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">
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
            <div id="services" class="mt-24 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-scroll-reveal>
                @foreach ($services as $index => $service)
                    <article class="group flex min-h-[248px] flex-col rounded-2xl bg-surface p-6 ring-1 ring-line transition-shadow duration-500 hover:shadow-[0_20px_50px_-20px_rgba(0,0,0,0.4)]">
                        <div class="flex items-center justify-between gap-4">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-accent/15 text-sm font-black text-accent" aria-hidden="true">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-[10px] font-semibold uppercase tracking-[0.25em] text-smoke">Service</span>
                        </div>
                        <h3 class="mt-7 text-xl font-black tracking-tight text-paper">{{ $service['name'] }}</h3>
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-smoke">{{ $service['desc'] }}</p>
                        <a href="{{ $service['href'] }}"
                           class="mt-6 inline-flex items-center gap-2 self-start rounded-full border border-paper/20 px-5 py-2.5 text-xs font-semibold text-paper transition-colors duration-300 hover:border-accent hover:text-accent">
                            Explore service <span aria-hidden="true">→</span>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-16 flex flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between" data-scroll-reveal>
                <p class="max-w-md text-sm leading-relaxed text-smoke">
                    Need a focused technical partner? Explore the dedicated React Native and Laravel pages.
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

    <!-- ========== WHY WORK WITH ME ========== -->
    <section id="why-work-with-me" class="px-5 pb-32 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4" data-scroll-reveal>
                    <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">Why work with me</p>
                    <h2 class="text-3xl font-black leading-tight tracking-tight text-paper sm:text-5xl">
                        One developer who can stay close to the <span class="text-outline">whole product</span>.
                    </h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:col-span-8">
                    <article class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                        <h3 class="text-lg font-black tracking-tight text-paper">Direct collaboration</h3>
                        <p class="mt-3 text-sm leading-relaxed text-smoke">You work directly with Faisal from the first brief through the build, release, and next decisions.</p>
                    </article>
                    <article class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                        <h3 class="text-lg font-black tracking-tight text-paper">Real-world applications</h3>
                        <p class="mt-3 text-sm leading-relaxed text-smoke">The portfolio includes PhotoTrail, Prime Circle Finance, ResQ, Grubly, and business platforms across mobile and web.</p>
                    </article>
                    <article class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                        <h3 class="text-lg font-black tracking-tight text-paper">Design to development</h3>
                        <p class="mt-3 text-sm leading-relaxed text-smoke">Meta Car represents UI/UX work, while the wider portfolio covers implementation, integrations, and release-ready product work.</p>
                    </article>
                    <article class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                        <h3 class="text-lg font-black tracking-tight text-paper">Full-stack support</h3>
                        <p class="mt-3 text-sm leading-relaxed text-smoke">Project categories span React Native, Ionic, Laravel, Node.js, Supabase, MySQL, and the systems behind the interface.</p>
                    </article>
                    <article class="rounded-2xl bg-surface p-6 ring-1 ring-line sm:col-span-2" data-scroll-reveal>
                        <h3 class="text-lg font-black tracking-tight text-paper">Support after launch</h3>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-smoke">The service offering includes fixes, maintenance, improvements, and new features when the product needs another phase of work.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== PROCESS ========== -->
    <section id="process" class="border-y border-line bg-surface/40 px-5 py-24 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="mb-14 max-w-2xl" data-scroll-reveal>
                <p class="mb-6 text-[11px] font-semibold uppercase tracking-[0.5em] text-smoke">A clear way to start</p>
                <h2 class="text-3xl font-black tracking-tight text-paper sm:text-5xl">From first conversation to a product you can use.</h2>
                <p class="mt-5 text-base leading-relaxed text-smoke">The exact scope depends on your product, users, and existing systems. The first step is simply getting the context on the table.</p>
            </div>
            <ol class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <li class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                    <span class="text-sm font-black text-accent">01</span>
                    <h3 class="mt-8 text-lg font-black tracking-tight text-paper">Discuss Your Idea</h3>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">Share the product, users, current stage, and where you need a development partner.</p>
                </li>
                <li class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                    <span class="text-sm font-black text-accent">02</span>
                    <h3 class="mt-8 text-lg font-black tracking-tight text-paper">Plan &amp; Estimate</h3>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">Turn the conversation into a practical scope, technical direction, and project estimate.</p>
                </li>
                <li class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                    <span class="text-sm font-black text-accent">03</span>
                    <h3 class="mt-8 text-lg font-black tracking-tight text-paper">Design &amp; Development</h3>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">Build the agreed experience, connect the required systems, and keep the work aligned with the product goal.</p>
                </li>
                <li class="rounded-2xl bg-surface p-6 ring-1 ring-line" data-scroll-reveal>
                    <span class="text-sm font-black text-accent">04</span>
                    <h3 class="mt-8 text-lg font-black tracking-tight text-paper">Launch &amp; Support</h3>
                    <p class="mt-3 text-sm leading-relaxed text-smoke">Prepare the release work in scope, hand over clearly, and continue with fixes or improvements when needed.</p>
                </li>
            </ol>
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

    <!-- ========== CONVERSION CTA ========== -->
    <section id="start-project" class="px-5 pb-10 pt-10 sm:px-10 lg:px-6">
        <div class="mx-auto max-w-7xl">
            <div class="relative overflow-hidden rounded-3xl bg-paper p-8 text-ink sm:p-12 lg:p-16" data-scroll-reveal>
                <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-accent/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-end">
                    <div class="max-w-2xl">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.5em] text-ink/60">Next step</p>
                        <h2 class="mt-5 text-3xl font-black tracking-tight sm:text-5xl">Share the product, problem, or next phase.</h2>
                        <p class="mt-4 max-w-xl text-base leading-relaxed text-ink/70">A short outline is enough to start the conversation about what you want to launch, improve, or support.</p>
                    </div>
                    <a href="#contact"
                       class="magnetic-btn group inline-flex shrink-0 items-center gap-2 rounded-full bg-ink px-7 py-4 text-sm font-bold text-paper transition-colors hover:bg-surface hover:text-ink"
                       data-magnetic>
                        Discuss Your Project
                        <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                    </a>
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
                        Have an App or Website Idea? Let&rsquo;s Build It<span class="text-accent">.</span>
                    </h2>
                    <p class="mt-6 max-w-md text-sm leading-relaxed text-smoke">
                        Whether you&rsquo;re launching a new product, improving an existing application, or looking for a reliable development partner, tell me a little about your project and I&rsquo;ll help you explore the next steps.
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
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">Phone</span>
                                <span class="mt-0.5 block text-sm font-bold text-paper">+92 300 6770770</span>
                            </span>
                        </a>
                        <a href="https://wa.me/923006770770?text={{ rawurlencode('Hi Faisal, I would like to discuss a project.') }}" target="_blank" rel="noopener" class="group flex items-center gap-4" data-magnetic>
                            <span class="flex h-12 w-12 items-center justify-center rounded-full border border-paper/20 text-paper transition-colors duration-300 group-hover:border-accent group-hover:text-accent" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                </svg>
                            </span>
                            <span class="leading-tight">
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">WhatsApp</span>
                                <span class="mt-0.5 block text-sm font-bold text-paper">+92 300 6770770</span>
                            </span>
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap items-center gap-6 border-t border-line pt-8 text-[11px] font-semibold uppercase tracking-[0.25em] text-smoke">
                        <a href="https://www.facebook.com/iamfaisalimtiaz/" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Facebook</a>
                        <a href="https://www.instagram.com/iamfaysalimtiaz/" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Instagram</a>
                        <a href="https://www.linkedin.com/in/faysalimtiaz/" target="_blank" rel="noopener" class="transition-colors hover:text-paper">LinkedIn</a>
                        <a href="https://www.youtube.com/@iamfaisalimtiaz" target="_blank" rel="noopener" class="transition-colors hover:text-paper">YouTube</a>
                        <a href="https://www.fiverr.com/faysal1994" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Fiverr</a>
                        <a href="https://www.upwork.com/freelancers/~01f4d63b18385cb19b?viewMode=1" target="_blank" rel="noopener" class="transition-colors hover:text-paper">Upwork</a>
                    </div>
                </div>

                <!-- Right: form -->
                <div class="lg:col-span-7" data-scroll-reveal>
                    <form id="studio_contact_form" method="POST" action="{{ url('contactus') }}"
                          class="rounded-2xl bg-surface p-8 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] sm:p-10">
                        @csrf
                        <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"
                               class="absolute -left-[9999px] h-px w-px overflow-hidden" />

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="firstname" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">Name <span class="normal-case text-smoke/70">(required)</span></label>
                                <input type="text" id="firstname" name="firstname" placeholder="Your name" autocomplete="name" maxlength="150" required aria-describedby="firstname_error"
                                       class="w-full rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none transition-colors focus:border-accent">
                                <span id="firstname_error" class="mt-2 hidden text-xs font-medium text-red-600 dark:text-red-300"></span>
                            </div>
                            <div>
                                <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">Email address <span class="normal-case text-smoke/70">(required)</span></label>
                                <input type="email" id="email" name="email" placeholder="email@site.com" autocomplete="email" maxlength="254" required aria-describedby="email_error"
                                       class="w-full rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none transition-colors focus:border-accent">
                                <span id="email_error" class="mt-2 hidden text-xs font-medium text-red-600 dark:text-red-300"></span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="project_type" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">
                                Project type <span class="normal-case text-smoke/70">(optional)</span>
                            </label>
                            <select id="project_type" name="project_type" aria-label="Choose your project type"
                                    class="w-full rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper outline-none transition-colors focus:border-accent">
                                <option value="" class="bg-ink">Choose a project type</option>
                                <option value="Website development" class="bg-ink">Website development</option>
                                <option value="Mobile app development" class="bg-ink">Mobile app development</option>
                                <option value="MVP development" class="bg-ink">MVP development</option>
                                <option value="Existing app improvements" class="bg-ink">Existing app improvements</option>
                                <option value="Laravel backend or API" class="bg-ink">Laravel backend or API</option>
                                <option value="SEO services" class="bg-ink">SEO services</option>
                                <option value="Not sure yet" class="bg-ink">Not sure yet</option>
                            </select>
                        </div>

                        <div class="mt-6">
                            <label for="budget" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">
                                Approximate budget <span class="normal-case text-smoke/70">(optional)</span>
                            </label>
                            <select id="budget" name="budget" aria-label="Choose your approximate budget"
                                    class="w-full rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper outline-none transition-colors focus:border-accent">
                                <option value="" class="bg-ink">Choose an approximate budget</option>
                                <option value="Under $5,000" class="bg-ink">Under $5,000</option>
                                <option value="$5,000–$15,000" class="bg-ink">$5,000–$15,000</option>
                                <option value="$15,000–$30,000" class="bg-ink">$15,000–$30,000</option>
                                <option value="$30,000+" class="bg-ink">$30,000+</option>
                                <option value="Not sure yet" class="bg-ink">Not sure yet</option>
                            </select>
                        </div>

                        <div class="mt-6">
                            <label for="details" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-smoke">Project description <span class="normal-case text-smoke/70">(required)</span></label>
                            <textarea id="details" name="details" rows="5" maxlength="5000" required aria-describedby="details_error"
                                      placeholder="What are you building, and what would you like help with?"
                                      class="w-full resize-none rounded-lg border border-line bg-transparent px-4 py-3 text-sm text-paper placeholder:text-smoke/50 outline-none transition-colors focus:border-accent"></textarea>
                            <span id="details_error" class="mt-2 hidden text-xs font-medium text-red-600 dark:text-red-300"></span>
                        </div>

                        <div class="mt-8">
                            <button type="submit" aria-busy="false"
                                    class="magnetic-btn group inline-flex w-full items-center justify-center gap-2 rounded-full bg-paper px-8 py-4 text-sm font-bold text-ink transition-colors hover:bg-white hover:text-black"
                                    data-magnetic>
                                Send project details
                                <span class="transition-transform duration-300 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                            </button>
                            <div id="studio_form_success" tabindex="-1" role="status" aria-live="polite" class="mt-4 hidden rounded-lg bg-accent/15 px-4 py-3 text-center text-sm font-medium text-accent">
                                Thanks. I've received your message and will reply soon.
                            </div>
                            <div id="studio_form_error" role="alert" aria-live="assertive" class="mt-4 hidden rounded-lg bg-red-500/10 px-4 py-3 text-center text-sm font-medium text-red-600 dark:text-red-300">
                                Something went wrong. Please try again or email ctlinc.faisal@gmail.com.
                            </div>
                            <p class="mt-4 text-center text-xs text-smoke">I’ll review your message and reply with the next steps.</p>
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
            try { localStorage.setItem('studio-theme-v2', isDark ? 'dark' : 'light'); } catch (e) {}
            var meta = document.getElementById('theme-color-meta');
            if (meta) meta.setAttribute('content', isDark ? '#090A0C' : '#EDEDE7');
        });
    }

    /* ---------- Mobile nav toggle ---------- */
    var navToggle = document.getElementById('nav-toggle');
    var mobileMenu = document.getElementById('mobile-menu');
    var navOpenIcon = document.getElementById('nav-open-icon');
    var navCloseIcon = document.getElementById('nav-close-icon');
    if (navToggle && mobileMenu) {
        navToggle.addEventListener('click', function () {
            var isHidden = mobileMenu.classList.toggle('hidden');
            navToggle.setAttribute('aria-expanded', String(!isHidden));
            if (navOpenIcon) navOpenIcon.classList.toggle('hidden', !isHidden);
            if (navCloseIcon) navCloseIcon.classList.toggle('hidden', isHidden);
        });
        mobileMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                mobileMenu.classList.add('hidden');
                navToggle.setAttribute('aria-expanded', 'false');
                if (navOpenIcon) navOpenIcon.classList.remove('hidden');
                if (navCloseIcon) navCloseIcon.classList.add('hidden');
            });
        });
    }

    /* ---------- Contact form ---------- */
    var contactForm = document.getElementById('studio_contact_form');
    if (contactForm) {
        var markInvalid = function (el, invalid) {
            if (!el) return;
            el.classList.toggle('border-red-500', invalid);
            el.setAttribute('aria-invalid', invalid ? 'true' : 'false');
        };
        var fieldError = function (field, message) {
            var error = document.getElementById(field.id + '_error');
            var text = Array.isArray(message) ? message[0] : message;
            markInvalid(field, Boolean(text));
            if (error) {
                error.textContent = text || '';
                error.classList.toggle('hidden', !text);
            }
        };
        var details = document.getElementById('details');
        var project = new URLSearchParams(window.location.search).get('project');
        if (details && project && !details.value) {
            details.value = 'I am interested in a project similar to ' + project.slice(0, 120) + '.\n\n';
        }

        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var firstname = document.getElementById('firstname');
            var email = document.getElementById('email');
            var success = document.getElementById('studio_form_success');
            var errorMessage = document.getElementById('studio_form_error');
            var submitBtn = contactForm.querySelector('button[type="submit"]');

            fieldError(firstname, '');
            fieldError(email, '');
            fieldError(details, '');
            if (success) success.classList.add('hidden');
            if (errorMessage) errorMessage.classList.add('hidden');

            if (!contactForm.reportValidity()) {
                [firstname, email, details].forEach(function (field) {
                    if (!field.validity.valid) fieldError(field, field.validationMessage);
                });
                return;
            }

            var formData = new FormData(contactForm);
            submitBtn.disabled = true;
            submitBtn.setAttribute('aria-busy', 'true');
            submitBtn.textContent = 'Sending...';

            fetch(contactForm.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (data) {
                    if (!res.ok || data.msg !== 'success') {
                        var requestError = new Error(data.message || 'Unable to send your enquiry.');
                        requestError.response = data;
                        throw requestError;
                    }
                    return data;
                });
            }).then(function () {
                    contactForm.reset();
                    fieldError(firstname, '');
                    fieldError(email, '');
                    fieldError(details, '');
                    if (success) success.classList.remove('hidden');
                    if (success) success.focus();
            }).catch(function (requestError) {
                var response = requestError.response || {};
                var errors = response.errors || {};
                fieldError(firstname, errors.firstname);
                fieldError(email, errors.email);
                fieldError(details, errors.details);
                if (errorMessage) {
                    errorMessage.textContent = response.message || 'Unable to send your enquiry right now. Please try again or email ctlinc.faisal@gmail.com.';
                    errorMessage.classList.remove('hidden');
                }
            }).then(function () {
                submitBtn.disabled = false;
                submitBtn.setAttribute('aria-busy', 'false');
                submitBtn.textContent = 'Send project details';
            });
        });
    }

    /* ---------- Work carousel ---------- */
    var workScroller = document.getElementById('work-scroller');
    var workPrev = document.getElementById('work-prev');
    var workNext = document.getElementById('work-next');
    if (workScroller && workPrev && workNext) {
        var workStep = function () {
            var card = workScroller.querySelector('[data-work-card]');
            return card ? card.offsetWidth : workScroller.clientWidth;
        };
        workPrev.addEventListener('click', function () {
            workScroller.scrollBy({ left: -workStep(), behavior: 'smooth' });
        });
        workNext.addEventListener('click', function () {
            workScroller.scrollBy({ left: workStep(), behavior: 'smooth' });
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
