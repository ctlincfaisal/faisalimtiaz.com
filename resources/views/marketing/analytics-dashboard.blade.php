@php
    $analyticsTrend = $analyticsTrend ?? collect();
    $trafficSources = $trafficSources ?? collect();
    $deviceBreakdown = $deviceBreakdown ?? collect();
    $countryBreakdown = $countryBreakdown ?? collect();
    $analyticsAvgSessionSeconds = $analyticsAvgSessionSeconds ?? 0;
    $analyticsPeriodLabel = $analyticsPeriodLabel ?? 'Last 30 days';
    $dashboardPeriod = (int) ($dashboardPeriod ?? 30);
    $dashboardPeriodOptions = $dashboardPeriodOptions ?? [
        7 => 'Last 7 days',
        30 => 'Last 30 days',
        90 => 'Last 90 days',
        365 => 'Last 12 months',
    ];
    $activeVisitorsCount = $activeVisitorsCount ?? 0;
    $websiteVisitsCount = $websiteVisitsCount ?? 0;
    $websiteClicksCount = $websiteClicksCount ?? 0;
    $topPages = $topPages ?? collect();
    $topClicks = $topClicks ?? collect();
    $websiteVisits = $websiteVisits ?? collect();
    $activeVisitGroups = $activeVisitGroups ?? collect();
    $chartWidth = 1000;
    $chartHeight = 260;
    $analyticsCount = $analyticsTrend->count();
    $chartMax = max(1, $analyticsTrend->flatMap(fn ($point) => [$point['visitors'], $point['pageviews']])->max() ?: 1);
    $visitorPoints = $analyticsTrend->values()->map(function ($point, $index) use ($chartWidth, $chartHeight, $chartMax, $analyticsCount) {
        $x = $analyticsCount > 1 ? ($index / ($analyticsCount - 1)) * $chartWidth : 0;
        $y = $chartHeight - (($point['visitors'] / $chartMax) * ($chartHeight - 12));
        return round($x, 2).','.round($y, 2);
    })->implode(' ');
    $pageviewPoints = $analyticsTrend->values()->map(function ($point, $index) use ($chartWidth, $chartHeight, $chartMax, $analyticsCount) {
        $x = $analyticsCount > 1 ? ($index / ($analyticsCount - 1)) * $chartWidth : 0;
        $y = $chartHeight - (($point['pageviews'] / $chartMax) * ($chartHeight - 12));
        return round($x, 2).','.round($y, 2);
    })->implode(' ');
    $visitorArea = trim($visitorPoints.' '.$chartWidth.','.$chartHeight.' 0,'.$chartHeight);
    $pageviewArea = trim($pageviewPoints.' '.$chartWidth.','.$chartHeight.' 0,'.$chartHeight);
    $avgMinutes = intdiv($analyticsAvgSessionSeconds, 60);
    $avgSeconds = $analyticsAvgSessionSeconds % 60;
@endphp

<div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><span>Workspace</span><span>/</span><span class="font-medium text-violet-500">Analytics</span></div>
        <h1 class="text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">Website Analytics</h1>
        <p class="mt-2 text-slate-500 dark:text-slate-400">Understand your traffic. Measure what converts.</p>
        <div class="mt-4 flex items-center gap-3 text-sm"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(17,185,129,0.12)]"></span><span class="font-semibold text-lime-600 dark:text-lime-300">Tracking active</span><span class="text-slate-300 dark:text-slate-700">|</span><span class="text-slate-500 dark:text-slate-400">Updated just now</span></div>
    </div>
    <div class="flex flex-wrap items-end gap-3">
        @include('marketing.website-selector', ['selectorTab' => 'analytics'])
        <form action="{{ route('marketing') }}" method="GET" class="flex items-center">
            <input type="hidden" name="tab" value="analytics">
            @if ($selectedWebsite?->id)
                <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
            @endif
            <label class="sr-only" for="analyticsPeriod">Analytics date range</label>
            <div class="relative">
                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400"></i>
                <select id="analyticsPeriod" name="dashboard_period" onchange="this.form.submit()" class="h-10 appearance-none rounded-xl border border-slate-300 bg-white py-2 pl-9 pr-9 text-sm font-medium text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    @foreach ($dashboardPeriodOptions as $days => $label)
                        <option value="{{ $days }}" @selected($dashboardPeriod === (int) $days)>{{ $label }}</option>
                    @endforeach
                </select>
                <i class="bi bi-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 dark:text-slate-400"></i>
            </div>
        </form>
        <button type="button" onclick="window.print()" class="inline-flex h-10 items-center gap-2 rounded-xl bg-violet-600 px-4 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-500"><i class="bi bi-download"></i>Export</button>
    </div>
</div>

<div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        ['label' => 'Visitors', 'value' => $analyticsTrend->sum('visitors'), 'change' => $activeVisitorsCount.' online', 'icon' => 'bi-people'],
        ['label' => 'Pageviews', 'value' => $analyticsTrend->sum('pageviews'), 'change' => number_format($websiteVisitsCount).' total', 'icon' => 'bi-layers'],
        ['label' => 'Avg. session', 'value' => sprintf('%dm %02ds', $avgMinutes, $avgSeconds), 'change' => $analyticsPeriodLabel, 'icon' => 'bi-clock'],
        ['label' => 'Tracked clicks', 'value' => $websiteClicksCount, 'change' => 'Across this website', 'icon' => 'bi-bullseye'],
    ] as $stat)
        <div class="{{ $card }} overflow-hidden p-5">
            <div class="flex items-start justify-between gap-3"><span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-violet-600/15 text-lg text-violet-500"><i class="bi {{ $stat['icon'] }}"></i></span><span class="rounded-lg bg-lime-400/15 px-2 py-1 text-xs font-semibold text-lime-600 dark:text-lime-300">{{ $stat['change'] }}</span></div>
            <div class="mt-4 text-sm text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</div>
            <strong class="mt-1 block text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</strong>
            <svg class="mt-4 h-10 w-full" viewBox="0 0 240 40" preserveAspectRatio="none" aria-hidden="true"><polyline points="0,30 20,25 40,28 60,18 80,24 100,14 120,19 140,10 160,17 180,8 200,14 220,5 240,9" fill="none" stroke="#11b981" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" /></svg>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(300px,0.9fr)]">
        <section class="{{ $card }} overflow-hidden p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-4"><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Traffic overview</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Visitors and pageviews over {{ strtolower($analyticsPeriodLabel) }}.</p></div><div class="marketing-chart-legend flex items-center gap-4 text-sm"><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-sky-500"></i>Visitors</span><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-lime-400"></i>Pageviews</span></div></div>
        <div class="mt-5 overflow-x-auto"><svg class="min-w-[640px] text-slate-500 dark:text-slate-400" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight + 42 }}" role="img" aria-label="Traffic overview chart"><defs><linearGradient id="visitorFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#8b5cf6" stop-opacity=".35"/><stop offset="1" stop-color="#8b5cf6" stop-opacity="0"/></linearGradient><linearGradient id="pageviewFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#9cff3b" stop-opacity=".25"/><stop offset="1" stop-color="#9cff3b" stop-opacity="0"/></linearGradient></defs>@for ($line = 0; $line <= 4; $line++)<line x1="0" x2="{{ $chartWidth }}" y1="{{ $line * ($chartHeight / 4) }}" y2="{{ $line * ($chartHeight / 4) }}" stroke="currentColor" stroke-opacity=".12"/>@endfor<polygon points="{{ $visitorArea }}" fill="url(#visitorFill)"/><polygon points="{{ $pageviewArea }}" fill="url(#pageviewFill)"/><polyline points="{{ $visitorPoints }}" fill="none" stroke="#a855f7" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"/><polyline points="{{ $pageviewPoints }}" fill="none" stroke="#9cff3b" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"/>@foreach ($analyticsTrend as $index => $point)@if ($index === 0 || $index === $analyticsTrend->count() - 1 || $index % 5 === 0)<text x="{{ $analyticsTrend->count() > 1 ? round(($index / ($analyticsTrend->count() - 1)) * $chartWidth) : 0 }}" y="{{ $chartHeight + 28 }}" fill="currentColor" font-size="14" text-anchor="middle">{{ $point['label'] }}</text>@endif @endforeach</svg></div>
    </section>
    <section class="{{ $card }} p-5 sm:p-6"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Traffic sources</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Where visitors came from.</p><div class="mt-6 space-y-5">@forelse ($trafficSources as $source)<div><div class="mb-2 flex items-center justify-between gap-3 text-sm"><span class="text-slate-700 dark:text-slate-200">{{ $source['name'] }}</span><strong class="text-slate-950 dark:text-white">{{ $source['percentage'] }}%</strong></div><div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"><div class="h-full rounded-full bg-violet-500" style="width: {{ $source['percentage'] }}%"></div></div></div>@empty<span class="text-sm text-slate-500 dark:text-slate-400">No traffic sources recorded yet.</span>@endforelse</div></section>
</div>

<div class="mt-5 grid gap-5 xl:grid-cols-2">
    <section class="{{ $card }} p-5 sm:p-6"><div class="flex items-center justify-between gap-3"><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Online now</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Live visitors in the last five minutes.</p></div><span class="inline-flex items-center gap-2 rounded-full bg-lime-400/15 px-3 py-1 text-xs font-semibold text-lime-600 dark:text-lime-300"><i class="h-2 w-2 rounded-full bg-lime-400"></i>{{ number_format($activeVisitorsCount) }} active</span></div><div class="mt-4 space-y-3">@forelse ($activeVisitGroups->take(4) as $group)<div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3 last:border-0 last:pb-0 dark:border-slate-800"><div class="min-w-0"><div class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $group['count'] }} {{ $group['count'] === 1 ? 'visitor' : 'visitors' }} from {{ $group['location'] }}</div><div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $group['page'] }}</div></div><i class="bi bi-broadcast text-lime-500"></i></div>@empty<div class="py-5 text-sm text-slate-500 dark:text-slate-400">No active visitors right now.</div>@endforelse</div></section>
    <section class="{{ $card }} p-5 sm:p-6"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Recent visitors</h2><div class="mt-4 space-y-1">@forelse ($websiteVisits->take(5) as $visit)<div class="flex items-center justify-between gap-3 border-b border-slate-200 py-3 last:border-0 dark:border-slate-800"><div class="min-w-0"><div class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $visit->path ?: '/' }}</div><div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ collect([$visit->city, $visit->region, $visit->country])->filter()->join(', ') ?: 'Unknown location' }} · {{ $visit->device_type ?: 'Unknown device' }}</div></div><span class="shrink-0 text-xs text-slate-500 dark:text-slate-400">{{ optional($visit->visited_at)->diffForHumans() }}</span></div>@empty<div class="py-5 text-sm text-slate-500 dark:text-slate-400">No visitors tracked yet.</div>@endforelse</div></section>
</div>

<div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,.9fr)]">
    <section class="{{ $card }} overflow-hidden p-5 sm:p-6"><div class="flex items-center justify-between gap-3"><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Top pages</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pages receiving the most visits.</p></div><i class="bi bi-arrow-up-right text-violet-500"></i></div><div class="mt-5 overflow-x-auto"><table class="w-full min-w-[560px] text-left text-sm"><thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:text-slate-400"><tr><th class="px-3 py-3">Page</th><th class="px-3 py-3">Visitors</th><th class="px-3 py-3">Pageviews</th><th class="px-3 py-3">Share</th></tr></thead><tbody class="divide-y divide-slate-200 dark:divide-slate-800">@forelse ($topPages as $page)@php $pageShare = $websiteVisitsCount > 0 ? round(($page->visits_count / $websiteVisitsCount) * 100, 1) : 0; @endphp<tr><td class="px-3 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $page->path ?: '/' }}</td><td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ number_format($page->visits_count) }}</td><td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ number_format($page->visits_count) }}</td><td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ $pageShare }}%</td></tr>@empty<tr><td colspan="4" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">No page visits recorded yet.</td></tr>@endforelse</tbody></table></div></section>
    <section class="{{ $card }} p-5 sm:p-6"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Top interactions</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Most clicked elements.</p><div class="mt-5 space-y-1">@forelse ($topClicks as $click)<div class="flex items-center justify-between gap-3 border-b border-slate-200 py-3 last:border-0 dark:border-slate-800"><div class="min-w-0"><div class="truncate font-medium text-slate-800 dark:text-slate-100">{{ $click->element_text ?: $click->element ?: 'Unknown element' }}</div><div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $click->path }}</div></div><strong class="shrink-0 text-slate-950 dark:text-white">{{ number_format($click->clicks_count) }}</strong></div>@empty<div class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">No clicks recorded yet.</div>@endforelse</div></section>
</div>

<div class="mt-5 grid gap-5 lg:grid-cols-2">
    <section class="{{ $card }} p-5 sm:p-6"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Devices</h2><div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">@foreach ($deviceBreakdown as $device)<span class="inline-block h-full {{ $loop->odd ? 'bg-violet-500' : 'bg-lime-400' }}" style="width: {{ $device['percentage'] }}%"></span>@endforeach</div><div class="mt-5 space-y-3">@forelse ($deviceBreakdown as $device)<div class="flex items-center justify-between text-sm"><span class="flex items-center gap-2 text-slate-700 dark:text-slate-200"><i class="h-2.5 w-2.5 rounded-sm {{ $loop->odd ? 'bg-violet-500' : 'bg-lime-400' }}"></i>{{ $device['name'] }}</span><strong class="text-slate-950 dark:text-white">{{ $device['percentage'] }}%</strong></div>@empty<span class="text-sm text-slate-500 dark:text-slate-400">No device data recorded yet.</span>@endforelse</div></section>
    <section class="{{ $card }} p-5 sm:p-6"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Top countries</h2><div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">@foreach ($countryBreakdown as $country)<span class="inline-block h-full {{ $loop->odd ? 'bg-violet-500' : 'bg-lime-400' }}" style="width: {{ $country['percentage'] }}%"></span>@endforeach</div><div class="mt-5 space-y-3">@forelse ($countryBreakdown as $country)<div class="flex items-center justify-between text-sm"><span class="flex items-center gap-2 text-slate-700 dark:text-slate-200"><i class="h-2.5 w-2.5 rounded-sm {{ $loop->odd ? 'bg-violet-500' : 'bg-lime-400' }}"></i>{{ $country['name'] }}</span><strong class="text-slate-950 dark:text-white">{{ $country['percentage'] }}%</strong></div>@empty<span class="text-sm text-slate-500 dark:text-slate-400">No country data recorded yet.</span>@endforelse</div></section>
</div>

