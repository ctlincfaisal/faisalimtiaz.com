@php
    $allContacts = $allContacts ?? collect();
    $allContactCategories = $allContactCategories ?? collect();
    $openedContacts = $allContacts->where('opened', true)->count();
    $contactedContacts = $allContacts->filter(fn ($contact) => filled($contact['date_contacted']))->count();
@endphp

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><span>Clients</span><span>/</span><span class="font-medium text-emerald-700 dark:text-emerald-300">All contacts</span></div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">All contacts</h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400">View every lead and contact added to this workspace, along with their email activity.</p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-people"></i> {{ number_format($allContacts->count()) }} records</span>
    </div>

    @if (! ($allContactsReady ?? true))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">Contacts could not be loaded. Please check that the contacts and leads tables are available.</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="{{ $card }} p-5"><span class="{{ $muted }}">Total contacts</span><strong class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($allContacts->count()) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Leads and contact form entries</span></div>
        <div class="{{ $card }} p-5"><span class="{{ $muted }}">Contacted</span><strong class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($contactedContacts) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">At least one email sent</span></div>
        <div class="{{ $card }} p-5"><span class="{{ $muted }}">Read an email</span><strong class="mt-2 block text-3xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($openedContacts) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Recorded email opens</span></div>
        <div class="{{ $card }} p-5"><span class="{{ $muted }}">Categories</span><strong class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($allContactCategories->count()) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Lead targeting categories</span></div>
    </div>

    <section class="{{ $card }} overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2"><label class="{{ $label }}" for="allContactsSearch">Search contacts</label><input id="allContactsSearch" type="search" class="{{ $input }}" placeholder="Search name, email, company..." autocomplete="off"></div>
                <div><label class="{{ $label }}" for="allContactsCategory">Category</label><select id="allContactsCategory" class="{{ $input }}"><option value="">All categories</option>@foreach ($allContactCategories as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select></div>
                <div><label class="{{ $label }}" for="allContactsRead">Email activity</label><select id="allContactsRead" class="{{ $input }}"><option value="">All contacts</option><option value="opened">Read an email</option><option value="not-opened">Not read yet</option></select></div>
                <div><label class="{{ $label }}" for="allContactsContactedFrom">Contacted from</label><input id="allContactsContactedFrom" type="date" class="{{ $input }}"></div>
                <div><label class="{{ $label }}" for="allContactsContactedTo">Contacted to</label><input id="allContactsContactedTo" type="date" class="{{ $input }}"></div>
            </div>
            <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div><label class="{{ $label }}" for="allContactsSort">Sort records</label><select id="allContactsSort" class="{{ $input }} min-w-56"><option value="added-desc">Added: newest first</option><option value="added-asc">Added: oldest first</option><option value="contacted-desc">Date contacted: newest first</option><option value="contacted-asc">Date contacted: oldest first</option><option value="name-asc">Name: A–Z</option><option value="name-desc">Name: Z–A</option><option value="email-asc">Email: A–Z</option><option value="email-desc">Email: Z–A</option><option value="category-asc">Category: A–Z</option><option value="category-desc">Category: Z–A</option></select></div>
                <div class="flex flex-wrap items-center gap-3"><span id="allContactsFilteredCount" class="{{ $muted }}" aria-live="polite"></span><button id="exportAllContacts" type="button" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10"><i class="bi bi-download"></i> Export filtered</button></div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 border-b border-slate-200 bg-slate-50/70 px-5 py-3 dark:border-slate-800 dark:bg-slate-950/40"><label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200"><input id="selectAllAllContacts" type="checkbox" class="h-4 w-4 accent-emerald-600" @disabled($allContacts->isEmpty())> Select all visible</label><span id="allContactsSelectionCount" class="{{ $muted }}" aria-live="polite"></span><button id="clearAllContactsSelection" type="button" hidden class="text-sm font-medium text-emerald-700 hover:underline dark:text-emerald-300">Clear selection</button></div>

        <div class="overflow-x-auto">
            <table id="allContactsTable" class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"><tr><th class="w-12 px-5 py-3"><span class="sr-only">Select</span></th><th class="px-3 py-3">Name</th><th class="px-3 py-3">Email</th><th class="px-3 py-3">Category</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Date contacted</th><th class="px-5 py-3">Email activity</th></tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($allContacts as $contact)
                        <tr data-all-contact data-contact-key="{{ $contact['key'] }}" data-name="{{ $contact['name'] }}" data-email="{{ $contact['email'] }}" data-company="{{ $contact['company'] }}" data-category="{{ $contact['category'] }}" data-status="{{ $contact['status'] }}" data-added="{{ $contact['added_at_value'] }}" data-contacted="{{ $contact['date_contacted_value'] }}" data-opened="{{ $contact['opened'] ? 'opened' : 'not-opened' }}" data-contact="{{ json_encode($contact, JSON_UNESCAPED_SLASHES) }}" class="cursor-pointer transition hover:bg-emerald-50/60 focus-within:bg-emerald-50/60 dark:hover:bg-emerald-500/5 dark:focus-within:bg-emerald-500/5">
                            <td class="px-5 py-4"><input data-all-contact-select type="checkbox" class="h-4 w-4 accent-emerald-600" aria-label="Select {{ $contact['name'] }}"></td>
                            <td class="px-3 py-4"><div class="font-medium text-slate-900 dark:text-slate-100">{{ $contact['name'] }}</div><div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $contact['source'] }}</div></td>
                            <td class="px-3 py-4 font-medium text-slate-700 dark:text-slate-200">{{ $contact['email'] }}</td>
                            <td class="px-3 py-4"><span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $contact['category'] }}</span></td>
                            <td class="px-3 py-4 text-slate-600 dark:text-slate-300">{{ $contact['status'] }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-slate-600 dark:text-slate-300">{{ $contact['date_contacted'] ?: 'Not contacted' }}</td>
                            <td class="px-5 py-4">@if ($contact['opened'])<span class="inline-flex items-center gap-1.5 font-medium text-emerald-700 dark:text-emerald-300"><i class="bi bi-envelope-open"></i> Read</span>@else<span class="text-slate-500 dark:text-slate-400">Not read</span>@endif<span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ count($contact['email_history']) }} email{{ count($contact['email_history']) === 1 ? '' : 's' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-14 text-center text-slate-500 dark:text-slate-400">No contacts or leads have been added yet.</td></tr>
                    @endforelse
                    <tr id="allContactsNoMatches" hidden><td colspan="7" class="px-5 py-14 text-center text-slate-500 dark:text-slate-400">No contacts match your filters.</td></tr>
                </tbody>
            </table>
        </div>
        <p class="border-t border-slate-200 px-5 py-4 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">Select a checkbox to choose records. Click any contact row to view their profile and complete email history.</p>
    </section>
</div>

<div id="allContactsModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200 ease-out" role="dialog" aria-modal="true" aria-labelledby="allContactsModalTitle">
    <div data-all-contacts-backdrop class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm"></div>
    <div data-all-contacts-panel class="relative z-10 max-h-[90vh] w-full max-w-4xl translate-y-3 scale-95 overflow-y-auto rounded-xl border border-slate-200 bg-white opacity-0 shadow-2xl transition duration-200 ease-out dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-700"><div><p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Contact details</p><h2 id="allContactsModalTitle" class="mt-1 text-xl font-semibold text-slate-950 dark:text-white">Contact</h2><p id="allContactsModalEmail" class="mt-1 text-sm text-slate-500 dark:text-slate-400"></p></div><button type="button" data-all-contacts-close class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close contact details"><i class="bi bi-x-lg"></i></button></div>
        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
            <div class="rounded-lg border border-slate-200 p-5 dark:border-slate-700"><div class="flex items-center gap-3"><span id="allContactsModalAvatar" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"></span><div><h3 id="allContactsModalProfileName" class="font-semibold text-slate-950 dark:text-white"></h3><p id="allContactsModalSource" class="text-xs text-slate-500 dark:text-slate-400"></p></div></div><dl class="mt-5 divide-y divide-slate-200 text-sm dark:divide-slate-700"><div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Company</dt><dd id="allContactsModalCompany" class="text-right font-medium text-slate-900 dark:text-slate-100"></dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Category</dt><dd id="allContactsModalCategory" class="text-right font-medium text-slate-900 dark:text-slate-100"></dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Status</dt><dd id="allContactsModalStatus" class="text-right font-medium text-slate-900 dark:text-slate-100"></dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Added</dt><dd id="allContactsModalAdded" class="text-right font-medium text-slate-900 dark:text-slate-100"></dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Date contacted</dt><dd id="allContactsModalContacted" class="text-right font-medium text-slate-900 dark:text-slate-100"></dd></div><div class="flex justify-between gap-4 py-3"><dt class="text-slate-500 dark:text-slate-400">Email opened</dt><dd id="allContactsModalOpened" class="text-right font-medium text-slate-900 dark:text-slate-100"></dd></div></dl><div id="allContactsModalFormDetails" class="mt-4 hidden rounded-lg bg-slate-50 p-4 text-sm dark:bg-slate-950"></div></div>
            <div><div class="flex items-end justify-between gap-3"><div><h3 class="text-lg font-semibold text-slate-950 dark:text-white">Email history</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Every email and follow-up sent to this contact.</p></div><span id="allContactsModalEmailCount" class="text-sm text-slate-500 dark:text-slate-400"></span></div><div id="allContactsModalHistory" class="mt-4 grid gap-3"></div></div>
        </div>
    </div>
</div>

<script>
(() => {
    const table = document.getElementById('allContactsTable');
    if (!table) return;
    const body = table.querySelector('tbody');
    const rows = Array.from(body.querySelectorAll('[data-all-contact]'));
    const search = document.getElementById('allContactsSearch');
    const category = document.getElementById('allContactsCategory');
    const read = document.getElementById('allContactsRead');
    const contactedFrom = document.getElementById('allContactsContactedFrom');
    const contactedTo = document.getElementById('allContactsContactedTo');
    const sort = document.getElementById('allContactsSort');
    const selectAll = document.getElementById('selectAllAllContacts');
    const clear = document.getElementById('clearAllContactsSelection');
    const selectionCount = document.getElementById('allContactsSelectionCount');
    const filteredCount = document.getElementById('allContactsFilteredCount');
    const noMatches = document.getElementById('allContactsNoMatches');
    const checkbox = row => row.querySelector('[data-all-contact-select]');
    const visibleRows = () => rows.filter(row => !row.hidden);

    const updateSelection = () => {
        const visible = visibleRows();
        const selected = rows.filter(row => checkbox(row)?.checked);
        const visibleSelected = visible.filter(row => checkbox(row)?.checked).length;
        selectAll.disabled = visible.length === 0;
        selectAll.checked = visible.length > 0 && visibleSelected === visible.length;
        selectAll.indeterminate = visibleSelected > 0 && visibleSelected < visible.length;
        selectionCount.textContent = selected.length ? `${selected.length} selected` : '';
        clear.hidden = selected.length === 0;
        rows.forEach(row => row.classList.toggle('bg-emerald-50', checkbox(row)?.checked));
    };
    const applyFilters = () => {
        const query = search.value.trim().toLowerCase();
        rows.forEach(row => {
            const matchesText = [row.dataset.name, row.dataset.email, row.dataset.company, row.dataset.category, row.dataset.status].join(' ').toLowerCase().includes(query);
            const matchesCategory = !category.value || row.dataset.category === category.value;
            const matchesRead = !read.value || row.dataset.opened === read.value;
            const matchesFrom = !contactedFrom.value || (row.dataset.contacted && row.dataset.contacted >= contactedFrom.value);
            const matchesTo = !contactedTo.value || (row.dataset.contacted && row.dataset.contacted <= contactedTo.value);
            row.hidden = !(matchesText && matchesCategory && matchesRead && matchesFrom && matchesTo);
        });
        filteredCount.textContent = `Showing ${visibleRows().length} of ${rows.length} records`;
        noMatches.hidden = rows.length === 0 || visibleRows().length > 0;
        updateSelection();
    };
    const applySort = () => {
        const [field, direction] = sort.value.split('-');
        const multiplier = direction === 'asc' ? 1 : -1;
        const fieldMap = { name: 'name', email: 'email', category: 'category', added: 'added', contacted: 'contacted' };
        rows.sort((a, b) => {
            const left = a.dataset[fieldMap[field]] || '';
            const right = b.dataset[fieldMap[field]] || '';
            if (['added', 'contacted'].includes(field)) {
                if (!left || !right) return Number(!left) - Number(!right);
                return (left.localeCompare(right)) * multiplier;
            }
            return left.localeCompare(right, undefined, { sensitivity: 'base', numeric: true }) * multiplier;
        });
        rows.forEach(row => body.appendChild(row));
        body.appendChild(noMatches);
        applyFilters();
    };
    rows.forEach(row => {
        checkbox(row)?.addEventListener('click', event => event.stopPropagation());
        checkbox(row)?.addEventListener('change', updateSelection);
    });
    [search, category, read, contactedFrom, contactedTo].forEach(control => control.addEventListener(control.type === 'search' ? 'input' : 'change', applyFilters));
    sort.addEventListener('change', applySort);
    selectAll.addEventListener('change', () => { visibleRows().forEach(row => { checkbox(row).checked = selectAll.checked; }); updateSelection(); });
    clear.addEventListener('click', () => { rows.forEach(row => { checkbox(row).checked = false; }); updateSelection(); });
    document.getElementById('exportAllContacts').addEventListener('click', () => {
        const escapeCsv = value => `"${String(value ?? '').replaceAll('"', '""')}"`;
        const csv = [['Name', 'Email', 'Company', 'Category', 'Status', 'Date contacted'], ...visibleRows().map(row => [row.dataset.name, row.dataset.email, row.dataset.company, row.dataset.category, row.dataset.status, row.dataset.contacted || 'Not contacted'])].map(line => line.map(escapeCsv).join(',')).join('\r\n');
        const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' })); link.download = 'all-contacts.csv'; link.click(); URL.revokeObjectURL(link.href);
    });

    const modal = document.getElementById('allContactsModal');
    const panel = modal.querySelector('[data-all-contacts-panel]');
    let closeTimer;
    const text = (id, value) => { const element = document.getElementById(id); if (element) element.textContent = value || 'Not recorded'; };
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[character]);
    const closeModal = () => { window.clearTimeout(closeTimer); modal.classList.add('opacity-0'); panel.classList.add('translate-y-3', 'scale-95', 'opacity-0'); closeTimer = window.setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 200); };
    const openModal = row => {
        const data = JSON.parse(row.dataset.contact || '{}');
        text('allContactsModalTitle', data.name); text('allContactsModalEmail', data.email); text('allContactsModalProfileName', data.name); text('allContactsModalSource', data.source); text('allContactsModalCompany', data.company); text('allContactsModalCategory', data.category); text('allContactsModalStatus', data.status); text('allContactsModalAdded', data.added_at); text('allContactsModalContacted', data.date_contacted || 'Not contacted'); text('allContactsModalOpened', data.opened ? 'Yes' : 'Not yet');
        document.getElementById('allContactsModalAvatar').textContent = (data.name || data.email || '?').slice(0, 2).toUpperCase();
        const formDetails = document.getElementById('allContactsModalFormDetails'); const contactForm = data.details?.contact_form;
        formDetails.classList.toggle('hidden', !contactForm); if (contactForm) formDetails.innerHTML = `<strong class="text-slate-900 dark:text-slate-100">Contact form details</strong><p class="mt-2 text-slate-600 dark:text-slate-300"><span class="font-medium">Budget:</span> ${escapeHtml(contactForm.budget || 'Not specified')}</p><p class="mt-2 whitespace-pre-wrap text-slate-600 dark:text-slate-300">${escapeHtml(contactForm.details || 'No message provided.')}</p>`;
        const history = data.email_history || []; text('allContactsModalEmailCount', `${history.length} email${history.length === 1 ? '' : 's'}`);
        const historyElement = document.getElementById('allContactsModalHistory'); historyElement.innerHTML = history.length ? history.map(email => `<article class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><div class="flex flex-wrap items-start justify-between gap-3"><div><span class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">${escapeHtml(email.type)}</span><h4 class="mt-1 font-semibold text-slate-950 dark:text-white">${escapeHtml(email.subject || 'No subject')}</h4><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">${escapeHtml(email.campaign || 'Direct email')} · Sent ${escapeHtml(email.sent_at)}</p></div><span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">${escapeHtml(email.status)}</span></div><div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500 dark:text-slate-400"><span>Opened: ${escapeHtml(email.opened_at)}</span><span>${email.open_count || 0} open${email.open_count === 1 ? '' : 's'}</span>${email.from_email ? `<span>From: ${escapeHtml(email.from_email)}</span>` : ''}</div><details class="mt-3"><summary class="cursor-pointer text-xs font-medium text-emerald-700 dark:text-emerald-300">View email content</summary><div class="mt-2 whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-700 dark:bg-slate-950 dark:text-slate-300">${escapeHtml(email.body || 'No email body recorded.')}</div></details></article>`).join('') : '<div class="rounded-lg border border-dashed border-slate-300 px-5 py-12 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No sent emails recorded for this contact.</div>';
        modal.classList.remove('hidden'); modal.classList.add('flex'); requestAnimationFrame(() => { modal.classList.remove('opacity-0'); panel.classList.remove('translate-y-3', 'scale-95', 'opacity-0'); });
    };
    rows.forEach(row => row.addEventListener('click', () => openModal(row)));
    modal.querySelectorAll('[data-all-contacts-close]').forEach(button => button.addEventListener('click', closeModal)); modal.querySelector('[data-all-contacts-backdrop]').addEventListener('click', closeModal); document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
    applySort();
})();
</script>
