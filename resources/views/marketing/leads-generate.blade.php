@php
    $leadSourceTab = $leadSourceTab ?? 'import';
    $leadImportPreview = $leadImportPreview ?? null;
    $leadSegments = $leadSegments ?? collect();
    $importHeaders = $leadImportPreview['headers'] ?? [];
    $headerFor = function (array $needles) use ($importHeaders): string {
        foreach ($importHeaders as $header) {
            if (\Illuminate\Support\Str::contains(strtolower($header), $needles)) {
                return $header;
            }
        }

        return '';
    };
    $defaultFirstName = $headerFor(['first', 'name']);
    $defaultLastName = $headerFor(['last', 'surname']);
    $defaultCompany = $headerFor(['company', 'business', 'organization']);
    $defaultEmail = $headerFor(['email', 'e-mail']);
    $defaultStatus = $headerFor(['status', 'stage']);
@endphp

<div class="flex flex-col gap-6">
    <div>
        <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><span>Clients</span><span>/</span><span class="font-medium text-emerald-700">Generate Leads</span></div>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Generate Leads</h1>
                <p class="mt-2 text-slate-500 dark:text-slate-400">Bring new contacts into your workspace from the lead source that fits your workflow.</p>
            </div>
            <a href="{{ route('marketing', ['tab' => 'leads']) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-700 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-emerald-500/10"><i class="bi bi-list-ul"></i> All Leads</a>
        </div>
    </div>

    <nav class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800" aria-label="Lead generation sources">
        <a href="{{ route('marketing', ['tab' => 'leads-generate', 'lead_source_tab' => 'import']) }}" class="lead-source-tab inline-flex items-center gap-2 px-4 py-3 text-sm font-semibold transition {{ $leadSourceTab === 'import' ? 'lead-source-tab-active text-emerald-700' : 'text-slate-500 hover:text-emerald-700 dark:text-slate-400 dark:hover:text-emerald-300' }}" @if ($leadSourceTab === 'import') aria-current="page" @endif><i class="bi bi-file-earmark-arrow-up"></i> Import Leads</a>
        <a href="{{ route('marketing', ['tab' => 'leads-generate', 'lead_source_tab' => 'search']) }}" class="lead-source-tab inline-flex items-center gap-2 px-4 py-3 text-sm font-semibold transition {{ $leadSourceTab === 'search' ? 'lead-source-tab-active text-emerald-700' : 'text-slate-500 hover:text-emerald-700 dark:text-slate-400 dark:hover:text-emerald-300' }}" @if ($leadSourceTab === 'search') aria-current="page" @endif><i class="bi bi-search"></i> Search Leads</a>
    </nav>

    @if (! $leadsReady)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">Leads storage is not ready yet. Run <code>php artisan migrate</code> on the server to enable it.</div>
    @elseif ($leadSourceTab === 'import')
        <section class="{{ $card }} overflow-hidden">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 px-5 py-5 dark:border-slate-800">
                <div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Import Leads</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Upload a CSV, map its columns, and add the contacts to a category.</p></div>
                <a href="{{ route('marketing.leads.sample') }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10"><i class="bi bi-download"></i> Download sample CSV</a>
            </div>
            <div class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_300px]">
                @if ($leadImportPreview)
                    <div>
                        <div class="flex items-start justify-between gap-3"><div><h3 class="text-lg font-semibold text-slate-950 dark:text-white">Map your columns</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $leadImportPreview['filename'] }} · {{ number_format($leadImportPreview['row_count']) }} rows ready to import.</p></div><i class="bi bi-file-earmark-spreadsheet text-xl text-emerald-600"></i></div>
                        <form action="{{ route('marketing.leads.import') }}" method="POST" class="mt-5 space-y-4">
                            @csrf
                            @foreach ([['first_name_column', 'First name', $defaultFirstName, false], ['last_name_column', 'Last name', $defaultLastName, false], ['email_column', 'Email', $defaultEmail, true], ['company_column', 'Company', $defaultCompany, false], ['status_column', 'Status', $defaultStatus, false]] as [$field, $labelText, $default, $required])
                                <label class="grid items-center gap-3 text-sm sm:grid-cols-[150px_minmax(0,1fr)]"><span class="font-medium text-slate-700 dark:text-slate-200">{{ $labelText }}</span><select name="{{ $field }}" class="{{ $input }} py-2" @required($required)><option value="">Not mapped</option>@foreach ($importHeaders as $header)<option value="{{ $header }}" @selected($default === $header)>{{ $header }}</option>@endforeach</select></label>
                            @endforeach
                            <div>
                                <label for="generateLeadImportCategory" class="{{ $label }}">Add to category</label>
                                <select id="generateLeadImportCategory" name="segment" class="{{ $input }}"><option value="">Unassigned</option>@foreach ($leadSegments as $segment)<option value="{{ $segment->name }}">{{ $segment->name }}</option>@endforeach<option value="__create__">+ Add category</option></select>
                                <div id="generateLeadImportNewCategoryFields" class="mt-3 hidden"><label for="generateLeadImportNewCategory" class="mb-2 block text-xs font-medium text-slate-600 dark:text-slate-300">New category name</label><input id="generateLeadImportNewCategory" name="new_category" type="text" class="{{ $input }}" placeholder="e.g. Wedding photographers" maxlength="80"><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">This category will be created and applied to the imported leads.</p></div>
                            </div>
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"><i class="bi bi-check-circle mr-1"></i> Your CSV is ready. Review the mapping and import the leads.</div>
                            <button type="submit" class="marketing-primary-action inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold"><i class="bi bi-cloud-arrow-up"></i> Import {{ number_format($leadImportPreview['row_count']) }} leads</button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('marketing.leads.import.preview') }}" method="POST" enctype="multipart/form-data" class="flex min-h-72 flex-col items-center justify-center rounded-xl border border-dashed border-emerald-300 bg-emerald-50/40 p-8 text-center dark:border-emerald-500/30 dark:bg-emerald-500/5">
                        @csrf
                        <input type="hidden" name="return_tab" value="leads-generate">
                        <i class="bi bi-cloud-arrow-up text-4xl text-emerald-600"></i>
                        <h3 class="mt-4 text-lg font-semibold text-slate-950 dark:text-white">Upload a lead list</h3>
                        <p class="mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">Choose a CSV with an email column. You can map names, companies, statuses, and categories before importing.</p>
                        <label for="generateLeadCsv" class="marketing-primary-action mt-5 inline-flex cursor-pointer items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold"><i class="bi bi-upload"></i> Choose CSV file<input id="generateLeadCsv" name="file" type="file" accept=".csv,.txt,text/csv" class="sr-only" required onchange="this.form.submit()"></label>
                        <span class="mt-3 text-xs text-slate-500 dark:text-slate-400">Maximum 5 MB · Up to 5,000 rows per import</span>
                    </form>
                @endif
                <aside class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950"><h3 class="font-semibold text-slate-950 dark:text-white">Import checklist</h3><ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300"><li class="flex gap-2"><i class="bi bi-check2 text-emerald-600"></i><span>Use the sample CSV for the correct columns.</span></li><li class="flex gap-2"><i class="bi bi-check2 text-emerald-600"></i><span>Email is the only required field.</span></li><li class="flex gap-2"><i class="bi bi-check2 text-emerald-600"></i><span>Duplicates are updated instead of added twice.</span></li></ul><a href="{{ route('marketing', ['tab' => 'leads']) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:underline">View All Leads <i class="bi bi-arrow-right"></i></a></aside>
            </div>
        </section>
    @else
        <section class="{{ $card }} p-6">
            <div class="flex items-start gap-4"><span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-xl text-emerald-700"><i class="bi bi-search"></i></span><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Search Leads</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Search providers will let you discover and import new contacts directly into your workspace.</p></div></div>
            <div class="mt-6 grid gap-4 md:grid-cols-2"><div class="rounded-xl border border-slate-200 p-5 dark:border-slate-800"><div class="flex items-center justify-between gap-3"><h3 class="font-semibold text-slate-950 dark:text-white">Lead discovery</h3><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Coming soon</span></div><p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Search by job title, industry, company, location, and other criteria when a provider is connected.</p><button type="button" disabled class="mt-5 inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-400 dark:border-slate-700"><i class="bi bi-lock"></i> Search providers</button></div><div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/5"><h3 class="font-semibold text-slate-950 dark:text-white">Your lead sources</h3><p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Import Leads is available now. Additional search integrations can be added here later without changing how your All Leads list works.</p><a href="{{ route('marketing', ['tab' => 'leads-generate', 'lead_source_tab' => 'import']) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:underline">Import leads instead <i class="bi bi-arrow-right"></i></a></div></div>
        </section>
    @endif
</div>

<style>
    .marketing-dashboard .lead-source-tab {
        border-bottom: 2px solid transparent !important;
    }

    .marketing-dashboard .lead-source-tab-active {
        border-bottom-color: #10b981 !important;
    }
</style>

@if ($leadSourceTab === 'import')
<script>
    (() => {
        const category = document.getElementById('generateLeadImportCategory');
        const fields = document.getElementById('generateLeadImportNewCategoryFields');
        const input = document.getElementById('generateLeadImportNewCategory');
        category?.addEventListener('change', () => {
            const creating = category.value === '__create__';
            fields?.classList.toggle('hidden', !creating);
            if (input) {
                input.required = creating;
                if (creating) input.focus();
            }
        });
    })();
</script>
@endif
