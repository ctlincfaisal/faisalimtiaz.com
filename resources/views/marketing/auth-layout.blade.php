<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | MyLeadTrail</title>
    <meta name="description" content="@yield('meta_description')">
    <meta name="robots" content="noindex,nofollow">
    <link rel="canonical" href="@yield('canonical')">
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <link rel="stylesheet" href="{{ url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --auth-ink:#101828; --auth-muted:#51627c; --auth-line:#d8e1ea; --auth-green:#10c98b; --auth-green-dark:#008f63; --auth-green-soft:#e3fbf1; --auth-red:#c24142; }
        * { box-sizing: border-box; }
        body { margin: 0; min-width: 320px; color: var(--auth-ink); background: #fbfdff; font-family: Poppins, ui-sans-serif, system-ui, sans-serif; }
        a { color: inherit; text-decoration: none; } button, input { font: inherit; }
        .auth-shell { min-height:100vh; background:radial-gradient(circle at 83% 17%,#eaf9ff 0,rgba(234,249,255,0) 24%),linear-gradient(180deg,#fff 0%,#f7fbfd 100%); }
        .auth-header { width:min(1120px,calc(100% - 40px)); display:flex; align-items:center; justify-content:space-between; margin:0 auto; padding:23px 0; }
        .auth-logo { display:inline-flex; align-items:center; gap:9px; font-size:21px; font-weight:800; letter-spacing:-.05em; }
        .auth-logo i { color:var(--auth-green); font-size:28px; transform:rotate(-10deg); }
        .auth-back { display:inline-flex; align-items:center; gap:7px; color:var(--auth-muted); font-size:12px; font-weight:600; }
        .auth-back:hover { color:var(--auth-green-dark); }
        .auth-main { width:min(1120px,calc(100% - 40px)); display:grid; grid-template-columns:minmax(0,1fr) minmax(360px,455px); align-items:center; gap:90px; min-height:calc(100vh - 86px); margin:0 auto; padding:42px 0 70px; }
        .auth-intro { max-width:505px; } .auth-kicker { display:inline-flex; align-items:center; gap:8px; color:var(--auth-green-dark); font-size:12px; font-weight:700; } .auth-kicker i { font-size:16px; }
        .auth-intro h1 { margin:15px 0; font-size:clamp(38px,5vw,60px); line-height:1.02; letter-spacing:-.065em; } .auth-intro>p { max-width:450px; margin:0; color:var(--auth-muted); font-size:16px; line-height:1.7; }
        .auth-benefits { display:grid; gap:13px; margin-top:30px; } .auth-benefit { display:flex; align-items:center; gap:11px; color:#253247; font-size:13px; font-weight:600; } .auth-benefit i { display:grid; place-items:center; width:30px; height:30px; border-radius:9px; color:var(--auth-green-dark); background:var(--auth-green-soft); font-size:16px; }
        .auth-preview { max-width:425px; margin-top:35px; padding:17px; border:1px solid var(--auth-line); border-radius:15px; background:#fff; box-shadow:0 18px 45px rgba(26,51,72,.09); } .auth-preview-top { display:flex; align-items:center; justify-content:space-between; padding-bottom:13px; border-bottom:1px solid #e9eff4; color:var(--auth-muted); font-size:11px; } .auth-preview-dots { display:flex; gap:5px; } .auth-preview-dots span { width:7px; height:7px; border-radius:50%; background:#ff665d; } .auth-preview-dots span:nth-child(2) { background:#f4bd45; } .auth-preview-dots span:nth-child(3) { background:#3dc75a; } .auth-preview-row { display:grid; grid-template-columns:1.1fr .8fr .7fr; gap:9px; margin-top:13px; } .auth-preview-row span { height:9px; border-radius:99px; background:#e9f8f2; } .auth-preview-row span:nth-child(2) { background:#e8f2fb; } .auth-preview-row span:nth-child(3) { background:#eef1f5; }
        .auth-card { width:100%; padding:31px; border:1px solid var(--auth-line); border-radius:15px; background:#fff; box-shadow:0 18px 45px rgba(26,51,72,.1); } .auth-card-heading { margin-bottom:25px; } .auth-card-heading h2 { margin:0 0 7px; font-size:26px; line-height:1.15; letter-spacing:-.045em; } .auth-card-heading p { margin:0; color:var(--auth-muted); font-size:13px; line-height:1.55; }
        .auth-alert { margin-bottom:18px; padding:11px 13px; border:1px solid #f4c8c8; border-radius:9px; color:var(--auth-red); background:#fff5f5; font-size:12px; line-height:1.5; } .auth-form { display:grid; gap:17px; } .auth-field { display:grid; gap:7px; } .auth-label { color:#253247; font-size:12px; font-weight:600; }
        .auth-input { width:100%; min-height:43px; padding:0 12px; border:1px solid var(--auth-line); border-radius:9px; color:var(--auth-ink); background:#fff; outline:none; font-size:13px; transition:border-color .2s ease,box-shadow .2s ease; } .auth-input::placeholder { color:#8a99ad; } .auth-input:focus { border-color:var(--auth-green); box-shadow:0 0 0 3px rgba(16,201,139,.14); } .auth-input.auth-invalid { border-color:#df6a6a; } .auth-error { color:var(--auth-red); font-size:11px; line-height:1.4; }
        .auth-submit { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; margin-top:2px; padding:0 17px; border:1px solid var(--auth-green); border-radius:9px; color:#06281d; background:var(--auth-green); box-shadow:0 10px 22px rgba(16,201,139,.2); cursor:pointer; font-size:13px; font-weight:700; transition:transform .2s ease,background .2s ease,box-shadow .2s ease; } .auth-submit:hover { background:#08b97c; box-shadow:0 13px 28px rgba(16,201,139,.28); transform:translateY(-1px); } .auth-switch { margin:22px 0 0; color:var(--auth-muted); text-align:center; font-size:12px; } .auth-switch a { color:var(--auth-green-dark); font-weight:700; } .auth-switch a:hover { text-decoration:underline; } .auth-legal { margin:16px 0 0; color:#7a8aa0; text-align:center; font-size:10px; line-height:1.5; }
        @media (max-width:850px) { .auth-main { grid-template-columns:1fr; gap:42px; min-height:auto; padding-top:35px; } .auth-intro { max-width:600px; } .auth-preview { display:none; } .auth-card { max-width:520px; } }
        @media (max-width:520px) { .auth-header,.auth-main { width:min(100% - 30px,455px); } .auth-header { padding:17px 0; } .auth-main { padding-top:24px; padding-bottom:38px; } .auth-intro h1 { font-size:40px; } .auth-card { padding:23px 19px; } .auth-back span { display:none; } }
    </style>
</head>
<body>
<div class="auth-shell">
    <header class="auth-header">
        <a class="auth-logo" href="{{ route('marketing.landing') }}"><i class="bi bi-send-fill"></i><span>MyLeadTrail</span></a>
        <a class="auth-back" href="{{ route('studio') }}"><i class="bi bi-arrow-left"></i><span>Back to Faisal Imtiaz</span></a>
    </header>
    <main class="auth-main">
        <section class="auth-intro" aria-label="MyLeadTrail benefits">
            <div class="auth-kicker"><i class="bi bi-stars"></i> Better outreach, clearer results</div>
            <h1>Build relationships that move forward.</h1>
            <p>Bring your leads, campaigns, follow-ups, and website insights together in one thoughtful workspace.</p>
            <div class="auth-benefits">
                <div class="auth-benefit"><i class="bi bi-people"></i><span>Organize leads with lists and categories</span></div>
                <div class="auth-benefit"><i class="bi bi-send-check"></i><span>Create personal campaigns and sequences</span></div>
                <div class="auth-benefit"><i class="bi bi-bar-chart"></i><span>Understand what happens after you send</span></div>
            </div>
            <div class="auth-preview" aria-hidden="true">
                <div class="auth-preview-top"><span>Email campaign overview</span><div class="auth-preview-dots"><span></span><span></span><span></span></div></div>
                <div class="auth-preview-row"><span></span><span></span><span></span></div><div class="auth-preview-row"><span></span><span></span><span></span></div><div class="auth-preview-row"><span></span><span></span><span></span></div>
            </div>
        </section>
        <section class="auth-card">@yield('auth_content')</section>
    </main>
</div>
</body>
</html>
