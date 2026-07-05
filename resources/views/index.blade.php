@extends('_layout.app')

@section('title', 'Web and mobile solutions for startups | Faisal Imtiaz')
@section('meta_description', 'Faisal Imtiaz helps startups and small businesses design, build, launch, and maintain Laravel websites and React Native mobile apps.')
@section('canonical', url('/'))
@section('og_image', url('assets/logo.png'))

@php
    $homepageFaqs = [
        ['q' => 'What services do you offer?', 'a' => 'I build websites, Laravel applications, React Native mobile apps, and SEO-friendly pages for startups and small businesses.'],
        ['q' => 'Who are your services for?', 'a' => 'They are for founders, startups, and small businesses that want one partner to design, build, launch, and support the work.'],
        ['q' => 'How long does a project take?', 'a' => 'It depends on the scope. Smaller pages can move quickly, while larger apps and custom systems take longer.'],
        ['q' => 'How do you price projects?', 'a' => 'I price based on scope, features, and complexity so the quote matches the actual work.'],
        ['q' => 'Do you support work after launch?', 'a' => 'Yes. I can help with fixes, updates, and improvements after the first release.'],
        ['q' => 'What is your process?', 'a' => 'We start with discovery, then design, build, launch, and post-launch support.'],
    ];
@endphp

@push('structured_data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Person',
            '@id' => url('/').'#person',
            'name' => 'Faisal Imtiaz',
            'url' => url('/'),
            'image' => url('assets/faisalimtiaz/faisalimtiaz.jpg'),
            'jobTitle' => 'Web and mobile application developer',
            'description' => 'Faisal Imtiaz helps startups and small businesses launch websites and mobile apps that convert.',
            'sameAs' => [
                'https://www.youtube.com/@iamfaisalimtiaz',
                'https://www.linkedin.com/in/faysalimtiaz/',
                'https://www.instagram.com/iamfaysalimtiaz/',
                'https://www.facebook.com/iamfaisalimtiaz/',
                'https://www.behance.net/ficreations',
                'https://github.com/ctlincfaisal',
            ],
        ],
        [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => 'Faisal Imtiaz',
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url('assets/logo.png'),
            ],
            'sameAs' => [
                'https://www.youtube.com/@iamfaisalimtiaz',
                'https://www.linkedin.com/in/faysalimtiaz/',
                'https://www.instagram.com/iamfaysalimtiaz/',
                'https://www.facebook.com/iamfaisalimtiaz/',
                'https://www.behance.net/ficreations',
                'https://github.com/ctlincfaisal',
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => url('/'),
            'name' => 'Faisal Imtiaz',
            'description' => 'Faisal Imtiaz helps startups and small businesses launch websites and mobile apps that convert.',
            'publisher' => [
                '@id' => url('/').'#organization',
            ],
            'inLanguage' => 'en',
        ],
        [
            '@type' => 'FAQPage',
            '@id' => url('/').'#faq',
            'mainEntity' => collect($homepageFaqs)->map(function ($faq) {
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

@section('content')




<!-- Hero -->
<!-- content-space-t-4 content-space-b-1 content-space-b-sm-2  -->
<div class="container mt-lg-6 content-space-t-3 content-space-b-1 content-space-b-sm-2">

    <div class="d-flex flex-column">
        <!-- Default Logo -->
        <a class="navbar-brand mt-3" href="{{ url('/') }}" aria-label="Front">
            <img class="navbar-brand-logo w-100" src="{{ url('assets/logo.png') }}" alt="Faisal Imtiaz logo" style="max-width: 50%;">
        </a>
        <!-- End Default Logo -->

        <p class="mt-3 fw-semibold">
            Laravel and React Native developer for startups and small businesses
        </p>


    </div>


    <div class="row">
        <div class="w-lg-50">
            <!-- <span>Welcome guests! I am Faisal. An</span> -->
            <h1 class="mb-4 w-lg-75 lh-sm" style="font-family:Poppins, sans-serif!important; font-size: 1.9rem; max-width: 18ch;">
                I help startups and small businesses launch websites and mobile apps that convert.
            </h1>

            <p class="lead">
                I help you turn an idea into a launch-ready product with clear design, solid development,
                deployment support, and ongoing maintenance.
            </p>

            <a href="#contact" class="btn btn-primary btn-transition px-6">
                Request a quote
            </a>
            <a href="mailto:ctlinc.faisal@gmail.com?subject=Project%20enquiry%20from%20faisalimtiaz.com" class="btn btn-outline-primary btn-transition px-3">
                Email me
            </a>

            <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
                <span><i class="bi bi-check2-circle text-primary me-1"></i>Working with clients since 2013</span>
                <span><i class="bi bi-check2-circle text-primary me-1"></i>50+ projects delivered</span>
                <span><i class="bi bi-check2-circle text-primary me-1"></i>Replies in 3-4 business hours</span>
            </div>

        </div>

        <div class="w-lg-50">

            <!-- SVG Element -->
            <div class="position-relative mx-auto" style="max-width: 28rem; min-height: 30rem;">
                <figure class="position-absolute top-0 end-0 zi-2 me-10" data-aos="fade-up" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 450 450" width="165"
                        height="165">
                        <g>
                            <defs>
                                <path id="circleImgID2" d="M225,448.7L225,448.7C101.4,448.7,1.3,348.5,1.3,225l0,0C1.2,101.4,101.4,1.3,225,1.3l0,0
                      c123.6,0,223.7,100.2,223.7,223.7l0,0C448.7,348.6,348.5,448.7,225,448.7z"></path>
                            </defs>
                            <clipPath id="circleImgID1">
                                <use xlink:href="#circleImgID2"></use>
                            </clipPath>
                            <g clip-path="url(#circleImgID1)">
                                <image width="450" height="450" xlink:href="{{ url('assets/img/450x450/img1.jpg') }}"></image>
                            </g>
                        </g>
                    </svg>
                </figure>

                <figure class="position-absolute top-0 start-0" data-aos="fade-up" data-aos-delay="300" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 335.2 335.2" width="120"
                        height="120">
                        <circle fill="none" stroke="#377dff" stroke-width="75" cx="167.6" cy="167.6" r="130.1"></circle>
                    </svg>
                </figure>

                <figure class="d-none d-sm-block position-absolute top-0 start-0 mt-10" data-aos="fade-up"
                    data-aos-delay="200" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 515 515" width="200"
                        height="200">
                        <g>
                            <defs>
                                <path id="circleImgID4" d="M260,515h-5C114.2,515,0,400.8,0,260v-5C0,114.2,114.2,0,255,0h5c140.8,0,255,114.2,255,255v5
                      C515,400.9,400.8,515,260,515z"></path>
                            </defs>
                            <clipPath id="circleImgID3">
                                <use xlink:href="#circleImgID4"></use>
                            </clipPath>
                            <g clip-path="url(#circleImgID3)">
                                <image width="515" height="515" xlink:href="{{ url('assets/img/515x515/img1.jpg') }}"
                                    transform="matrix(1 0 0 1 1.639390e-02 2.880859e-02)"></image>
                            </g>
                        </g>
                    </svg>
                </figure>

                <figure class="position-absolute top-0 end-0" style="margin-top: 11rem; margin-right: 13rem;"
                    data-aos="fade-up" data-aos-delay="250" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 67 67" width="25" height="25">
                        <circle fill="#00C9A7" cx="33.5" cy="33.5" r="33.5"></circle>
                    </svg>
                </figure>

                <figure class="position-absolute top-0 end-0 me-3" style="margin-top: 8rem;" data-aos="fade-up"
                    data-aos-delay="350" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 141 141" width="50"
                        height="50">
                        <circle fill="#FFC107" cx="70.5" cy="70.5" r="70.5"></circle>
                    </svg>
                </figure>

                <figure class="position-absolute bottom-0 end-0" data-aos="fade-up" data-aos-delay="400" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 770.4 770.4" width="280"
                        height="280">
                        <g>
                            <defs>
                                <path id="circleImgID6" d="M385.2,770.4L385.2,770.4c212.7,0,385.2-172.5,385.2-385.2l0,0C770.4,172.5,597.9,0,385.2,0l0,0
                      C172.5,0,0,172.5,0,385.2l0,0C0,597.9,172.4,770.4,385.2,770.4z"></path>
                            </defs>
                            <clipPath id="circleImgID5">
                                <use xlink:href="#circleImgID6"></use>
                            </clipPath>
                            <g clip-path="url(#circleImgID5)">
                                <image width="900" height="900" xlink:href="{{ url('assets/img/900x900/img2.jpg') }}"
                                    transform="matrix(1 0 0 1 -64.8123 -64.8055)"></image>
                            </g>
                        </g>
                    </svg>
                </figure>
            </div>
            <!-- End SVG Element -->
        </div>
    </div>

</div>
<!-- End Hero -->


@include('components.homepage.services')

@include('components.homepage.credibility')

@include('components.homepage.features')

<div class="border-top mx-auto" style="max-width: 25rem;"></div>

@include('components.homepage.case-highlights')

@include('components.homepage.portfolios')

@include('components.faq-grid', [
    'id' => 'faq',
    'heading' => 'FAQ',
    'intro' => 'Short answers to the questions people usually ask before they contact me.',
    'faqs' => $homepageFaqs,
])

@include('components.homepage.blog-teaser')

@include('components.homepage.technologies')

@include('components.homepage.contact')


@endsection
