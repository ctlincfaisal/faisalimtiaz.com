@extends('_layout.app')

@section('title', $post['title'] . ' | Faisal Imtiaz')
@section('meta_description', $post['meta_description'])
@section('canonical', $post['canonical'])
@section('og_type', 'article')

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

@section('content')
<div class="container content-space-t-3 content-space-b-2 content-space-b-lg-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="mb-4">
                <a href="{{ url('/') }}" class="text-decoration-none small text-primary fw-semibold">Home</a>
                <span class="mx-2 text-muted">/</span>
                <a href="{{ route('blog') }}" class="text-decoration-none small text-primary fw-semibold">Blog</a>
                <span class="mx-2 text-muted">/</span>
                <span class="small text-muted">{{ $post['eyebrow'] }}</span>
            </div>

            <span class="text-uppercase text-primary small fw-semibold">{{ $post['eyebrow'] }}</span>
            <h1 class="display-5 mt-2 mb-4" style="font-family:Poppins, sans-serif!important; line-height: 1.1;">
                {{ $post['h1'] }}
            </h1>
            <p class="lead mb-4">
                {{ $post['intro'] }}
            </p>

            <div class="d-flex flex-wrap gap-2 mb-4">
                <span class="badge bg-light text-dark border">{{ $post['reading_time'] }}</span>
                @foreach ($post['tags'] as $tag)
                    <span class="badge bg-light text-dark border">{{ $tag }}</span>
                @endforeach
            </div>

            <div class="d-flex flex-wrap gap-3 mb-5">
                <a class="btn btn-primary btn-transition px-5" href="{{ url('/#contact') }}">Start a project</a>
                @php($primaryRelated = $post['related'][0] ?? null)
                <a class="btn btn-outline-primary btn-transition px-4" href="{{ $primaryRelated ? ($primaryRelated['href'] ?? route($primaryRelated['route'])) : route('blog') }}">Related service</a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="row g-4">
                @foreach ($post['sections'] as $section)
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body p-4 p-lg-5">
                                <h2 class="h3 mb-3">{{ $section['title'] }}</h2>
                                <p>{{ $section['text'] }}</p>
                                @if (!empty($section['bullets']))
                                    <ul class="list-pointer mb-0">
                                        @foreach ($section['bullets'] as $bullet)
                                            <li class="list-pointer-item">{{ $bullet }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col-lg-10">
            <div class="bg-light rounded-3 p-4 p-lg-5">
                <h2 class="h3 mb-3">What to do next</h2>
                <p class="mb-4">Use this article as a planning guide, then move into the service page that matches your current need. If you already know the project is ready, the contact form is the fastest next step.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a class="btn btn-primary btn-transition px-4" href="{{ url('/#contact') }}">Contact me</a>
                    <a class="btn btn-outline-primary btn-transition px-4" href="{{ route('blog') }}">Back to blog</a>
                </div>
            </div>
        </div>
    </div>

    @if (count($faqItems))
        <div class="row justify-content-center mt-5">
            <div class="col-lg-10">
                <div class="w-lg-50 mx-lg-auto text-center mb-5">
                    <h2>FAQ</h2>
                    <p>Short answers to common questions.</p>
                </div>
                <div class="row g-4">
                    @foreach ($faqItems as $faq)
                        <div class="col-lg-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h3 class="h5">{{ $faq['q'] }}</h3>
                                    <p class="mb-0">{{ $faq['a'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="row justify-content-center mt-5">
        <div class="col-lg-10">
            <div class="w-lg-50 mx-lg-auto text-center mb-5">
                <h2>Related links</h2>
                <p>Keep moving toward the page that fits your next step.</p>
            </div>
            <div class="row g-4">
                @foreach ($post['related'] as $link)
                    <div class="col-md-4">
                        <a class="card h-100 shadow-sm text-decoration-none text-reset" href="{{ $link['href'] ?? route($link['route']) }}">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <span>{{ $link['label'] }}</span>
                                <i class="bi bi-arrow-right"></i>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col-lg-10">
            <div class="bg-primary rounded-3 p-5 text-white">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <h2 class="h3 mb-2">Want help applying this to your project?</h2>
                        <p class="mb-0">I can help turn the ideas in this article into a real homepage, service page, or product plan.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a class="btn btn-light btn-transition px-4 me-2" href="{{ url('/#contact') }}">Contact me</a>
                        <a class="btn btn-outline-light btn-transition px-4" href="{{ route('services.website-development') }}">Services</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
