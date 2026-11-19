<h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Performance</h1>
<p class="mt-2 text-slate-500 dark:text-slate-400">Browse the full history of recorded visitor activity, newest first.</p>

<div class="mt-6 flex flex-wrap items-end justify-between gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
    @include('marketing.website-selector', ['selectorTab' => 'performance'])
</div>

<form action="{{ route('marketing') }}" method="GET" class="mt-7 grid items-end gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
    <input type="hidden" name="tab" value="performance">
    @if ($selectedWebsite)
        <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
    @endif
    <div>
        <label for="performanceFrom" class="{{ $label }}">From date</label>
        <input id="performanceFrom" name="performance_from" type="date" class="{{ $input }}" value="{{ $performanceFrom }}">
    </div>
    <div>
        <label for="performanceTo" class="{{ $label }}">To date</label>
        <input id="performanceTo" name="performance_to" type="date" class="{{ $input }}" value="{{ $performanceTo }}">
    </div>
    <div class="flex items-center gap-4">
        <button type="submit" class="whitespace-nowrap rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Apply filter</button>
        <a href="{{ route('marketing', ['tab' => 'performance']) }}" class="whitespace-nowrap text-sm text-slate-500 hover:underline dark:text-slate-400">All history</a>
    </div>
</form>
<p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Both selected dates are included. Dates and times are shown in {{ config('app.timezone') }}. Leave both dates blank to see all history.</p>

@if ($performanceDateErrors)
    <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
        @foreach ($performanceDateErrors as $message)
            <p>{{ $message }}</p>
        @endforeach
    </div>
@elseif (! $performanceReady)
    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">Visitor history is unavailable right now. Check the database connection and website analytics migrations.</div>
@else
    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="{{ $card }} p-4">
            <div class="flex items-center justify-between gap-3"><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><i class="bi bi-eye"></i></span><span class="text-xs font-semibold text-emerald-700">Visits</span></div>
            <strong class="mt-3 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($performanceTotalVisits) }}</strong>
            <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Recorded activity</span>
        </div>
        <div class="{{ $card }} p-4">
            <div class="flex items-center justify-between gap-3"><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><i class="bi bi-people"></i></span><span class="text-xs font-semibold text-emerald-700">Sessions</span></div>
            <strong class="mt-3 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($performanceVisitorSessions) }}</strong>
            <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Unique visitor sessions</span>
        </div>
        <div class="{{ $card }} p-4">
            <div class="flex items-center justify-between gap-3"><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><i class="bi bi-cursor"></i></span><span class="text-xs font-semibold text-emerald-700">Clicks</span></div>
            <strong class="mt-3 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($performanceTotalClicks) }}</strong>
            <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Tracked interactions</span>
        </div>
        <div class="{{ $card }} p-4">
            <div class="flex items-center justify-between gap-3"><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><i class="bi bi-bar-chart"></i></span><span class="text-xs font-semibold text-emerald-700">Top page</span></div>
            <strong class="mt-3 block truncate text-lg font-semibold text-slate-950 dark:text-white" title="{{ $performanceTopPagePath ?: 'No data yet' }}">{{ $performanceTopPagePath ?: 'No data yet' }}</strong>
            <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ number_format($performanceTopPageVisits) }} visit{{ $performanceTopPageVisits === 1 ? '' : 's' }}</span>
        </div>
        <div class="{{ $card }} p-4">
            <div class="flex items-center justify-between gap-3"><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><i class="bi bi-hand-index-thumb"></i></span><span class="text-xs font-semibold text-emerald-700">Most clicked</span></div>
            <strong class="mt-3 block truncate text-lg font-semibold text-slate-950 dark:text-white" title="{{ $performanceTopClickedPagePath ?: 'No data yet' }}">{{ $performanceTopClickedPagePath ?: 'No data yet' }}</strong>
            <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ number_format($performanceTopClickedPageClicks) }} click{{ $performanceTopClickedPageClicks === 1 ? '' : 's' }}</span>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Visitor history</h2>
        <span class="{{ $muted }}">Showing {{ $performanceVisits->firstItem() ?: 0 }}–{{ $performanceVisits->lastItem() ?: 0 }} of {{ number_format($performanceTotalVisits) }} visits</span>
    </div>
    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-4 py-3">Visitor</th>
                    <th scope="col" class="px-4 py-3">Page</th>
                    <th scope="col" class="px-4 py-3">Device / Browser</th>
                    <th scope="col" class="whitespace-nowrap px-4 py-3">Visited at</th>
                    <th scope="col" class="px-4 py-3">Clicks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($performanceVisits as $visit)
                    <tr>
                        <td class="px-4 py-4">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $visit->ip_address ?: 'Unknown IP' }}</div>
                            <div class="mt-1 min-w-28 text-xs text-slate-500 dark:text-slate-400">{{ collect([$visit->city, $visit->region, $visit->country])->filter()->join(', ') ?: 'Unknown location' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="min-w-28 max-w-52 break-words text-slate-700 dark:text-slate-200">{{ $visit->path }}</div>
                            <div class="mt-1 max-w-52 break-words text-xs text-slate-500 dark:text-slate-400">{{ $visit->referrer ? 'From: '.$visit->referrer : 'Direct visit' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="min-w-28 text-slate-700 dark:text-slate-200">{{ $visit->device_type ?: 'Unknown device' }} · {{ $visit->operating_system ?: 'Unknown OS' }}</div>
                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $visit->browser ?: 'Unknown browser' }}{{ $visit->browser_version ? ' '.$visit->browser_version : '' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4">
                            <div class="text-slate-600 dark:text-slate-300">{{ $visit->visited_at->format('M d, Y h:i A') }}</div>
                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Last seen: {{ $visit->last_seen_at?->format('M d, Y h:i A') ?: 'Not recorded' }}</div>
                        </td>
                        <td class="px-4 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $visit->clicks_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">{{ $performanceFrom !== '' || $performanceTo !== '' ? 'No visits recorded in this date range.' : 'No visitor history recorded yet.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $performanceVisits->links() }}</div>
@endif
