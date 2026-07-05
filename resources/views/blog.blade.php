@extends('_layout.app')

@section('title', 'Blog | Faisal Imtiaz')
@section('meta_description', 'Practical articles on websites, Laravel, React Native, and SEO that help startups and small businesses launch with more clarity.')
@section('canonical', url('/blog'))
@section('og_type', 'website')

@section('content')
@php
    $coverImages = [
        'homepage-that-converts-visitors-into-leads' => 'https://images.unsplash.com/photo-1559028012-481c04fa702d?auto=format&fit=crop&w=1200&q=80',
        'website-vs-web-app' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
        'why-react-native-is-a-good-fit-for-startup-apps' => url('assets/faisalimtiaz/app-development.png'),
        'when-laravel-is-the-right-backend-choice' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80',
        'seo-basics-for-service-businesses' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=80',
        'improve-website-speed-without-a-full-redesign' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1200&q=80',
        'what-to-include-on-a-service-page' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
        'react-native-launch-checklist-for-startups' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=1200&q=80',
        'react-native-app-cost' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
        'how-long-app-development-takes' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80',
        'seo-for-small-businesses' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=80',
        'website-vs-app-for-startups' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
        'maintenance-and-support-after-launch' => 'https://images.unsplash.com/photo-1551808525-51a94da548ce?auto=format&fit=crop&w=1200&q=80',
        'laravel-dashboard-features-small-businesses-need' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80',
        'internal-linking-for-service-business-blogs' => 'https://images.unsplash.com/photo-1516321165247-4aa89a48be28?auto=format&fit=crop&w=1200&q=80',
    ];
@endphp

<div class="container content-space-t-3 content-space-b-2 content-space-b-lg-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="mb-4">
                <a href="{{ url('/') }}" class="text-decoration-none small text-primary fw-semibold">Home</a>
                <span class="mx-2 text-muted">/</span>
                <span class="small text-muted">Blog</span>
            </div>

            <span class="text-uppercase text-primary small fw-semibold">Blog</span>
            <h1 class="display-5 mt-2 mb-4" style="font-family:Poppins, sans-serif!important; line-height: 1.1;">
                Notes on websites, apps, and SEO that support real projects.
            </h1>
            <p class="lead mb-4">
                The blog lives on faisalimtiaz.com so the articles, services, and case examples all strengthen the same domain. That keeps the authority in one place and makes it easier for readers to move from learning to enquiry.
            </p>
            <div class="d-flex flex-wrap gap-3 mb-5">
                <a class="btn btn-primary btn-transition px-5" href="{{ url('/#contact') }}">Start a project</a>
                <a class="btn btn-outline-primary btn-transition px-4" href="{{ route('services.website-development') }}">Explore services</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach ($posts as $slug => $post)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm overflow-hidden">
                    <div class="bg-light" style="height: 190px; overflow: hidden;">
                        <img
                            src="{{ $coverImages[$slug] ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80' }}"
                            alt="{{ $post['title'] }} illustration"
                            class="w-100 h-100"
                            style="object-fit: cover; display: block;"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <span class="badge bg-soft-primary text-primary">{{ $post['eyebrow'] }}</span>
                            <span class="ms-2 small text-muted">{{ $post['reading_time'] }}</span>
                        </div>
                        <h2 class="h4">{{ $post['title'] }}</h2>
                        <p class="mb-4">{{ $post['summary'] }}</p>
                        <div class="mt-auto d-flex flex-wrap gap-2">
                            @foreach (array_slice($post['tags'], 0, 3) as $tag)
                                <span class="badge bg-light text-dark border">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <a class="btn btn-link px-0 mt-3" href="{{ route('blog.post', $slug) }}">Read article <i class="bi-chevron-right small ms-1"></i></a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5 bg-light rounded-3 p-4 p-lg-5">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <h2 class="h3 mb-2">Why keep the blog here?</h2>
                <p class="mb-0">When articles live under the main domain, they can reinforce the same topical authority as the service pages instead of splitting signals across another site.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-primary btn-transition px-4" href="{{ url('/#contact') }}">Contact me</a>
            </div>
        </div>
    </div>
</div>
@endsection
