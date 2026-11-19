<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marketing Dashboard</title>
    <meta name="description" content="Private marketing dashboard for managing email campaigns and contact form submissions.">
    <meta name="robots" content="noindex,nofollow">
    <link rel="canonical" href="{{ route('marketing') }}">
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <script>
        window.tailwind = window.tailwind || {};
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif']
                    }
                }
            }
        };

        const accountTheme = @json(auth()->user()?->theme);
        const resolvedTheme = accountTheme === 'dark' ? 'dark' : 'light';
        localStorage.setItem('marketing-theme', resolvedTheme);

        // Light mode is the workspace default; dark mode remains available from Account Settings.
        if (resolvedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .marketing-dashboard {
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif !important;
            --dashboard-bg: #f7fafc;
            --dashboard-surface: #ffffff;
            --dashboard-border: #d8e1ea;
            --dashboard-ink: #101828;
            --dashboard-muted: #51627c;
            --dashboard-green: #10c98b;
            --dashboard-green-strong: #08b97c;
            --dashboard-green-dark: #008f63;
            --dashboard-green-soft: #e3fbf1;
            --dashboard-blue: #1688cf;
            --dashboard-shadow: 0 12px 30px rgba(26, 51, 72, .07);
        }

        .marketing-dashboard button,
        .marketing-dashboard input,
        .marketing-dashboard textarea,
        .marketing-dashboard select {
            font-family: inherit;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-action]:hover,
        .marketing-dashboard [data-rich-text-editor] [data-editor-action]:focus-visible,
        .marketing-dashboard [data-rich-text-editor] [data-editor-action]:active {
            background-color: #e3fbf1 !important;
            color: #008f63 !important;
        }

        .dark .marketing-dashboard [data-rich-text-editor] [data-editor-action]:hover,
        .dark .marketing-dashboard [data-rich-text-editor] [data-editor-action]:focus-visible,
        .dark .marketing-dashboard [data-rich-text-editor] [data-editor-action]:active {
            background-color: rgba(16, 185, 129, .16) !important;
            color: #a7f3d0 !important;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-surface] h1 {
            margin: .75rem 0 .5rem;
            font-size: 1.875rem;
            line-height: 2.25rem;
            font-weight: 700;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-surface] h2 {
            margin: .65rem 0 .45rem;
            font-size: 1.5rem;
            line-height: 2rem;
            font-weight: 700;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-surface] p {
            margin: .45rem 0;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-surface] ul {
            margin: .5rem 0;
            padding-left: 1.5rem;
            list-style-type: disc;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-surface] ol {
            margin: .5rem 0;
            padding-left: 1.5rem;
            list-style-type: decimal;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-surface] blockquote {
            margin: .75rem 0;
            border-left: 3px solid #10b981;
            padding-left: .85rem;
            color: #475569;
            font-style: italic;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-surface] pre {
            margin: .75rem 0;
            overflow-x: auto;
            border-radius: .5rem;
            background: #f1f5f9;
            padding: .75rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: .8rem;
            line-height: 1.5;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-image-controls] {
            color: #087f5b !important;
        }

        .marketing-dashboard [data-rich-text-editor] [data-editor-image-controls] span {
            color: #087f5b !important;
        }

        .dark .marketing-dashboard [data-rich-text-editor] [data-editor-surface] blockquote {
            color: #cbd5e1;
        }

        .dark .marketing-dashboard [data-rich-text-editor] [data-editor-surface] pre {
            background: #1e293b;
        }

        .dark .marketing-dashboard [data-rich-text-editor] [data-editor-image-controls],
        .dark .marketing-dashboard [data-rich-text-editor] [data-editor-image-controls] span {
            color: #a7f3d0 !important;
        }

        .marketing-dashboard [data-rich-text-editor] .editor-tab-active {
            border: 1px solid #d8e1ea !important;
            border-bottom: 0 !important;
            background-color: #ffffff !important;
            color: #008f63 !important;
        }

        .marketing-dashboard [data-rich-text-editor] [hidden] {
            display: none !important;
        }

        .dark .marketing-dashboard [data-rich-text-editor] .editor-tab-active {
            border-color: #2b3044 !important;
            background-color: #10131d !important;
            color: #a7f3d0 !important;
        }

        .dark .marketing-dashboard {
            --dashboard-bg: #070910;
            --dashboard-surface: #10131d;
            --dashboard-border: #2b3044;
            --dashboard-muted: #9aa1b5;
        }

        .marketing-dashboard aside,
        .marketing-dashboard main {
            background-color: var(--dashboard-bg) !important;
        }

        .marketing-dashboard aside {
            background-color: #ffffff !important;
            border-color: var(--dashboard-border) !important;
            color: var(--dashboard-ink) !important;
        }

        .marketing-dashboard aside nav a,
        .marketing-dashboard aside nav summary,
        .marketing-dashboard aside form button {
            color: var(--dashboard-muted) !important;
        }

        .marketing-dashboard aside [class~="dark:text-white"],
        .marketing-dashboard aside [class~="dark:text-slate-100"],
        .marketing-dashboard aside [class~="dark:text-slate-200"],
        .marketing-dashboard aside [class~="dark:text-slate-300"] {
            color: #334155 !important;
        }

        .marketing-dashboard aside [class~="text-slate-950"] {
            color: #0f172a !important;
        }

        .marketing-dashboard aside nav a:hover,
        .marketing-dashboard aside nav summary:hover,
        .marketing-dashboard aside form button:hover {
            color: #008f63 !important;
        }

        .marketing-dashboard aside nav a,
        .marketing-dashboard aside nav summary {
            min-height: 36px;
            border-radius: 9px !important;
        }

        .marketing-dashboard aside nav a.bg-violet-600,
        .marketing-dashboard aside nav summary.bg-violet-600,
        .marketing-dashboard aside nav a.bg-emerald-500,
        .marketing-dashboard aside nav summary.bg-emerald-500,
        .marketing-dashboard aside nav a.bg-emerald-50,
        .marketing-dashboard aside nav summary.bg-emerald-50 {
            color: var(--dashboard-green-dark) !important;
            background: var(--dashboard-green-soft) !important;
            box-shadow: none !important;
        }

        .marketing-dashboard aside nav a:not(.bg-emerald-500):hover,
        .marketing-dashboard aside nav summary:not(.bg-emerald-500):hover {
            color: var(--dashboard-green-dark) !important;
            background-color: var(--dashboard-green-soft) !important;
        }

        /* Keep helper, placeholder, and loading copy readable in light mode. */
        .marketing-dashboard input::placeholder,
        .marketing-dashboard textarea::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }

        .marketing-dashboard [class~="dark:placeholder:text-slate-500"]::placeholder {
            color: #94a3b8 !important;
        }

        .marketing-dashboard [class~="dark:text-white"],
        .marketing-dashboard [class~="dark:text-slate-100"] {
            color: #0f172a !important;
        }

        .marketing-dashboard [class~="dark:text-slate-200"],
        .marketing-dashboard [class~="dark:text-slate-300"] {
            color: #334155 !important;
        }

        .marketing-dashboard [class~="dark:text-slate-400"],
        .marketing-dashboard [class~="dark:text-slate-500"] {
            color: #64748b !important;
        }

        .marketing-dashboard [class~="dark:bg-slate-950"],
        .marketing-dashboard [class~="dark:bg-slate-900"] {
            background-color: var(--dashboard-surface) !important;
        }

        .marketing-dashboard [class~="dark:bg-slate-800"] {
            background-color: #f1f5f9 !important;
        }

        .marketing-dashboard [class~="dark:border-slate-800"],
        .marketing-dashboard [class~="dark:border-slate-700"] {
            border-color: var(--dashboard-border) !important;
        }

        .dark .marketing-dashboard aside,
        .dark .marketing-dashboard .bg-slate-900,
        .dark .marketing-dashboard [class*="dark:bg-slate-900"] {
            background-color: #0b0e17 !important;
        }

        .dark .marketing-dashboard [class*="dark:bg-slate-950"] {
            background-color: #070910 !important;
        }

        .dark .marketing-dashboard [class*="dark:border-slate-800"],
        .dark .marketing-dashboard [class*="dark:border-slate-700"] {
            border-color: var(--dashboard-border) !important;
        }

        .dark .marketing-dashboard [class*="dark:divide-slate-800"] > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--dashboard-border) !important;
        }

        .marketing-dashboard [class~="bg-emerald-600"],
        .marketing-dashboard [class~="bg-emerald-500"] {
            background-color: var(--dashboard-green) !important;
        }

        .marketing-dashboard [class~="bg-emerald-600"],
        .marketing-dashboard [class~="bg-violet-600"] {
            color: #ffffff !important;
        }

        .marketing-dashboard [class*="hover:bg-emerald-700"]:hover,
        .marketing-dashboard [class*="hover:bg-emerald-500"]:hover {
            background-color: var(--dashboard-green-strong) !important;
        }

        .marketing-dashboard [class*="text-emerald-700"],
        .marketing-dashboard [class*="text-emerald-600"],
        .marketing-dashboard [class*="text-emerald-500"] {
            color: #008f63 !important;
        }

        .marketing-dashboard .marketing-primary-action {
            color: #ffffff !important;
            background-color: var(--dashboard-green-strong) !important;
            border-color: var(--dashboard-green-strong) !important;
        }

        .marketing-dashboard .marketing-primary-action:hover {
            color: #ffffff !important;
            background-color: #06a971 !important;
        }

        .marketing-dashboard .marketing-chart-legend {
            color: #334155 !important;
        }

        .marketing-dashboard .marketing-audience-notice {
            border-color: #8edfc1 !important;
            background: #e3fbf1 !important;
            color: #087f5b !important;
        }

        .dark .marketing-dashboard .marketing-audience-notice {
            border-color: rgba(52, 211, 153, .35) !important;
            background: rgba(16, 185, 129, .12) !important;
            color: #a7f3d0 !important;
        }

        .marketing-dashboard .marketing-smtp-required,
        .marketing-dashboard .marketing-signature-required {
            border-color: #8edfc1 !important;
            background: #e3fbf1 !important;
            color: #075e46 !important;
        }

        .marketing-dashboard .marketing-smtp-required a,
        .marketing-dashboard .marketing-signature-required a {
            color: #006f50 !important;
        }

        .dark .marketing-dashboard .marketing-smtp-required,
        .dark .marketing-dashboard .marketing-signature-required {
            border-color: rgba(52, 211, 153, .35) !important;
            background: rgba(16, 185, 129, .12) !important;
            color: #a7f3d0 !important;
        }

        .dark .marketing-dashboard .marketing-smtp-required a,
        .dark .marketing-dashboard .marketing-signature-required a {
            color: #d1fae5 !important;
        }

        .dark .marketing-dashboard .marketing-chart-legend {
            color: #e2e8f0 !important;
        }

        .marketing-dashboard .plan-usage-card {
            border-color: #8edfc1 !important;
            background: #e3fbf1 !important;
            color: #087f5b !important;
        }

        .marketing-dashboard .plan-usage-card .plan-usage-title {
            color: #087f5b !important;
        }

        .marketing-dashboard .plan-usage-card .plan-usage-copy,
        .marketing-dashboard .plan-usage-card .plan-usage-icon {
            color: #0b815e !important;
        }

        .dark .marketing-dashboard .plan-usage-card {
            border-color: rgba(52, 211, 153, .35) !important;
            background: rgba(16, 185, 129, .12) !important;
            color: #a7f3d0 !important;
        }

        .dark .marketing-dashboard .plan-usage-card .plan-usage-title,
        .dark .marketing-dashboard .plan-usage-card .plan-usage-copy,
        .dark .marketing-dashboard .plan-usage-card .plan-usage-icon {
            color: #a7f3d0 !important;
        }

        .marketing-dashboard [class~="bg-violet-600"] {
            background-color: var(--dashboard-green-strong) !important;
        }

        .marketing-dashboard [class*="bg-violet-600/"] {
            background-color: color-mix(in srgb, var(--dashboard-green-strong) 16%, transparent) !important;
        }

        .marketing-dashboard [class~="bg-violet-500"] {
            background-color: var(--dashboard-green) !important;
        }

        .marketing-dashboard [class~="bg-violet-100"],
        .marketing-dashboard [class~="bg-violet-50"] {
            background-color: #e4fbf1 !important;
        }

        .marketing-dashboard [class~="text-violet-500"],
        .marketing-dashboard [class~="text-violet-600"],
        .marketing-dashboard [class~="text-violet-700"] {
            color: #008f63 !important;
        }

        .marketing-dashboard [class~="border-violet-200"],
        .marketing-dashboard [class~="border-violet-300"],
        .marketing-dashboard [class~="border-violet-400"],
        .marketing-dashboard [class~="border-violet-500"] {
            border-color: color-mix(in srgb, var(--dashboard-green) 45%, var(--dashboard-border)) !important;
        }

        .marketing-dashboard [class*="hover:bg-violet-"]:hover {
            background-color: #e6fbf3 !important;
        }

        .marketing-dashboard [class~="bg-violet-600"][class*="hover:bg-violet-"]:hover,
        .marketing-dashboard [class~="bg-emerald-600"][class*="hover:bg-emerald-"]:hover {
            background-color: #06a971 !important;
            color: #ffffff !important;
        }

        .marketing-dashboard [class*="hover:text-violet-"]:hover {
            color: #007a55 !important;
        }

        .marketing-dashboard [class*="shadow-violet-"] {
            --tw-shadow-color: color-mix(in srgb, var(--dashboard-green-strong) 25%, transparent) !important;
        }

        .marketing-dashboard [class*="border-emerald-200"],
        .marketing-dashboard [class*="border-emerald-500"] {
            border-color: color-mix(in srgb, var(--dashboard-green) 35%, var(--dashboard-border)) !important;
        }

        /* Authenticated form controls use the same neutral border as the login form. */
        .marketing-dashboard input:not([class*="border-red-"]),
        .marketing-dashboard textarea:not([class*="border-red-"]),
        .marketing-dashboard select:not([class*="border-red-"]) {
            border-color: var(--dashboard-border) !important;
        }

        .marketing-dashboard button[name="save_draft"] {
            color: #334155 !important;
            background-color: #ffffff !important;
            border-color: var(--dashboard-border) !important;
        }

        .marketing-dashboard button[name="save_draft"]:hover,
        .marketing-dashboard button[name="save_draft"]:focus-visible {
            color: var(--dashboard-ink) !important;
            background-color: #f7fafc !important;
        }

        .dark .marketing-dashboard button[name="save_draft"] {
            color: #e2e8f0 !important;
            background-color: #10131d !important;
            border-color: var(--dashboard-border) !important;
        }

        .dark .marketing-dashboard button[name="save_draft"]:hover,
        .dark .marketing-dashboard button[name="save_draft"]:focus-visible {
            color: #ffffff !important;
            background-color: #1e293b !important;
        }

        .marketing-dashboard [class*="bg-lime-100"],
        .marketing-dashboard [class*="bg-lime-50"] {
            background-color: #e4fbf1 !important;
        }

        .marketing-dashboard [class~="bg-lime-400"] {
            background-color: #34d399 !important;
        }

        .marketing-dashboard [class*="bg-lime-400/"] {
            background-color: color-mix(in srgb, #34d399 16%, transparent) !important;
        }

        .marketing-dashboard [class~="text-lime-500"],
        .marketing-dashboard [class~="text-lime-600"],
        .marketing-dashboard [class~="text-lime-700"] {
            color: #008f63 !important;
        }

        .marketing-dashboard [class~="bg-sky-500"] {
            background-color: #dff2ff !important;
        }

        .marketing-dashboard [class~="text-sky-500"],
        .marketing-dashboard [class~="text-sky-600"],
        .marketing-dashboard [class~="text-sky-700"] {
            color: var(--dashboard-blue) !important;
        }

        .marketing-dashboard svg polyline[stroke="#a855f7"] {
            stroke: var(--dashboard-blue) !important;
        }

        .marketing-dashboard svg polyline[stroke="#9cff3b"] {
            stroke: var(--dashboard-green) !important;
        }

        .marketing-dashboard svg stop[stop-color="#8b5cf6"] {
            stop-color: var(--dashboard-blue) !important;
        }

        .marketing-dashboard svg stop[stop-color="#9cff3b"] {
            stop-color: var(--dashboard-green) !important;
        }

        .marketing-dashboard [class*="bg-white"] {
            background-color: var(--dashboard-surface);
        }

        .marketing-dashboard [class*="border-slate-200"],
        .marketing-dashboard [class*="border-slate-300"] {
            border-color: var(--dashboard-border);
        }

        .marketing-dashboard [class*="text-slate-500"],
        .marketing-dashboard [class*="text-slate-400"] {
            color: var(--dashboard-muted);
        }

        .marketing-dashboard [class*="text-slate-600"],
        .marketing-dashboard [class*="text-slate-700"] {
            color: #253247;
        }

        .marketing-dashboard [class*="text-slate-900"],
        .marketing-dashboard [class*="text-slate-950"] {
            color: var(--dashboard-ink);
        }

        .marketing-dashboard select,
        .marketing-dashboard input,
        .marketing-dashboard textarea {
            color: var(--dashboard-ink) !important;
            background-color: #ffffff !important;
        }

        .marketing-dashboard select option {
            color: var(--dashboard-ink);
            background: #ffffff;
        }

        .marketing-dashboard input:focus,
        .marketing-dashboard textarea:focus,
        .marketing-dashboard select:focus {
            border-color: var(--dashboard-green) !important;
            box-shadow: 0 0 0 3px rgba(16, 201, 139, .14) !important;
        }

        .marketing-dashboard [class~="bg-slate-50"] { background-color: #f7fafc !important; }
        .marketing-dashboard [class~="bg-slate-100"] { background-color: #f1f6f9 !important; }
        .marketing-dashboard [class~="bg-slate-200"] { background-color: #e5edf2 !important; }
        .marketing-dashboard [class~="bg-emerald-50"],
        .marketing-dashboard [class~="bg-emerald-100"] { background-color: var(--dashboard-green-soft) !important; }
        .marketing-dashboard [class~="bg-amber-50"] { background-color: #fff9e9 !important; }
        .marketing-dashboard [class~="bg-red-50"] { background-color: #fff5f5 !important; }

        .marketing-dashboard [class~="bg-red-50"][class~="text-red-800"] {
            border-color: #fca5a5 !important;
            background-color: #fff1f2 !important;
            color: #991b1b !important;
        }

        .marketing-dashboard [class~="bg-red-50"][class~="text-red-800"] .opacity-80 {
            color: #7f1d1d !important;
            opacity: 1 !important;
        }

        .dark .marketing-dashboard [class~="bg-red-50"][class~="text-red-800"] {
            border-color: rgba(248, 113, 113, .45) !important;
            background-color: rgba(127, 29, 29, .2) !important;
            color: #fecaca !important;
        }

        .dark .marketing-dashboard [class~="bg-red-50"][class~="text-red-800"] .opacity-80 {
            color: #fee2e2 !important;
        }

        .marketing-dashboard .marketing-alert {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            border-radius: 10px !important;
            font-weight: 500;
            line-height: 1.5;
        }

        .marketing-dashboard .marketing-alert i {
            flex: 0 0 auto;
            margin-top: .05rem;
            font-size: 1rem;
        }

        .marketing-dashboard .marketing-alert-success {
            color: #087f5b !important;
            background: #e3fbf1 !important;
            border-color: #9fe3c8 !important;
        }

        .marketing-dashboard .marketing-alert-error {
            color: #b42318 !important;
            background: #fff5f5 !important;
            border-color: #f2b8b5 !important;
        }

        .marketing-dashboard .min-h-full {
            background: #ffffff;
            box-shadow: var(--dashboard-shadow);
        }

        /* The modal's full-height alignment layer must stay transparent so its black backdrop is visible. */
        .marketing-dashboard #leadDetailsModal .min-h-full {
            background: transparent !important;
            box-shadow: none !important;
        }

        .marketing-dashboard #sentEmailDetailModal .sent-email-detail-modal-panel {
            opacity: 0;
            transform: translateY(12px) scale(.985);
            transition: opacity .16s ease, transform .16s ease;
        }

        .marketing-dashboard #sentEmailDetailModal.is-open .sent-email-detail-modal-panel {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .marketing-dashboard .marketing-email-detail-body h1,
        .marketing-dashboard .marketing-email-detail-body h2,
        .marketing-dashboard .marketing-email-detail-body h3 {
            margin: 0 0 .75rem;
            font-weight: 700;
            line-height: 1.25;
        }

        .marketing-dashboard .marketing-email-detail-body p { margin: 0 0 .75rem; }
        .marketing-dashboard .marketing-email-detail-body ul,
        .marketing-dashboard .marketing-email-detail-body ol { margin: 0 0 .75rem; padding-left: 1.25rem; }
        .marketing-dashboard .marketing-email-detail-body img { max-width: 100%; height: auto; }
        .marketing-dashboard .marketing-email-detail-body a { color: #008f63; text-decoration: underline; }

        /* Use tighter corners across the workspace while preserving pills/circles. */
        .marketing-dashboard [class~="rounded-2xl"] {
            border-radius: 0.75rem !important;
        }

        .marketing-dashboard [class~="rounded-xl"] {
            border-radius: 0.625rem !important;
        }

        .marketing-dashboard [class~="rounded-lg"] {
            border-radius: 0.5rem !important;
        }

        .marketing-dashboard [class~="rounded-md"] {
            border-radius: 0.375rem !important;
        }

        .marketing-dashboard [class~="rounded-sm"] {
            border-radius: 0.25rem !important;
        }

        .marketing-dashboard aside nav a.nav-sub-active,
        .marketing-dashboard aside nav a.nav-sub-active i {
            background-color: transparent !important;
            color: #008f63 !important;
            box-shadow: none !important;
        }

        .dark .marketing-dashboard aside nav a.nav-sub-active,
        .dark .marketing-dashboard aside nav a.nav-sub-active i {
            color: #34d399 !important;
        }

        /* Keep the workspace dense and readable across every dashboard page. */
        .marketing-dashboard {
            font-size: 0.875rem;
        }

        .marketing-dashboard [class*="text-4xl"] {
            font-size: 1.75rem !important;
            line-height: 1.2 !important;
        }

        .marketing-dashboard [class*="text-3xl"] {
            font-size: 1.5rem !important;
            line-height: 1.25 !important;
        }

        .marketing-dashboard [class*="text-2xl"] {
            font-size: 1.25rem !important;
            line-height: 1.3 !important;
        }

        .marketing-dashboard [class*="text-xl"] {
            font-size: 1.125rem !important;
            line-height: 1.35 !important;
        }

        .marketing-dashboard [class*="text-lg"] {
            font-size: 1rem !important;
            line-height: 1.4 !important;
        }

        .marketing-dashboard [class*="text-sm"] {
            font-size: 0.8125rem !important;
            line-height: 1.45 !important;
        }

        .marketing-dashboard [class*="text-xs"] {
            font-size: 0.6875rem !important;
            line-height: 1.4 !important;
        }

        /* Second compact pass for dense dashboard layouts. */
        .marketing-dashboard {
            font-size: 0.8125rem;
        }

        .marketing-dashboard [class*="text-4xl"] {
            font-size: 1.5rem !important;
        }

        .marketing-dashboard [class*="text-3xl"] {
            font-size: 1.35rem !important;
        }

        .marketing-dashboard [class*="text-2xl"] {
            font-size: 1.15rem !important;
        }

        .marketing-dashboard [class*="text-xl"] {
            font-size: 1.05rem !important;
        }

        .marketing-dashboard [class*="text-lg"] {
            font-size: 0.95rem !important;
        }

        .marketing-dashboard [class*="text-sm"] {
            font-size: 0.75rem !important;
        }

        .marketing-dashboard [class*="text-xs"] {
            font-size: 0.625rem !important;
        }

        .marketing-dashboard [class~="p-7"] {
            padding: 1.25rem !important;
        }

        .marketing-dashboard [class~="p-6"] {
            padding: 1.125rem !important;
        }

        .marketing-dashboard [class~="p-5"] {
            padding: 0.875rem !important;
        }

        .marketing-dashboard [class~="p-4"] {
            padding: 0.75rem !important;
        }

        .marketing-dashboard [class~="p-3"] {
            padding: 0.625rem !important;
        }

        .marketing-dashboard [class~="px-6"] {
            padding-left: 1.125rem !important;
            padding-right: 1.125rem !important;
        }

        .marketing-dashboard [class~="px-5"] {
            padding-left: 0.875rem !important;
            padding-right: 0.875rem !important;
        }

        .marketing-dashboard [class~="px-4"] {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .marketing-dashboard [class~="py-6"] {
            padding-top: 1.125rem !important;
            padding-bottom: 1.125rem !important;
        }

        .marketing-dashboard [class~="py-5"] {
            padding-top: 0.875rem !important;
            padding-bottom: 0.875rem !important;
        }

        .marketing-dashboard [class~="py-4"] {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .marketing-dashboard [class~="gap-6"] {
            gap: 1.25rem !important;
        }

        .marketing-dashboard [class~="gap-5"] {
            gap: 1rem !important;
        }

        .marketing-dashboard [class~="gap-4"] {
            gap: 0.75rem !important;
        }

        /* Campaign creation needs a denser editing surface than the other dashboard views. */
        .marketing-campaign-page {
            font-size: 0.8rem;
        }

        .marketing-campaign-page [class*="text-4xl"] {
            font-size: 1.35rem !important;
        }

        .marketing-campaign-page [class*="text-xl"] {
            font-size: 1rem !important;
        }

        .marketing-campaign-page [class~="p-6"] {
            padding: 1rem !important;
        }

        .marketing-campaign-page [class~="p-5"] {
            padding: 0.75rem !important;
        }

        .marketing-campaign-page [class~="p-4"] {
            padding: 0.625rem !important;
        }

        .marketing-campaign-page [class~="px-5"] {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .marketing-campaign-page [class~="py-5"] {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .marketing-campaign-page [class~="py-4"] {
            padding-top: 0.625rem !important;
            padding-bottom: 0.625rem !important;
        }

        .marketing-campaign-page [class~="gap-6"] {
            gap: 1rem !important;
        }

        .marketing-campaign-page [class~="gap-5"] {
            gap: 0.75rem !important;
        }

        .marketing-campaign-page [class~="gap-4"] {
            gap: 0.625rem !important;
        }

        .marketing-campaign-page [class~="mt-7"] {
            margin-top: 1.25rem !important;
        }

        .marketing-campaign-page [class~="mt-6"] {
            margin-top: 1rem !important;
        }

        .marketing-campaign-page [class~="mt-5"] {
            margin-top: 0.75rem !important;
        }

        .marketing-campaign-page [class~="mt-4"] {
            margin-top: 0.625rem !important;
        }

        /* If an older session left the dark class on <html>, keep this workspace light and coherent. */
        html.dark .marketing-dashboard [class~="dark:text-white"],
        html.dark .marketing-dashboard [class~="dark:text-slate-100"] {
            color: #0f172a !important;
        }

        html.dark .marketing-dashboard [class~="dark:text-slate-200"],
        html.dark .marketing-dashboard [class~="dark:text-slate-300"] {
            color: #334155 !important;
        }

        html.dark .marketing-dashboard [class~="dark:text-slate-400"],
        html.dark .marketing-dashboard [class~="dark:text-slate-500"] {
            color: #64748b !important;
        }

        html.dark .marketing-dashboard [class~="dark:bg-slate-950"],
        html.dark .marketing-dashboard [class~="dark:bg-slate-900"] {
            background-color: var(--dashboard-surface) !important;
        }

        html.dark .marketing-dashboard [class~="dark:bg-slate-800"] {
            background-color: #f1f5f9 !important;
        }

        html.dark .marketing-dashboard [class~="dark:border-slate-800"],
        html.dark .marketing-dashboard [class~="dark:border-slate-700"] {
            border-color: var(--dashboard-border) !important;
        }

        html.dark .marketing-dashboard aside,
        html.dark .marketing-dashboard main {
            background-color: var(--dashboard-bg) !important;
        }

        html.dark .marketing-dashboard input::placeholder,
        html.dark .marketing-dashboard textarea::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }

        .marketing-dashboard aside details[open] > div {
            animation: marketing-submenu-open 180ms ease-out both;
            transform-origin: top center;
        }

        .marketing-dashboard .marketing-sidebar {
            flex-basis: 18rem !important;
            width: 18rem !important;
            transition: flex-basis 180ms ease, width 180ms ease;
        }

        .marketing-dashboard .sidebar-header-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .marketing-dashboard .sidebar-collapse-toggle {
            color: #51627c !important;
        }

        .marketing-dashboard .sidebar-collapse-toggle:hover,
        .marketing-dashboard .sidebar-collapse-toggle:focus-visible {
            background: #e3fbf1 !important;
            color: #008f63 !important;
            outline: none;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar {
            flex-basis: 4.5rem !important;
            width: 4.5rem !important;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .sidebar-brand-copy,
        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .sidebar-brand-logo,
        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar nav a > span,
        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar nav summary > span,
        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar nav summary > i:last-child,
        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .sidebar-section-label,
        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .plan-usage-card {
            display: none !important;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar > div:first-child {
            justify-content: center;
            padding: .75rem .5rem;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar .sidebar-brand {
            justify-content: center;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .sidebar-header-actions {
            flex-direction: column;
            gap: .35rem;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar nav {
            padding: .5rem;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar nav a,
        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar nav summary {
            justify-content: center;
            gap: 0;
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar nav details > div,
        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar nav > div:not(.sidebar-section-label) {
            padding-left: 0;
        }

        .marketing-dashboard #marketingWorkspace.sidebar-collapsed .marketing-sidebar > .border-t {
            padding: .75rem .5rem;
        }

        @media (prefers-reduced-motion: reduce) {
            .marketing-dashboard .marketing-sidebar {
                transition: none;
            }
        }

        @keyframes marketing-submenu-open {
            from {
                max-height: 0;
                opacity: 0;
                transform: translateY(-0.35rem);
            }
            to {
                max-height: 20rem;
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .marketing-dashboard aside details[open] > div {
                animation: none;
            }
        }
    </style>
</head>

<body class="marketing-dashboard h-screen overflow-hidden bg-slate-100 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
@php
    $analyticsTrend = $analyticsTrend ?? collect();
    $trafficSources = $trafficSources ?? collect();
    $deviceBreakdown = $deviceBreakdown ?? collect();
    $countryBreakdown = $countryBreakdown ?? collect();
    $analyticsAvgSessionSeconds = $analyticsAvgSessionSeconds ?? 0;
    $analyticsPeriodLabel = $analyticsPeriodLabel ?? 'Last 30 days';
    $navBase = 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-normal text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-200';
    $navActive = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-700';
    $navSubActive = 'nav-sub-active';
    $card = 'rounded-xl border border-slate-200 bg-white shadow-[0_12px_30px_rgba(26,51,72,0.07)] dark:border-slate-800 dark:bg-slate-900';
    $input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500';
    $label = 'mb-2 block text-sm font-medium text-slate-800 dark:text-slate-200';
    $muted = 'text-sm text-slate-500 dark:text-slate-400';
    $currentUser = auth()->user();
    $marketingPlan = $marketingPlan ?? config('marketing.plans.'.($currentUser?->account?->plan ?: 'free'), config('marketing.plans.free'));
    $marketingPlanLimits = $marketingPlanLimits ?? ($marketingPlan['limits'] ?? []);
@endphp

<section id="marketingWorkspace" class="flex h-screen overflow-hidden">
    <aside id="marketingSidebar" class="marketing-sidebar flex h-screen w-72 shrink-0 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <div class="sidebar-brand flex items-center gap-2">
                <i class="sidebar-brand-logo bi bi-send-fill text-2xl text-emerald-500" style="transform: rotate(-10deg)"></i>
                <div class="sidebar-brand-copy">
                <div class="text-xl font-bold tracking-tight text-slate-950 dark:text-white">MyLeadTrail</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Marketing workspace</div>
                </div>
            </div>
            <div class="sidebar-header-actions">
                <button id="sidebarCollapseToggle" type="button" class="sidebar-collapse-toggle inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition dark:border-slate-700 dark:text-slate-300" aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
                    <i class="bi bi-layout-sidebar-inset"></i>
                </button>
                <button id="themeToggle" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Toggle dark mode">
                    <i class="bi bi-moon-stars dark:hidden"></i>
                    <i class="bi bi-sun hidden dark:inline"></i>
                </button>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Marketing dashboard">
            <a class="{{ $navBase }} {{ $activeTab === 'dashboard' ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'dashboard']) }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <details class="group" @if (in_array($activeTab, ['analytics', 'performance'], true)) open @endif>
                <summary class="{{ $navBase }} {{ in_array($activeTab, ['analytics', 'performance'], true) ? $navActive : '' }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Analytics</span>
                    <i class="bi bi-chevron-down ml-auto text-xs transition group-open:rotate-180"></i>
                </summary>
                <div class="mt-1 space-y-1 pl-3">
                    <a class="{{ $navBase }} {{ $activeTab === 'analytics' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'analytics']) }}" @if ($activeTab === 'analytics') aria-current="page" @endif>
                        <i class="bi bi-bar-chart"></i>
                        <span>Website Analytics</span>
                    </a>
                    <a class="{{ $navBase }} {{ $activeTab === 'performance' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'performance']) }}" @if ($activeTab === 'performance') aria-current="page" @endif>
                        <i class="bi bi-clock-history"></i>
                        <span>Performance</span>
                    </a>
                </div>
            </details>
            <details class="group" @if (in_array($activeTab, ['campaigns-create', 'campaigns-list', 'campaigns-detail', 'sent-emails', 'sent-email-detail', 'send'], true)) open @endif>
                <summary class="{{ $navBase }} {{ in_array($activeTab, ['campaigns-create', 'campaigns-list', 'campaigns-detail', 'sent-emails', 'sent-email-detail', 'send'], true) ? $navActive : '' }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                    <i class="bi bi-megaphone"></i>
                    <span>Campaigns</span>
                    <i class="bi bi-chevron-down ml-auto text-xs transition group-open:rotate-180"></i>
                </summary>
                <div class="mt-1 space-y-1 pl-3">
                    <a class="{{ $navBase }} {{ in_array($activeTab, ['campaigns-create', 'send'], true) ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'campaigns-create']) }}" @if (in_array($activeTab, ['campaigns-create', 'send'], true)) aria-current="page" @endif>
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Campaign</span>
                    </a>
                    <a class="{{ $navBase }} {{ in_array($activeTab, ['campaigns-list', 'campaigns-detail'], true) ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'campaigns-list']) }}" @if (in_array($activeTab, ['campaigns-list', 'campaigns-detail'], true)) aria-current="page" @endif>
                        <i class="bi bi-list-ul"></i>
                        <span>All Campaigns</span>
                    </a>
                    <a class="{{ $navBase }} {{ in_array($activeTab, ['sent-emails', 'sent-email-detail'], true) ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'sent-emails']) }}" @if (in_array($activeTab, ['sent-emails', 'sent-email-detail'], true)) aria-current="page" @endif>
                        <i class="bi bi-envelope-check"></i>
                        <span>Sent emails</span>
                    </a>
                </div>
            </details>
            {{-- Followup email menu item is intentionally hidden for now; keep the feature code for later. --}}
            {{--
            <a class="{{ $navBase }} {{ in_array($activeTab, ['followups', 'followups-create', 'followups-edit'], true) ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'followups']) }}">
                <i class="bi bi-calendar2-check"></i>
                <span>Followup email</span>
            </a>
            --}}
            <details class="group" @if (in_array($activeTab, ['active-clients', 'contacts-all', 'leads-generate', 'leads', 'contacts', 'email-groups'], true)) open @endif>
                <summary class="{{ $navBase }} {{ in_array($activeTab, ['active-clients', 'contacts-all', 'leads-generate', 'leads', 'contacts', 'email-groups'], true) ? $navActive : '' }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                    <i class="bi bi-people"></i>
                    <span>Clients</span>
                    <i class="bi bi-chevron-down ml-auto text-xs transition group-open:rotate-180"></i>
                </summary>
                <div class="mt-1 space-y-1 pl-3">
                    <a class="{{ $navBase }} {{ $activeTab === 'leads-generate' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'leads-generate', 'lead_source_tab' => 'import']) }}" @if ($activeTab === 'leads-generate') aria-current="page" @endif>
                        <i class="bi bi-stars"></i>
                        <span>Generate Leads</span>
                    </a>
                    <a class="{{ $navBase }} {{ $activeTab === 'leads' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'leads']) }}" @if ($activeTab === 'leads') aria-current="page" @endif>
                        <i class="bi bi-person-plus"></i>
                        <span>All Leads</span>
                    </a>
                    <a class="{{ $navBase }} {{ $activeTab === 'active-clients' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'active-clients']) }}" @if ($activeTab === 'active-clients') aria-current="page" @endif>
                        <i class="bi bi-person-check"></i>
                        <span>Active Clients</span>
                    </a>
                    <a class="{{ $navBase }} {{ $activeTab === 'contacts-all' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'contacts-all']) }}" @if ($activeTab === 'contacts-all') aria-current="page" @endif>
                        <i class="bi bi-person-lines-fill"></i>
                        <span>All contacts</span>
                    </a>
                    {{-- Hidden from the sidebar for now; routes and pages remain available. --}}
                    {{--
                    <a class="{{ $navBase }} {{ $activeTab === 'contacts' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'contacts']) }}" @if ($activeTab === 'contacts') aria-current="page" @endif>
                        <i class="bi bi-person-lines-fill"></i>
                        <span>My Contacts</span>
                    </a>
                    <a class="{{ $navBase }} {{ $activeTab === 'email-groups' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'email-groups']) }}" @if ($activeTab === 'email-groups') aria-current="page" @endif>
                        <i class="bi bi-collection"></i>
                        <span>Email groups</span>
                    </a>
                    --}}
                </div>
            </details>
            {{--
            <a class="{{ $navBase }} {{ $activeTab === 'contact-form' ? $navActive : '' }}" href="{{ route('marketing.contact-form') }}">
                <i class="bi bi-chat-square-text"></i>
                <span>Contact Form</span>
            </a>
            --}}
            <a class="{{ $navBase }} {{ $activeTab === 'settings' ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'websites']) }}">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>

            <div class="sidebar-section-label px-3 pt-4 text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Templates</div>
            <div class="space-y-1 pl-3">
                <a class="{{ $navBase }} {{ $activeTab === 'templates-create' ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'templates-create']) }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>Create template</span>
                </a>
                <a class="{{ $navBase }} {{ in_array($activeTab, ['templates-list', 'templates-edit'], true) ? $navSubActive : '' }}" href="{{ route('marketing', ['tab' => 'templates-list']) }}">
                    <i class="bi bi-list-ul"></i>
                <span>Template listings</span>
                </a>
            </div>
        </nav>

        <div class="border-t border-slate-200 p-3 dark:border-slate-800">
            <a href="{{ route('marketing.landing') }}#pricing" class="plan-usage-card block rounded-lg border px-3 py-3 transition hover:brightness-[.98] dark:hover:brightness-110">
                <div class="flex items-center justify-between gap-2"><span class="plan-usage-title text-xs font-semibold">{{ $marketingPlan['label'] ?? 'Starter' }} plan</span><i class="plan-usage-icon bi bi-arrow-up-right text-xs"></i></div>
                <p class="plan-usage-copy mt-1 text-[11px] leading-4">{{ ($marketingPlan['price'] ?? 0) > 0 ? '$'.number_format($marketingPlan['price']).'/month' : 'Free to use' }} · View plan limits</p>
            </a>
        </div>

    </aside>

    <main class="min-w-0 flex-1 overflow-y-auto bg-slate-100 dark:bg-slate-950">
        <div class="min-h-full p-6">
            <header class="mb-5 flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">MyLeadTrail</div>
                    <div class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200">Marketing workspace</div>
                </div>
                <div id="profileMenuWrapper" class="relative">
                    <button id="profileMenuToggle" type="button" class="flex items-center gap-2 rounded-lg px-2 py-1.5 transition hover:bg-emerald-50 dark:hover:bg-emerald-500/10" aria-label="Open user menu" aria-haspopup="menu" aria-expanded="false">
                    @if ($currentUser?->profile_photo_path)
                        <img src="{{ route('marketing.account.profile-image', $currentUser) }}" alt="{{ $currentUser->name }}" class="h-8 w-8 rounded-full object-cover ring-2 ring-emerald-100 dark:ring-emerald-500/20">
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ strtoupper(substr(trim($currentUser?->name ?: 'U'), 0, 1)) }}</span>
                    @endif
                    <span class="hidden text-right sm:block">
                        <span class="block text-sm font-medium text-slate-800 dark:text-slate-100">{{ $currentUser?->name ?: 'User' }}</span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">{{ str_replace('_', ' ', $currentUser?->role ?: 'user') }}</span>
                    </span>
                    <i class="bi bi-chevron-down text-xs text-slate-400 dark:text-slate-500"></i>
                    </button>
                    <div id="profileMenu" class="absolute right-0 top-full z-50 mt-2 hidden w-48 rounded-lg border border-slate-200 bg-white p-1 shadow-lg shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900" role="menu">
                        <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'account']) }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-200 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300" role="menuitem">
                            <i class="bi bi-person-gear"></i>
                            Account Settings
                        </a>
                        <form action="{{ route('marketing.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-red-50 hover:text-red-700 dark:text-slate-200 dark:hover:bg-red-500/10 dark:hover:text-red-300" role="menuitem">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            @if (session('marketing_success'))
                <div class="marketing-alert marketing-alert-success mb-4 rounded-lg border px-4 py-3 text-sm" role="alert"><i class="bi bi-check-circle-fill"></i><span>{{ session('marketing_success') }}</span></div>
            @endif

            @if (session('marketing_error'))
                <div class="marketing-alert marketing-alert-error mb-4 rounded-lg border px-4 py-3 text-sm" role="alert"><i class="bi bi-exclamation-circle-fill"></i><span>{{ session('marketing_error') }}</span></div>
            @endif

            @if (! $databaseReady)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">
                    Marketing storage is not ready yet. Run the migration after your database credentials are configured.
                </div>
            @endif

            <div class="{{ $card }} min-h-[calc(100vh-3rem)] p-7">
                @if ($activeTab === 'settings')
                    @include('marketing.settings')
                @elseif ($activeTab === 'dashboard')
                    @include('marketing.dashboard', [
                        'dashboardEmailTrend' => $dashboardEmailTrend,
                        'dashboardLeadCategories' => $dashboardLeadCategories,
                        'dashboardCountryBreakdown' => $dashboardCountryBreakdown,
                        'dashboardTopPages' => $dashboardTopPages,
                        'dashboardEmailsSent' => $dashboardEmailsSent,
                        'dashboardEstimatedOpens' => $dashboardEstimatedOpens,
                        'dashboardReplies' => $dashboardReplies,
                        'dashboardLeadConversionRate' => $dashboardLeadConversionRate,
                        'dashboardLeadTotal' => $dashboardLeadTotal,
                        'dashboardPeriod' => $dashboardPeriod,
                        'dashboardPeriodLabel' => $dashboardPeriodLabel,
                        'dashboardPeriodOptions' => $dashboardPeriodOptions,
                        'pendingEmails' => $pendingEmails,
                        'analyticsTrend' => $analyticsTrend,
                        'websiteVisitsTotal' => $websiteVisitsTotal,
                        'websiteClicksTotal' => $websiteClicksTotal,
                        'selectedWebsite' => $selectedWebsite,
                        'connectedWebsites' => $connectedWebsites,
                        'card' => $card,
                        'muted' => $muted,
                    ])
                @elseif ($activeTab === 'leads-generate')
                    @include('marketing.leads-generate', ['leadSourceTab' => $leadSourceTab, 'leadImportPreview' => $leadImportPreview, 'leadSegments' => $leadSegments, 'leadsReady' => $leadsReady])
                @elseif ($activeTab === 'leads')
                    @include('marketing.leads')
                @elseif ($activeTab === 'performance')
                    @include('marketing.performance')
                @elseif ($activeTab === 'analytics')
                    @include('marketing.analytics-dashboard', [
                        'analyticsTrend' => $analyticsTrend ?? collect(),
                        'trafficSources' => $trafficSources ?? collect(),
                        'deviceBreakdown' => $deviceBreakdown ?? collect(),
                        'countryBreakdown' => $countryBreakdown ?? collect(),
                        'analyticsAvgSessionSeconds' => $analyticsAvgSessionSeconds ?? 0,
                        'analyticsPeriodLabel' => $analyticsPeriodLabel ?? 'Last 30 days',
                        'dashboardPeriod' => $dashboardPeriod ?? 30,
                        'dashboardPeriodOptions' => $dashboardPeriodOptions ?? [
                            7 => 'Last 7 days',
                            30 => 'Last 30 days',
                            90 => 'Last 90 days',
                            365 => 'Last 12 months',
                        ],
                        'selectedWebsite' => $selectedWebsite ?? null,
                        'selectedWebsiteId' => $selectedWebsiteId ?? null,
                        'connectedWebsites' => $connectedWebsites ?? collect(),
                        'activeVisitorsCount' => $activeVisitorsCount ?? 0,
                        'websiteVisitsCount' => $websiteVisitsCount ?? 0,
                        'websiteClicksCount' => $websiteClicksCount ?? 0,
                        'topPages' => $topPages ?? collect(),
                        'topClicks' => $topClicks ?? collect(),
                        'websiteVisits' => $websiteVisits ?? collect(),
                        'activeVisitGroups' => $activeVisitGroups ?? collect(),
                        'card' => $card,
                        'muted' => $muted,
                    ])
                    @if (false)
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Website analytics</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">See recent visitors, pages viewed, and click activity from the website.</p>

                    <div class="mt-6 flex flex-wrap items-end justify-between gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                        @include('marketing.website-selector', ['selectorTab' => 'analytics'])
                    </div>

                    <div class="mt-7 grid gap-4 md:grid-cols-4">
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Online now</span>
                            <strong class="mt-2 flex items-center gap-3 text-4xl font-semibold text-slate-950 dark:text-white">
                                <span class="h-3 w-3 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.15)]"></span>
                                {{ number_format($activeVisitorsCount) }}
                            </strong>
                        </div>
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Tracked visits</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($websiteVisitsCount) }}</strong>
                        </div>
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Tracked clicks</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($websiteClicksCount) }}</strong>
                        </div>
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Unique visitors</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($websiteVisits->pluck('session_id')->unique()->count()) }}</strong>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Online now</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($activeVisitGroups as $group)
                                <div class="{{ $card }} flex items-center justify-between gap-4 p-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="h-3 w-3 shrink-0 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.15)]"></span>
                                        <div class="min-w-0">
                                            <strong class="block truncate font-medium text-slate-900 dark:text-slate-100">
                                                {{ $group['count'] }} {{ $group['count'] === 1 ? 'person' : 'people' }} online from {{ $group['location'] }}
                                            </strong>
                                            <div class="{{ $muted }}">Active on {{ $group['page'] }}</div>
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Online</span>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No active visitors right now.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-8 grid gap-6 xl:grid-cols-3">
                        <section>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Top pages</h2>
                            <div class="mt-4 divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                                @forelse ($topPages as $page)
                                    <div class="flex items-center justify-between gap-4 bg-white px-4 py-3 dark:bg-slate-900">
                                        <span class="truncate text-sm text-slate-700 dark:text-slate-200">{{ $page->path }}</span>
                                        <strong class="text-sm text-slate-950 dark:text-white">{{ number_format($page->visits_count) }}</strong>
                                    </div>
                                @empty
                                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No visits tracked yet.</div>
                                @endforelse
                            </div>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Top locations</h2>
                            <div class="mt-4 divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                                @forelse ($topLocations as $location)
                                    <div class="flex items-center justify-between gap-4 bg-white px-4 py-3 dark:bg-slate-900">
                                        <span class="truncate text-sm text-slate-700 dark:text-slate-200">
                                            {{ collect([$location->city, $location->region, $location->country])->filter()->join(', ') }}
                                        </span>
                                        <strong class="text-sm text-slate-950 dark:text-white">{{ number_format($location->visits_count) }}</strong>
                                    </div>
                                @empty
                                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No locations tracked yet.</div>
                                @endforelse
                            </div>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Top clicks</h2>
                            <div class="mt-4 divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                                @forelse ($topClicks as $click)
                                    <div class="bg-white px-4 py-3 dark:bg-slate-900">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $click->element_text ?: $click->element ?: 'Unknown element' }}</span>
                                            <strong class="text-sm text-slate-950 dark:text-white">{{ number_format($click->clicks_count) }}</strong>
                                        </div>
                                        <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $click->path }}</div>
                                    </div>
                                @empty
                                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No clicks tracked yet.</div>
                                @endforelse
                            </div>
                        </section>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Recent visitors</h2>
                        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 dark:border-slate-800">
                            <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] gap-4 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                                <span>Page</span>
                                <span>Place</span>
                                <span>Machine</span>
                                <span>Clicks</span>
                            </div>
                            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($websiteVisits as $visit)
                                    <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] gap-4 bg-white px-4 py-3 text-sm dark:bg-slate-900">
                                        <div class="min-w-0">
                                            <div class="truncate font-medium text-slate-800 dark:text-slate-100">{{ $visit->path }}</div>
                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ optional($visit->visited_at)->format('M d, Y h:i A') }}</div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-slate-600 dark:text-slate-300">
                                                {{ collect([$visit->city, $visit->region, $visit->country])->filter()->join(', ') ?: 'Unknown location' }}
                                            </div>
                                            <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $visit->ip_address }}{{ $visit->organization ? ' · '.$visit->organization : '' }}</div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-slate-600 dark:text-slate-300">{{ $visit->device_type ?: 'Unknown device' }} · {{ $visit->operating_system ?: 'Unknown OS' }}</div>
                                            <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $visit->browser ?: 'Unknown browser' }}{{ $visit->browser_version ? ' '.$visit->browser_version : '' }} · {{ $visit->referrer ?: 'Direct visit' }}</div>
                                        </div>
                                        <strong class="text-sm text-slate-950 dark:text-white">{{ $visit->clicks_count }}</strong>
                                    </div>
                                @empty
                                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No visitors tracked yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Recent clicks</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($websiteClicks as $click)
                                <div class="{{ $card }} grid gap-3 p-4 md:grid-cols-[minmax(0,1fr)_auto]">
                                    <div class="min-w-0">
                                        <strong class="block truncate font-medium text-slate-900 dark:text-slate-100">{{ $click->element_text ?: $click->element ?: 'Unknown element' }}</strong>
                                        <div class="{{ $muted }} truncate">{{ $click->path }} · {{ optional($click->clicked_at)->format('M d, Y h:i A') }}</div>
                                    </div>
                                    <span class="{{ $muted }}">x{{ $click->x ?: 0 }}, y{{ $click->y ?: 0 }}</span>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No clicks tracked yet.</div>
                            @endforelse
                        </div>
                    </div>
                    @endif
                @elseif ($activeTab === 'campaigns-create')
                    @include('marketing.campaigns')
                @elseif ($activeTab === 'campaigns-list')
                    @include('marketing.campaigns-list', ['campaignListStats' => $campaignListStats])
                @elseif ($activeTab === 'campaigns-detail')
                    @include('marketing.campaign-detail')
                @elseif ($activeTab === 'send')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Send new email</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Write one message and send it to comma-separated email addresses.</p>

                    @if ($mailServices->isEmpty())
                        <div class="marketing-smtp-required mt-5 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm" role="alert">
                            <i class="bi bi-lock mt-0.5"></i>
                            <div><strong class="font-semibold">SMTP settings required.</strong> Add an SMTP service in Email settings before sending an email. <a class="font-semibold underline" href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'email']) }}">Open Email settings</a>.</div>
                        </div>
                    @endif
                    @if ($signatures->isEmpty())
                        <div class="marketing-signature-required mt-3 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm" role="alert">
                            <i class="bi bi-lock mt-0.5"></i>
                            <div><strong class="font-semibold">Email signature required.</strong> Add at least one signature in Email settings before sending an email. <a class="font-semibold underline" href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'email']) }}">Open Email settings</a>.</div>
                        </div>
                    @endif

                    <form id="sendForm" class="mt-7 grid gap-5" action="{{ route('marketing.send') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label class="{{ $label }}" for="template">Template</label>
                            <select id="template" class="{{ $input }}" name="template_id">
                                <option value="">Choose a saved template</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('template_id') == $template->id)>{{ $template->name }}</option>
                                @endforeach
                            </select>
                            <div id="templateAttachmentNote" class="mt-2 hidden text-sm text-slate-500 dark:text-slate-400"></div>
                        </div>

                        <div id="templateSubjectChoiceWrap" class="hidden">
                            <label class="{{ $label }}" for="templateSubjectChoice">Saved subject</label>
                            <select id="templateSubjectChoice" class="{{ $input }}"></select>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="marketingMailService">Send using</label>
                                <select id="marketingMailService" class="{{ $input }} @error('marketing_mail_service_id') border-red-500 @enderror" name="marketing_mail_service_id">
                                    <option value="">Default mail settings</option>
                                    @foreach ($mailServices as $service)
                                        <option value="{{ $service->id }}" @selected(old('marketing_mail_service_id') == $service->id)>{{ $service->name }} · {{ $service->from_email }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Add and manage SMTP services from Settings → Email settings.</p>
                            </div>
                            <div>
                                <label class="{{ $label }}" for="marketingSignature">Email signature</label>
                                <select id="marketingSignature" class="{{ $input }} @error('marketing_signature_id') border-red-500 @enderror" name="marketing_signature_id">
                                    <option value="">No uploaded signature</option>
                                    @foreach ($signatures as $signature)
                                        <option value="{{ $signature->id }}" @selected(old('marketing_signature_id') == $signature->id)>{{ $signature->name }} · {{ $signature->image_name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">The selected image is embedded with the email and the open-tracking pixel remains active.</p>
                            </div>
                        </div>

                        <div>
                            <label class="{{ $label }}" for="recipients">Email addresses</label>
                            <input id="recipients" class="{{ $input }} @error('recipients') border-red-500 @enderror" type="text" name="recipients" value="{{ old('recipients', request('recipients')) }}" placeholder="name@example.com, second@example.com">
                            @error('recipients')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="subject">Email title</label>
                            <input id="subject" class="{{ $input }} @error('subject') border-red-500 @enderror" type="text" name="subject" value="{{ old('subject') }}">
                            @error('subject')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="content">Email content</label>
                            <textarea id="content" class="{{ $input }} min-h-44 @error('content') border-red-500 @enderror" name="content">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="attachment">Attach file</label>
                            <input id="attachment" class="{{ $input }} @error('attachment') border-red-500 @enderror" type="file" name="attachment">
                            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">Uploading a file here replaces the selected template attachment for this send only.</div>
                            @error('attachment')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button id="sendButton" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50" type="submit" @disabled($mailServices->isEmpty() || $signatures->isEmpty())>
                                <i class="bi bi-send"></i>
                                Send email
                            </button>
                        </div>
                    </form>

                    <div id="sendError" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"></div>
                @elseif ($activeTab === 'sent-emails')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Sent emails</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Review emails you sent, delivery status, and open status.</p>

                    <form action="{{ route('marketing') }}" method="GET" class="{{ $card }} mt-6 grid gap-3 p-4 md:grid-cols-[minmax(0,1.5fr)_180px_180px_150px_150px_auto] md:items-end">
                        <input type="hidden" name="tab" value="sent-emails">
                        <div><label class="{{ $label }}" for="sentEmailSearch">Search emails</label><input id="sentEmailSearch" name="sent_search" type="search" class="{{ $input }}" value="{{ $sentEmailSearch }}" placeholder="Subject, recipient, or campaign"></div>
                        <div><label class="{{ $label }}" for="sentEmailStatus">Delivery</label><select id="sentEmailStatus" name="sent_status" class="{{ $input }}"><option value="">All statuses</option>@foreach ($sentEmailStatusOptions as $status)<option value="{{ $status }}" @selected($sentEmailStatus === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                        <div><label class="{{ $label }}" for="sentEmailOpened">Open status</label><select id="sentEmailOpened" name="sent_opened" class="{{ $input }}"><option value="">All opens</option><option value="opened" @selected($sentEmailOpened === 'opened')>Opened</option><option value="not-opened" @selected($sentEmailOpened === 'not-opened')>Not opened</option></select></div>
                        <div><label class="{{ $label }}" for="sentEmailFrom">From date</label><input id="sentEmailFrom" name="sent_from" type="date" class="{{ $input }}" value="{{ $sentEmailFrom }}"></div>
                        <div><label class="{{ $label }}" for="sentEmailTo">To date</label><input id="sentEmailTo" name="sent_to" type="date" class="{{ $input }}" value="{{ $sentEmailTo }}"></div>
                        <div class="flex items-center gap-2"><button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('marketing', ['tab' => 'sent-emails']) }}" class="text-sm font-medium text-slate-500 hover:text-emerald-700 dark:text-slate-400 dark:hover:text-emerald-300">Clear</a></div>
                    </form>
                    @if ($sentEmailDateErrors)
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">@foreach ($sentEmailDateErrors as $message)<p>{{ $message }}</p>@endforeach</div>
                    @endif
                    <div class="mt-5 flex items-center justify-between gap-3"><p class="text-sm text-slate-500 dark:text-slate-400">Showing {{ $sentEmails->count() }} of {{ $sentEmailTotal }} sent email{{ $sentEmailTotal === 1 ? '' : 's' }}</p></div>

                    <div class="mt-7 grid gap-3">
                        @forelse ($sentEmails as $email)
                            @php
                                $openedCount = $email->opens->whereNotNull('opened_at')->count();
                                $deliveryStatus = $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'failed');
                                $sentAt = $email->sent_at ?: $email->created_at;
                                $emailRecipients = collect($email->recipients ?: [])->filter()->values();
                                $visibleEmailRecipients = $emailRecipients->take(6);
                                $hiddenEmailRecipientsCount = max($emailRecipients->count() - $visibleEmailRecipients->count(), 0);
                                $openedEmailRecipients = $email->opens
                                    ->whereNotNull('opened_at')
                                    ->pluck('email')
                                    ->map(fn ($recipient) => strtolower(trim((string) $recipient)))
                                    ->flip();
                            @endphp
                            <div class="{{ $card }} block p-4 transition hover:border-slate-300 hover:shadow-md dark:hover:border-slate-700">
                                <div class="grid items-center gap-4 md:grid-cols-[minmax(0,1fr)_auto_auto_auto_auto]">
                                    <div class="min-w-0">
                                        <strong class="font-medium text-slate-900 dark:text-slate-100">{{ $email->subject }}</strong>
                                        <div class="{{ $muted }}">
                                            {{ $email->recipient_count }} recipient{{ $email->recipient_count === 1 ? '' : 's' }}
                                            &middot;
                                            {{ $openedCount }} opened
                                            &middot;
                                            {{ optional($sentAt)->format('M d, Y h:i A') }}
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @forelse ($visibleEmailRecipients as $recipient)
                                                @php
                                                    $recipientOpened = $openedEmailRecipients->has(strtolower(trim((string) $recipient)));
                                                @endphp
                                                <span class="inline-flex max-w-full items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $recipientOpened ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                                    <i class="bi bi-envelope"></i>
                                                    <span class="max-w-56 truncate">{{ $recipient }}</span>
                                                </span>
                                            @empty
                                                <span class="text-xs text-slate-500 dark:text-slate-400">No recipient emails saved.</span>
                                            @endforelse
                                            @if ($hiddenEmailRecipientsCount > 0)
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                    +{{ $hiddenEmailRecipientsCount }} more
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($deliveryStatus === 'delivered')
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Delivered</span>
                                    @elseif ($deliveryStatus === 'pending')
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Pending</span>
                                    @else
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">Not delivered</span>
                                    @endif

                                    @if ($openedCount > 0)
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Opened</span>
                                    @else
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Not opened</span>
                                    @endif

                                    <button type="button" data-sent-email-view data-email-id="{{ $email->id }}" class="inline-flex min-w-24 items-center justify-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                        <i class="bi bi-eye"></i>
                                        View
                                    </button>
                                    <a class="inline-flex min-w-32 items-center justify-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10" href="{{ route('marketing', ['tab' => 'followups-create', 'email' => $email->id]) }}">
                                        <i class="bi bi-calendar-plus"></i>
                                        Followup
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No sent emails yet.</div>
                        @endforelse
                    </div>

                    @php
                        $sentEmailModalData = $sentEmails->mapWithKeys(function ($email) {
                            $deliveryStatus = $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'failed');
                            $sentAt = $email->sent_at ?: $email->created_at;
                            $openedRecords = $email->opens->whereNotNull('opened_at')->sortBy('opened_at')->values();
                            $emailIsHtml = $email->content_format === 'html' || strip_tags((string) $email->body) !== (string) $email->body;
                            $templateName = $email->template_name ?: ($email->campaignStep?->template?->name ?: 'Custom email');

                            return [$email->id => [
                                'subject' => $email->subject,
                                'recipient_count' => $email->recipient_count,
                                'delivery_status' => $deliveryStatus,
                                'sent_count' => $email->sent_count,
                                'failed_count' => $email->failed_count,
                                'sent_at' => $sentAt?->format('M d, Y h:i A'),
                                'opened_count' => $openedRecords->count(),
                                'first_opened_at' => $openedRecords->first()?->opened_at?->format('M d, Y h:i A'),
                                'campaign_name' => $email->campaign?->name ?: 'Direct email',
                                'step' => $email->campaignStep?->step_order,
                                'template_name' => $templateName,
                                'body_html' => \App\Support\MarketingHtml::render((string) $email->body, $emailIsHtml),
                                'format' => $emailIsHtml ? 'HTML email' : 'Plain text',
                                'from_email' => $email->mailService?->from_email ?: 'Default mail settings',
                                'mail_service' => $email->mailService?->name ?: 'Default mail settings',
                                'signature' => $email->signature?->name ?: 'No signature recorded',
                                'attachment' => $email->attachment_name ?: 'None',
                                'delivery_error' => $email->delivery_error ?: 'No failure reason was recorded.',
                                'retry_url' => route('marketing.campaigns.email.retry', $email->id),
                                'recipients' => collect($email->recipients ?: [])->map(function ($recipient) use ($email) {
                                    $open = $email->opens->first(fn ($item) => strtolower(trim((string) $item->email)) === strtolower(trim((string) $recipient)));

                                    return [
                                        'email' => $recipient,
                                        'status' => $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'failed'),
                                        'opened_at' => $open?->opened_at?->format('M d, Y h:i A'),
                                        'open_count' => $open?->open_count ?: 0,
                                    ];
                                })->values()->all(),
                            ]];
                        })->all();
                    @endphp

                    <div id="sentEmailDetailModal" class="fixed inset-0 z-[80] hidden" aria-hidden="true">
                        <button type="button" data-sent-email-backdrop class="absolute inset-0 bg-slate-950/40 backdrop-blur-[2px]" aria-label="Close email details"></button>
                        <div class="relative flex h-full items-center justify-center overflow-y-auto p-4 md:p-8">
                            <div data-sent-email-modal-panel role="dialog" aria-modal="true" aria-labelledby="sentEmailModalTitle" class="sent-email-detail-modal-panel relative my-auto w-full max-w-6xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 md:px-7 dark:border-slate-700">
                                    <div class="min-w-0"><span class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Sent email details</span><h2 id="sentEmailModalTitle" data-sent-email-title class="mt-1 truncate text-xl font-semibold text-slate-900 dark:text-white"></h2><p data-sent-email-subtitle class="mt-1 text-sm text-slate-500 dark:text-slate-400"></p></div>
                                    <button type="button" data-sent-email-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300" aria-label="Close email details"><i class="bi bi-x-lg"></i></button>
                                </div>
                                <div class="max-h-[calc(100vh-10rem)] overflow-y-auto p-5 md:p-7">
                                    <div class="flex flex-wrap items-center justify-between gap-3"><span data-sent-email-status class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold"></span><form data-sent-email-retry-form method="POST" class="hidden">@csrf<button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"><i class="bi bi-arrow-repeat"></i> Retry email</button></form></div>
                                    <div data-sent-email-error class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"><div class="flex items-start gap-3"><i class="bi bi-exclamation-triangle mt-0.5"></i><div><strong>Delivery failed</strong><p data-sent-email-error-text class="mt-1 whitespace-pre-wrap break-words"></p></div></div></div>
                                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4"><div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><span class="{{ $muted }}">Sent at</span><strong data-sent-email-sent-at class="mt-2 block text-sm font-semibold text-slate-900 dark:text-white"></strong></div><div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><span class="{{ $muted }}">Open activity</span><strong data-sent-email-opened class="mt-2 block text-sm font-semibold text-slate-900 dark:text-white"></strong></div><div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><span class="{{ $muted }}">Campaign</span><strong data-sent-email-campaign class="mt-2 block truncate text-sm font-semibold text-slate-900 dark:text-white"></strong></div><div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><span class="{{ $muted }}">Template</span><strong data-sent-email-template class="mt-2 block truncate text-sm font-semibold text-slate-900 dark:text-white"></strong></div></div>
                                    <div class="mt-5 grid items-start gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(280px,.65fr)]"><section class="rounded-lg border border-slate-200 p-5 dark:border-slate-700"><div class="flex items-center justify-between gap-3"><h3 class="text-lg font-semibold text-slate-900 dark:text-white">Email content</h3><span data-sent-email-format class="text-xs font-semibold text-slate-500 dark:text-slate-400"></span></div><div class="mt-4"><span class="{{ $muted }}">Subject</span><p data-sent-email-subject class="mt-1 font-semibold text-slate-900 dark:text-white"></p></div><div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700"><span class="{{ $muted }}">Message</span><div data-sent-email-body class="marketing-email-detail-body mt-3 max-h-96 overflow-auto rounded-lg border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></div></div></section><aside class="rounded-lg border border-slate-200 p-5 dark:border-slate-700"><h3 class="text-lg font-semibold text-slate-900 dark:text-white">Sending details</h3><dl class="mt-4 divide-y divide-slate-200 text-sm dark:divide-slate-700"><div class="flex items-start justify-between gap-3 py-3 first:pt-0"><dt class="{{ $muted }}">From email</dt><dd data-sent-email-from class="text-right font-medium text-slate-900 dark:text-white"></dd></div><div class="flex items-start justify-between gap-3 py-3"><dt class="{{ $muted }}">SMTP service</dt><dd data-sent-email-service class="text-right font-medium text-slate-900 dark:text-white"></dd></div><div class="flex items-start justify-between gap-3 py-3"><dt class="{{ $muted }}">Signature</dt><dd data-sent-email-signature class="text-right font-medium text-slate-900 dark:text-white"></dd></div><div class="flex items-start justify-between gap-3 py-3 last:pb-0"><dt class="{{ $muted }}">Attachment</dt><dd data-sent-email-attachment class="text-right font-medium text-slate-900 dark:text-white"></dd></div></dl></aside></div>
                                    <section class="mt-5 rounded-lg border border-slate-200 p-5 dark:border-slate-700"><div class="flex items-center justify-between gap-3"><div><h3 class="text-lg font-semibold text-slate-900 dark:text-white">Recipients</h3><p data-sent-email-recipient-count class="mt-1 text-sm text-slate-500 dark:text-slate-400"></p></div></div><div data-sent-email-recipients class="mt-4 grid gap-2"></div></section>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        (() => {
                            const modal = document.getElementById('sentEmailDetailModal');
                            if (!modal) return;
                            const data = @json($sentEmailModalData);
                            const panel = modal.querySelector('[data-sent-email-modal-panel]');
                            const retryForm = modal.querySelector('[data-sent-email-retry-form]');
                            const elements = {
                                title: modal.querySelector('[data-sent-email-title]'), subtitle: modal.querySelector('[data-sent-email-subtitle]'), status: modal.querySelector('[data-sent-email-status]'), error: modal.querySelector('[data-sent-email-error]'), errorText: modal.querySelector('[data-sent-email-error-text]'), sentAt: modal.querySelector('[data-sent-email-sent-at]'), opened: modal.querySelector('[data-sent-email-opened]'), campaign: modal.querySelector('[data-sent-email-campaign]'), template: modal.querySelector('[data-sent-email-template]'), subject: modal.querySelector('[data-sent-email-subject]'), format: modal.querySelector('[data-sent-email-format]'), body: modal.querySelector('[data-sent-email-body]'), from: modal.querySelector('[data-sent-email-from]'), service: modal.querySelector('[data-sent-email-service]'), signature: modal.querySelector('[data-sent-email-signature]'), attachment: modal.querySelector('[data-sent-email-attachment]'), recipientCount: modal.querySelector('[data-sent-email-recipient-count]'), recipients: modal.querySelector('[data-sent-email-recipients]'),
                            };
                            let previousOverflow = '';
                            let modalPoll = null;
                            const setText = (element, value) => { if (element) element.textContent = value || '—'; };
                            const closeModal = () => { if (modalPoll) { window.clearInterval(modalPoll); modalPoll = null; } modal.classList.remove('is-open'); document.body.style.overflow = previousOverflow; setTimeout(() => { modal.classList.add('hidden'); modal.setAttribute('aria-hidden', 'true'); }, 160); };
                            const statusClasses = { delivered: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300', pending: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300', failed: 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300' };
                            const refreshOpenActivity = id => fetch('{{ route('marketing.progress', ['email' => '__ID__']) }}'.replace('__ID__', id), { headers: { Accept: 'application/json' } })
                                .then(response => response.ok ? response.json() : null)
                                .then(progress => {
                                    if (!progress || !data[id]) return;
                                    const email = data[id];
                                    email.opened_count = progress.opened_count || 0;
                                    email.first_opened_at = progress.first_opened_at || null;
                                    setText(elements.opened, email.opened_count + ' opened' + (email.first_opened_at ? ' · First: ' + email.first_opened_at : ' · Not opened'));
                                    (progress.recipients || []).forEach(recipient => {
                                        const key = String(recipient.email || '').trim().toLowerCase();
                                        const row = Array.from(elements.recipients.querySelectorAll('[data-sent-modal-recipient]')).find(item => item.dataset.sentModalRecipient === key);
                                        if (!row || !recipient.opened_at) return;
                                        const meta = row.querySelector('[data-sent-modal-recipient-meta]');
                                        const status = row.querySelector('[data-sent-modal-recipient-status]');
                                        if (meta) meta.textContent = 'Delivered · Opened ' + recipient.opened_at + ' · ' + recipient.open_count + ' open' + (recipient.open_count === 1 ? '' : 's');
                                        if (status) {
                                            status.textContent = 'Opened';
                                            status.className = 'text-xs font-semibold text-emerald-700 dark:text-emerald-300';
                                        }
                                    });
                                })
                                .catch(() => {});
                            const openModal = id => {
                                const email = data[id];
                                if (!email) return;
                                setText(elements.title, email.subject);
                                setText(elements.subtitle, email.recipient_count + ' recipient' + (email.recipient_count === 1 ? '' : 's') + ' · ' + email.campaign_name + (email.step ? ' · Step ' + email.step : ''));
                                elements.status.className = 'inline-flex rounded-full px-3 py-1.5 text-xs font-semibold ' + (statusClasses[email.delivery_status] || statusClasses.pending);
                                elements.status.textContent = email.delivery_status.charAt(0).toUpperCase() + email.delivery_status.slice(1);
                                elements.error.classList.toggle('hidden', email.delivery_status !== 'failed');
                                setText(elements.errorText, email.delivery_error);
                                retryForm.classList.toggle('hidden', email.delivery_status !== 'failed');
                                retryForm.action = email.retry_url;
                                setText(elements.sentAt, email.sent_at || 'Not sent');
                                setText(elements.opened, email.opened_count + ' opened' + (email.first_opened_at ? ' · First: ' + email.first_opened_at : ' · Not opened'));
                                setText(elements.campaign, email.campaign_name + (email.step ? ' · Step ' + email.step : ''));
                                setText(elements.template, email.template_name);
                                setText(elements.subject, email.subject);
                                setText(elements.format, email.format);
                                elements.body.innerHTML = email.body_html;
                                setText(elements.from, email.from_email);
                                setText(elements.service, email.mail_service);
                                setText(elements.signature, email.signature);
                                setText(elements.attachment, email.attachment);
                                setText(elements.recipientCount, email.recipients.length + ' recipient' + (email.recipients.length === 1 ? '' : 's'));
                                elements.recipients.innerHTML = '';
                                email.recipients.forEach(recipient => {
                                    const row = document.createElement('div');
                                    row.className = 'flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700';
                                    row.dataset.sentModalRecipient = String(recipient.email || '').trim().toLowerCase();
                                    const address = document.createElement('strong'); address.className = 'font-medium text-slate-900 dark:text-white'; address.textContent = recipient.email;
                                    const meta = document.createElement('span'); meta.dataset.sentModalRecipientMeta = 'true'; meta.className = 'text-xs text-slate-500 dark:text-slate-400'; meta.textContent = (recipient.status === 'delivered' ? 'Delivered' : recipient.status === 'pending' ? 'Pending' : 'Not delivered') + ' · ' + (recipient.opened_at ? 'Opened ' + recipient.opened_at + ' · ' + recipient.open_count + ' opens' : 'Not opened');
                                    const status = document.createElement('span'); status.dataset.sentModalRecipientStatus = 'true'; status.className = recipient.opened_at ? 'text-xs font-semibold text-emerald-700 dark:text-emerald-300' : 'text-xs font-semibold text-slate-500 dark:text-slate-400'; status.textContent = recipient.opened_at ? 'Opened' : 'Not opened';
                                    row.append(address, meta, status); elements.recipients.appendChild(row);
                                });
                                previousOverflow = document.body.style.overflow; document.body.style.overflow = 'hidden'; modal.classList.remove('hidden'); modal.setAttribute('aria-hidden', 'false'); requestAnimationFrame(() => modal.classList.add('is-open')); elements.title?.focus();
                                refreshOpenActivity(id);
                                if (modalPoll) window.clearInterval(modalPoll);
                                modalPoll = window.setInterval(() => refreshOpenActivity(id), 5000);
                            };
                            document.querySelectorAll('[data-sent-email-view]').forEach(button => button.addEventListener('click', () => openModal(button.dataset.emailId)));
                            modal.querySelector('[data-sent-email-close]')?.addEventListener('click', closeModal);
                            modal.querySelector('[data-sent-email-backdrop]')?.addEventListener('click', closeModal);
                            document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
                        })();
                    </script>
                @elseif ($activeTab === 'sent-email-detail')
                    @php
                        $sentAt = $selectedEmail->sent_at ?: $selectedEmail->created_at;
                        $deliveryStatus = $selectedEmail->delivery_status ?: ($selectedEmail->sent_at ? 'delivered' : 'failed');
                        $openedRecords = $selectedEmail->opens->whereNotNull('opened_at')->sortBy('opened_at')->values();
                        $templateName = $selectedEmail->template_name ?: ($selectedEmail->campaignStep?->template?->name ?: 'Custom email');
                        $campaignName = $selectedEmail->campaign?->name ?: 'Direct email';
                        $campaignStep = $selectedEmail->campaignStep;
                        $emailIsHtml = $selectedEmail->content_format === 'html' || strip_tags((string) $selectedEmail->body) !== (string) $selectedEmail->body;
                    @endphp
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start" data-sent-email-live data-email-id="{{ $selectedEmail->id }}">
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $selectedEmail->subject }}</h1>
                            <p class="mt-2 text-slate-500 dark:text-slate-400">
                                {{ $selectedEmail->recipient_count }} recipient{{ $selectedEmail->recipient_count === 1 ? '' : 's' }}
                                &middot;
                                {{ $campaignName }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($deliveryStatus === 'failed')
                                <form action="{{ route('marketing.campaigns.email.retry', $selectedEmail->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"><i class="bi bi-arrow-repeat"></i> Retry email</button>
                                </form>
                            @endif
                            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'sent-emails']) }}"><i class="bi bi-arrow-left"></i> Back</a>
                        </div>
                    </div>

                    @if ($deliveryStatus === 'failed')
                        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                            <div class="flex items-start gap-3"><i class="bi bi-exclamation-triangle mt-0.5"></i><div class="min-w-0"><strong>Email delivery failed</strong><p class="mt-1 whitespace-pre-wrap break-words">{{ $selectedEmail->delivery_error ?: 'No failure reason was recorded.' }}</p></div></div>
                        </div>
                    @endif

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="{{ $card }} p-4"><span class="{{ $muted }}">Delivery status</span><strong class="mt-2 block text-lg capitalize text-slate-900 dark:text-white">{{ $deliveryStatus }}</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $selectedEmail->sent_count }} sent · {{ $selectedEmail->failed_count }} failed</span></div>
                        <div class="{{ $card }} p-4"><span class="{{ $muted }}">Sent at</span><strong class="mt-2 block text-lg text-slate-900 dark:text-white">{{ $sentAt ? $sentAt->format('M d, Y') : 'Not sent' }}</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $sentAt ? $sentAt->format('h:i A') : 'Waiting in queue' }}</span></div>
                        <div class="{{ $card }} p-4"><span class="{{ $muted }}">Open activity</span><strong data-sent-detail-open-count class="mt-2 block text-lg text-slate-900 dark:text-white">{{ $openedRecords->count() }} opened</strong><span data-sent-detail-open-first class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $openedRecords->first()?->opened_at?->format('M d, Y h:i A') ?: 'Not opened' }}</span></div>
                        <div class="{{ $card }} p-4"><span class="{{ $muted }}">Campaign</span><strong class="mt-2 block truncate text-lg text-slate-900 dark:text-white">{{ $campaignName }}</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $campaignStep ? 'Step '.$campaignStep->step_order : 'One-time email' }}</span></div>
                    </div>

                    <div class="mt-5 grid items-start gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(280px,.65fr)]">
                        <section class="{{ $card }} p-5">
                            <div class="flex items-start justify-between gap-4"><div><h2 class="text-xl font-semibold text-slate-900 dark:text-white">Email content</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The exact subject and message content used for this delivery.</p></div><span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $emailIsHtml ? 'HTML email' : 'Plain text' }}</span></div>
                            <div class="mt-5"><span class="{{ $muted }}">Subject</span><p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $selectedEmail->subject }}</p></div>
                            <div class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800"><span class="{{ $muted }}">Message</span><div class="marketing-email-detail-body mt-3 overflow-x-auto rounded-lg border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{!! \App\Support\MarketingHtml::render((string) $selectedEmail->body, $emailIsHtml) !!}</div></div>
                        </section>
                        <aside class="{{ $card }} p-5"><h2 class="text-xl font-semibold text-slate-900 dark:text-white">Sending details</h2><dl class="mt-5 divide-y divide-slate-200 text-sm dark:divide-slate-800"><div class="flex items-start justify-between gap-4 py-3 first:pt-0"><dt class="{{ $muted }}">Template</dt><dd class="text-right font-medium text-slate-900 dark:text-white">{{ $templateName }}</dd></div><div class="flex items-start justify-between gap-4 py-3"><dt class="{{ $muted }}">From email</dt><dd class="text-right font-medium text-slate-900 dark:text-white">{{ $selectedEmail->mailService?->from_email ?: 'Default mail settings' }}</dd></div><div class="flex items-start justify-between gap-4 py-3"><dt class="{{ $muted }}">SMTP service</dt><dd class="text-right font-medium text-slate-900 dark:text-white">{{ $selectedEmail->mailService?->name ?: 'Default mail settings' }}</dd></div><div class="flex items-start justify-between gap-4 py-3"><dt class="{{ $muted }}">Signature</dt><dd class="text-right font-medium text-slate-900 dark:text-white">{{ $selectedEmail->signature?->name ?: 'No signature recorded' }}</dd></div><div class="flex items-start justify-between gap-4 py-3 last:pb-0"><dt class="{{ $muted }}">Attachment</dt><dd class="text-right font-medium text-slate-900 dark:text-white">{{ $selectedEmail->attachment_name ?: 'None' }}</dd></div></dl></aside>
                    </div>

                    <div class="mt-7 flex items-center justify-between gap-4">
                        <span class="text-sm font-medium text-slate-900 dark:text-white">
                            Recipients ({{ $selectedRecipientStatuses->count() }})
                        </span>
                        <label class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            Sort by
                            <select id="recipientSort" class="{{ $input }} w-auto py-1.5 text-sm">
                                <option value="email" @selected($recipientSort === 'email')>Email</option>
                                <option value="opened" @selected($recipientSort === 'opened')>Opened first</option>
                                <option value="not-opened" @selected($recipientSort === 'not-opened')>Not opened first</option>
                                <option value="last-opened" @selected($recipientSort === 'last-opened')>Last opened</option>
                            </select>
                        </label>
                    </div>

                    <div class="mt-4 grid gap-3">
                        @foreach ($selectedRecipientStatuses as $recipient)
                            <div class="{{ $card }} grid items-center gap-4 p-4 md:grid-cols-[minmax(0,1fr)_auto_auto]" data-sent-recipient="{{ strtolower(trim((string) $recipient['email'])) }}">
                                <div>
                                    <strong class="font-medium text-slate-900 dark:text-slate-100">{{ $recipient['email'] }}</strong>
                                    @if ($recipient['last_opened_at'])
                                        <div data-sent-recipient-meta class="{{ $muted }}">
                                            Last opened {{ $recipient['last_opened_at']->format('M d, Y h:i A') }}
                                            &middot;
                                            {{ $recipient['open_count'] }} open{{ $recipient['open_count'] === 1 ? '' : 's' }}
                                        </div>
                                    @else
                                        <div data-sent-recipient-meta class="{{ $muted }}">No open recorded yet</div>
                                    @endif
                                </div>

                                @if ($recipient['delivery_status'] === 'delivered')
                                    <span class="inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Delivered</span>
                                @elseif ($recipient['delivery_status'] === 'pending')
                                    <span class="inline-flex min-w-28 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Pending</span>
                                @else
                                    <span class="inline-flex min-w-28 justify-center rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">Not delivered</span>
                                @endif

                                @if ($recipient['opened_at'])
                                    <span data-sent-recipient-status class="inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Opened</span>
                                @else
                                    <span data-sent-recipient-status class="inline-flex min-w-28 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Not opened</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <script>
                        (() => {
                            const root = document.querySelector('[data-sent-email-live]');
                            if (!root) return;
                            const emailId = root.dataset.emailId;
                            const progressUrl = '{{ route('marketing.progress', ['email' => '__ID__']) }}'.replace('__ID__', emailId);
                            const openedCount = document.querySelector('[data-sent-detail-open-count]');
                            const firstOpened = document.querySelector('[data-sent-detail-open-first]');
                            const recipientRows = new Map(Array.from(document.querySelectorAll('[data-sent-recipient]')).map(row => [row.dataset.sentRecipient, row]));
                            const refreshOpenActivity = () => fetch(progressUrl, { headers: { Accept: 'application/json' } })
                                .then(response => response.ok ? response.json() : null)
                                .then(data => {
                                    if (!data) return;
                                    if (openedCount) openedCount.textContent = (data.opened_count || 0) + ' opened';
                                    if (firstOpened) firstOpened.textContent = data.first_opened_at || 'Not opened';
                                    (data.recipients || []).forEach(recipient => {
                                        const row = recipientRows.get(String(recipient.email || '').trim().toLowerCase());
                                        if (!row) return;
                                        const meta = row.querySelector('[data-sent-recipient-meta]');
                                        const status = row.querySelector('[data-sent-recipient-status]');
                                        if (recipient.opened_at) {
                                            if (meta) meta.textContent = 'Last opened ' + recipient.opened_at + ' · ' + recipient.open_count + ' open' + (recipient.open_count === 1 ? '' : 's');
                                            if (status) {
                                                status.textContent = 'Opened';
                                                status.className = 'inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300';
                                            }
                                        }
                                    });
                                })
                                .catch(() => {});
                            refreshOpenActivity();
                            window.setInterval(refreshOpenActivity, 5000);
                        })();
                    </script>
                @elseif ($activeTab === 'followups-create')
                    @php
                        $sentAt = $selectedFollowupEmail->sent_at ?: $selectedFollowupEmail->created_at;
                    @endphp
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Schedule follow-up</h1>
                            <p class="mt-2 text-slate-500 dark:text-slate-400">Set a template and time for a follow-up to this sent email.</p>
                        </div>
                        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'sent-emails']) }}">
                            <i class="bi bi-arrow-left"></i>
                            Back
                        </a>
                    </div>

                    <div class="{{ $card }} mt-7 p-5">
                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $selectedFollowupEmail->subject }}</div>
                        <div class="{{ $muted }} mt-1">
                            {{ $selectedFollowupEmail->recipient_count }} recipient{{ $selectedFollowupEmail->recipient_count === 1 ? '' : 's' }}
                            &middot;
                            {{ optional($sentAt)->format('M d, Y h:i A') }}
                        </div>
                    </div>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.followups.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="marketing_email_id" value="{{ $selectedFollowupEmail->id }}">

                        <div>
                            <label class="{{ $label }}" for="followup_template">Template</label>
                            <select id="followup_template" class="{{ $input }} @error('template_id') border-red-500 @enderror" name="template_id">
                                <option value="">Choose a saved template</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('template_id') == $template->id)>{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="followup_scheduled_at">Follow-up date and time</label>
                            <input id="followup_scheduled_at" class="{{ $input }} @error('scheduled_at') border-red-500 @enderror" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}">
                            @error('scheduled_at')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-calendar-plus"></i>
                                Schedule follow-up
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'followups-edit')
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Edit follow-up</h1>
                            <p class="mt-2 text-slate-500 dark:text-slate-400">Update the template or date for this scheduled follow-up.</p>
                        </div>
                        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'followups']) }}">
                            <i class="bi bi-arrow-left"></i>
                            Back
                        </a>
                    </div>

                    <div class="{{ $card }} mt-7 p-5">
                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $editingFollowupEmail->subject }}</div>
                        <div class="{{ $muted }} mt-1">
                            {{ $editingFollowupEmail->recipient_count }} recipient{{ $editingFollowupEmail->recipient_count === 1 ? '' : 's' }}
                            &middot;
                            Original: {{ optional($editingFollowupEmail->originalEmail)->subject ?: 'Deleted email' }}
                        </div>
                    </div>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.followups.update', $editingFollowupEmail) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="{{ $label }}" for="edit_followup_template">Template</label>
                            <select id="edit_followup_template" class="{{ $input }} @error('template_id') border-red-500 @enderror" name="template_id">
                                <option value="">Choose a saved template</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('template_id', $editingFollowupEmail->marketing_template_id) == $template->id)>{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="edit_followup_scheduled_at">Follow-up date and time</label>
                            <input id="edit_followup_scheduled_at" class="{{ $input }} @error('scheduled_at') border-red-500 @enderror" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', optional($editingFollowupEmail->scheduled_at)->format('Y-m-d\TH:i')) }}">
                            @error('scheduled_at')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <a class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'followups']) }}">Cancel</a>
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-save"></i>
                                Update follow-up
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'followups')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Followup email</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Review follow-up emails scheduled from sent campaigns.</p>

                    <div class="mt-7 grid gap-3">
                        @forelse ($followupEmails as $followup)
                            @php
                                $followupRecipients = collect($followup->recipients ?: [])->filter()->values();
                                $visibleFollowupRecipients = $followupRecipients->take(6);
                                $hiddenFollowupRecipientsCount = max($followupRecipients->count() - $visibleFollowupRecipients->count(), 0);
                                $openedFollowupRecipients = $followup->opens
                                    ->whereNotNull('opened_at')
                                    ->pluck('email')
                                    ->map(fn ($recipient) => strtolower(trim((string) $recipient)))
                                    ->flip();
                                $followupTimeDistance = $followup->scheduled_at
                                    ? $followup->scheduled_at->diffForHumans(now(), true, false, 2)
                                    : null;
                                $followupTimeLabel = match (true) {
                                    ! $followup->scheduled_at => null,
                                    $followup->status === 'pending' && $followup->scheduled_at->isFuture() => 'Send after '.$followupTimeDistance,
                                    $followup->status === 'pending' => 'Due now',
                                    $followup->status === 'sent' && $followup->sent_at => 'Sent '.$followup->sent_at->diffForHumans(),
                                    $followup->scheduled_at->isPast() => 'Was due '.$followupTimeDistance.' ago',
                                    default => 'Scheduled after '.$followupTimeDistance,
                                };
                            @endphp
                            <div class="{{ $card }} grid items-center gap-4 p-4 md:grid-cols-[minmax(0,1fr)_auto_auto_auto]">
                                <div class="min-w-0">
                                    <strong class="block truncate font-medium text-slate-900 dark:text-slate-100">{{ $followup->subject }}</strong>
                                    <div class="{{ $muted }} mt-1">
                                        {{ $followup->recipient_count }} recipient{{ $followup->recipient_count === 1 ? '' : 's' }}
                                        &middot;
                                        Scheduled {{ optional($followup->scheduled_at)->format('M d, Y h:i A') }}
                                        @if ($followupTimeLabel)
                                            &middot;
                                            {{ $followupTimeLabel }}
                                        @endif
                                    </div>
                                    <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                        Original: {{ optional($followup->originalEmail)->subject ?: 'Deleted email' }}
                                        @if ($followup->template)
                                            &middot; Template: {{ $followup->template->name }}
                                        @endif
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @forelse ($visibleFollowupRecipients as $recipient)
                                            @php
                                                $recipientOpened = $openedFollowupRecipients->has(strtolower(trim((string) $recipient)));
                                                $recipientChipClass = match (true) {
                                                    $recipientOpened || $followup->status === 'sent' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                                    $followup->status === 'failed' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                                    default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                                                };
                                            @endphp
                                            <span class="inline-flex max-w-full items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $recipientChipClass }}">
                                                <i class="bi bi-envelope"></i>
                                                <span class="max-w-56 truncate">{{ $recipient }}</span>
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-500 dark:text-slate-400">No recipient emails saved.</span>
                                        @endforelse
                                        @if ($hiddenFollowupRecipientsCount > 0)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                +{{ $hiddenFollowupRecipientsCount }} more
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($followup->status === 'sent')
                                    <span class="inline-flex min-w-24 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Sent</span>
                                @elseif ($followup->status === 'failed')
                                    <span class="inline-flex min-w-24 justify-center rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">Failed</span>
                                @else
                                    <span class="inline-flex min-w-24 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Pending</span>
                                @endif

                                <span class="{{ $muted }}">{{ optional($followup->created_at)->format('M d, Y') }}</span>

                                <div class="flex flex-wrap justify-end gap-2">
                                    @if ($followup->status !== 'sent')
                                        <a class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10" href="{{ route('marketing', ['tab' => 'followups-edit', 'followup' => $followup->id]) }}">
                                            <i class="bi bi-pencil"></i>
                                            Edit
                                        </a>
                                    @endif
                                    <form action="{{ route('marketing.followups.delete', $followup) }}" method="POST" onsubmit="return confirm('Remove this follow-up email?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10" type="submit">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No follow-up emails scheduled yet.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'contacts-all')
                    @include('marketing.all-contacts', ['allContacts' => $allContacts, 'allContactCategories' => $allContactCategories, 'allContactsReady' => $allContactsReady])
                @elseif ($activeTab === 'active-clients')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Active Clients</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Contacts with a recorded email open across all campaigns and follow-ups.</p>

                    <div id="activeClientsControls" class="mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                        <div>
                            <label for="activeClientsSearch" class="{{ $label }}">Filter emails</label>
                            <input id="activeClientsSearch" type="search" class="{{ $input }}" placeholder="Search email, campaign or subject" autocomplete="off">
                        </div>
                        <div>
                            <label for="activeClientsCampaign" class="{{ $label }}">Campaign</label>
                            <select id="activeClientsCampaign" class="{{ $input }}">
                                <option value="">All campaigns</option>
                                @foreach ($activeClients->pluck('campaign_name')->unique()->sort() as $campaignName)
                                    <option value="{{ $campaignName }}">{{ $campaignName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="activeClientsTemplate" class="{{ $label }}">Template</label>
                            <select id="activeClientsTemplate" class="{{ $input }}">
                                <option value="">All templates</option>
                                @foreach ($activeClients->pluck('template_name')->unique()->sort() as $templateName)
                                    <option value="{{ $templateName }}">{{ $templateName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="activeClientsOpenedFrom" class="{{ $label }}">Opened from</label>
                            <input id="activeClientsOpenedFrom" type="date" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="activeClientsOpenedTo" class="{{ $label }}">Opened to</label>
                            <input id="activeClientsOpenedTo" type="date" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="activeClientsSort" class="{{ $label }}">Sort by</label>
                            <select id="activeClientsSort" class="{{ $input }}">
                                <option value="opened-desc">Date opened: newest first</option>
                                <option value="opened-asc">Date opened: oldest first</option>
                                <option value="sent-desc">Date sent: newest first</option>
                                <option value="sent-asc">Date sent: oldest first</option>
                                <option value="email-asc">Email: A–Z</option>
                                <option value="email-desc">Email: Z–A</option>
                                <option value="campaign-asc">Campaign: A–Z</option>
                                <option value="campaign-desc">Campaign: Z–A</option>
                                <option value="template-asc">Template name: A–Z</option>
                                <option value="template-desc">Template name: Z–A</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-sm font-medium text-slate-900 dark:text-white">{{ number_format($activeClients->pluck('email')->unique()->count()) }} active contacts &middot; {{ number_format($activeClients->count()) }} opened emails</span>
                        <span id="activeClientsFilteredCount" class="{{ $muted }}" aria-live="polite"></span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button id="exportActiveClients" type="button" class="rounded-lg border border-emerald-200 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10">
                            <i class="bi bi-download"></i>
                            Export filtered list
                        </button>
                        <button id="copyActiveClients" type="button" hidden class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-60">
                            <i class="bi bi-clipboard"></i>
                            Copy Emails
                        </button>
                        <button id="clearActiveClientsSelection" type="button" hidden class="text-sm font-medium text-emerald-700 hover:underline dark:text-emerald-300">Clear selection</button>
                        <span id="activeClientsSelectionCount" class="{{ $muted }}" aria-live="polite"></span>
                        <span id="activeClientsCopyStatus" class="text-sm text-emerald-700 dark:text-emerald-300" role="status"></span>
                    </div>
                    <textarea id="activeClientsCopyFallback" class="{{ $input }} mt-3" rows="3" readonly hidden aria-label="Selected email addresses to copy"></textarea>

                    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                        <table id="activeClientsTable" class="w-full text-left text-sm">
                            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="w-12 px-4 py-3"><input id="selectAllActiveClients" type="checkbox" class="h-4 w-4 accent-emerald-600" aria-label="Select all filtered records" @disabled($activeClients->isEmpty())></th>
                                    <th scope="col" class="px-4 py-3">Email</th>
                                    <th scope="col" class="px-4 py-3">Campaign</th>
                                    <th scope="col" class="px-4 py-3">Email template name</th>
                                    <th scope="col" class="whitespace-nowrap px-4 py-3">Date sent</th>
                                    <th scope="col" class="whitespace-nowrap px-4 py-3">Date opened</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($activeClients as $client)
                                    <tr data-active-client data-email="{{ $client['email'] }}" data-campaign="{{ $client['campaign_name'] }}" data-template="{{ $client['template_name'] }}" data-subject="{{ $client['subject'] }}" data-sent="{{ $client['sent_at']?->getTimestamp() }}" data-opened="{{ $client['opened_at']->getTimestamp() }}" data-opened-date="{{ $client['opened_at']->toDateString() }}">
                                        <td class="px-4 py-4"><input type="checkbox" data-active-client-select class="h-4 w-4 accent-emerald-600" aria-label="Select {{ $client['email'] }} — {{ $client['subject'] }}"></td>
                                        <td class="px-4 py-4 font-medium text-slate-900 dark:text-slate-100">{{ $client['email'] }}</td>
                                        <td class="px-4 py-4">
                                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $client['campaign_name'] }}</div>
                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $client['is_followup'] ? 'Follow-up' : 'Initial email' }}</div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $client['template_name'] }}</div>
                                            @if ($client['template_inferred'])
                                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Matched by subject and message</div>
                                            @endif
                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $client['subject'] }}{{ $client['is_followup'] ? ' · Follow-up' : '' }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-slate-600 dark:text-slate-300">{{ $client['sent_at']?->format('M d, Y h:i A') ?: 'Not recorded' }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-slate-600 dark:text-slate-300">{{ $client['opened_at']->format('M d, Y h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">No email opens recorded yet. Leads will appear here when an email open is tracked.</td>
                                    </tr>
                                @endforelse
                                <tr id="activeClientsNoMatches" hidden>
                                    <td colspan="6" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">No opened leads match your filters.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">Only recipients with a recorded open are shown. Date opened is the first recorded open. Tracking depends on email images loading. Export includes the currently filtered records.</p>
                @elseif ($activeTab === 'email-groups')
                    @include('marketing.email-groups')
                @elseif ($activeTab === 'contacts')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">My contacts</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Contacts are collected from email addresses you have sent messages to.</p>

                    @if ($contacts->isNotEmpty())
                        <div class="mt-7">
                            <div class="{{ $card }} p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">All emails ({{ $contacts->count() }})</span>
                                    <button id="copyContacts" type="button" class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10">
                                        <i class="bi bi-clipboard"></i>
                                        Copy
                                    </button>
                                </div>
                                <p id="contactsCsv" class="mt-3 break-words text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $contacts->implode(', ') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-7 grid gap-3">
                        @forelse ($contacts as $contact)
                            <div class="{{ $card }} flex items-center justify-between gap-4 p-4">
                                <span class="min-w-0 truncate">{{ $contact }}</span>
                                <div class="flex shrink-0 items-center gap-3">
                                    @if ($contactStatuses->get($contact, false))
                                        <span class="inline-flex min-w-20 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Verified</span>
                                    @else
                                        <span class="inline-flex min-w-20 justify-center rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">Failed</span>
                                    @endif
                                    <i class="bi bi-envelope text-slate-400"></i>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No contacts yet.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'contact-form')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Contact Form</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">All submissions from the home page contact form.</p>

                    <div class="mt-7 grid gap-4 md:grid-cols-3">
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Total submissions</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($contactFormCount) }}</strong>
                        </div>
                    </div>

                    <div class="mt-7 overflow-hidden rounded-lg border border-slate-200 dark:border-slate-800">
                        <div class="grid grid-cols-[1.4fr_1.4fr_1fr_1.2fr_1.4fr_auto] gap-4 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                            <span>Name</span>
                            <span>Email</span>
                            <span>Project type</span>
                            <span>Details</span>
                            <span>Submitted</span>
                            <span class="text-right">Actions</span>
                        </div>
                        <div class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse ($contactFormEntries as $entry)
                                @php
                                    $contactName = trim($entry->firstname.' '.$entry->lastname);
                                @endphp
                                <div class="grid grid-cols-[1.4fr_1.4fr_1fr_1.2fr_1.4fr_auto] gap-4 bg-white px-4 py-4 text-sm dark:bg-slate-900">
                                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ $contactName !== '' ? $contactName : 'Not provided' }}</span>
                                    <span class="truncate text-slate-700 dark:text-slate-200">{{ $entry->email }}</span>
                                    <span class="text-slate-700 dark:text-slate-200">{{ $entry->budget ?: 'Not specified' }}</span>
                                    <span class="truncate text-slate-700 dark:text-slate-200">{{ $entry->details }}</span>
                                    <span class="whitespace-nowrap text-slate-500 dark:text-slate-400">{{ optional($entry->created_at)->format('M d, Y h:i A') }}</span>
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing.contact-form.show', $entry) }}">
                                            <i class="bi bi-eye"></i>
                                            Detail
                                        </a>
                                        <a class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10" href="{{ route('marketing', ['tab' => 'send', 'recipients' => $entry->email]) }}">
                                            <i class="bi bi-reply"></i>
                                            Reply
                                        </a>
                                        <form action="{{ route('marketing.contact-form.destroy', $entry) }}" method="POST" onsubmit="return confirm('Delete this contact form submission?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10" type="submit">
                                                <i class="bi bi-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No contact form submissions yet.</div>
                            @endforelse
                        </div>
                    </div>
                @elseif ($activeTab === 'contact-form-detail')
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Contact Form Detail</h1>
                            <p class="mt-2 text-slate-500 dark:text-slate-400">Full submission details from the home page contact form.</p>
                        </div>
                        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing.contact-form') }}">
                            <i class="bi bi-arrow-left"></i>
                            Back
                        </a>
                    </div>

                    <div class="mt-7 grid gap-4 lg:grid-cols-2">
                        <div class="{{ $card }} p-5">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Name</div>
                            <div class="mt-2 text-lg font-medium text-slate-950 dark:text-white">{{ trim($selectedContactFormEntry->firstname.' '.$selectedContactFormEntry->lastname) ?: 'Not provided' }}</div>
                        </div>
                        <div class="{{ $card }} p-5">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Email</div>
                            <div class="mt-2 text-lg font-medium text-slate-950 dark:text-white">{{ $selectedContactFormEntry->email }}</div>
                        </div>
                        <div class="{{ $card }} p-5">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Project type</div>
                            <div class="mt-2 text-lg font-medium text-slate-950 dark:text-white">{{ $selectedContactFormEntry->budget ?: 'Not specified' }}</div>
                        </div>
                        <div class="{{ $card }} p-5 lg:col-span-2">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Details</div>
                            <div class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $selectedContactFormEntry->details }}</div>
                        </div>
                        <div class="{{ $card }} p-5 lg:col-span-2">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Submitted</div>
                            <div class="mt-2 text-lg font-medium text-slate-950 dark:text-white">{{ optional($selectedContactFormEntry->created_at)->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="lg:col-span-2 flex flex-wrap gap-2">
                            <a class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" href="{{ route('marketing', ['tab' => 'send', 'recipients' => $selectedContactFormEntry->email]) }}">
                                <i class="bi bi-reply"></i>
                                Reply
                            </a>
                            <form action="{{ route('marketing.contact-form.destroy', $selectedContactFormEntry) }}" method="POST" onsubmit="return confirm('Delete this contact form submission?');">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10" type="submit">
                                    <i class="bi bi-trash"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif ($activeTab === 'templates-create')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Create template</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Create a reusable email template for the send page.</p>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label class="{{ $label }}" for="name">Template name</label>
                            <input id="name" class="{{ $input }} @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name') }}">
                            @error('name')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $createSubjects = old('subjects', []);
                            $createSubjects = is_array($createSubjects) ? $createSubjects : [];
                            $createSubjects = array_pad(array_slice($createSubjects, 0, 5), 5, '');
                        @endphp
                        <div>
                            <label class="{{ $label }}" for="template_subject_0">Email titles</label>
                            <div class="grid gap-3">
                                @foreach ($createSubjects as $index => $subject)
                                    <input id="template_subject_{{ $index }}" class="{{ $input }} @error('subjects.'.$index) border-red-500 @enderror" type="text" name="subjects[]" value="{{ $subject }}" placeholder="Subject {{ $index + 1 }}{{ $index === 0 ? ' (required)' : '' }}">
                                    @error('subjects.'.$index)
                                        <div class="-mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            </div>
                            @error('subjects')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="template_content">Email template</label>
                            @include('marketing.rich-editor', ['editorId' => 'template_content_editor', 'inputId' => 'template_content', 'initialContent' => old('content', ''), 'hasError' => $errors->has('content')])
                        </div>

                        <div>
                            <label class="{{ $label }}" for="template_attachment">Template attachment</label>
                            <input id="template_attachment" class="{{ $input }} @error('attachment') border-red-500 @enderror" type="file" name="attachment">
                            @error('attachment')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-save"></i>
                                Save template
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'templates-edit')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Edit template</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Update this saved template.</p>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.templates.update', $editingTemplate) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="{{ $label }}" for="edit_name">Template name</label>
                            <input id="edit_name" class="{{ $input }} @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name', $editingTemplate->name) }}">
                            @error('name')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $editSubjects = old('subjects', $editingTemplate->subject_options ?: [$editingTemplate->subject]);
                            $editSubjects = is_array($editSubjects) ? $editSubjects : [];
                            $editSubjects = array_pad(array_slice($editSubjects, 0, 5), 5, '');
                        @endphp
                        <div>
                            <label class="{{ $label }}" for="edit_template_subject_0">Email titles</label>
                            <div class="grid gap-3">
                                @foreach ($editSubjects as $index => $subject)
                                    <input id="edit_template_subject_{{ $index }}" class="{{ $input }} @error('subjects.'.$index) border-red-500 @enderror" type="text" name="subjects[]" value="{{ $subject }}" placeholder="Subject {{ $index + 1 }}{{ $index === 0 ? ' (required)' : '' }}">
                                    @error('subjects.'.$index)
                                        <div class="-mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            </div>
                            @error('subjects')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="edit_template_content">Email template</label>
                            @include('marketing.rich-editor', ['editorId' => 'edit_template_content_editor', 'inputId' => 'edit_template_content', 'initialContent' => old('content', $editingTemplate->content), 'hasError' => $errors->has('content')])
                        </div>

                        <div>
                            <label class="{{ $label }}" for="edit_template_attachment">Template attachment</label>
                            @if ($editingTemplate->attachment_name)
                                <div class="mb-3 flex flex-col gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm dark:border-slate-800 dark:bg-slate-950 md:flex-row md:items-center md:justify-between">
                                    <span class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-200">
                                        <i class="bi bi-paperclip"></i>
                                        {{ $editingTemplate->attachment_name }}
                                    </span>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                        <input class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950" type="checkbox" name="remove_attachment" value="1">
                                        Remove attachment
                                    </label>
                                </div>
                            @endif
                            <input id="edit_template_attachment" class="{{ $input }} @error('attachment') border-red-500 @enderror" type="file" name="attachment">
                            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">Choose a new file to replace the current attachment.</div>
                            @error('attachment')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <a class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'templates-list']) }}">Cancel</a>
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-save"></i>
                                Update template
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'templates-list')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Template listings</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Manage saved templates for the send page.</p>

                    <div class="mt-7 grid gap-3">
                        @forelse ($templates as $template)
                            <div class="{{ $card }} grid items-center gap-4 p-4 md:grid-cols-[minmax(0,1fr)_auto]">
                                <div>
                                    <strong class="font-medium text-slate-900 dark:text-slate-100">{{ $template->name }}</strong>
                                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                        {{ count($template->subject_options ?: [$template->subject]) }} subject{{ count($template->subject_options ?: [$template->subject]) === 1 ? '' : 's' }}
                                    </div>
                                    @if ($template->attachment_name)
                                        <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            <i class="bi bi-paperclip"></i>
                                            {{ $template->attachment_name }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10" href="{{ route('marketing', ['tab' => 'templates-edit', 'template' => $template->id]) }}">
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>
                                    <form action="{{ route('marketing.templates.delete', $template) }}" method="POST" onsubmit="return confirm('Delete this template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10" type="submit">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No templates yet.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </main>
</section>

@if (in_array($activeTab, ['templates-create', 'templates-edit', 'campaigns-create'], true))
    @vite('resources/js/marketing-editor.js')
@endif

<script>
    const marketingWorkspace = document.getElementById('marketingWorkspace');
    const sidebarCollapseToggle = document.getElementById('sidebarCollapseToggle');
    const sidebarCollapsedStorageKey = 'marketing-sidebar-collapsed';

    function setSidebarCollapsed(collapsed) {
        if (!marketingWorkspace || !sidebarCollapseToggle) return;
        marketingWorkspace.classList.toggle('sidebar-collapsed', collapsed);
        sidebarCollapseToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        sidebarCollapseToggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        sidebarCollapseToggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        sidebarCollapseToggle.innerHTML = `<i class="bi ${collapsed ? 'bi-layout-sidebar' : 'bi-layout-sidebar-inset'}"></i>`;
        window.localStorage.setItem(sidebarCollapsedStorageKey, collapsed ? '1' : '0');
    }

    if (marketingWorkspace && sidebarCollapseToggle) {
        setSidebarCollapsed(window.localStorage.getItem(sidebarCollapsedStorageKey) === '1');
        sidebarCollapseToggle.addEventListener('click', () => {
            setSidebarCollapsed(!marketingWorkspace.classList.contains('sidebar-collapsed'));
        });
    }

    const templateSelect = document.getElementById('template');
    const templates = @json($templateOptions);
    const themeToggle = document.getElementById('themeToggle');
    const profileMenuWrapper = document.getElementById('profileMenuWrapper');
    const profileMenuToggle = document.getElementById('profileMenuToggle');
    const profileMenu = document.getElementById('profileMenu');

    function closeProfileMenu() {
        if (!profileMenu || !profileMenuToggle) {
            return;
        }

        profileMenu.classList.add('hidden');
        profileMenuToggle.setAttribute('aria-expanded', 'false');
    }

    if (profileMenuWrapper && profileMenuToggle && profileMenu) {
        profileMenuToggle.addEventListener('click', function () {
            const isOpen = !profileMenu.classList.contains('hidden');
            profileMenu.classList.toggle('hidden', isOpen);
            profileMenuToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });

        document.addEventListener('click', function (event) {
            if (!profileMenuWrapper.contains(event.target)) {
                closeProfileMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeProfileMenu();
                profileMenuToggle.focus();
            }
        });
    }

    if (templateSelect) {
        const templateAttachmentNote = document.getElementById('templateAttachmentNote');
        const templateSubjectChoiceWrap = document.getElementById('templateSubjectChoiceWrap');
        const templateSubjectChoice = document.getElementById('templateSubjectChoice');
        const subjectInput = document.getElementById('subject');

        function updateTemplateAttachmentNote(template) {
            if (!templateAttachmentNote) {
                return;
            }

            if (template && template.attachment_name) {
                templateAttachmentNote.textContent = 'Template attachment: ' + template.attachment_name;
                templateAttachmentNote.classList.remove('hidden');
                return;
            }

            templateAttachmentNote.textContent = '';
            templateAttachmentNote.classList.add('hidden');
        }

        function updateTemplateSubjectChoices(template, shouldSetSubject) {
            if (!templateSubjectChoice || !templateSubjectChoiceWrap || !subjectInput) {
                return;
            }

            templateSubjectChoice.innerHTML = '';

            if (!template || !Array.isArray(template.subjects) || template.subjects.length === 0) {
                templateSubjectChoiceWrap.classList.add('hidden');
                return;
            }

            template.subjects.forEach(function (subject, index) {
                const option = document.createElement('option');
                option.value = subject;
                option.textContent = subject || ('Subject ' + (index + 1));
                templateSubjectChoice.appendChild(option);
            });

            templateSubjectChoiceWrap.classList.remove('hidden');

            if (subjectInput.value && template.subjects.includes(subjectInput.value)) {
                templateSubjectChoice.value = subjectInput.value;
            }

            if (shouldSetSubject || !subjectInput.value) {
                subjectInput.value = template.subjects[0] || template.subject || '';
                templateSubjectChoice.value = subjectInput.value;
            }
        }

        updateTemplateAttachmentNote(templates[templateSelect.value]);
        updateTemplateSubjectChoices(templates[templateSelect.value], false);

        if (templateSubjectChoice) {
            templateSubjectChoice.addEventListener('change', function () {
                if (subjectInput) {
                    subjectInput.value = this.value;
                }
            });
        }

        templateSelect.addEventListener('change', function () {
            const content = document.getElementById('content');
            const template = templates[this.value];

            if (!template) {
                updateTemplateAttachmentNote(null);
                updateTemplateSubjectChoices(null, true);
                return;
            }

            content.value = template.content || '';
            updateTemplateAttachmentNote(template);
            updateTemplateSubjectChoices(template, true);
        });
    }

    const sendForm = document.getElementById('sendForm');
    const sendError = document.getElementById('sendError');

    if (sendForm) {
        const sendButton = document.getElementById('sendButton');
        const sendFormFields = sendForm.querySelectorAll('input, select, textarea, button');

        function showSendError(message) {
            if (!sendError) {
                alert(message);
                return;
            }

            sendError.textContent = message;
            sendError.classList.remove('hidden');
        }

        sendForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(sendForm);

            if (sendError) {
                sendError.classList.add('hidden');
            }

            sendButton.disabled = true;
            sendButton.classList.add('opacity-60');
            sendFormFields.forEach(function (field) {
                field.disabled = true;
            });

            fetch(sendForm.action, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(async function (response) {
                    if (response.redirected || response.status === 401 || response.status === 419) {
                        throw new Error('Your session has expired. Refresh the page and sign in again before sending.');
                    }

                    if (response.status === 413) {
                        throw new Error('The attachment exceeds the server upload limit. Choose a smaller file and try again.');
                    }

                    let payload;
                    try {
                        payload = JSON.parse(await response.text());
                    } catch (error) {
                        throw new Error('The server returned an unexpected response (HTTP ' + response.status + '). Check Sent emails before trying again.');
                    }

                    if (!response.ok) {
                        const message = payload && payload.errors
                            ? Object.values(payload.errors).flat().join(' ')
                            : (payload && payload.message) || 'Could not submit the form.';
                        throw new Error(message);
                    }

                    return payload;
                })
                .then(function (data) {
                    if (!data || !data.id) {
                        throw new Error('Could not start sending.');
                    }

                    window.location.href = '{{ route('marketing', ['tab' => 'dashboard']) }}';
                })
                .catch(function (error) {
                    sendButton.disabled = false;
                    sendButton.classList.remove('opacity-60');
                    sendFormFields.forEach(function (field) {
                        field.disabled = false;
                    });

                    showSendError(error.message);
                });
        });
    }

    function fetchMarketingProgress(emailId) {
        return fetch('{{ route('marketing.progress', ['email' => '__ID__']) }}'.replace('__ID__', emailId), {
            headers: { 'Accept': 'application/json' },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Progress request failed.');
                }

                return response.json();
            });
    }

    function initSendingProgress() {
        const cards = document.querySelectorAll('[data-progress-card]');

        cards.forEach(function (card) {
            const emailId = card.getAttribute('data-progress-id');
            const bar = card.querySelector('[data-progress-bar]');
            const label = card.querySelector('[data-progress-label]');
            const badge = card.querySelector('.animate-pulse');

            let total = parseInt(card.getAttribute('data-progress-total'), 10) || 0;
            let sent = parseInt(card.getAttribute('data-progress-sent'), 10) || 0;
            let failed = parseInt(card.getAttribute('data-progress-failed'), 10) || 0;

            function render() {
                const percent = total > 0 ? Math.min(Math.round(((sent + failed) / total) * 100), 100) : 0;

                if (bar) {
                    bar.style.width = percent + '%';
                }

                if (label) {
                    label.textContent = sent + '/' + total + ' sent' + (failed > 0 ? ' (' + failed + ' failed)' : '');
                }
            }

            render();

            (function poll() {
                fetchMarketingProgress(emailId)
                    .then(function (data) {
                        total = data.recipient_count || total;
                        sent = data.sent_count || 0;
                        failed = data.failed_count || 0;
                        render();

                        if (data.delivery_status !== 'pending') {
                            card.classList.add('opacity-60');

                            if (badge) {
                                badge.classList.remove('animate-pulse', 'bg-emerald-500');
                                badge.classList.add('bg-slate-400');
                            }

                            if (data.delivery_status === 'failed' && label) {
                                label.textContent = sent + '/' + total + ' sent' + ' (' + failed + ' failed)';
                            }

                            setTimeout(function () { card.remove(); }, 3000);

                            return;
                        }

                        setTimeout(poll, 1500);
                    })
                    .catch(function () {
                        setTimeout(poll, 3000);
                    });
            })();
        });
    }

    const recipientSort = document.getElementById('recipientSort');

    if (recipientSort) {
        recipientSort.addEventListener('change', function () {
            const url = new URL(window.location.href);

            if (this.value === 'email') {
                url.searchParams.delete('sort');
            } else {
                url.searchParams.set('sort', this.value);
            }

            window.location.href = url.toString();
        });
    }

    const activeClientsTable = document.getElementById('activeClientsTable');

    if (activeClientsTable) {
        const body = activeClientsTable.querySelector('tbody');
        const rows = Array.from(body.querySelectorAll('[data-active-client]'));
        const search = document.getElementById('activeClientsSearch');
        const campaignFilter = document.getElementById('activeClientsCampaign');
        const templateFilter = document.getElementById('activeClientsTemplate');
        const openedFrom = document.getElementById('activeClientsOpenedFrom');
        const openedTo = document.getElementById('activeClientsOpenedTo');
        const sort = document.getElementById('activeClientsSort');
        const selectAll = document.getElementById('selectAllActiveClients');
        const exportButton = document.getElementById('exportActiveClients');
        const copyButton = document.getElementById('copyActiveClients');
        const clearButton = document.getElementById('clearActiveClientsSelection');
        const selectionCount = document.getElementById('activeClientsSelectionCount');
        const filteredCount = document.getElementById('activeClientsFilteredCount');
        const copyStatus = document.getElementById('activeClientsCopyStatus');
        const fallback = document.getElementById('activeClientsCopyFallback');
        const noMatches = document.getElementById('activeClientsNoMatches');
        const checkbox = row => row.querySelector('[data-active-client-select]');
        const visibleRows = () => rows.filter(row => !row.hidden);
        const selectedRows = () => rows.filter(row => checkbox(row).checked);
        const selectedEmails = () => [...new Set(selectedRows().map(row => row.dataset.email))];

        function updateSelection() {
            const visible = visibleRows();
            const selected = selectedRows();
            const emailCount = selectedEmails().length;
            const visibleSelected = visible.filter(row => checkbox(row).checked).length;
            const hiddenSelected = selected.filter(row => row.hidden).length;
            selectAll.disabled = visible.length === 0;
            selectAll.checked = visible.length > 0 && visibleSelected === visible.length;
            selectAll.indeterminate = visibleSelected > 0 && visibleSelected < visible.length;
            copyButton.hidden = selected.length === 0;
            clearButton.hidden = selected.length === 0;
            selectionCount.textContent = selected.length
                ? selected.length + ' selected · ' + emailCount + ' unique email' + (emailCount === 1 ? '' : 's')
                    + (hiddenSelected ? ' (' + hiddenSelected + ' hidden by filters)' : '')
                : '';
            copyStatus.textContent = '';
            fallback.hidden = true;
            rows.forEach(row => {
                row.classList.toggle('bg-emerald-50', checkbox(row).checked);
                row.classList.toggle('dark:bg-emerald-500/10', checkbox(row).checked);
            });
        }

        function applyFilters() {
            const query = search.value.trim().toLowerCase();
            rows.forEach(row => {
                const text = [row.dataset.email, row.dataset.campaign, row.dataset.template, row.dataset.subject].join(' ').toLowerCase();
                row.hidden = !text.includes(query)
                    || (campaignFilter.value !== '' && row.dataset.campaign !== campaignFilter.value)
                    || (templateFilter.value !== '' && row.dataset.template !== templateFilter.value);
                if (openedFrom.value !== '' && row.dataset.openedDate < openedFrom.value) row.hidden = true;
                if (openedTo.value !== '' && row.dataset.openedDate > openedTo.value) row.hidden = true;
            });
            const count = visibleRows().length;
            filteredCount.textContent = 'Showing ' + count + ' of ' + rows.length + ' records';
            noMatches.hidden = rows.length === 0 || count > 0;
            updateSelection();
        }

        function applySort() {
            const [field, direction] = sort.value.split('-');
            const multiplier = direction === 'asc' ? 1 : -1;
            rows.sort((a, b) => {
                if (field === 'sent' || field === 'opened') {
                    // Missing dates always stay at the bottom in either direction.
                    if (!a.dataset[field] || !b.dataset[field]) {
                        return Number(!a.dataset[field]) - Number(!b.dataset[field]);
                    }
                    return (Number(a.dataset[field]) - Number(b.dataset[field])) * multiplier;
                }
                return a.dataset[field].localeCompare(b.dataset[field], undefined, { sensitivity: 'base', numeric: true }) * multiplier;
            });
            rows.forEach(row => body.appendChild(row));
            body.appendChild(noMatches);
        }

        rows.forEach(row => checkbox(row).addEventListener('change', updateSelection));
        search.addEventListener('input', applyFilters);
        campaignFilter.addEventListener('change', applyFilters);
        templateFilter.addEventListener('change', applyFilters);
        openedFrom.addEventListener('change', applyFilters);
        openedTo.addEventListener('change', applyFilters);
        sort.addEventListener('change', applySort);
        selectAll.addEventListener('change', () => {
            visibleRows().forEach(row => { checkbox(row).checked = selectAll.checked; });
            updateSelection();
        });
        clearButton.addEventListener('click', () => {
            rows.forEach(row => { checkbox(row).checked = false; });
            updateSelection();
        });
        copyButton.addEventListener('click', async () => {
            const emails = selectedEmails();
            if (emails.length === 0) return;
            const csv = emails.join(', ');
            copyButton.disabled = true;
            copyStatus.textContent = '';
            try {
                if (!navigator.clipboard || !navigator.clipboard.writeText) throw new Error('Clipboard unavailable');
                await navigator.clipboard.writeText(csv);
                copyStatus.textContent = 'Copied ' + emails.length + ' email' + (emails.length === 1 ? '' : 's') + '.';
            } catch (error) {
                fallback.value = csv;
                fallback.hidden = false;
                fallback.focus();
                fallback.select();
                let copied = false;
                try { copied = document.execCommand('copy'); } catch (copyError) {}
                if (copied) {
                    fallback.hidden = true;
                    copyButton.focus();
                    copyStatus.textContent = 'Copied ' + emails.length + ' email' + (emails.length === 1 ? '' : 's') + '.';
                } else {
                    copyStatus.textContent = 'Automatic copying is unavailable. Copy the selected addresses below.';
                }
            } finally {
                copyButton.disabled = false;
            }
        });

        exportButton.addEventListener('click', () => {
            const escapeCsv = value => `"${String(value ?? '').replaceAll('"', '""')}"`;
            const exportRows = visibleRows();
            const csvRows = [
                ['Email', 'Campaign', 'Email type', 'Template', 'Subject', 'Date sent', 'Date opened'],
                ...exportRows.map(row => {
                    const sent = row.querySelector('td:nth-child(5)')?.textContent.trim() || '';
                    const opened = row.querySelector('td:nth-child(6)')?.textContent.trim() || '';
                    return [row.dataset.email, row.dataset.campaign, row.querySelector('td:nth-child(3) .text-xs')?.textContent.trim() || '', row.dataset.template, row.dataset.subject, sent, opened];
                }),
            ];
            const csv = csvRows.map(row => row.map(escapeCsv).join(',')).join('\r\n');
            const link = document.createElement('a');
            link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
            link.download = 'opened-leads.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });

        applySort();
        applyFilters();
    }

    const copyContactsButton = document.getElementById('copyContacts');

    if (copyContactsButton) {
        const contactsCsv = document.getElementById('contactsCsv');

        copyContactsButton.addEventListener('click', function () {
            if (!contactsCsv || !navigator.clipboard || !navigator.clipboard.writeText) {
                return;
            }

            navigator.clipboard.writeText(contactsCsv.textContent.trim()).then(function () {
                const original = copyContactsButton.innerHTML;
                copyContactsButton.innerHTML = '<i class="bi bi-check2"></i> Copied';

                setTimeout(function () {
                    copyContactsButton.innerHTML = original;
                }, 1500);
            });
        });
    }

    initSendingProgress();

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('marketing-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        });
    }
</script>
</body>

</html>
