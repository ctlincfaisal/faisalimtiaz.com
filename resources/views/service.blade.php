@extends('_layout.app')

@section('title', $page['title'])
@section('meta_description', $page['meta_description'])
@section('canonical', $page['canonical'])
@section('og_image', url('assets/logo.png'))

@push('structured_data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $page['canonical'].'#breadcrumb',
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
                    'name' => $page['eyebrow'],
                    'item' => $page['canonical'],
                ],
            ],
        ],
        [
            '@type' => 'Service',
            '@id' => $page['canonical'].'#service',
            'name' => $page['h1'],
            'serviceType' => $page['primaryKeyword'] ?? $page['eyebrow'],
            'description' => $page['meta_description'],
            'provider' => [
                '@type' => 'Person',
                '@id' => url('/').'#person',
                'name' => 'Faisal Imtiaz',
                'url' => url('/'),
            ],
            'areaServed' => [
                '@type' => 'Place',
                'name' => 'Worldwide',
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $page['canonical'].'#faq',
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

@section('content')
    @include('components.service-page', $page)
@endsection
