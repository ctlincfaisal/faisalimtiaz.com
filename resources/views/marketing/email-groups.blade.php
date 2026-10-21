<h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Email groups</h1>
<p class="mt-2 text-slate-500 dark:text-slate-400">Add contacts manually and organize them as old clients, new leads or users.</p>

@if (! $emailGroupsReady)
    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">Email groups storage is not ready yet. Run <code>php artisan migrate</code> on the server to enable it.</div>
@endif

<form action="{{ route('marketing.email-groups.store') }}" method="POST" class="mt-7">
    @csrf
    <fieldset class="grid gap-5 md:grid-cols-2 xl:grid-cols-4" @disabled(! $emailGroupsReady)>
        <legend class="sr-only">Add a contact</legend>
        <div>
            <label for="groupContactName" class="{{ $label }}">Name</label>
            <input id="groupContactName" name="name" type="text" class="{{ $input }}" value="{{ old('name') }}" maxlength="255" autocomplete="name" required>
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="groupContactProject" class="{{ $label }}">Projects</label>
            <input id="groupContactProject" name="project" type="text" class="{{ $input }}" value="{{ old('project') }}" maxlength="255" placeholder="Project name (optional)">
            @error('project')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="groupContactEmail" class="{{ $label }}">Email</label>
            <input id="groupContactEmail" name="email" type="email" class="{{ $input }}" value="{{ old('email') }}" maxlength="254" autocomplete="email" required>
            @error('email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="groupContactSource" class="{{ $label }}">Source</label>
            <select id="groupContactSource" name="source" class="{{ $input }}" required>
                <option value="">Choose a source</option>
                @foreach (\App\Models\MarketingEmailGroupContact::SOURCES as $value => $sourceLabel)
                    <option value="{{ $value }}" @selected(old('source') === $value)>{{ $sourceLabel }}</option>
                @endforeach
            </select>
            @error('source')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex justify-end md:col-span-2 xl:col-span-4">
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">Submit</button>
        </div>
    </fieldset>
</form>

<div class="mt-8 border-t border-slate-200 pt-7 dark:border-slate-800">
    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Saved contacts</h2>
    <form id="emailGroupFilters" method="GET" action="{{ route('marketing') }}" class="mt-4 grid items-end gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
        <input type="hidden" name="tab" value="email-groups">
        <div>
            <label for="emailGroupsSearch" class="{{ $label }}">Filter emails</label>
            <input id="emailGroupsSearch" name="group_search" type="search" class="{{ $input }}" placeholder="Search name, email or project" value="{{ $emailGroupSearch }}">
        </div>
        <div>
            <label for="emailGroupsSource" class="{{ $label }}">Filter by source</label>
            <select id="emailGroupsSource" name="group_source" class="{{ $input }}" onchange="this.form.requestSubmit()">
                <option value="">All sources</option>
                @foreach (\App\Models\MarketingEmailGroupContact::SOURCES as $value => $sourceLabel)
                    <option value="{{ $value }}" @selected($emailGroupSource === $value)>{{ $sourceLabel }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="emailGroupsSort" class="{{ $label }}">Sort by</label>
            <select id="emailGroupsSort" name="group_sort" class="{{ $input }}" onchange="this.form.requestSubmit()">
                <option value="newest" @selected($emailGroupSort === 'newest')>Date added: newest first</option>
                <option value="oldest" @selected($emailGroupSort === 'oldest')>Date added: oldest first</option>
                <option value="name-asc" @selected($emailGroupSort === 'name-asc')>Name: A–Z</option>
                <option value="name-desc" @selected($emailGroupSort === 'name-desc')>Name: Z–A</option>
                <option value="email-asc" @selected($emailGroupSort === 'email-asc')>Email: A–Z</option>
                <option value="email-desc" @selected($emailGroupSort === 'email-desc')>Email: Z–A</option>
                <option value="project-asc" @selected($emailGroupSort === 'project-asc')>Project: A–Z</option>
                <option value="project-desc" @selected($emailGroupSort === 'project-desc')>Project: Z–A</option>
                <option value="source-asc" @selected($emailGroupSort === 'source-asc')>Source: A–Z</option>
                <option value="source-desc" @selected($emailGroupSort === 'source-desc')>Source: Z–A</option>
            </select>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="whitespace-nowrap rounded-lg border border-emerald-200 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10">Apply filters</button>
            <a href="{{ route('marketing', ['tab' => 'email-groups']) }}" class="text-sm text-slate-500 hover:underline dark:text-slate-400">Reset</a>
        </div>
    </form>

    @if ($emailGroupsReady)
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ number_format($emailGroupContacts->total()) }} matching {{ $emailGroupContacts->total() === 1 ? 'contact' : 'contacts' }}</p>
            <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                <label for="emailGroupsPerPage">Rows per page</label>
                <select id="emailGroupsPerPage" name="group_per_page" form="emailGroupFilters" class="{{ $input }} w-auto" onchange="this.form.requestSubmit()">
                    @foreach (['25' => '25', '50' => '50', '100' => '100', '250' => '250', 'all' => 'All matching contacts'] as $value => $text)
                        <option value="{{ $value }}" @selected($emailGroupPerPage === (string) $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button id="copyEmailGroupContacts" type="button" hidden class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-60">
            <i class="bi bi-clipboard"></i>
            COPY EMAILS
        </button>
        <button id="clearEmailGroupSelection" type="button" hidden class="text-sm font-medium text-emerald-700 hover:underline dark:text-emerald-300">Clear selection</button>
        <span id="emailGroupSelectionCount" class="{{ $muted }}" aria-live="polite"></span>
        <span id="emailGroupCopyStatus" class="text-sm text-emerald-700 dark:text-emerald-300" role="status"></span>
    </div>
    <textarea id="emailGroupCopyFallback" class="{{ $input }} mt-3" rows="3" readonly hidden aria-label="Selected email addresses to copy"></textarea>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
        <table id="emailGroupContactsTable" class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                <tr>
                    <th scope="col" class="w-12 px-4 py-3"><input id="selectAllEmailGroupContacts" type="checkbox" class="h-4 w-4 accent-emerald-600" aria-label="Select all contacts on this page" @disabled($emailGroupContacts->isEmpty())></th>
                    <th scope="col" class="px-4 py-3">Name</th>
                    <th scope="col" class="px-4 py-3">Project</th>
                    <th scope="col" class="px-4 py-3">Email</th>
                    <th scope="col" class="px-4 py-3">Source</th>
                    <th scope="col" class="whitespace-nowrap px-4 py-3">Date added</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($emailGroupContacts as $contact)
                    <tr data-email-group-contact>
                        <td class="px-4 py-4"><input type="checkbox" data-email-group-select value="{{ $contact->email }}" class="h-4 w-4 accent-emerald-600" aria-label="Select {{ $contact->email }}"></td>
                        <td class="px-4 py-4 font-medium text-slate-900 dark:text-slate-100">{{ $contact->name }}</td>
                        <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $contact->project ?: '—' }}</td>
                        <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $contact->email }}</td>
                        @php
                            $sourceClass = match ($contact->source) {
                                'old_client' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                'users' => 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300',
                                default => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
                            };
                        @endphp
                        <td class="px-4 py-4"><span class="inline-block whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium {{ $sourceClass }}">{{ \App\Models\MarketingEmailGroupContact::SOURCES[$contact->source] ?? $contact->source }}</span></td>
                        <td class="whitespace-nowrap px-4 py-4 text-slate-600 dark:text-slate-300">{{ $contact->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">{{ $emailGroupSearch !== '' || $emailGroupSource !== '' ? 'No contacts match your filters.' : 'No contacts saved yet. Add your first contact using the form above.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Select contacts on this page to copy their email addresses. Selections reset when changing pages, filters or sorting.</p>
    @if ($emailGroupsReady)
        <div class="mt-5">{{ $emailGroupContacts->links() }}</div>
    @endif
</div>

<script>
    (() => {
        const table = document.getElementById('emailGroupContactsTable');
        const rows = Array.from(table.querySelectorAll('[data-email-group-contact]'));
        const selectAll = document.getElementById('selectAllEmailGroupContacts');
        const copyButton = document.getElementById('copyEmailGroupContacts');
        const clearButton = document.getElementById('clearEmailGroupSelection');
        const selectionCount = document.getElementById('emailGroupSelectionCount');
        const copyStatus = document.getElementById('emailGroupCopyStatus');
        const fallback = document.getElementById('emailGroupCopyFallback');
        const checkbox = row => row.querySelector('[data-email-group-select]');
        const selectedRows = () => rows.filter(row => checkbox(row).checked);

        function updateSelection() {
            const selected = selectedRows();
            selectAll.disabled = rows.length === 0;
            selectAll.checked = rows.length > 0 && selected.length === rows.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < rows.length;
            copyButton.hidden = selected.length === 0;
            clearButton.hidden = selected.length === 0;
            selectionCount.textContent = selected.length ? selected.length + ' selected' : '';
            copyStatus.textContent = '';
            fallback.hidden = true;
            rows.forEach(row => {
                row.classList.toggle('bg-emerald-50', checkbox(row).checked);
                row.classList.toggle('dark:bg-emerald-500/10', checkbox(row).checked);
            });
        }

        rows.forEach(row => checkbox(row).addEventListener('change', updateSelection));
        selectAll.addEventListener('change', () => {
            rows.forEach(row => { checkbox(row).checked = selectAll.checked; });
            updateSelection();
        });
        clearButton.addEventListener('click', () => {
            rows.forEach(row => { checkbox(row).checked = false; });
            updateSelection();
        });
        copyButton.addEventListener('click', async () => {
            const emails = [...new Set(selectedRows().map(row => checkbox(row).value.trim().toLowerCase()).filter(Boolean))];
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

        updateSelection();
    })();
</script>
