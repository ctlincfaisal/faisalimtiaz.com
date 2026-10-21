<h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Email groups</h1>
<p class="mt-2 text-slate-500 dark:text-slate-400">Add contacts manually and organize them as old clients or new leads.</p>

@if (! $emailGroupsReady)
    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">Email groups storage is not ready yet. Run <code>php artisan migrate</code> on the server to enable it.</div>
@endif

<form action="{{ route('marketing.email-groups.store') }}" method="POST" class="mt-7">
    @csrf
    <fieldset class="grid gap-5 md:grid-cols-3" @disabled(! $emailGroupsReady)>
        <legend class="sr-only">Add a contact</legend>
        <div>
            <label for="groupContactName" class="{{ $label }}">Name</label>
            <input id="groupContactName" name="name" type="text" class="{{ $input }}" value="{{ old('name') }}" maxlength="255" autocomplete="name" required>
            @error('name')
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
                <option value="old_client" @selected(old('source') === 'old_client')>Old client</option>
                <option value="new_lead" @selected(old('source') === 'new_lead')>New lead</option>
            </select>
            @error('source')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex justify-end md:col-span-3">
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">Submit</button>
        </div>
    </fieldset>
</form>

<div class="mt-8 border-t border-slate-200 pt-7 dark:border-slate-800">
    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Saved contacts</h2>
    <form method="GET" action="{{ route('marketing') }}" class="mt-4 grid items-end gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
        <input type="hidden" name="tab" value="email-groups">
        <div>
            <label for="emailGroupsSearch" class="{{ $label }}">Filter emails</label>
            <input id="emailGroupsSearch" name="group_search" type="search" class="{{ $input }}" placeholder="Search name or email" value="{{ $emailGroupSearch }}">
        </div>
        <div>
            <label for="emailGroupsSource" class="{{ $label }}">Filter by source</label>
            <select id="emailGroupsSource" name="group_source" class="{{ $input }}" onchange="this.form.requestSubmit()">
                <option value="">All sources</option>
                <option value="old_client" @selected($emailGroupSource === 'old_client')>Old client</option>
                <option value="new_lead" @selected($emailGroupSource === 'new_lead')>New lead</option>
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
        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">{{ number_format($emailGroupContacts->total()) }} matching {{ $emailGroupContacts->total() === 1 ? 'contact' : 'contacts' }}</p>
    @endif

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-4 py-3">Name</th>
                    <th scope="col" class="px-4 py-3">Email</th>
                    <th scope="col" class="px-4 py-3">Source</th>
                    <th scope="col" class="whitespace-nowrap px-4 py-3">Date added</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($emailGroupContacts as $contact)
                    <tr>
                        <td class="px-4 py-4 font-medium text-slate-900 dark:text-slate-100">{{ $contact->name }}</td>
                        <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $contact->email }}</td>
                        <td class="px-4 py-4"><span class="inline-block whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium {{ $contact->source === 'old_client' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300' }}">{{ $contact->source === 'old_client' ? 'Old client' : 'New lead' }}</span></td>
                        <td class="whitespace-nowrap px-4 py-4 text-slate-600 dark:text-slate-300">{{ $contact->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">{{ $emailGroupSearch !== '' || $emailGroupSource !== '' ? 'No contacts match your filters.' : 'No contacts saved yet. Add your first contact using the form above.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($emailGroupsReady)
        <div class="mt-5">{{ $emailGroupContacts->links() }}</div>
    @endif
</div>
