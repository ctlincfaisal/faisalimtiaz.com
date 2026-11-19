@php
    $dashboardEmailTrend = $dashboardEmailTrend ?? collect();
    $dashboardLeadCategories = $dashboardLeadCategories ?? collect();
    $dashboardCountryBreakdown = $dashboardCountryBreakdown ?? collect();
    $dashboardTopPages = $dashboardTopPages ?? collect();
    $analyticsTrend = $analyticsTrend ?? collect();
    $dashboardEmailsSent = (int) ($dashboardEmailsSent ?? 0);
    $dashboardEstimatedOpens = (int) ($dashboardEstimatedOpens ?? 0);
    $dashboardReplies = (int) ($dashboardReplies ?? 0);
    $dashboardLeadConversionRate = (float) ($dashboardLeadConversionRate ?? 0);
    $dashboardLeadTotal = (int) ($dashboardLeadTotal ?? 0);
    $activeVisitorsCount = (int) ($activeVisitorsCount ?? 0);
    $dashboardPeriod = (int) ($dashboardPeriod ?? 30);
    $dashboardPeriodLabel = $dashboardPeriodLabel ?? 'Last 30 days';
    $dashboardPeriodOptions = $dashboardPeriodOptions ?? [7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days', 365 => 'Last 12 months'];
    $pendingEmails = $pendingEmails ?? collect();
    $websiteVisitsTotal = (int) ($websiteVisitsTotal ?? 0);
    $websiteClicksTotal = (int) ($websiteClicksTotal ?? 0);
    $emailOpenRate = $dashboardEmailsSent > 0 ? round(($dashboardEstimatedOpens / $dashboardEmailsSent) * 100, 1) : 0;
    $websiteClickRate = $websiteVisitsTotal > 0 ? round(($websiteClicksTotal / $websiteVisitsTotal) * 100, 2) : 0;
    $sentRecent = $dashboardEmailTrend->slice(-7)->sum('sent');
    $sentPrevious = $dashboardEmailTrend->slice(0, 7)->sum('sent');
    $openRecent = $dashboardEmailTrend->slice(-7)->sum('opened');
    $openPrevious = $dashboardEmailTrend->slice(0, 7)->sum('opened');
    $sentChange = $sentPrevious > 0 ? round((($sentRecent - $sentPrevious) / $sentPrevious) * 100, 1) : 0;
    $openChange = $openPrevious > 0 ? round((($openRecent - $openPrevious) / $openPrevious) * 100, 1) : 0;
    $emailChartWidth = 820;
    $emailChartHeight = 230;
    $emailChartMax = max(1, (int) $dashboardEmailTrend->flatMap(fn ($point) => [$point['sent'], $point['opened']])->max());
    $emailCount = max(1, $dashboardEmailTrend->count());
    $dashboardPoints = function (string $key) use ($dashboardEmailTrend, $emailChartWidth, $emailChartHeight, $emailChartMax, $emailCount) {
        return $dashboardEmailTrend->values()->map(function ($point, $index) use ($key, $emailChartWidth, $emailChartHeight, $emailChartMax, $emailCount) {
            $x = $emailCount > 1 ? ($index / ($emailCount - 1)) * $emailChartWidth : 0;
            $y = $emailChartHeight - (($point[$key] / $emailChartMax) * ($emailChartHeight - 12));

            return round($x, 2).','.round($y, 2);
        })->implode(' ');
    };
    $sentPoints = $dashboardPoints('sent');
    $openedPoints = $dashboardPoints('opened');
    $sentArea = trim($sentPoints.' '.$emailChartWidth.','.$emailChartHeight.' 0,'.$emailChartHeight);
    $openedArea = trim($openedPoints.' '.$emailChartWidth.','.$emailChartHeight.' 0,'.$emailChartHeight);
    $visitorChartWidth = 560;
    $visitorChartHeight = 160;
    $visitorChartMax = max(1, (int) $analyticsTrend->flatMap(fn ($point) => [$point['visitors'], $point['pageviews']])->max());
    $visitorCount = max(1, $analyticsTrend->count());
    $visitorPoints = $analyticsTrend->values()->map(function ($point, $index) use ($visitorChartWidth, $visitorChartHeight, $visitorChartMax, $visitorCount) {
        $x = $visitorCount > 1 ? ($index / ($visitorCount - 1)) * $visitorChartWidth : 0;
        $y = $visitorChartHeight - (($point['visitors'] / $visitorChartMax) * ($visitorChartHeight - 10));

        return round($x, 2).','.round($y, 2);
    })->implode(' ');
    $pageviewPoints = $analyticsTrend->values()->map(function ($point, $index) use ($visitorChartWidth, $visitorChartHeight, $visitorChartMax, $visitorCount) {
        $x = $visitorCount > 1 ? ($index / ($visitorCount - 1)) * $visitorChartWidth : 0;
        $y = $visitorChartHeight - (($point['pageviews'] / $visitorChartMax) * ($visitorChartHeight - 10));

        return round($x, 2).','.round($y, 2);
    })->implode(' ');
    $countryMax = max(1, (int) $dashboardCountryBreakdown->max('count'));
@endphp

<style>
    .marketing-dashboard-page > .dashboard-website-overview { order: 1; }
    .marketing-dashboard-page > .dashboard-website-detail-grid { order: 2; }
    .marketing-dashboard-page > .dashboard-outreach-overview { order: 3; }
    .marketing-dashboard-page > .dashboard-pending-emails { order: 4; }
    .marketing-dashboard-page > .dashboard-outreach-detail-grid { order: 5; }
</style>

<div class="marketing-dashboard-page flex flex-col gap-5">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><span>Workspace</span><span>/</span><span class="font-medium text-emerald-700">Overview</span></div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Your outreach and website performance at a glance.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('marketing') }}" method="GET" class="flex items-center">
                <input type="hidden" name="tab" value="dashboard">
                @if ($selectedWebsite?->id)
                    <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                @endif
                <label class="sr-only" for="dashboardPeriod">Dashboard date range</label>
                <select id="dashboardPeriod" name="dashboard_period" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" onchange="this.form.submit()">
                    @foreach ($dashboardPeriodOptions as $days => $label)
                        <option value="{{ $days }}" @selected($dashboardPeriod === (int) $days)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('marketing', ['tab' => 'campaigns-create']) }}" class="marketing-primary-action inline-flex h-10 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-lg shadow-emerald-600/20 transition"><i class="bi bi-plus-lg"></i>Create campaign</a>
        </div>
    </div>

    <section class="dashboard-outreach-overview">
        <div class="mb-3 flex items-center justify-between gap-3"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Outreach overview</h2><a href="{{ route('marketing', ['tab' => 'campaigns-list']) }}" class="text-sm font-medium text-emerald-700 hover:underline">View campaigns</a></div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Emails sent', 'value' => number_format($dashboardEmailsSent), 'change' => $sentChange, 'comparison' => 'vs previous 7 days', 'icon' => 'bi-envelope', 'positive' => $sentChange >= 0],
                ['label' => 'Estimated opens', 'value' => number_format($dashboardEstimatedOpens), 'change' => $openChange, 'comparison' => $emailOpenRate.'% open rate', 'icon' => 'bi-eye', 'positive' => $openChange >= 0],
                ['label' => 'Replies', 'value' => number_format($dashboardReplies), 'change' => $dashboardReplies > 0 ? 'Active' : 'No replies', 'comparison' => 'from imported leads', 'icon' => 'bi-reply', 'positive' => $dashboardReplies > 0],
                ['label' => 'Lead conversion rate', 'value' => $dashboardLeadConversionRate.'%', 'change' => $dashboardLeadTotal.' leads', 'comparison' => 'replied leads', 'icon' => 'bi-funnel', 'positive' => $dashboardLeadConversionRate > 0],
            ] as $stat)
                <article class="{{ $card }} overflow-hidden p-4">
                    <div class="flex items-start justify-between gap-3"><span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-lg text-emerald-600"><i class="bi {{ $stat['icon'] }}"></i></span><span class="rounded-md {{ $stat['positive'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }} px-2 py-1 text-xs font-semibold">{{ is_numeric($stat['change']) ? ($stat['change'] >= 0 ? '↗ +'.$stat['change'].'%' : '↘ '.$stat['change'].'%') : $stat['change'] }}</span></div>
                    <div class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</div>
                    <strong class="mt-1 block text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $stat['value'] }}</strong>
                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $stat['comparison'] }}</div>
                    <svg class="mt-3 h-8 w-full" viewBox="0 0 240 40" preserveAspectRatio="none" aria-hidden="true"><polyline points="0,30 20,25 40,28 60,18 80,24 100,14 120,19 140,10 160,17 180,8 200,14 220,5 240,9" fill="none" stroke="#11b981" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" /></svg>
                </article>
            @endforeach
        </div>
    </section>

    @if ($pendingEmails->isNotEmpty())
        <section class="dashboard-pending-emails {{ $card }} p-4" id="sendingProgressList">
            <div class="flex items-center justify-between gap-3"><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Sending in progress</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Delivery continues in the background.</p></div><span class="text-sm font-medium text-emerald-700">{{ $pendingEmails->count() }} active</span></div>
            <div class="mt-3 grid gap-2">
                @foreach ($pendingEmails as $pendingEmail)
                    <div class="rounded-lg border border-slate-200 p-3" data-progress-card data-progress-id="{{ $pendingEmail->id }}" data-progress-total="{{ $pendingEmail->recipient_count }}" data-progress-sent="{{ $pendingEmail->sent_count }}" data-progress-failed="{{ $pendingEmail->failed_count }}">
                        <div class="flex items-center justify-between gap-3"><strong class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">{{ $pendingEmail->subject }}</strong><span class="text-xs font-semibold text-emerald-700" data-progress-label>{{ $pendingEmail->sent_count }}/{{ $pendingEmail->recipient_count }} sent</span></div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200"><div data-progress-bar class="h-full rounded-full bg-emerald-500 transition-all duration-300" style="width: {{ $pendingEmail->recipient_count > 0 ? min(round((($pendingEmail->sent_count + $pendingEmail->failed_count) / $pendingEmail->recipient_count) * 100), 100) : 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="dashboard-outreach-detail-grid grid gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(300px,.9fr)]">
        <section class="{{ $card }} overflow-hidden p-5">
            <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Email performance</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sent and opened emails over {{ strtolower($dashboardPeriodLabel) }}.</p></div><div class="marketing-chart-legend flex items-center gap-3 text-sm"><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-emerald-500"></i>Sent</span><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-sky-500"></i>Opened</span></div></div>
            <div class="mt-4 overflow-x-auto"><svg class="min-w-[620px] text-slate-500 dark:text-slate-400" viewBox="0 0 {{ $emailChartWidth }} {{ $emailChartHeight + 30 }}" role="img" aria-label="Email performance chart"><defs><linearGradient id="dashboardSentFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#11b981" stop-opacity=".28"/><stop offset="1" stop-color="#11b981" stop-opacity="0"/></linearGradient><linearGradient id="dashboardOpenedFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#1688cf" stop-opacity=".22"/><stop offset="1" stop-color="#1688cf" stop-opacity="0"/></linearGradient></defs>@for ($line = 0; $line <= 4; $line++)<line x1="0" x2="{{ $emailChartWidth }}" y1="{{ $line * ($emailChartHeight / 4) }}" y2="{{ $line * ($emailChartHeight / 4) }}" stroke="currentColor" stroke-opacity=".14"/>@endfor<polygon points="{{ $sentArea }}" fill="url(#dashboardSentFill)"/><polygon points="{{ $openedArea }}" fill="url(#dashboardOpenedFill)"/><polyline points="{{ $sentPoints }}" fill="none" stroke="#11b981" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"/><polyline points="{{ $openedPoints }}" fill="none" stroke="#1688cf" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"/>@foreach ($dashboardEmailTrend as $index => $point)@if ($index === 0 || $index === $dashboardEmailTrend->count() - 1 || $index % 5 === 0)<text x="{{ $dashboardEmailTrend->count() > 1 ? round(($index / ($dashboardEmailTrend->count() - 1)) * $emailChartWidth) : 0 }}" y="{{ $emailChartHeight + 22 }}" fill="currentColor" font-size="13" text-anchor="middle">{{ $point['label'] }}</text>@endif @endforeach</svg></div>
        </section>

        <section class="{{ $card }} p-5"><div class="flex items-center justify-between gap-3"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Best performing lead categories</h2><a href="{{ route('marketing', ['tab' => 'leads']) }}" class="text-emerald-700 hover:underline" aria-label="View leads"><i class="bi bi-arrow-up-right"></i></a></div><div class="mt-4 overflow-x-auto"><table class="w-full min-w-[420px] text-left text-sm"><thead class="bg-slate-100 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"><tr><th class="px-3 py-2">Category</th><th class="px-3 py-2">Conversion</th><th class="px-3 py-2">Trend</th></tr></thead><tbody class="divide-y divide-slate-200 dark:divide-slate-700">@forelse ($dashboardLeadCategories as $category)<tr><td class="px-3 py-3 font-medium text-slate-800 dark:text-slate-100"><a href="{{ route('marketing', ['tab' => 'leads', 'lead_segment' => $category['name'] === 'Uncategorized' ? null : $category['name']]) }}" class="hover:text-emerald-700">{{ $category['name'] }}</a></td><td class="px-3 py-3 font-semibold text-slate-900 dark:text-white">{{ $category['conversion'] }}%</td><td class="px-3 py-3 {{ $category['trend_positive'] ? 'text-emerald-700' : 'text-slate-500' }}">{{ $category['trend_positive'] ? '↗' : '—' }} {{ $category['trend'] }}</td></tr>@empty<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Import leads to see category performance.</td></tr>@endforelse</tbody></table></div></section>
    </div>

    <section class="dashboard-website-overview" data-live-visitors-card data-live-visitors-url="{{ route('marketing.dashboard.live-visitors', $selectedWebsite?->id ? ['website_id' => $selectedWebsite->id] : []) }}">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3"><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Website performance</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Analytics for the selected website.</p></div>@include('marketing.website-selector', ['selectorTab' => 'dashboard'])</div>
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ([['label' => 'Visitors', 'value' => number_format($activeVisitorsCount), 'change' => 'Online now', 'icon' => 'bi-people', 'live' => true], ['label' => 'Pageviews', 'value' => number_format($websiteVisitsTotal), 'change' => $dashboardPeriodLabel, 'icon' => 'bi-layers', 'live' => false], ['label' => 'Website click rate', 'value' => $websiteClickRate.'%', 'change' => number_format($websiteClicksTotal).' tracked clicks', 'icon' => 'bi-bullseye', 'live' => false]] as $stat)
                <article class="{{ $card }} overflow-hidden p-4"><div class="flex items-start justify-between gap-3"><span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-lg text-emerald-600"><i class="bi {{ $stat['icon'] }}"></i></span><span class="rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">{{ $stat['change'] }}</span></div><div class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</div><strong @if ($stat['live']) data-live-visitor-count aria-live="polite" @endif class="mt-1 block text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $stat['value'] }}</strong><svg class="mt-3 h-7 w-full" viewBox="0 0 240 40" preserveAspectRatio="none" aria-hidden="true"><polyline points="0,30 20,25 40,28 60,18 80,24 100,14 120,19 140,10 160,17 180,8 200,14 220,5 240,9" fill="none" stroke="#11b981" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" /></svg></article>
            @endforeach
        </div>
    </section>

    <div class="dashboard-website-detail-grid grid gap-5 lg:grid-cols-[minmax(0,1.05fr)_minmax(260px,.9fr)_minmax(0,1fr)]">
        <section class="{{ $card }} overflow-hidden p-5"><div class="flex items-center justify-between gap-3"><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Visitor trend</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Visitors and pageviews over {{ strtolower($dashboardPeriodLabel) }}.</p></div><div class="marketing-chart-legend flex items-center gap-3 text-xs"><span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-emerald-500"></i>Visitors</span><span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-sky-500"></i>Pageviews</span></div></div><div class="mt-4 overflow-x-auto"><svg class="min-w-[500px] text-slate-500 dark:text-slate-400" viewBox="0 0 {{ $visitorChartWidth }} {{ $visitorChartHeight + 24 }}" role="img" aria-label="Visitor trend chart">@for ($line = 0; $line <= 4; $line++)<line x1="0" x2="{{ $visitorChartWidth }}" y1="{{ $line * ($visitorChartHeight / 4) }}" y2="{{ $line * ($visitorChartHeight / 4) }}" stroke="currentColor" stroke-opacity=".14"/>@endfor<polyline points="{{ $visitorPoints }}" fill="none" stroke="#11b981" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"/><polyline points="{{ $pageviewPoints }}" fill="none" stroke="#1688cf" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"/>@foreach ($analyticsTrend as $index => $point)@if ($index === 0 || $index === $analyticsTrend->count() - 1 || $index % max(1, (int) floor($analyticsTrend->count() / 5)) === 0)<text x="{{ $analyticsTrend->count() > 1 ? round(($index / ($analyticsTrend->count() - 1)) * $visitorChartWidth) : 0 }}" y="{{ $visitorChartHeight + 18 }}" fill="currentColor" font-size="11" text-anchor="middle">{{ $point['label'] }}</text>@endif @endforeach</svg></div></section>

        <section class="{{ $card }} p-5"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Top countries</h2><div class="mt-4 space-y-3">@forelse ($dashboardCountryBreakdown as $country)<div><div class="flex items-center justify-between gap-3 text-sm"><span class="text-slate-700 dark:text-slate-200">{{ $country['name'] }}</span><strong class="text-slate-900 dark:text-white">{{ number_format($country['count']) }}</strong></div><div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-sky-500" style="width: {{ round(($country['count'] / $countryMax) * 100) }}%"></div></div></div>@empty<div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">No country data yet.</div>@endforelse</div></section>

        <section class="{{ $card }} p-5"><div class="flex items-center justify-between gap-3"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Best performing pages</h2><a href="{{ route('marketing', ['tab' => 'analytics']) }}" class="text-emerald-700 hover:underline" aria-label="View analytics"><i class="bi bi-arrow-up-right"></i></a></div><div class="mt-4 overflow-hidden"><table class="w-full text-left text-sm"><thead class="bg-slate-100 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"><tr><th class="px-3 py-2">Page</th><th class="px-3 py-2">Visitors</th><th class="px-3 py-2">Share</th></tr></thead><tbody class="divide-y divide-slate-200 dark:divide-slate-700">@forelse ($dashboardTopPages as $page)@php $pageShare = $websiteVisitsTotal > 0 ? round(($page->visits_count / $websiteVisitsTotal) * 100, 1) : 0; @endphp<tr><td class="max-w-[150px] truncate px-3 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $page->path ?: '/' }}</td><td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ number_format($page->visits_count) }}</td><td class="px-3 py-3 font-semibold text-emerald-700">{{ $pageShare }}%</td></tr>@empty<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Connect a website to see page performance.</td></tr>@endforelse</tbody></table></div></section>
    </div>
</div>

<script>
(() => {
    const card = document.querySelector('[data-live-visitors-card]');
    const count = card?.querySelector('[data-live-visitor-count]');
    const url = card?.dataset.liveVisitorsUrl;
    if (!card || !count || !url) return;

    const refresh = async () => {
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' });
            if (response.ok) {
                const data = await response.json();
                count.textContent = Number(data.active_visitors || 0).toLocaleString();
            }
        } catch (error) {
            // Keep the last known count when a refresh cannot reach the server.
        } finally {
            window.setTimeout(refresh, 15000);
        }
    };

    refresh();
})();
</script>
