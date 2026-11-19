<form action="{{ route('marketing') }}" method="GET" class="flex flex-wrap items-end gap-3">
    <input type="hidden" name="tab" value="{{ $selectorTab ?? $activeTab }}">
    @if (($selectorTab ?? null) === 'performance')
        <input type="hidden" name="performance_from" value="{{ $performanceFrom }}">
        <input type="hidden" name="performance_to" value="{{ $performanceTo }}">
    @endif
    <div class="min-w-64">
        <label for="websiteSelector" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Viewing website</label>
        <select id="websiteSelector" name="website_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" onchange="this.form.submit()" @disabled($connectedWebsites->isEmpty())>
            @if ($connectedWebsites->isEmpty())
                <option value="">No websites connected yet</option>
            @else
                <option value="all" @selected(request()->has('website_id') && $selectedWebsite === null)>All connected websites</option>
                @foreach ($connectedWebsites as $website)
                    <option value="{{ $website->id }}" @selected($selectedWebsite?->id === $website->id)>{{ $website->name }} · {{ $website->domain }}</option>
                @endforeach
            @endif
        </select>
    </div>
    @if ($selectedWebsite)
        <span class="mb-2 inline-flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ $selectedWebsite->domain }}</span>
    @else
        <a class="mb-2 text-xs font-medium text-emerald-700 hover:underline dark:text-emerald-300" href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'websites']) }}">Connect a website in Settings</a>
    @endif
</form>
