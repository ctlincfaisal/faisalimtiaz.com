@php
    $marketingLeads = $marketingLeads ?? collect();
    $leadSegments = $leadSegments ?? collect();
    $leadSegmentCounts = $leadSegmentCounts ?? collect();
    $leadTotal = $leadTotal ?? 0;
    $newLeadTotal = $newLeadTotal ?? 0;
    $leadSearch = $leadSearch ?? '';
    $leadSegment = $leadSegment ?? '';
    $leadStatus = $leadStatus ?? '';
    $leadSort = $leadSort ?? 'newest';
    $leadPerPage = $leadPerPage ?? '25';
    $leadEmailHistory = $leadEmailHistory ?? [];
    $leadRows = method_exists($marketingLeads, 'getCollection') ? $marketingLeads->getCollection() : collect($marketingLeads);
    $leadDetails = $leadRows->mapWithKeys(function ($lead) use ($leadEmailHistory) {
        $email = strtolower(trim((string) $lead->email));

        return [(string) $lead->id => [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'company' => $lead->company,
            'email' => $lead->email,
            'status' => $lead->status,
            'category' => $lead->segment,
            'created_at' => $lead->created_at?->toIso8601String(),
            'updated_at' => $lead->updated_at?->toIso8601String(),
            'emails' => $leadEmailHistory[$email] ?? [],
        ]];
    })->all();
    $leadMatchCount = method_exists($marketingLeads, 'total') ? $marketingLeads->total() : 0;
    $leadFirstItem = method_exists($marketingLeads, 'firstItem') ? $marketingLeads->firstItem() : null;
    $leadLastItem = method_exists($marketingLeads, 'lastItem') ? $marketingLeads->lastItem() : null;
@endphp

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><span>Clients</span><span>/</span><span class="font-medium text-emerald-700">All Leads</span></div>
            <h1 class="text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">All Leads</h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400">Manage your leads, build categories, and import new contacts.</p>
        </div>
    </div>

    @if (! $leadsReady)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">Leads storage is not ready yet. Run <code>php artisan migrate</code> on the server to enable it.</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="{{ $card }} p-5"><span class="{{ $muted }}">Total leads</span><strong class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($leadTotal) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Across all lists</span></div>
        <div class="{{ $card }} p-5"><span class="{{ $muted }}">Categories</span><strong class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($leadSegments->count()) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Custom categories for targeting</span></div>
        <div class="{{ $card }} p-5"><span class="{{ $muted }}">New leads</span><strong class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($newLeadTotal) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Ready for outreach</span></div>
    </div>

    <form method="GET" action="{{ route('marketing') }}" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_180px_auto]">
        <input type="hidden" name="tab" value="leads">
        <label class="relative block"><span class="sr-only">Search leads</span><i class="bi bi-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i><input name="lead_search" type="search" value="{{ $leadSearch }}" placeholder="Search leads (name, company, email...)" class="{{ $input }} h-11 pl-11"></label>
        <label><span class="sr-only">Filter by category</span><select name="lead_segment" class="{{ $input }} h-11"><option value="">All categories</option>@foreach ($leadSegments as $segment)<option value="{{ $segment->name }}" @selected($leadSegment === $segment->name)>{{ $segment->name }}</option>@endforeach</select></label>
        <label><span class="sr-only">Filter by status</span><select name="lead_status" class="{{ $input }} h-11"><option value="">All statuses</option>@foreach (\App\Models\MarketingLead::STATUSES as $value => $label)<option value="{{ $value }}" @selected($leadStatus === $value)>{{ $label }}</option>@endforeach</select></label>
        <button type="submit" class="h-11 rounded-xl border border-violet-200 px-5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 dark:border-violet-500/30 dark:text-violet-300 dark:hover:bg-violet-500/10">Search</button>
    </form>

    <div class="lead-category-filters flex flex-wrap items-center gap-2">
        <a href="{{ route('marketing', ['tab' => 'leads', 'lead_search' => $leadSearch, 'lead_status' => $leadStatus]) }}" data-lead-segment="all" @if ($leadSegment === '') aria-current="page" @endif class="lead-category-pill {{ $leadSegment === '' ? 'lead-category-pill-active' : '' }}"><span>All leads</span><span class="lead-category-count">{{ number_format($leadTotal) }}</span></a>
        @foreach ($leadSegments as $segment)
            @php $segmentCount = $leadSegmentCounts->firstWhere('name', $segment->name)['count'] ?? 0; $segmentActive = $leadSegment === $segment->name; @endphp
            <a href="{{ route('marketing', ['tab' => 'leads', 'lead_segment' => $segment->name, 'lead_search' => $leadSearch, 'lead_status' => $leadStatus]) }}" data-lead-segment="{{ $segment->name }}" @if ($segmentActive) aria-current="page" @endif class="lead-category-pill {{ $segmentActive ? 'lead-category-pill-active' : '' }}"><span>{{ $segment->name }}</span><span class="lead-category-count">{{ number_format($segmentCount) }}</span></a>
        @endforeach
        <details class="lead-category-create">
            <summary class="lead-category-create-trigger"><i class="bi bi-plus"></i><span>Add category</span></summary>
            <form action="{{ route('marketing.leads.segments.store') }}" method="POST" class="lead-category-popover">
                @csrf
                <div class="lead-category-popover-heading"><span class="lead-category-popover-icon"><i class="bi bi-tags"></i></span><div class="min-w-0"><p class="lead-category-popover-title">New category</p><p class="lead-category-popover-copy">Group leads for easier targeting.</p></div><button type="button" class="lead-category-popover-close" data-lead-category-close aria-label="Close create category"><i class="bi bi-x-lg"></i></button></div>
                <label for="newLeadSegment" class="lead-category-popover-label">Category name</label>
                <input id="newLeadSegment" name="name" type="text" class="lead-category-popover-input" placeholder="e.g. Photographers" maxlength="80" required>
                <button type="submit" class="lead-category-popover-submit">Create category</button>
                @error('name')<p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </form>
        </details>
    </div>

    <div>
        <section class="{{ $card }} overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Lead list</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ number_format($leadMatchCount) }} matching leads</p></div>
                <form method="GET" action="{{ route('marketing') }}" class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    @foreach (['tab' => 'leads', 'lead_search' => $leadSearch, 'lead_segment' => $leadSegment, 'lead_status' => $leadStatus, 'lead_sort' => $leadSort] as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                    <label for="leadSort" class="sr-only">Sort leads</label><select id="leadSort" name="lead_sort" class="rounded-lg border border-slate-200 bg-transparent px-2 py-1.5 text-xs dark:border-slate-700" onchange="this.form.submit()"><option value="newest" @selected($leadSort === 'newest')>Newest</option><option value="oldest" @selected($leadSort === 'oldest')>Oldest</option><option value="name-asc" @selected($leadSort === 'name-asc')>Name A–Z</option><option value="name-desc" @selected($leadSort === 'name-desc')>Name Z–A</option><option value="company-asc" @selected($leadSort === 'company-asc')>Company A–Z</option><option value="company-desc" @selected($leadSort === 'company-desc')>Company Z–A</option></select>
                </form>
            </div>
            <form id="leadBulkForm" action="{{ route('marketing.leads.bulk') }}" method="POST" class="hidden items-center justify-between gap-4 border-b border-emerald-200 bg-emerald-50 px-5 py-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                @csrf
                <div class="flex items-center gap-2 text-sm font-medium text-emerald-800 dark:text-emerald-200"><i class="bi bi-check2-square"></i><span><strong id="selectedLeadCount">0</strong> selected</span></div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <button type="submit" name="action" value="campaign" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"><i class="bi bi-megaphone"></i> Create campaign</button>
                    <select name="segment" class="rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs text-slate-700 dark:border-emerald-500/30 dark:bg-slate-900 dark:text-slate-200"><option value="">Assign to category…</option>@foreach ($leadSegments as $segment)<option value="{{ $segment->name }}">{{ $segment->name }}</option>@endforeach</select>
                    <button type="submit" name="action" value="segment" class="rounded-lg border border-emerald-300 px-3 py-2 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100 dark:border-emerald-500/40 dark:text-emerald-200 dark:hover:bg-emerald-500/10">Assign</button>
                    <button type="submit" name="action" value="delete" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10">Delete</button>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"><tr><th class="w-12 px-5 py-3"><input type="checkbox" class="lead-select-all h-4 w-4 accent-violet-600" aria-label="Select all visible leads"></th><th class="px-3 py-3">Name</th><th class="px-3 py-3">Company</th><th class="px-3 py-3">Email</th><th class="px-3 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($marketingLeads as $lead)
                            @php $fullName = trim($lead->first_name.' '.$lead->last_name) ?: 'Unnamed lead'; $initials = collect(preg_split('/\s+/', $fullName))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->join(''); $statusClass = match ($lead->status) { 'replied' => 'bg-lime-100 text-lime-700 dark:bg-lime-500/15 dark:text-lime-300', 'contacted' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300', default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }; @endphp
                            <tr data-lead-id="{{ $lead->id }}" tabindex="0" role="button" aria-label="View details for {{ $fullName }}" class="lead-detail-row cursor-pointer transition hover:bg-violet-50/50 focus:bg-violet-50/50 focus:outline-none dark:hover:bg-violet-500/5 dark:focus:bg-violet-500/5"><td class="px-5 py-4"><input type="checkbox" value="{{ $lead->id }}" class="lead-row-select h-4 w-4 accent-violet-600" aria-label="Select {{ $fullName }}"></td><td class="px-3 py-4"><div class="flex items-center gap-3"><span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-semibold text-violet-700 dark:bg-violet-500/20 dark:text-violet-200">{{ $initials }}</span><span class="font-medium text-slate-900 dark:text-slate-100">{{ $fullName }}</span></div></td><td class="px-3 py-4 text-slate-600 dark:text-slate-300">{{ $lead->company ?: '—' }}</td><td class="px-3 py-4 text-slate-600 dark:text-slate-300">{{ $lead->email }}</td><td class="px-3 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ \App\Models\MarketingLead::STATUSES[$lead->status] ?? ucfirst($lead->status) }}</span></td></tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500 dark:text-slate-400">{{ $leadSearch !== '' || $leadSegment !== '' || $leadStatus !== '' ? 'No leads match your filters.' : 'No leads yet. Import a CSV to get started.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
                <span>Showing {{ $leadFirstItem ?: 0 }}–{{ $leadLastItem ?: 0 }} of {{ number_format($leadMatchCount) }} leads</span>
                <div class="flex items-center gap-3"><label for="leadPerPage" class="sr-only">Rows per page</label><select id="leadPerPage" name="lead_per_page" form="leadPerPageForm" class="rounded-lg border border-slate-200 bg-transparent px-2 py-1.5 text-xs dark:border-slate-700"><option value="25" @selected($leadPerPage === '25')>25 / page</option><option value="50" @selected($leadPerPage === '50')>50 / page</option><option value="100" @selected($leadPerPage === '100')>100 / page</option><option value="all" @selected($leadPerPage === 'all')>All</option></select><form id="leadPerPageForm" method="GET" action="{{ route('marketing') }}">@foreach (['tab' => 'leads', 'lead_search' => $leadSearch, 'lead_segment' => $leadSegment, 'lead_status' => $leadStatus, 'lead_sort' => $leadSort] as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach</form></div>
            </div>
            @if ($leadsReady && $marketingLeads->hasPages())<div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $marketingLeads->links() }}</div>@endif
        </section>

    </div>
</div>

<style>
    .marketing-dashboard .lead-category-pill,
    .marketing-dashboard .lead-category-create-trigger {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        gap: .5rem;
        border: 1px solid var(--dashboard-border);
        border-radius: 9px;
        background: #fff;
        color: #334155;
        padding: .5rem .75rem;
        font-size: .8125rem;
        font-weight: 600;
        line-height: 1;
        transition: border-color 160ms ease, background-color 160ms ease, color 160ms ease, box-shadow 160ms ease;
    }

    .marketing-dashboard .lead-category-pill:hover,
    .marketing-dashboard .lead-category-create-trigger:hover,
    .marketing-dashboard .lead-category-create[open] .lead-category-create-trigger {
        border-color: #76d8b4;
        background: #e3fbf1;
        color: #087f5b;
    }

    .marketing-dashboard .lead-category-pill-active {
        border-color: var(--dashboard-green-strong) !important;
        background: var(--dashboard-green-strong) !important;
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(8, 185, 124, .18);
    }

    .marketing-dashboard .lead-category-pill-active:hover {
        border-color: #06a971 !important;
        background: #06a971 !important;
        color: #fff !important;
    }

    .marketing-dashboard .lead-category-count {
        display: inline-flex;
        min-width: 1.35rem;
        height: 1.35rem;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #eef2f7;
        color: #51627c;
        padding: 0 .35rem;
        font-size: .7rem;
        font-weight: 700;
        line-height: 1;
    }

    .marketing-dashboard .lead-category-pill-active .lead-category-count {
        background: rgba(255, 255, 255, .22) !important;
        color: #fff !important;
    }

    .marketing-dashboard .lead-category-create {
        position: relative;
    }

    .marketing-dashboard .lead-category-create > summary {
        cursor: pointer;
        list-style: none;
    }

    .marketing-dashboard .lead-category-create > summary::-webkit-details-marker {
        display: none;
    }

    .marketing-dashboard .lead-category-create-trigger {
        border-style: dashed;
        border-color: #8edfc1;
        background: #fff;
        color: #087f5b;
    }

    .marketing-dashboard .lead-category-popover {
        position: absolute;
        top: calc(100% + .5rem);
        left: 0;
        z-index: 40;
        width: min(19rem, calc(100vw - 2rem));
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: #fff;
        padding: 1rem;
        box-shadow: 0 16px 35px rgba(26, 51, 72, .16);
    }

    .marketing-dashboard .lead-category-popover-heading {
        display: flex;
        align-items: center;
        gap: .65rem;
        margin-bottom: 1rem;
    }

    .marketing-dashboard .lead-category-popover-icon {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #e3fbf1;
        color: #087f5b;
    }

    .marketing-dashboard .lead-category-popover-title {
        color: #101828;
        font-size: .875rem;
        font-weight: 700;
    }

    .marketing-dashboard .lead-category-popover-copy {
        margin-top: .15rem;
        color: #64748b;
        font-size: .75rem;
        line-height: 1.35;
    }

    .marketing-dashboard .lead-category-popover-close {
        display: inline-flex;
        width: 1.75rem;
        height: 1.75rem;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        margin-left: auto;
        border: 0;
        border-radius: 7px;
        background: transparent !important;
        color: #64748b !important;
        font-size: .75rem;
        transition: background-color 160ms ease, color 160ms ease;
    }

    .marketing-dashboard .lead-category-popover-close:hover,
    .marketing-dashboard .lead-category-popover-close:focus-visible {
        background: #e3fbf1 !important;
        color: #087f5b !important;
        outline: none;
    }

    .marketing-dashboard .lead-category-popover-label {
        display: block;
        margin-bottom: .4rem;
        color: #334155;
        font-size: .75rem;
        font-weight: 600;
    }

    .marketing-dashboard .lead-category-popover-input {
        width: 100%;
        border: 1px solid var(--dashboard-border) !important;
        border-radius: 8px;
        background: #fff !important;
        color: #101828 !important;
        padding: .6rem .75rem;
        font-size: .8125rem;
        outline: none;
    }

    .marketing-dashboard .lead-category-popover-input:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .12);
    }

    .marketing-dashboard .lead-category-popover-submit {
        width: 100%;
        margin-top: .75rem;
        border: 0;
        border-radius: 8px;
        background: var(--dashboard-green-strong) !important;
        color: #fff !important;
        padding: .65rem .75rem;
        font-size: .8125rem;
        font-weight: 700;
        transition: background-color 160ms ease, transform 160ms ease;
    }

    .marketing-dashboard .lead-category-popover-submit:hover {
        background: #06a971 !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    @media (max-width: 640px) {
        .marketing-dashboard .lead-category-popover {
            left: auto;
            right: 0;
        }
    }

    #leadDetailsModal {
        isolation: isolate;
        background: rgba(0, 0, 0, .45) !important;
    }

    #leadDetailsModal .lead-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 0 !important;
        opacity: 1;
        background: rgba(0, 0, 0, .2) !important;
        backdrop-filter: blur(5px);
        transition: opacity 180ms ease;
    }

    #leadDetailsModal .lead-modal-panel {
        opacity: 0;
        transform: translateY(14px) scale(.98);
        transition: opacity 180ms ease, transform 180ms ease;
    }

    #leadDetailsModal.lead-modal-open .lead-modal-panel {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    @media (prefers-reduced-motion: reduce) {
        #leadDetailsModal,
        #leadDetailsModal .lead-modal-backdrop,
        #leadDetailsModal .lead-modal-panel {
            transition: none;
        }
    }
</style>

<div id="leadDetailsModal" class="fixed inset-0 z-50 hidden" style="background: rgba(0, 0, 0, .45) !important;" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="leadDetailsModalTitle">
    <div data-lead-modal-close class="lead-modal-backdrop pointer-events-auto absolute inset-0 z-0 backdrop-blur-sm" style="background: rgba(0, 0, 0, .2) !important;"></div>
    <div class="pointer-events-none relative z-10 flex min-h-full items-center justify-center p-4 sm:p-6">
        <div class="lead-modal-panel pointer-events-auto relative flex max-h-[88vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 dark:border-slate-700 sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Lead details</p>
                    <h2 id="leadDetailsModalTitle" class="mt-1 text-xl font-semibold text-slate-950 dark:text-white">Lead</h2>
                    <p id="leadDetailsModalEmail" class="mt-1 text-sm text-slate-500 dark:text-slate-400"></p>
                </div>
                <button type="button" data-lead-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close lead details"><i class="bi bi-x-lg"></i></button>
            </div>
            <div id="leadDetailsModalBody" class="overflow-y-auto p-5 sm:p-6"></div>
        </div>
    </div>
</div>

<script>
(() => {
    const leadDetails = @json($leadDetails);
    const leadCategoryCreate = document.querySelector('.lead-category-create');

    leadCategoryCreate?.querySelector('[data-lead-category-close]')?.addEventListener('click', () => {
        leadCategoryCreate.removeAttribute('open');
    });

    const leadModal = document.getElementById('leadDetailsModal');
    const leadModalBody = document.getElementById('leadDetailsModalBody');
    const leadModalTitle = document.getElementById('leadDetailsModalTitle');
    const leadModalEmail = document.getElementById('leadDetailsModalEmail');
    if (leadModal && leadModal.parentElement !== document.body) document.body.appendChild(leadModal);
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character]));
    const formatDate = value => value ? new Date(value).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) : 'Not recorded';
    const statusClass = status => status === 'sent' || status === 'delivered'
        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
        : status === 'failed'
            ? 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300'
            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';

    const renderLeadDetails = lead => {
        const fullName = [lead.first_name, lead.last_name].filter(Boolean).join(' ') || 'Unnamed lead';
        const emails = lead.emails || [];
        leadModalTitle.textContent = fullName;
        leadModalEmail.textContent = lead.email || '';
        leadModalBody.innerHTML = `<div class="grid gap-5 lg:grid-cols-[minmax(0,.8fr)_minmax(0,1.2fr)]">
            <section class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                <div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">${escapeHtml((fullName.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase())}</span><div><h3 class="font-semibold text-slate-950 dark:text-white">${escapeHtml(fullName)}</h3><p class="text-xs text-slate-500 dark:text-slate-400">Lead profile</p></div></div>
                <dl class="mt-5 divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Email</dt><dd class="break-all text-right font-medium text-slate-900 dark:text-slate-100">${escapeHtml(lead.email || '—')}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Company</dt><dd class="text-right font-medium text-slate-900 dark:text-slate-100">${escapeHtml(lead.company || '—')}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Status</dt><dd class="text-right font-medium text-slate-900 dark:text-slate-100">${escapeHtml(lead.status ? lead.status.charAt(0).toUpperCase() + lead.status.slice(1) : '—')}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Category</dt><dd class="text-right font-medium text-slate-900 dark:text-slate-100">${escapeHtml(lead.category || 'Unassigned')}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Added</dt><dd class="text-right font-medium text-slate-900 dark:text-slate-100">${escapeHtml(formatDate(lead.created_at))}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Last updated</dt><dd class="text-right font-medium text-slate-900 dark:text-slate-100">${escapeHtml(formatDate(lead.updated_at))}</dd></div>
                </dl>
            </section>
            <section>
                <div class="flex items-end justify-between gap-3"><div><h3 class="text-lg font-semibold text-slate-950 dark:text-white">Email history</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">All messages sent to this lead.</p></div><span class="text-sm text-slate-500 dark:text-slate-400">${emails.length} email${emails.length === 1 ? '' : 's'}</span></div>
                <div class="mt-4 grid gap-3">${emails.length ? emails.map(email => `<article class="rounded-xl border border-slate-200 p-4 dark:border-slate-700"><div class="flex flex-wrap items-start justify-between gap-3"><div><span class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">${escapeHtml(email.type)}</span><h4 class="mt-1 font-semibold text-slate-950 dark:text-white">${escapeHtml(email.subject || 'No subject')}</h4><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">${escapeHtml(email.campaign_name || 'Direct email')} · ${escapeHtml(email.template_name || 'Custom email')}</p></div><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${statusClass(email.delivery_status)}">${escapeHtml(email.delivery_status || 'pending')}</span></div><div class="mt-3 grid gap-2 text-xs text-slate-500 dark:text-slate-400 sm:grid-cols-2"><span><strong class="text-slate-700 dark:text-slate-200">Sent:</strong> ${escapeHtml(formatDate(email.sent_at))}</span><span><strong class="text-slate-700 dark:text-slate-200">Opened:</strong> ${escapeHtml(formatDate(email.opened_at))}</span><span><strong class="text-slate-700 dark:text-slate-200">From:</strong> ${escapeHtml(email.from_email || 'Default mail settings')}</span><span><strong class="text-slate-700 dark:text-slate-200">Signature:</strong> ${escapeHtml(email.signature_name || 'None')}</span></div>${email.attachment_name ? `<p class="mt-3 text-xs text-slate-500 dark:text-slate-400"><i class="bi bi-paperclip"></i> ${escapeHtml(email.attachment_name)}</p>` : ''}<div class="mt-3 whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-700 dark:bg-slate-950 dark:text-slate-300">${escapeHtml(email.body || 'No email body recorded.')}</div></article>`).join('') : '<div class="rounded-xl border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No sent emails recorded for this lead.</div>'}</div>
            </section>
        </div>`;
    };

    let leadModalCloseTimer;
    const closeLeadModal = () => {
        if (!leadModal || leadModal.classList.contains('hidden')) return;
        leadModal.classList.remove('lead-modal-open');
        leadModal.setAttribute('aria-hidden', 'true');
        leadModalCloseTimer = window.setTimeout(() => leadModal.classList.add('hidden'), 180);
        document.body.classList.remove('overflow-hidden');
    };
    leadModal?.querySelector('.lead-modal-backdrop')?.addEventListener('click', closeLeadModal);
    leadModal?.addEventListener('click', event => {
        if (event.target === leadModal || event.target.classList.contains('lead-modal-backdrop')) closeLeadModal();
    });
    document.querySelectorAll('.lead-detail-row').forEach(row => {
        const open = () => {
            const lead = leadDetails[row.dataset.leadId];
            if (!lead || !leadModal) return;
            window.clearTimeout(leadModalCloseTimer);
            renderLeadDetails(lead);
            leadModal.classList.remove('hidden');
            leadModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            window.requestAnimationFrame(() => leadModal.classList.add('lead-modal-open'));
        };
        row.addEventListener('click', event => { if (!event.target.closest('input, button, a, select')) open(); });
        row.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); open(); } });
    });
    leadModal?.querySelector('button[data-lead-modal-close]')?.addEventListener('click', closeLeadModal);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeLeadModal(); });

    const selectAll = document.querySelector('.lead-select-all');
    const rowSelections = [...document.querySelectorAll('.lead-row-select')];
    const bulkForm = document.getElementById('leadBulkForm');
    const selectedCount = document.getElementById('selectedLeadCount');

    const selectedRows = () => rowSelections.filter((checkbox) => checkbox.checked);
    const refreshSelection = () => {
        const selected = selectedRows();
        if (bulkForm) bulkForm.classList.toggle('hidden', selected.length === 0);
        if (bulkForm) bulkForm.classList.toggle('flex', selected.length > 0);
        if (selectedCount) selectedCount.textContent = selected.length;
        if (selectAll) {
            selectAll.checked = rowSelections.length > 0 && selected.length === rowSelections.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < rowSelections.length;
        }
    };

    selectAll?.addEventListener('change', () => {
        rowSelections.forEach((checkbox) => { checkbox.checked = selectAll.checked; });
        refreshSelection();
    });
    rowSelections.forEach((checkbox) => checkbox.addEventListener('change', refreshSelection));

    bulkForm?.addEventListener('submit', (event) => {
        const selected = selectedRows();
        if (selected.length === 0) {
            event.preventDefault();
            return;
        }

        bulkForm.querySelectorAll('input[name="lead_ids[]"]').forEach((input) => input.remove());
        selected.forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'lead_ids[]';
            input.value = checkbox.value;
            bulkForm.appendChild(input);
        });

        if (event.submitter?.value === 'delete' && ! window.confirm(`Delete ${selected.length} selected lead${selected.length === 1 ? '' : 's'}?`)) {
            event.preventDefault();
        }
    });
})();
</script>
