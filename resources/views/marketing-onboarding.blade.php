<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set up your workspace | MyLeadTrail</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="stylesheet" href="{{ url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#101828; --muted:#51627c; --line:#d8e1ea; --green:#10c98b; --green-dark:#008f63; --green-soft:#e3fbf1; --red:#b42318; }
        * { box-sizing:border-box; }
        body { margin:0; min-width:320px; color:var(--ink); background:#f7fbfd; font-family:Poppins,ui-sans-serif,system-ui,sans-serif; }
        a { color:inherit; text-decoration:none; } button,input,select { font:inherit; }
        .setup-shell { min-height:100vh; background:radial-gradient(circle at 82% 7%,#eaf9ff 0,rgba(234,249,255,0) 25%),linear-gradient(180deg,#fff 0%,#f7fbfd 100%); }
        .setup-header { width:min(1120px,calc(100% - 40px)); display:flex; align-items:center; justify-content:space-between; margin:0 auto; padding:23px 0; }
        .setup-logo { display:inline-flex; align-items:center; gap:9px; font-size:21px; font-weight:800; letter-spacing:-.05em; } .setup-logo i { color:var(--green); font-size:28px; transform:rotate(-10deg); }
        .setup-exit { display:inline-flex; align-items:center; gap:7px; padding:0; border:0; color:var(--muted); background:transparent; cursor:pointer; font-size:12px; font-weight:600; } .setup-exit:hover { color:var(--green-dark); }
        .setup-main { width:min(1120px,calc(100% - 40px)); margin:0 auto; padding:28px 0 70px; }
        .setup-intro { max-width:650px; margin-bottom:25px; } .setup-kicker { display:inline-flex; align-items:center; gap:8px; color:var(--green-dark); font-size:12px; font-weight:700; } .setup-kicker i { font-size:16px; } .setup-intro h1 { margin:13px 0 9px; font-size:clamp(34px,5vw,54px); line-height:1.03; letter-spacing:-.06em; } .setup-intro p { margin:0; color:var(--muted); font-size:15px; line-height:1.65; }
        .setup-progress { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin:30px 0 18px; } .setup-progress-step { display:flex; align-items:center; gap:9px; min-width:0; padding:12px 13px; border:1px solid var(--line); border-radius:10px; color:#8290a2; background:#fff; font-size:12px; font-weight:600; } .setup-progress-step.is-current { border-color:#9fe3c8; color:var(--green-dark); background:var(--green-soft); } .setup-progress-step.is-done { color:var(--green-dark); } .setup-progress-number { display:grid; place-items:center; flex:0 0 auto; width:24px; height:24px; border-radius:50%; color:#617188; background:#eef2f5; font-size:11px; } .is-current .setup-progress-number,.is-done .setup-progress-number { color:#fff; background:var(--green-dark); } .setup-progress-label { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .setup-card { padding:28px; border:1px solid var(--line); border-radius:15px; background:#fff; box-shadow:0 16px 40px rgba(26,51,72,.08); } .setup-card-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; margin-bottom:24px; } .setup-card-heading h2 { margin:0 0 6px; font-size:23px; letter-spacing:-.04em; } .setup-card-heading p { margin:0; color:var(--muted); font-size:13px; line-height:1.55; } .setup-step-count { flex:0 0 auto; color:var(--green-dark); font-size:11px; font-weight:700; }
        .setup-pricing { display:grid; grid-template-columns:repeat(3,1fr); gap:15px; } .setup-plan { position:relative; display:flex; flex-direction:column; padding:22px 20px; border:1px solid var(--line); border-radius:12px; background:#fff; } .setup-plan.is-selected { border-color:var(--green); box-shadow:0 10px 24px rgba(16,201,139,.13); } .setup-plan-badge { position:absolute; top:-12px; right:17px; padding:4px 9px; border-radius:99px; color:#075c43; background:var(--green-soft); font-size:9px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; } .setup-plan h3 { margin:0 0 7px; font-size:17px; } .setup-plan p { min-height:38px; margin:0; color:var(--muted); font-size:11px; line-height:1.5; } .setup-plan-price { margin:17px 0 16px; font-size:29px; font-weight:800; letter-spacing:-.05em; } .setup-plan-price small { color:var(--muted); font-size:10px; font-weight:500; letter-spacing:0; } .setup-plan ul { display:grid; gap:9px; margin:18px 0 0; padding:17px 0 0; border-top:1px solid #e7edf2; list-style:none; } .setup-plan li { display:flex; align-items:flex-start; gap:7px; color:var(--muted); font-size:11px; line-height:1.45; } .setup-plan li i { color:var(--green-dark); font-size:14px; }
        .setup-form { display:grid; gap:16px; } .setup-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:15px; } .setup-field { display:grid; gap:7px; } .setup-label { color:#253247; font-size:12px; font-weight:600; } .setup-input { width:100%; min-height:43px; padding:0 12px; border:1px solid var(--line); border-radius:9px; color:var(--ink); background:#fff; outline:0; font-size:13px; } .setup-input::placeholder { color:#8a99ad; } .setup-input:focus { border-color:var(--green); box-shadow:0 0 0 3px rgba(16,201,139,.14); } .setup-help { margin:0; color:#7a8aa0; font-size:11px; line-height:1.5; } .setup-error { padding:11px 13px; border:1px solid #f2b8b5; border-radius:9px; color:var(--red); background:#fff5f5; font-size:12px; line-height:1.5; }
        .setup-button-primary, .setup-button-primary:hover { color:#fff; }
        .setup-alert { display:flex; gap:9px; margin-bottom:18px; padding:11px 13px; border:1px solid #9fe3c8; border-radius:9px; color:#087f5b; background:var(--green-soft); font-size:12px; } .setup-note { margin-top:18px; color:#7a8aa0; font-size:11px; line-height:1.5; }
        @media (max-width:800px) { .setup-pricing { grid-template-columns:1fr; } .setup-plan p { min-height:auto; } .setup-progress { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:520px) { .setup-header,.setup-main { width:min(100% - 30px,520px); } .setup-header { padding:17px 0; } .setup-main { padding-top:20px; } .setup-card { padding:20px 17px; } .setup-card-heading { display:block; } .setup-step-count { display:block; margin-top:10px; } .setup-grid { grid-template-columns:1fr; } .setup-progress-step { padding:10px; } .setup-exit span { display:none; } .setup-actions { align-items:stretch; flex-direction:column-reverse; } .setup-actions .setup-button { width:100%; } }
    </style>
</head>
<body>
<div class="setup-shell">
    <header class="setup-header">
        <a class="setup-logo" href="{{ route('marketing.landing') }}"><i class="bi bi-send-fill"></i><span>MyLeadTrail</span></a>
        <form action="{{ route('marketing.onboarding.finish') }}" method="POST">
            @csrf
            <button class="setup-exit" type="submit"><i class="bi bi-arrow-left"></i><span>Skip setup and open workspace</span></button>
        </form>
    </header>

    <main class="setup-main">
        <div class="setup-intro">
            <div class="setup-kicker"><i class="bi bi-stars"></i> Welcome to MyLeadTrail</div>
            <h1>Let’s set up your workspace.</h1>
            <p>These four quick steps help you get ready to organize leads and send your first campaign. You can skip any step and finish it later from Settings.</p>
        </div>

        <div class="setup-progress" aria-label="Setup progress">
            @foreach ([1 => 'Choose a plan', 2 => 'Add SMTP', 3 => 'Connect website', 4 => 'Add signature'] as $number => $labelText)
                <a class="setup-progress-step {{ $step === $number ? 'is-current' : '' }} {{ $step > $number ? 'is-done' : '' }}" href="{{ route('marketing.onboarding', ['step' => $number]) }}">
                    <span class="setup-progress-number">@if ($step > $number)<i class="bi bi-check2"></i>@else{{ $number }}@endif</span><span class="setup-progress-label">{{ $labelText }}</span>
                </a>
            @endforeach
        </div>

        @if (session('marketing_success'))
            <div class="setup-alert"><i class="bi bi-check-circle-fill"></i><span>{{ session('marketing_success') }}</span></div>
        @endif
        @if (session('marketing_onboarding_error'))
            <div class="setup-error">{{ session('marketing_onboarding_error') }}</div>
        @endif

        <section class="setup-card">
            @if ($step === 1)
                <div class="setup-card-heading"><div><h2>Choose your plan</h2><p>Start with the free plan today. You can upgrade when you need more capacity.</p></div><span class="setup-step-count">Step 1 of 4</span></div>
                <form class="setup-form" action="{{ route('marketing.onboarding.plan') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="free">
                    <div class="setup-pricing">
                        <article class="setup-plan is-selected"><h3>Starter</h3><p>For getting your first outreach workflow organized.</p><div class="setup-plan-price">$0 <small>/ forever</small></div><button class="setup-button setup-button-primary" type="submit">Use free plan <i class="bi bi-arrow-right"></i></button><ul><li><i class="bi bi-check2-circle"></i>Up to 100 leads</li><li><i class="bi bi-check2-circle"></i>One website and signature</li><li><i class="bi bi-check2-circle"></i>One SMTP service</li></ul></article>
                        <article class="setup-plan"><span class="setup-plan-badge">Most popular</span><h3>Growth</h3><p>For consistent campaigns and growing lead lists.</p><div class="setup-plan-price">$12 <small>/ month</small></div><button class="setup-button setup-button-primary" type="button" data-paddle-plan="growth">Choose Growth <i class="bi bi-arrow-right"></i></button><ul><li><i class="bi bi-check2-circle"></i>More leads and categories</li><li><i class="bi bi-check2-circle"></i>Multiple websites</li><li><i class="bi bi-check2-circle"></i>More sending services</li></ul></article>
                        <article class="setup-plan"><h3>Scale</h3><p>For agencies and teams managing outreach at volume.</p><div class="setup-plan-price">$49 <small>/ month</small></div><button class="setup-button" type="button" data-paddle-plan="scale">Choose Scale <i class="bi bi-arrow-right"></i></button><ul><li><i class="bi bi-check2-circle"></i>Team workflows</li><li><i class="bi bi-check2-circle"></i>More connected websites</li><li><i class="bi bi-check2-circle"></i>Priority reporting</li></ul></article>
                    </div>
                    <div class="setup-actions"><button class="setup-button setup-skip" type="submit" name="skip" value="1">Skip for now</button><span></span></div>
                </form>
            @elseif ($step === 2)
                <div class="setup-card-heading"><div><h2>Add your first SMTP service</h2><p>Connect the mailbox you want MyLeadTrail to use when sending campaigns.</p></div><span class="setup-step-count">Step 2 of 4</span></div>
                <form class="setup-form" action="{{ route('marketing.onboarding.smtp') }}" method="POST">
                    @csrf
                    <div class="setup-grid"><div class="setup-field"><label class="setup-label" for="setupServiceName">Service name</label><input id="setupServiceName" class="setup-input" name="name" value="{{ old('name') }}" placeholder="My business SMTP" required></div><div class="setup-field"><label class="setup-label" for="setupFromName">From name</label><input id="setupFromName" class="setup-input" name="from_name" value="{{ old('from_name') }}" placeholder="Your name"></div></div>
                    <div class="setup-grid"><div class="setup-field"><label class="setup-label" for="setupFromEmail">From email</label><input id="setupFromEmail" class="setup-input" type="email" name="from_email" value="{{ old('from_email') }}" placeholder="hello@example.com" required></div><div class="setup-field"><label class="setup-label" for="setupSmtpHost">SMTP host</label><input id="setupSmtpHost" class="setup-input" name="smtp_host" value="{{ old('smtp_host') }}" placeholder="smtp.example.com" required></div></div>
                    <div class="setup-grid"><div class="setup-field"><label class="setup-label" for="setupSmtpPort">SMTP port</label><input id="setupSmtpPort" class="setup-input" type="number" name="smtp_port" value="{{ old('smtp_port', 587) }}" min="1" max="65535" required></div><div class="setup-field"><label class="setup-label" for="setupSmtpEncryption">Encryption</label><select id="setupSmtpEncryption" class="setup-input" name="smtp_encryption"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="none">None</option></select></div></div>
                    <div class="setup-grid"><div class="setup-field"><label class="setup-label" for="setupSmtpUsername">SMTP username</label><input id="setupSmtpUsername" class="setup-input" name="smtp_username" value="{{ old('smtp_username') }}" placeholder="Usually your email address"></div><div class="setup-field"><label class="setup-label" for="setupSmtpPassword">SMTP password</label><input id="setupSmtpPassword" class="setup-input" type="password" name="smtp_password" required autocomplete="new-password"></div></div>
                    @error('name')<div class="setup-error">{{ $message }}</div>@enderror @error('from_email')<div class="setup-error">{{ $message }}</div>@enderror @error('smtp_host')<div class="setup-error">{{ $message }}</div>@enderror @error('smtp_password')<div class="setup-error">{{ $message }}</div>@enderror
                    <p class="setup-help">Your SMTP password is encrypted before it is stored. You can add more sending services later from Settings.</p>
                    <div class="setup-actions"><button class="setup-button setup-skip" type="submit" name="skip" value="1" formnovalidate>Skip for now</button><button class="setup-button setup-button-primary" type="submit">Save SMTP and continue <i class="bi bi-arrow-right"></i></button></div>
                </form>
            @elseif ($step === 3)
                <div class="setup-card-heading"><div><h2>Connect a website</h2><p>Add the site you want to understand through MyLeadTrail analytics.</p></div><span class="setup-step-count">Step 3 of 4</span></div>
                <form class="setup-form" action="{{ route('marketing.onboarding.website') }}" method="POST">
                    @csrf
                    <div class="setup-grid"><div class="setup-field"><label class="setup-label" for="setupWebsiteName">Website name</label><input id="setupWebsiteName" class="setup-input" name="name" value="{{ old('name') }}" placeholder="My website" required></div><div class="setup-field"><label class="setup-label" for="setupWebsiteDomain">Domain</label><input id="setupWebsiteDomain" class="setup-input" name="domain" value="{{ old('domain') }}" placeholder="example.com" required></div></div>
                    @error('name')<div class="setup-error">{{ $message }}</div>@enderror @error('domain')<div class="setup-error">{{ $message }}</div>@enderror
                    <p class="setup-help">Use the domain only. After connecting, Settings will provide the tracking snippet to add to your website.</p>
                    <div class="setup-actions"><button class="setup-button setup-skip" type="submit" name="skip" value="1" formnovalidate>Skip for now</button><button class="setup-button setup-button-primary" type="submit">Connect and continue <i class="bi bi-arrow-right"></i></button></div>
                </form>
            @else
                <div class="setup-card-heading"><div><h2>Add an email signature</h2><p>Upload the signature image you want included with your outgoing emails.</p></div><span class="setup-step-count">Step 4 of 4</span></div>
                <form class="setup-form" action="{{ route('marketing.onboarding.signature') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="setup-field"><label class="setup-label" for="setupSignatureName">Signature name</label><input id="setupSignatureName" class="setup-input" name="name" value="{{ old('name') }}" placeholder="My email signature" required></div>
                    <div class="setup-field"><label class="setup-label" for="setupSignatureImage">Signature image</label><input id="setupSignatureImage" class="setup-input" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required><p class="setup-help">JPG, PNG, GIF, or WebP up to 5 MB. It will be used for email content and open tracking.</p></div>
                    @error('name')<div class="setup-error">{{ $message }}</div>@enderror @error('image')<div class="setup-error">{{ $message }}</div>@enderror
                    <div class="setup-actions"><button class="setup-button setup-skip" type="submit" name="skip" value="1" formnovalidate>Skip and finish</button><button class="setup-button setup-button-primary" type="submit">Save signature and finish <i class="bi bi-check2"></i></button></div>
                </form>
            @endif
            @if ($step < 4)
                <p class="setup-note"><i class="bi bi-info-circle"></i> You can revisit any skipped setup step later from Settings.</p>
            @endif
        </section>
    </main>
</div>
<script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
<script>
    (() => {
        const paddleToken = @json(config('marketing.paddle.client_token'));
        const prices = @json(config('marketing.paddle.prices'));
        const userEmail = @json(auth()->user()?->email);
        const userId = @json((string) (auth()->id() ?? ''));
        const accountId = @json((string) (auth()->user()?->marketing_account_id ?? ''));

        if (!paddleToken || !window.Paddle) {
            return;
        }

        if (@json(config('marketing.paddle.environment', 'sandbox')) === 'sandbox') {
            Paddle.Environment.set('sandbox');
        }

        Paddle.Initialize({ token: paddleToken });

        document.querySelectorAll('[data-paddle-plan]').forEach((button) => {
            button.addEventListener('click', () => {
                const plan = button.dataset.paddlePlan;
                const priceId = prices[plan];
                if (!priceId) {
                    return;
                }

                Paddle.Checkout.open({
                    items: [{ priceId, quantity: 1 }],
                    customer: userEmail ? { email: userEmail } : undefined,
                    customData: { user_id: userId, marketing_account_id: accountId, plan },
                    settings: {
                        displayMode: 'overlay',
                        variant: 'one-page',
                        successUrl: @json(route('marketing.onboarding', ['step' => 2, 'billing' => 'success'])),
                    },
                });
            });
        });
    })();
</script>
</body>
</html>
