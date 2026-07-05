@php
    $faqId = $id ?? 'faq';
@endphp

<div class="container content-space-2 content-space-lg-3" id="{{ $faqId }}">
    <div class="w-md-75 w-lg-50 text-center mx-md-auto mb-5">
        <h2>{{ $heading ?? 'FAQ' }}</h2>
        <p>{{ $intro ?? 'Quick answers to common questions.' }}</p>
    </div>

    <div class="row g-4">
        @foreach ($faqs as $faq)
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
