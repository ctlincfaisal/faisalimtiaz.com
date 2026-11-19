<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyLeadTrail — Better outreach, clearer results.</title>
    <meta name="description" content="MyLeadTrail helps teams organize leads, run thoughtful email campaigns, and understand what happens next.">
    <link rel="stylesheet" href="{{ url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #101828;
            --muted: #51627c;
            --line: #d8e1ea;
            --soft: #f4f8fb;
            --green: #10c98b;
            --green-dark: #008f63;
            --green-soft: #e3fbf1;
            --blue: #2296dd;
            --shadow: 0 16px 40px rgba(26, 51, 72, .09);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; color: var(--ink); background: #fff; font-family: Poppins, ui-sans-serif, system-ui, sans-serif; }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; }
        .lp-shell { overflow: hidden; }
        .lp-container { width: min(1180px, calc(100% - 48px)); margin: 0 auto; }
        .lp-header { position: sticky; top: 0; z-index: 20; border-bottom: 1px solid rgba(216,225,234,.85); background: rgba(255,255,255,.92); backdrop-filter: blur(14px); }
        .lp-nav { display: flex; align-items: center; justify-content: space-between; min-height: 76px; gap: 28px; }
        .lp-logo { display: inline-flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 800; letter-spacing: -.05em; }
        .lp-logo i { color: var(--green); font-size: 29px; transform: rotate(-10deg); }
        .lp-links { display: flex; align-items: center; gap: 32px; color: #172033; font-size: 13px; font-weight: 600; }
        .lp-links a:hover { color: var(--green-dark); }
        .lp-actions { display: flex; align-items: center; gap: 10px; }
        .lp-button { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 42px; padding: 0 20px; border: 1px solid var(--line); border-radius: 11px; font-size: 13px; font-weight: 700; transition: transform .2s ease, box-shadow .2s ease, background .2s ease; }
        .lp-button:hover { transform: translateY(-2px); }
        .lp-button-outline { background: #fff; }
        .lp-button-primary { border-color: var(--green); background: var(--green); box-shadow: 0 10px 22px rgba(16,201,139,.2); }
        .lp-button-primary, .lp-button-primary:hover { color: #fff; }
        .lp-button-primary:hover { background: #08b97c; box-shadow: 0 14px 28px rgba(16,201,139,.3); }
        .lp-menu-button { display: none; width: 42px; height: 42px; border: 1px solid var(--line); border-radius: 10px; background: #fff; }

        .lp-hero { padding: 82px 0 62px; background: radial-gradient(circle at 76% 28%, #eaf9ff 0, rgba(234,249,255,0) 30%), linear-gradient(180deg, #fff 0%, #fbfdff 100%); }
        .lp-hero-grid { display: grid; grid-template-columns: .92fr 1.08fr; align-items: center; gap: 58px; }
        .lp-eyebrow { display: inline-flex; align-items: center; gap: 8px; color: var(--green-dark); font-size: 13px; font-weight: 700; }
        .lp-eyebrow i { font-size: 17px; }
        .lp-hero h1 { max-width: 620px; margin: 16px 0 18px; font-size: clamp(42px, 5.7vw, 72px); line-height: .99; letter-spacing: -.065em; }
        .lp-hero-copy { max-width: 560px; color: var(--muted); font-size: 17px; line-height: 1.7; }
        .lp-hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
        .lp-hero-proof { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 34px; color: var(--muted); font-size: 12px; font-weight: 600; }
        .lp-hero-proof span { display: inline-flex; align-items: center; gap: 8px; }
        .lp-hero-proof i { color: var(--green); font-size: 17px; }
        .lp-hero-visual { position: relative; min-height: 390px; }
        .lp-cloud { position: absolute; inset: 35px 12px 22px; border-radius: 48% 52% 44% 56%; background: linear-gradient(135deg, #e8f8ff, #f0fffa); transform: rotate(-5deg); }
        .lp-envelope { position: absolute; display: grid; place-items: center; width: 190px; height: 122px; border-radius: 14px; box-shadow: 0 20px 38px rgba(37,104,142,.18); transform: rotate(-10deg); }
        .lp-envelope::before, .lp-envelope::after { content: ''; position: absolute; inset: 0; border-radius: inherit; }
        .lp-envelope::before { clip-path: polygon(0 0, 50% 58%, 100% 0, 100% 100%, 0 100%); background: rgba(255,255,255,.34); }
        .lp-envelope::after { clip-path: polygon(0 0, 50% 55%, 100% 0, 50% 73%); background: rgba(255,255,255,.46); }
        .lp-envelope i { position: relative; z-index: 1; color: rgba(255,255,255,.8); font-size: 30px; }
        .lp-envelope-blue { top: 92px; left: 74px; background: linear-gradient(145deg, #9ed9ff, #5da7e8); }
        .lp-envelope-green { top: 145px; right: 72px; background: linear-gradient(145deg, #7bf0c1, #13c989); transform: rotate(6deg); }
        .lp-envelope-white { right: 15px; bottom: 38px; width: 144px; height: 90px; background: linear-gradient(145deg, #fff, #c8dae8); transform: rotate(9deg); opacity: .9; }
        .lp-avatar { position: absolute; z-index: 2; display: grid; place-items: center; width: 58px; height: 58px; border: 5px solid #fff; border-radius: 50%; color: #0d6b55; background: #b9f4dd; font-size: 16px; font-weight: 800; box-shadow: 0 8px 20px rgba(33,80,95,.15); }
        .lp-avatar-a { top: 34px; right: 214px; } .lp-avatar-b { left: 26px; bottom: 112px; background: #bce4ff; color: #17699c; } .lp-avatar-c { right: 15px; top: 118px; background: #d9d8fb; color: #4d4d9e; }
        .lp-spark { position: absolute; color: var(--green); font-size: 29px; font-weight: 800; }
        .lp-spark-a { top: 80px; right: 115px; transform: rotate(15deg); } .lp-spark-b { left: 174px; bottom: 62px; transform: rotate(-15deg); }

        .lp-strip { border-top: 1px solid #e4ebf1; border-bottom: 1px solid #e4ebf1; background: #fff; }
        .lp-strip-grid { display: grid; grid-template-columns: repeat(3, 1fr); padding: 22px 0; }
        .lp-strip-item { display: flex; align-items: center; justify-content: center; gap: 12px; color: #1c2737; font-size: 14px; font-weight: 700; }
        .lp-strip-item + .lp-strip-item { border-left: 1px solid var(--line); }
        .lp-strip-item i { color: var(--green); font-size: 23px; }

        .lp-section { padding: 106px 0; }
        .lp-section-soft { background: var(--soft); }
        .lp-two-col { display: grid; grid-template-columns: .86fr 1.14fr; align-items: center; gap: 82px; }
        .lp-two-col.reverse { grid-template-columns: 1.14fr .86fr; }
        .lp-section h2 { margin: 0 0 13px; font-size: clamp(32px, 4vw, 48px); line-height: 1.04; letter-spacing: -.055em; }
        .lp-section p { margin: 0; color: var(--muted); font-size: 16px; line-height: 1.7; }
        .lp-kicker { margin-bottom: 10px; color: var(--green-dark); font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .lp-lead-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .lp-lead-card { padding: 22px 16px; border: 1px solid var(--line); border-radius: 15px; background: #fff; box-shadow: var(--shadow); text-align: center; }
        .lp-lead-avatar { display: grid; place-items: center; width: 58px; height: 58px; margin: 0 auto 12px; border-radius: 50%; background: #c9f5e4; color: var(--green-dark); font-weight: 800; }
        .lp-lead-card:nth-child(2) .lp-lead-avatar { background: #cce8ff; color: #186fa7; } .lp-lead-card:nth-child(3) .lp-lead-avatar { background: #dddafa; color: #5752a0; }
        .lp-lead-card strong { display: block; font-size: 14px; } .lp-lead-card span { display: inline-block; margin-top: 8px; padding: 5px 10px; border-radius: 999px; background: var(--green-soft); color: var(--green-dark); font-size: 11px; font-weight: 700; }
        .lp-lead-card:nth-child(2) span { background: #e0f2ff; color: #176da4; } .lp-lead-card:nth-child(3) span { background: #eeeaff; color: #6254aa; }
        .lp-editor { overflow: hidden; border: 1px solid var(--line); border-radius: 16px; background: #fff; box-shadow: var(--shadow); }
        .lp-editor-bar { display: flex; gap: 7px; padding: 14px 16px; border-bottom: 1px solid #e6edf2; background: #f8fafc; } .lp-editor-bar i { width: 10px; height: 10px; border-radius: 50%; background: #ff5d57; } .lp-editor-bar i:nth-child(2) { background: #fbc04f; } .lp-editor-bar i:nth-child(3) { background: #39c65b; }
        .lp-editor-body { padding: 24px; color: #24334a; font-size: 14px; line-height: 2; } .lp-editor-subject { padding-bottom: 12px; border-bottom: 1px solid #e2e9ef; font-weight: 700; } .lp-editor-subject span { margin-right: 24px; color: #718096; font-weight: 500; }
        .lp-feature-list { display: grid; gap: 18px; margin-top: 25px; } .lp-feature { display: flex; align-items: flex-start; gap: 14px; } .lp-feature i { flex: 0 0 auto; display: grid; place-items: center; width: 40px; height: 40px; border-radius: 11px; background: var(--green-soft); color: var(--green-dark); font-size: 20px; } .lp-feature strong { display: block; margin-bottom: 3px; font-size: 14px; } .lp-feature span { color: var(--muted); font-size: 13px; line-height: 1.55; }

        .lp-band { padding: 45px 0; background: #f3f6fa; } .lp-band-title { margin-bottom: 28px; text-align: center; font-size: 30px; letter-spacing: -.04em; }
        .lp-three-col { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; } .lp-work-card { display: flex; gap: 16px; padding: 24px; border: 1px solid var(--line); border-radius: 15px; background: #fff; box-shadow: 0 10px 24px rgba(30,56,78,.06); } .lp-work-card i { color: var(--green); font-size: 30px; } .lp-work-card strong { display: block; margin-bottom: 4px; font-size: 16px; } .lp-work-card p { font-size: 13px; line-height: 1.5; }
        .lp-chart { padding: 22px; border: 1px solid var(--line); border-radius: 16px; background: #fff; box-shadow: var(--shadow); } .lp-chart-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; font-size: 13px; font-weight: 700; } .lp-chart-select { padding: 7px 11px; border: 1px solid var(--line); border-radius: 8px; color: #334155; background: #fff; }
        .lp-chart svg { display: block; width: 100%; height: auto; } .lp-checks { display: grid; gap: 16px; } .lp-check { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; font-weight: 600; } .lp-check i { color: var(--green); font-size: 19px; }

        .lp-control-band { background: #fff; } .lp-control-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; } .lp-control { display: flex; gap: 14px; padding: 15px 24px; } .lp-control + .lp-control { border-left: 1px solid var(--line); } .lp-control i { color: var(--green); font-size: 24px; } .lp-control strong { display: block; margin-bottom: 3px; font-size: 14px; } .lp-control span { color: var(--muted); font-size: 12px; line-height: 1.45; }
        .lp-pricing-head { max-width: 650px; margin: 0 auto 42px; text-align: center; } .lp-pricing-head h2 { margin-bottom: 13px; } .lp-pricing-head p { margin: 0 auto; max-width: 570px; }
        .lp-pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); align-items: stretch; gap: 18px; }
        .lp-price-card { position: relative; display: flex; flex-direction: column; padding: 28px 25px; border: 1px solid var(--line); border-radius: 15px; background: #fff; box-shadow: var(--shadow); }
        .lp-price-card-popular { border-color: var(--green); box-shadow: 0 16px 38px rgba(16,201,139,.16); }
        .lp-price-badge { position: absolute; top: -13px; left: 24px; padding: 5px 11px; border-radius: 999px; color: #075c43; background: var(--green-soft); font-size: 10px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .lp-price-card h3 { margin: 0 0 7px; font-size: 20px; letter-spacing: -.035em; } .lp-price-description { min-height: 42px; color: var(--muted); font-size: 12px; line-height: 1.55; }
        .lp-price { display: flex; align-items: baseline; gap: 5px; margin: 22px 0 20px; } .lp-price strong { font-size: 39px; line-height: 1; letter-spacing: -.06em; } .lp-price span { color: var(--muted); font-size: 12px; }
        .lp-price-card .lp-button { width: 100%; } .lp-price-card .lp-button-outline:hover { border-color: var(--green); background: var(--green-soft); }
        .lp-price-rewards { margin: 25px 0 0; padding-top: 21px; border-top: 1px solid #e5edf2; color: #243247; font-size: 12px; font-weight: 700; } .lp-price-rewards ul { display: grid; gap: 11px; margin: 15px 0 0; padding: 0; list-style: none; } .lp-price-rewards li { display: flex; align-items: flex-start; gap: 8px; color: var(--muted); font-size: 12px; font-weight: 500; line-height: 1.45; } .lp-price-rewards li i { flex: 0 0 auto; color: var(--green-dark); font-size: 15px; }
        .lp-pricing-note { margin: 24px 0 0; color: #718096; text-align: center; font-size: 11px; }
        .lp-faq-grid { display: grid; grid-template-columns: .7fr 1.3fr; gap: 70px; align-items: start; } .lp-faq-grid h2 { max-width: 380px; } .lp-faqs { display: grid; gap: 9px; } .lp-faq { border: 1px solid var(--line); border-radius: 10px; background: #fff; } .lp-faq summary { cursor: pointer; list-style: none; padding: 15px 17px; font-size: 14px; font-weight: 700; } .lp-faq summary::-webkit-details-marker { display: none; } .lp-faq summary::after { float: right; content: '+'; color: var(--green-dark); font-size: 19px; line-height: 1; } .lp-faq[open] summary::after { content: '−'; } .lp-faq p { padding: 0 17px 16px; font-size: 13px; }
        .lp-cta { padding: 27px 30px; border-radius: 14px; color: #fff; background: #17202b; box-shadow: 0 18px 35px rgba(23,32,43,.18); } .lp-cta-inner { display: flex; align-items: center; justify-content: space-between; gap: 22px; } .lp-cta h2 { margin: 0 0 4px; font-size: 26px; letter-spacing: -.04em; } .lp-cta p { color: #c6d0da; font-size: 13px; } .lp-cta .lp-button { border-color: var(--green); }
        .lp-footer { padding: 34px 0 44px; } .lp-footer-grid { display: grid; grid-template-columns: 1.5fr repeat(3, 1fr); gap: 35px; } .lp-footer p, .lp-footer a { color: var(--muted); font-size: 12px; line-height: 1.7; } .lp-footer h3 { margin: 0 0 10px; font-size: 13px; } .lp-footer-brand p { max-width: 240px; }
        @media (max-width: 900px) { .lp-links { display: none; } .lp-menu-button { display: block; } .lp-nav.is-open .lp-links { position: absolute; top: 70px; left: 24px; right: 24px; display: grid; gap: 0; padding: 10px; border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: var(--shadow); } .lp-nav.is-open .lp-links a { padding: 13px; } .lp-hero-grid, .lp-two-col, .lp-two-col.reverse, .lp-faq-grid { grid-template-columns: 1fr; gap: 48px; } .lp-hero { padding-top: 58px; } .lp-hero-visual { min-height: 330px; } .lp-three-col, .lp-control-grid, .lp-pricing-grid { grid-template-columns: 1fr 1fr; } .lp-pricing-grid .lp-price-card:last-child { grid-column: 1 / -1; max-width: calc(50% - 9px); justify-self: center; width: 100%; } .lp-control:nth-child(3) { border-left: 0; } .lp-footer-grid { grid-template-columns: 1.5fr 1fr 1fr; } }
        @media (max-width: 600px) { .lp-container { width: min(100% - 32px, 520px); } .lp-actions .lp-button-outline { display: none; } .lp-hero h1 { font-size: 45px; } .lp-strip-grid, .lp-three-col, .lp-control-grid, .lp-pricing-grid, .lp-footer-grid { grid-template-columns: 1fr; } .lp-pricing-grid .lp-price-card:last-child { grid-column: auto; max-width: none; } .lp-strip-item { justify-content: flex-start; padding: 12px 0; } .lp-strip-item + .lp-strip-item, .lp-control + .lp-control { border-left: 0; border-top: 1px solid var(--line); } .lp-lead-cards { gap: 8px; } .lp-lead-card { padding: 15px 8px; } .lp-lead-card strong { font-size: 12px; } .lp-envelope { transform: scale(.78) rotate(-10deg); } .lp-envelope-green { transform: scale(.78) rotate(6deg); } .lp-envelope-white { transform: scale(.7) rotate(9deg); } .lp-avatar-a { right: 120px; } .lp-cta-inner { align-items: flex-start; flex-direction: column; } }
    </style>
</head>
<body>
<div class="lp-shell">
    <header class="lp-header">
        <div class="lp-container">
            <nav class="lp-nav" id="lpNav" aria-label="MyLeadTrail navigation">
                <a class="lp-logo" href="{{ route('studio') }}"><i class="bi bi-send-fill"></i><span>MyLeadTrail</span></a>
                <div class="lp-links">
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="#why-leadpilot">Why MyLeadTrail</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#faq">FAQ</a>
                </div>
                <div class="lp-actions">
                    <a class="lp-button lp-button-outline" href="{{ route('marketing.login') }}">Log in</a>
                    <a class="lp-button lp-button-primary" href="{{ route('marketing.register') }}">Get started</a>
                    <button class="lp-menu-button" id="lpMenuButton" type="button" aria-label="Open navigation" aria-expanded="false"><i class="bi bi-list"></i></button>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="lp-hero">
            <div class="lp-container lp-hero-grid">
                <div>
                    <div class="lp-eyebrow"><i class="bi bi-stars"></i> One connected workspace for better outreach</div>
                    <h1>Better outreach.<br>Clearer results.</h1>
                    <p class="lp-hero-copy">Manage your leads, send personalized campaigns, and connect every follow-up to what happens next.</p>
                    <div class="lp-hero-actions"><a class="lp-button lp-button-primary" href="{{ route('marketing.register') }}">Get started <i class="bi bi-arrow-right"></i></a><a class="lp-button lp-button-outline" href="#features">Explore features</a></div>
                    <div class="lp-hero-proof"><span><i class="bi bi-lightning-charge"></i>Less manual work</span><span><i class="bi bi-envelope"></i>More relevant messages</span></div>
                </div>
                <div class="lp-hero-visual" aria-label="Personalized outreach illustration">
                    <div class="lp-cloud"></div><div class="lp-envelope lp-envelope-blue"><i class="bi bi-envelope"></i></div><div class="lp-envelope lp-envelope-green"><i class="bi bi-envelope"></i></div><div class="lp-envelope lp-envelope-white"></div><div class="lp-avatar lp-avatar-a">EC</div><div class="lp-avatar lp-avatar-b">NS</div><div class="lp-avatar lp-avatar-c">OB</div><span class="lp-spark lp-spark-a">⌁</span><span class="lp-spark lp-spark-b">✦</span>
                </div>
            </div>
        </section>

        <div class="lp-strip"><div class="lp-container lp-strip-grid"><div class="lp-strip-item"><i class="bi bi-lightning-charge"></i>Less manual work</div><div class="lp-strip-item"><i class="bi bi-envelope"></i>More relevant messages</div><div class="lp-strip-item"><i class="bi bi-layers"></i>One connected workspace</div></div></div>

        <section class="lp-section" id="features"><div class="lp-container lp-two-col"><div><div class="lp-kicker">Leads & lists</div><h2>Start with the right people.</h2><p>Import leads from CSV, add custom fields, and organize contacts with categories so your outreach reaches the right audience.</p><div class="lp-feature-list"><div class="lp-feature"><i class="bi bi-cloud-arrow-up"></i><div><strong>Import in minutes</strong><span>Upload a sample CSV, map your columns, and validate every row.</span></div></div><div class="lp-feature"><i class="bi bi-funnel"></i><div><strong>Organize by category</strong><span>Keep photographers, venues, agencies, and custom audiences easy to find.</span></div></div></div></div><div class="lp-lead-cards"><div class="lp-lead-card"><div class="lp-lead-avatar">EC</div><strong>Emma Carter</strong><span>Photographers</span></div><div class="lp-lead-card"><div class="lp-lead-avatar">NB</div><strong>Noah Bennett</strong><span>Event venues</span></div><div class="lp-lead-card"><div class="lp-lead-avatar">OB</div><strong>Olivia Brooks</strong><span>Agencies</span></div></div></div></section>

        <section class="lp-section lp-section-soft" id="how-it-works"><div class="lp-container lp-two-col reverse"><div class="lp-editor"><div class="lp-editor-bar"><i></i><i></i><i></i></div><div class="lp-editor-body"><div class="lp-editor-subject"><span>Subject:</span>A quick note, {{ '{first_name}' }}</div><div>Hi {{ '{first_name}' }},</div><div>I came across {{ '{company}' }} and loved what you’re doing.</div><div>Could we chat about a potential collaboration?</div></div></div><div><div class="lp-kicker">Personalized campaigns</div><h2>Make every message feel personal.</h2><p>Use reusable templates, add first-name and company variables, and preview your message before sending so it always feels relevant.</p><div class="lp-feature-list"><div class="lp-feature"><i class="bi bi-pencil-square"></i><div><strong>Write once, improve often</strong><span>Rich text templates, attachments, signatures, and multiple subject options.</span></div></div><div class="lp-feature"><i class="bi bi-send-check"></i><div><strong>Send through your service</strong><span>Choose the SMTP service and signature for each campaign step.</span></div></div></div></div></div></section>

        <section class="lp-band"><div class="lp-container"><h2 class="lp-band-title">Follow up without losing the human touch.</h2><div class="lp-three-col"><div class="lp-work-card"><i class="bi bi-pencil"></i><div><strong>Write your message</strong><p>Clear, friendly messaging that starts the right conversation.</p></div></div><div class="lp-work-card"><i class="bi bi-calendar3"></i><div><strong>Choose your timing</strong><p>Set up follow-ups to go out automatically on your schedule.</p></div></div><div class="lp-work-card"><i class="bi bi-reply"></i><div><strong>Stop when they reply</strong><p>Follow-ups pause automatically when someone responds.</p></div></div></div></div></section>

        <section class="lp-section" id="why-leadpilot"><div class="lp-container"><div class="lp-two-col"><div><div class="lp-kicker">Website analytics</div><h2>Your website completes the story.</h2><p>See how your outreach drives real interest on your site and what people do next.</p><div class="lp-feature-list"><div class="lp-check"><i class="bi bi-check2"></i>Understand your visitors</div><div class="lp-check"><i class="bi bi-check2"></i>Find your best pages</div><div class="lp-check"><i class="bi bi-check2"></i>Connect campaigns to conversions</div></div></div><div class="lp-chart"><div class="lp-chart-head"><span>Website</span><select class="lp-chart-select" aria-label="Select website"><option>phototrail.app</option><option>faisalimtiaz.com</option></select></div><svg viewBox="0 0 640 210" role="img" aria-label="Website visitors trend"><defs><linearGradient id="lpChartFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#10c98b" stop-opacity=".34"/><stop offset="1" stop-color="#10c98b" stop-opacity="0"/></linearGradient></defs><line x1="0" y1="170" x2="640" y2="170" stroke="#d8e1ea"/><line x1="0" y1="115" x2="640" y2="115" stroke="#d8e1ea"/><line x1="0" y1="60" x2="640" y2="60" stroke="#d8e1ea"/><polygon points="0,160 55,145 105,151 160,125 215,136 270,104 325,116 385,91 435,101 490,76 540,86 590,55 640,35 640,180 0,180" fill="url(#lpChartFill)"/><polyline points="0,160 55,145 105,151 160,125 215,136 270,104 325,116 385,91 435,101 490,76 540,86 590,55 640,35" fill="none" stroke="#10b981" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg></div></div></div></section>

        <section class="lp-band"><div class="lp-container"><h2 class="lp-band-title">Built for the way you work.</h2><div class="lp-three-col"><div class="lp-work-card"><i class="bi bi-laptop"></i><div><strong>Freelancers</strong><p>Keep prospecting organized while you focus on client work.</p></div></div><div class="lp-work-card"><i class="bi bi-layers"></i><div><strong>Agencies</strong><p>Manage targeted outreach across campaigns and websites.</p></div></div><div class="lp-work-card"><i class="bi bi-people"></i><div><strong>Growing teams</strong><p>Share one clear view of leads, replies, and results.</p></div></div></div></div></section>

        <section class="lp-section lp-control-band"><div class="lp-container"><h2 class="lp-band-title">Stay in control of every campaign.</h2><div class="lp-control-grid"><div class="lp-control"><i class="bi bi-calendar-check"></i><div><strong>Sending schedules</strong><span>Plan when your messages go out and keep outreach consistent.</span></div></div><div class="lp-control"><i class="bi bi-reply"></i><div><strong>Reply-aware follow-ups</strong><span>Automatically follow up, and stop when you get a reply.</span></div></div><div class="lp-control"><i class="bi bi-shield-check"></i><div><strong>Unsubscribe controls</strong><span>Give people a simple way to opt out respectfully.</span></div></div><div class="lp-control"><i class="bi bi-bar-chart"></i><div><strong>Delivery reports</strong><span>See how your messages are performing at a glance.</span></div></div></div></div></section>

        <section class="lp-section" id="pricing"><div class="lp-container"><div class="lp-pricing-head"><div class="lp-kicker">Simple plans, meaningful progress</div><h2>Choose the pace that fits your outreach.</h2><p>Start free, then unlock more capacity as your lead list and campaigns grow. Every plan includes the essentials for thoughtful follow-up.</p></div><div class="lp-pricing-grid"><article class="lp-price-card"><h3>Starter</h3><p class="lp-price-description">For trying MyLeadTrail and building your first organized workflow.</p><div class="lp-price"><strong>$0</strong><span>/ forever</span></div><a class="lp-button lp-button-outline" href="{{ route('marketing.register') }}">Start for free <i class="bi bi-arrow-right"></i></a><div class="lp-price-rewards">Your rewards include:<ul><li><i class="bi bi-check2-circle"></i>Up to 100 leads and one active category</li><li><i class="bi bi-check2-circle"></i>One connected website for analytics</li><li><i class="bi bi-check2-circle"></i>One signature and one SMTP service</li><li><i class="bi bi-check2-circle"></i>Build and preview email templates</li></ul></div></article><article class="lp-price-card lp-price-card-popular"><span class="lp-price-badge">Most popular</span><h3>Growth</h3><p class="lp-price-description">For freelancers and small teams running consistent campaigns.</p><div class="lp-price"><strong>{{ '$' . number_format(config('marketing.plans.growth.price')) }}</strong><span>/ month</span></div><a class="lp-button lp-button-primary" href="{{ route('marketing.register') }}">Choose Growth <i class="bi bi-arrow-right"></i></a><div class="lp-price-rewards">Everything in Starter, plus:<ul><li><i class="bi bi-check2-circle"></i>Up to 2,500 leads and 5 categories</li><li><i class="bi bi-check2-circle"></i>Three connected websites</li><li><i class="bi bi-check2-circle"></i>Multiple signatures and sending services</li><li><i class="bi bi-check2-circle"></i>Automated multi-step sequences</li></ul></div></article><article class="lp-price-card"><h3>Scale</h3><p class="lp-price-description">For agencies and growing teams managing outreach at volume.</p><div class="lp-price"><strong>{{ '$' . number_format(config('marketing.plans.scale.price')) }}</strong><span>/ month</span></div><a class="lp-button lp-button-outline" href="{{ route('marketing.register') }}">Choose Scale <i class="bi bi-arrow-right"></i></a><div class="lp-price-rewards">Everything in Growth, plus:<ul><li><i class="bi bi-check2-circle"></i>Unlimited categories and team workflows</li><li><i class="bi bi-check2-circle"></i>Ten connected websites</li><li><i class="bi bi-check2-circle"></i>Moderator access for your workspace</li><li><i class="bi bi-check2-circle"></i>Priority campaign reporting</li></ul></div></article></div><p class="lp-pricing-note">Pricing shown is an initial product direction and can be refined as plans and billing are introduced.</p></div></section>

        <section class="lp-section lp-section-soft" id="faq"><div class="lp-container lp-faq-grid"><div><div class="lp-kicker">Questions, answered</div><h2>A few things you might be wondering.</h2><p>Everything you need to start organized, thoughtful outreach.</p></div><div class="lp-faqs"><details class="lp-faq" open><summary>Can I import my existing leads?</summary><p>Yes. Upload a CSV, map your fields, and organize contacts into lists and categories before starting a campaign.</p></details><details class="lp-faq"><summary>Can I schedule follow-ups?</summary><p>Yes. Build a sequence with multiple steps, choose the wait time, and pause the sequence when a lead replies.</p></details><details class="lp-faq"><summary>Can I track more than one website?</summary><p>Yes. Connect websites from Settings and select the website you want to inspect in Analytics and Performance.</p></details><details class="lp-faq"><summary>What results can I measure?</summary><p>Track delivery, opens, replies, website visitors, pageviews, clicks, countries, devices, and top-performing pages.</p></details></div></div></section>

        <div class="lp-container"><section class="lp-cta"><div class="lp-cta-inner"><div><h2>Ready to bring your outreach together?</h2><p>Organize your leads, run more personal campaigns, and see what happens next.</p></div><a class="lp-button lp-button-primary" href="{{ route('marketing.register') }}">Get started <i class="bi bi-arrow-right"></i></a></div></section></div>
    </main>

    <footer class="lp-footer"><div class="lp-container lp-footer-grid"><div class="lp-footer-brand"><a class="lp-logo" href="{{ route('studio') }}"><i class="bi bi-send-fill"></i><span>MyLeadTrail</span></a><p>Thoughtful follow-ups and website insights in one connected workspace.</p></div><div><h3>Product</h3><a href="#features">Features</a><br><a href="#how-it-works">How it works</a></div><div><h3>Resources</h3><a href="#why-leadpilot">Why MyLeadTrail</a><br><a href="#faq">FAQ</a></div><div><h3>Account</h3><a href="{{ route('marketing.login') }}">Log in</a><br><a href="{{ route('marketing.register') }}">Get started</a></div></div></footer>
</div>
<script>
    const lpNav = document.getElementById('lpNav');
    const lpMenuButton = document.getElementById('lpMenuButton');
    lpMenuButton?.addEventListener('click', () => {
        const open = lpNav.classList.toggle('is-open');
        lpMenuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    lpNav?.querySelectorAll('.lp-links a').forEach(link => link.addEventListener('click', () => {
        lpNav.classList.remove('is-open');
        lpMenuButton?.setAttribute('aria-expanded', 'false');
    }));
</script>
</body>
</html>
