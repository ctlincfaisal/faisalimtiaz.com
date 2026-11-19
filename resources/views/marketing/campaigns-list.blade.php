@php
    $campaigns = $campaigns ?? collect();
    $campaignLeadSegments = $campaignLeadSegments ?? collect();
    $campaignSearch = $campaignSearch ?? '';
    $campaignStatus = $campaignStatus ?? '';
    $campaignAudienceType = $campaignAudienceType ?? '';
    $campaignSegment = $campaignSegment ?? '';
    $campaignDateFrom = $campaignDateFrom ?? '';
    $campaignDateTo = $campaignDateTo ?? '';
    $campaignSort = $campaignSort ?? 'newest';
    $campaignPerPage = $campaignPerPage ?? '25';
    $campaignListStats = $campaignListStats ?? ['campaigns' => 0, 'sent' => 0, 'opened' => 0];
    $audienceLabels = ['all_leads' => 'All leads', 'segment' => 'Lead category', 'manual' => 'Email list'];
    $statusLabels = ['draft' => 'Draft', 'queued' => 'Queued', 'running' => 'Running', 'completed' => 'Completed', 'failed' => 'Failed'];
    $hasPagination = method_exists($campaigns, 'total');
@endphp

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
        <div><div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><span>Campaigns</span><span>/</span><span class="font-medium text-violet-500">All campaigns</span></div><h1 class="text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">All Campaigns</h1><p class="mt-2 text-slate-500 dark:text-slate-400">Search, filter, and monitor every campaign in this workspace.</p></div>
        <div class="flex items-center gap-2">
            <button type="button" data-campaign-filters-toggle aria-expanded="false" aria-controls="campaignFilters" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300"><i class="bi bi-funnel"></i><span class="hidden sm:inline">Filters</span></button>
            <a href="{{ route('marketing', ['tab' => 'campaigns-create']) }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-500"><i class="bi bi-plus"></i> Create Campaign</a>
        </div>
    </div>

    @if (! $campaignsReady)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">Campaign storage is not ready yet. Run <code>php artisan migrate</code> on the server to enable it.</div>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <div class="{{ $card }} flex items-center justify-between gap-4 p-4">
            <div><span class="{{ $muted }}">Campaigns created</span><strong class="mt-1 block text-2xl font-semibold text-slate-950 dark:text-white">{{ number_format($campaignListStats['campaigns'] ?? 0) }}</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Total in this workspace</span></div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-megaphone"></i></span>
        </div>
        <div class="{{ $card }} flex items-center justify-between gap-4 p-4">
            <div><span class="{{ $muted }}">Emails sent</span><strong class="mt-1 block text-2xl font-semibold text-slate-950 dark:text-white">{{ number_format($campaignListStats['sent'] ?? 0) }}</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Across all campaigns</span></div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-send"></i></span>
        </div>
        <div class="{{ $card }} flex items-center justify-between gap-4 p-4">
            <div><span class="{{ $muted }}">Emails opened</span><strong class="mt-1 block text-2xl font-semibold text-slate-950 dark:text-white">{{ number_format($campaignListStats['opened'] ?? 0) }}</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Tracked opens</span></div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-envelope-open"></i></span>
        </div>
    </div>

    <form id="campaignFilters" method="GET" action="{{ route('marketing') }}" class="{{ $card }} hidden grid gap-4 p-5 lg:grid-cols-[minmax(0,1.4fr)_repeat(2,minmax(150px,.6fr))_auto]">
        <input type="hidden" name="tab" value="campaigns-list">
        <div><label class="{{ $label }}" for="campaignSearch">Search campaigns</label><div class="relative"><i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i><input id="campaignSearch" name="campaign_search" type="search" class="{{ $input }} pl-10" value="{{ $campaignSearch }}" placeholder="Search name or description"></div></div>
        <div><label class="{{ $label }}" for="campaignStatus">Status</label><select id="campaignStatus" name="campaign_status" class="{{ $input }}"><option value="">All statuses</option>@foreach ($statusLabels as $value => $labelText)<option value="{{ $value }}" @selected($campaignStatus === $value)>{{ $labelText }}</option>@endforeach</select></div>
        <div><label class="{{ $label }}" for="campaignAudienceType">Lead type</label><select id="campaignAudienceType" name="campaign_audience_type" class="{{ $input }}"><option value="">All lead types</option>@foreach ($audienceLabels as $value => $labelText)<option value="{{ $value }}" @selected($campaignAudienceType === $value)>{{ $labelText }}</option>@endforeach</select></div>
        <div class="flex items-end"><button type="submit" class="w-full rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 hover:bg-violet-500">Apply filters</button></div>
        <div><label class="{{ $label }}" for="campaignSegment">Category</label><select id="campaignSegment" name="campaign_segment" class="{{ $input }}"><option value="">All categories</option>@foreach ($campaignLeadSegments as $segment)<option value="{{ $segment->name }}" @selected($campaignSegment === $segment->name)>{{ $segment->name }}</option>@endforeach</select></div>
        <div><label class="{{ $label }}" for="campaignFrom">Created from</label><input id="campaignFrom" name="campaign_from" type="date" class="{{ $input }}" value="{{ $campaignDateFrom }}"></div>
        <div><label class="{{ $label }}" for="campaignTo">Created to</label><input id="campaignTo" name="campaign_to" type="date" class="{{ $input }}" value="{{ $campaignDateTo }}"></div>
        <div class="flex items-end"><a href="{{ route('marketing', ['tab' => 'campaigns-list']) }}" class="w-full rounded-xl border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">Reset</a></div>
    </form>

    <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-500 dark:text-slate-400">{{ $hasPagination ? number_format($campaigns->total()) : 0 }} campaign{{ ($hasPagination ? $campaigns->total() : 0) === 1 ? '' : 's' }} found</p><form method="GET" action="{{ route('marketing') }}" class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><input type="hidden" name="tab" value="campaigns-list"><input type="hidden" name="campaign_search" value="{{ $campaignSearch }}"><input type="hidden" name="campaign_status" value="{{ $campaignStatus }}"><input type="hidden" name="campaign_audience_type" value="{{ $campaignAudienceType }}"><input type="hidden" name="campaign_segment" value="{{ $campaignSegment }}"><input type="hidden" name="campaign_from" value="{{ $campaignDateFrom }}"><input type="hidden" name="campaign_to" value="{{ $campaignDateTo }}"><label for="campaignSort" class="sr-only">Sort campaigns</label><select id="campaignSort" name="campaign_sort" class="rounded-lg border border-slate-200 bg-transparent px-2 py-1.5 text-xs dark:border-slate-700" onchange="this.form.submit()"><option value="newest" @selected($campaignSort === 'newest')>Newest first</option><option value="oldest" @selected($campaignSort === 'oldest')>Oldest first</option><option value="name-asc" @selected($campaignSort === 'name-asc')>Name A–Z</option><option value="name-desc" @selected($campaignSort === 'name-desc')>Name Z–A</option></select><label for="campaignPerPage" class="sr-only">Rows per page</label><select id="campaignPerPage" name="campaign_per_page" class="rounded-lg border border-slate-200 bg-transparent px-2 py-1.5 text-xs dark:border-slate-700" onchange="this.form.submit()"><option value="25" @selected($campaignPerPage === '25')>25 / page</option><option value="50" @selected($campaignPerPage === '50')>50 / page</option><option value="100" @selected($campaignPerPage === '100')>100 / page</option></select></form></div>

    <section class="{{ $card }} overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full min-w-[900px] text-left text-sm"><thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"><tr><th class="px-5 py-3">Campaign</th><th class="px-3 py-3">Audience</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Leads</th><th class="px-3 py-3">Sequence</th><th class="px-3 py-3">Created</th><th class="px-5 py-3 text-right">Action</th></tr></thead><tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($campaigns as $campaign)
                @php $campaignStatusClass = match ($campaign->status) { 'running' => 'bg-lime-100 text-lime-700 dark:bg-lime-500/15 dark:text-lime-300', 'queued' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300', 'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300', 'failed' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300', default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }; @endphp
                <tr class="transition hover:bg-violet-50/50 dark:hover:bg-violet-500/5"><td class="px-5 py-4"><strong class="block font-semibold text-slate-900 dark:text-white">{{ $campaign->name }}</strong><span class="mt-1 block max-w-xs truncate text-xs text-slate-500 dark:text-slate-400">{{ $campaign->description ?: 'No description' }}</span></td><td class="px-3 py-4"><span class="font-medium text-slate-700 dark:text-slate-200">{{ $audienceLabels[$campaign->audience_type] ?? ucfirst($campaign->audience_type) }}</span>@if ($campaign->audience_segment)<span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $campaign->audience_segment }}</span>@endif</td><td class="px-3 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $campaignStatusClass }}">{{ $statusLabels[$campaign->status] ?? ucfirst($campaign->status) }}</span></td><td class="px-3 py-4 text-slate-700 dark:text-slate-200">{{ number_format($campaign->recipient_count) }}<span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ number_format($campaign->sent_count) }} sent</span></td><td class="px-3 py-4 text-slate-700 dark:text-slate-200">{{ $campaign->sequence_count }} step{{ $campaign->sequence_count === 1 ? '' : 's' }}<span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $campaign->pending_emails > 0 ? number_format($campaign->pending_emails).' pending' : 'No pending jobs' }}</span></td><td class="whitespace-nowrap px-3 py-4 text-slate-600 dark:text-slate-300">{{ optional($campaign->created_at)->format('M d, Y') }}<span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $campaign->scheduled_at ? 'Starts '. $campaign->scheduled_at->format('M d, Y') : 'Not scheduled' }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('marketing', ['tab' => $campaign->status === 'draft' ? 'campaigns-create' : 'campaigns-detail', 'campaign_id' => $campaign->id, 'campaign_step' => $campaign->status === 'draft' ? 1 : null]) }}" class="inline-flex items-center gap-2 rounded-lg border border-violet-200 px-3 py-2 text-xs font-semibold text-violet-700 hover:bg-violet-50 dark:border-violet-500/30 dark:text-violet-300 dark:hover:bg-violet-500/10">{{ $campaign->status === 'draft' ? 'Continue' : 'View details' }} <i class="bi bi-arrow-up-right"></i></a></td></tr>
            @empty
                <tr><td colspan="7" class="px-5 py-14 text-center text-slate-500 dark:text-slate-400">{{ $campaignSearch !== '' || $campaignStatus !== '' || $campaignAudienceType !== '' || $campaignSegment !== '' || $campaignDateFrom !== '' || $campaignDateTo !== '' ? 'No campaigns match these filters.' : 'No campaigns yet. Create your first campaign to see it here.' }}</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($hasPagination && $campaigns->hasPages())<div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $campaigns->links() }}</div>@endif
    </section>
</div>

<script>
    (() => {
        const toggle = document.querySelector('[data-campaign-filters-toggle]');
        const filters = document.getElementById('campaignFilters');
        if (!toggle || !filters) return;

        toggle.addEventListener('click', () => {
            const isHidden = filters.classList.toggle('hidden');
            toggle.setAttribute('aria-expanded', String(!isHidden));
            toggle.classList.toggle('bg-emerald-50', !isHidden);
            toggle.classList.toggle('text-emerald-700', !isHidden);
            toggle.classList.toggle('dark:bg-emerald-500/10', !isHidden);
            toggle.classList.toggle('dark:text-emerald-300', !isHidden);
        });
    })();
</script>
