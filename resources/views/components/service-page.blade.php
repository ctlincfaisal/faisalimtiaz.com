<div class="container content-space-t-3 content-space-b-2 content-space-b-lg-3">
    <div class="mb-5">
        <a href="{{ url('/') }}" class="text-decoration-none small text-primary fw-semibold">Home</a>
        <span class="mx-2 text-muted">/</span>
        <span class="small text-muted">{{ $eyebrow }}</span>
    </div>

    <div class="row align-items-center g-5">
        <div class="col-lg-8">
            <span class="text-uppercase text-primary small fw-semibold">{{ $eyebrow }}</span>
            <h1 class="display-5 mt-2 mb-4" style="font-family:Poppins, sans-serif!important; line-height: 1.1;">
                {{ $h1 }}
            </h1>
            <p class="lead mb-4">
                {{ $intro }}
            </p>
            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach ($supporting_keywords as $keyword)
                    <span class="badge bg-light text-dark border">{{ $keyword }}</span>
                @endforeach
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <a class="btn btn-primary btn-transition px-5" href="{{ url('/#contact') }}">Start a project</a>
                <a class="btn btn-outline-primary btn-transition px-4" href="mailto:ctlinc.faisal@gmail.com?subject={{ rawurlencode($h1 . ' enquiry') }}">Email me</a>
                <a class="btn btn-link text-decoration-none px-0" href="{{ url('/') }}">Back to homepage</a>
            </div>
            <p class="small text-muted mt-2 mb-0">Prefer email? I usually reply within 3-4 business hours.</p>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 mb-3">Who this is for</h2>
                    <p class="mb-4">{{ $audience }}</p>
                    <h3 class="h6 text-uppercase text-muted mb-2">Main outcome</h3>
                    <p class="mb-0">{{ $outcome }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 rounded-3 border bg-light p-4">
        <p class="mb-0"><strong>Primary keyword:</strong> {{ $primaryKeyword ?? $eyebrow }}</p>
    </div>

    <div class="mt-5">
        <div class="w-lg-50 mx-lg-auto text-center mb-5">
            <h2>Quick answers</h2>
            <p>Short answers to the questions clients ask before they book a project.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-muted mb-2">Services offered</h3>
                        <p class="mb-0">{{ $eyebrow }} built around your project goals and launch needs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-muted mb-2">Who it is for</h3>
                        <p class="mb-0">{{ $audience }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-muted mb-2">Typical timeline</h3>
                        <p class="mb-0">{{ $timeline ?? 'Timeline depends on the scope and complexity of the project.' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-muted mb-2">Pricing and support</h3>
                        <p class="mb-0">{{ $pricing ?? 'Pricing is based on scope and complexity.' }} {{ $support ?? 'Support after launch is available when needed.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <div class="w-lg-50 mx-lg-auto text-center mb-5">
            <h2>Process</h2>
            <p>How I take the work from idea to launch.</p>
        </div>
        <div class="row g-4">
            @foreach ($process as $step)
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-primary mb-3">{{ $loop->iteration }}</span>
                            <h3 class="h5">{{ $step['title'] }}</h3>
                            <p class="mb-0">{{ $step['text'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5">
        <div class="w-lg-50 mx-lg-auto text-center mb-5">
            <h2>Benefits</h2>
            <p>Why this service helps your business move faster.</p>
        </div>
        <div class="row g-4">
            @foreach ($benefits as $benefit)
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <p class="mb-0">{{ $benefit }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5">
        <div class="w-lg-50 mx-lg-auto text-center mb-5">
            <h2>FAQ</h2>
            <p>Quick answers to common questions.</p>
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

    <div class="mt-5">
        <div class="w-lg-50 mx-lg-auto text-center mb-5">
            <h2>Related links</h2>
            <p>Explore related services or head back to the homepage.</p>
        </div>
        <div class="row g-4">
            @foreach ($related as $link)
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

    <div class="mt-5 bg-primary rounded-3 p-5 text-white">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <h2 class="h3 mb-2">Ready to talk about your project?</h2>
                <p class="mb-0">Send me a message and I’ll help you decide the right next step.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-light btn-transition px-4 me-2" href="{{ url('/#contact') }}">Contact me</a>
                <a class="btn btn-outline-light btn-transition px-4" href="mailto:ctlinc.faisal@gmail.com?subject={{ rawurlencode($h1 . ' enquiry') }}">Email me</a>
            </div>
        </div>
    </div>
</div>
