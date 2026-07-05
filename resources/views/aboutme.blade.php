@extends('_layout.app')

@section('title', 'About Faisal Imtiaz | Laravel & Mobile Application Developer')
@section('meta_description', 'Learn more about Faisal Imtiaz, a Laravel and mobile application developer experienced in React Native, Ionic, Firebase, MySQL, and MongoDB.')
@section('canonical', url('aboutme'))
@section('og_image', url('assets/logo.png'))

@php
    $aboutFaqs = [
        ['q' => 'What services do you offer?', 'a' => 'I build websites, Laravel applications, React Native mobile apps, and SEO-friendly pages.'],
        ['q' => 'Who are your services for?', 'a' => 'They are for startups and small businesses that want a practical partner for web and mobile work.'],
        ['q' => 'How long have you been doing this?', 'a' => 'I have been working with clients since 2013 and leading TeckCreators since 2018.'],
        ['q' => 'How do you price projects?', 'a' => 'I price based on scope, features, and complexity so the quote fits the actual work.'],
        ['q' => 'Do you support work after launch?', 'a' => 'Yes. I can help with fixes, updates, and maintenance after the project goes live.'],
        ['q' => 'What is your process?', 'a' => 'I usually start with discovery, then design, build, launch, and support.'],
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
            'url' => url('aboutme'),
            'image' => url('assets/faisalimtiaz/faisalimtiaz.jpg'),
            'jobTitle' => 'Laravel and mobile application developer',
            'description' => 'Learn more about Faisal Imtiaz, a Laravel and mobile application developer experienced in React Native, Ionic, Firebase, MySQL, and MongoDB.',
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
            '@type' => 'BreadcrumbList',
            '@id' => url('aboutme').'#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'About',
                    'item' => url('aboutme'),
                ],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => url('aboutme').'#faq',
            'mainEntity' => collect($aboutFaqs)->map(function ($faq) {
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

<link rel="stylesheet" href="{{ url('assets/vendor/quill/dist/quill.snow.css') }}">


<div class="container content-space-b-1 content-space-lg-1" id="technologiesiuse">

    @include('components.about.main')

</div>

@include('components.faq-grid', [
    'id' => 'faq',
    'heading' => 'FAQ',
    'intro' => 'A few short answers about experience, process, pricing, and support.',
    'faqs' => $aboutFaqs,
])
<!-- JS Implementing Plugins -->
<script src="{{ url('assets/vendor/hs-step-form/dist/hs-step-form.min.js') }}"></script>
<script src="{{ url('assets/vendor/hs-sticky-block/dist/hs-sticky-block.min.js') }}"></script>
<script src="{{ url('assets/vendor/hs-add-field/dist/hs-add-field.min.js') }}"></script>
<script src="{{ url('assets/vendor/imask/dist/imask.min.js') }}"></script>
<script src="{{ url('assets/vendor/quill/dist/quill.min.js') }}"></script>

<!-- JS Front -->

<!-- JS Plugins Init. -->
<script>
(function() {
    // INITIALIZATION OF STICKY BLOCKS
    // =======================================================
    new HSStickyBlock('.js-sticky-block', {
        targetSelector: document.getElementById('header').classList.contains('navbar-fixed') ? '#header' :
            null
    })


    // INITIALIZATION OF STEP FORM
    // =======================================================
    new HSStepForm('.js-step-form', {
        finish: () => {
            document.getElementById("uploadResumeStepFormProgress").style.display = 'none'
            document.getElementById("uploadResumeStepFormContent").style.display = 'none'
            document.getElementById("successMessageContent").style.display = 'block'
            scrollToTop('#header');
            const formContainerEg1 = document.getElementById('formContainerEg1')
            formContainerEg1.classList.remove('col-lg-8')
            formContainerEg1.classList.add('col-lg-12')
        },
        onNextStep: function() {
            // scrollToTop()
        },
        onPrevStep: function() {
            // scrollToTop()
        }
    })

    function scrollToTop(el = '.js-step-form') {
        el = document.querySelector(el)
        window.scrollTo({
            top: (el.getBoundingClientRect().top + window.scrollY) - 30,
            left: 0,
            behavior: 'smooth'
        })
    }

})()
</script>

@endsection
