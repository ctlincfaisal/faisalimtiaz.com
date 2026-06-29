<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marketing Dashboard</title>
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <script>
        window.tailwind = window.tailwind || {};
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif']
                    }
                }
            }
        };

        if (localStorage.getItem('marketing-theme') === 'dark' || (!localStorage.getItem('marketing-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-screen overflow-hidden bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
@php
    $navBase = 'flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-normal text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300';
    $navActive = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300';
    $card = 'rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900';
    $input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500';
    $label = 'mb-2 block text-sm font-medium text-slate-800 dark:text-slate-200';
    $muted = 'text-sm text-slate-500 dark:text-slate-400';
@endphp

<section class="flex h-screen overflow-hidden">
    <aside class="flex h-screen w-72 shrink-0 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
            <div>
                <div class="text-sm font-semibold text-slate-950 dark:text-white">Marketing</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Email console</div>
            </div>
            <button id="themeToggle" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Toggle dark mode">
                <i class="bi bi-moon-stars dark:hidden"></i>
                <i class="bi bi-sun hidden dark:inline"></i>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-4" aria-label="Marketing dashboard">
            <a class="{{ $navBase }} {{ $activeTab === 'dashboard' ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'dashboard']) }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a class="{{ $navBase }} {{ $activeTab === 'analytics' ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'analytics']) }}">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Website analytics</span>
            </a>
            <a class="{{ $navBase }} {{ $activeTab === 'send' ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'send']) }}">
                <i class="bi bi-send"></i>
                <span>Send new email</span>
            </a>
            <a class="{{ $navBase }} {{ in_array($activeTab, ['sent-emails', 'sent-email-detail'], true) ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'sent-emails']) }}">
                <i class="bi bi-envelope-check"></i>
                <span>Sent emails</span>
            </a>
            <a class="{{ $navBase }} {{ in_array($activeTab, ['followups', 'followups-create', 'followups-edit'], true) ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'followups']) }}">
                <i class="bi bi-calendar2-check"></i>
                <span>Followup email</span>
            </a>
            <a class="{{ $navBase }} {{ $activeTab === 'contacts' ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'contacts']) }}">
                <i class="bi bi-people"></i>
                <span>My contacts</span>
            </a>

            <div class="px-4 pt-5 text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Templates</div>
            <div class="space-y-1 pl-4">
                <a class="{{ $navBase }} {{ $activeTab === 'templates-create' ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'templates-create']) }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>Create template</span>
                </a>
                <a class="{{ $navBase }} {{ in_array($activeTab, ['templates-list', 'templates-edit'], true) ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'templates-list']) }}">
                    <i class="bi bi-list-ul"></i>
                <span>Template listings</span>
                </a>
            </div>
        </nav>

        <div class="border-t border-slate-200 p-4 dark:border-slate-800">
            <form action="{{ route('marketing.logout') }}" method="POST">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-normal text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" type="submit">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="min-w-0 flex-1 overflow-y-auto bg-slate-100 dark:bg-slate-950">
        <div class="min-h-full p-6">
            @if (session('marketing_success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="alert">{{ session('marketing_success') }}</div>
            @endif

            @if (session('marketing_error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">{{ session('marketing_error') }}</div>
            @endif

            @if (! $databaseReady)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">
                    Marketing storage is not ready yet. Run the migration after your database credentials are configured.
                </div>
            @endif

            <div class="{{ $card }} min-h-[calc(100vh-3rem)] p-7">
                @if ($activeTab === 'dashboard')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Marketing dashboard</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Quick summary of your sent email activity.</p>

                    <div class="mt-7 grid gap-4 md:grid-cols-3">
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Emails sent</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($emailsSent) }}</strong>
                        </div>
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Emails opened</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($emailsOpened) }}</strong>
                        </div>
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Contacts</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($contactsCount) }}</strong>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-800">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Recent emails</h2>
                        <div class="mt-4 divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse ($recentEmails as $email)
                                <div class="flex items-center justify-between gap-4 py-4">
                                    <div>
                                        <strong class="font-medium text-slate-900 dark:text-slate-100">{{ $email->subject }}</strong>
                                        <div class="{{ $muted }}">
                                            {{ $email->recipient_count }} recipient{{ $email->recipient_count === 1 ? '' : 's' }}
                                            &middot;
                                            {{ $email->opens->whereNotNull('opened_at')->count() }} opened
                                        </div>
                                    </div>
                                    <span class="{{ $muted }}">{{ optional($email->sent_at)->format('M d, Y') }}</span>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No emails sent yet.</div>
                            @endforelse
                        </div>
                    </div>
                @elseif ($activeTab === 'analytics')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Website analytics</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">See recent visitors, pages viewed, and click activity from the website.</p>

                    <div class="mt-7 grid gap-4 md:grid-cols-4">
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Online now</span>
                            <strong class="mt-2 flex items-center gap-3 text-4xl font-semibold text-slate-950 dark:text-white">
                                <span class="h-3 w-3 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.15)]"></span>
                                {{ number_format($activeVisitorsCount) }}
                            </strong>
                        </div>
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Tracked visits</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($websiteVisitsCount) }}</strong>
                        </div>
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Tracked clicks</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($websiteClicksCount) }}</strong>
                        </div>
                        <div class="{{ $card }} p-5">
                            <span class="{{ $muted }}">Unique visitors</span>
                            <strong class="mt-2 block text-4xl font-semibold text-slate-950 dark:text-white">{{ number_format($websiteVisits->pluck('session_id')->unique()->count()) }}</strong>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Online now</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($activeVisitGroups as $group)
                                <div class="{{ $card }} flex items-center justify-between gap-4 p-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="h-3 w-3 shrink-0 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.15)]"></span>
                                        <div class="min-w-0">
                                            <strong class="block truncate font-medium text-slate-900 dark:text-slate-100">
                                                {{ $group['count'] }} {{ $group['count'] === 1 ? 'person' : 'people' }} online from {{ $group['location'] }}
                                            </strong>
                                            <div class="{{ $muted }}">Active on {{ $group['page'] }}</div>
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Online</span>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No active visitors right now.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-8 grid gap-6 xl:grid-cols-3">
                        <section>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Top pages</h2>
                            <div class="mt-4 divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                                @forelse ($topPages as $page)
                                    <div class="flex items-center justify-between gap-4 bg-white px-4 py-3 dark:bg-slate-900">
                                        <span class="truncate text-sm text-slate-700 dark:text-slate-200">{{ $page->path }}</span>
                                        <strong class="text-sm text-slate-950 dark:text-white">{{ number_format($page->visits_count) }}</strong>
                                    </div>
                                @empty
                                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No visits tracked yet.</div>
                                @endforelse
                            </div>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Top locations</h2>
                            <div class="mt-4 divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                                @forelse ($topLocations as $location)
                                    <div class="flex items-center justify-between gap-4 bg-white px-4 py-3 dark:bg-slate-900">
                                        <span class="truncate text-sm text-slate-700 dark:text-slate-200">
                                            {{ collect([$location->city, $location->region, $location->country])->filter()->join(', ') }}
                                        </span>
                                        <strong class="text-sm text-slate-950 dark:text-white">{{ number_format($location->visits_count) }}</strong>
                                    </div>
                                @empty
                                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No locations tracked yet.</div>
                                @endforelse
                            </div>
                        </section>

                        <section>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Top clicks</h2>
                            <div class="mt-4 divide-y divide-slate-200 overflow-hidden rounded-lg border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                                @forelse ($topClicks as $click)
                                    <div class="bg-white px-4 py-3 dark:bg-slate-900">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $click->element_text ?: $click->element ?: 'Unknown element' }}</span>
                                            <strong class="text-sm text-slate-950 dark:text-white">{{ number_format($click->clicks_count) }}</strong>
                                        </div>
                                        <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $click->path }}</div>
                                    </div>
                                @empty
                                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No clicks tracked yet.</div>
                                @endforelse
                            </div>
                        </section>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Recent visitors</h2>
                        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 dark:border-slate-800">
                            <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] gap-4 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                                <span>Page</span>
                                <span>Place</span>
                                <span>Machine</span>
                                <span>Clicks</span>
                            </div>
                            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                                @forelse ($websiteVisits as $visit)
                                    <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] gap-4 bg-white px-4 py-3 text-sm dark:bg-slate-900">
                                        <div class="min-w-0">
                                            <div class="truncate font-medium text-slate-800 dark:text-slate-100">{{ $visit->path }}</div>
                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ optional($visit->visited_at)->format('M d, Y h:i A') }}</div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-slate-600 dark:text-slate-300">
                                                {{ collect([$visit->city, $visit->region, $visit->country])->filter()->join(', ') ?: 'Unknown location' }}
                                            </div>
                                            <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $visit->ip_address }}{{ $visit->organization ? ' · '.$visit->organization : '' }}</div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-slate-600 dark:text-slate-300">{{ $visit->device_type ?: 'Unknown device' }} · {{ $visit->operating_system ?: 'Unknown OS' }}</div>
                                            <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $visit->browser ?: 'Unknown browser' }}{{ $visit->browser_version ? ' '.$visit->browser_version : '' }} · {{ $visit->referrer ?: 'Direct visit' }}</div>
                                        </div>
                                        <strong class="text-sm text-slate-950 dark:text-white">{{ $visit->clicks_count }}</strong>
                                    </div>
                                @empty
                                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-900 dark:text-slate-400">No visitors tracked yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Recent clicks</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($websiteClicks as $click)
                                <div class="{{ $card }} grid gap-3 p-4 md:grid-cols-[minmax(0,1fr)_auto]">
                                    <div class="min-w-0">
                                        <strong class="block truncate font-medium text-slate-900 dark:text-slate-100">{{ $click->element_text ?: $click->element ?: 'Unknown element' }}</strong>
                                        <div class="{{ $muted }} truncate">{{ $click->path }} · {{ optional($click->clicked_at)->format('M d, Y h:i A') }}</div>
                                    </div>
                                    <span class="{{ $muted }}">x{{ $click->x ?: 0 }}, y{{ $click->y ?: 0 }}</span>
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No clicks tracked yet.</div>
                            @endforelse
                        </div>
                    </div>
                @elseif ($activeTab === 'send')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Send new email</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Write one message and send it to comma-separated email addresses.</p>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.send') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label class="{{ $label }}" for="template">Template</label>
                            <select id="template" class="{{ $input }}" name="template_id">
                                <option value="">Choose a saved template</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('template_id') == $template->id)>{{ $template->name }}</option>
                                @endforeach
                            </select>
                            <div id="templateAttachmentNote" class="mt-2 hidden text-sm text-slate-500 dark:text-slate-400"></div>
                        </div>

                        <div id="templateSubjectChoiceWrap" class="hidden">
                            <label class="{{ $label }}" for="templateSubjectChoice">Saved subject</label>
                            <select id="templateSubjectChoice" class="{{ $input }}"></select>
                        </div>

                        <div>
                            <label class="{{ $label }}" for="recipients">Email addresses</label>
                            <input id="recipients" class="{{ $input }} @error('recipients') border-red-500 @enderror" type="text" name="recipients" value="{{ old('recipients') }}" placeholder="name@example.com, second@example.com">
                            @error('recipients')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="subject">Email title</label>
                            <input id="subject" class="{{ $input }} @error('subject') border-red-500 @enderror" type="text" name="subject" value="{{ old('subject') }}">
                            @error('subject')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="content">Email content</label>
                            <textarea id="content" class="{{ $input }} min-h-44 @error('content') border-red-500 @enderror" name="content">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="attachment">Attach file</label>
                            <input id="attachment" class="{{ $input }} @error('attachment') border-red-500 @enderror" type="file" name="attachment">
                            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">Uploading a file here replaces the selected template attachment for this send only.</div>
                            @error('attachment')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-send"></i>
                                Send email
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'sent-emails')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Sent emails</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Review emails you sent, delivery status, and open status.</p>

                    <div class="mt-7 grid gap-3">
                        @forelse ($sentEmails as $email)
                            @php
                                $openedCount = $email->opens->whereNotNull('opened_at')->count();
                                $deliveryStatus = $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'failed');
                                $sentAt = $email->sent_at ?: $email->created_at;
                                $emailRecipients = collect($email->recipients ?: [])->filter()->values();
                                $visibleEmailRecipients = $emailRecipients->take(6);
                                $hiddenEmailRecipientsCount = max($emailRecipients->count() - $visibleEmailRecipients->count(), 0);
                                $openedEmailRecipients = $email->opens
                                    ->whereNotNull('opened_at')
                                    ->pluck('email')
                                    ->map(fn ($recipient) => strtolower(trim((string) $recipient)))
                                    ->flip();
                            @endphp
                            <div class="{{ $card }} block p-4 transition hover:border-slate-300 hover:shadow-md dark:hover:border-slate-700">
                                <div class="grid items-center gap-4 md:grid-cols-[minmax(0,1fr)_auto_auto_auto_auto]">
                                    <div class="min-w-0">
                                        <strong class="font-medium text-slate-900 dark:text-slate-100">{{ $email->subject }}</strong>
                                        <div class="{{ $muted }}">
                                            {{ $email->recipient_count }} recipient{{ $email->recipient_count === 1 ? '' : 's' }}
                                            &middot;
                                            {{ optional($sentAt)->format('M d, Y h:i A') }}
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @forelse ($visibleEmailRecipients as $recipient)
                                                @php
                                                    $recipientOpened = $openedEmailRecipients->has(strtolower(trim((string) $recipient)));
                                                @endphp
                                                <span class="inline-flex max-w-full items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $recipientOpened ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                                    <i class="bi bi-envelope"></i>
                                                    <span class="max-w-56 truncate">{{ $recipient }}</span>
                                                </span>
                                            @empty
                                                <span class="text-xs text-slate-500 dark:text-slate-400">No recipient emails saved.</span>
                                            @endforelse
                                            @if ($hiddenEmailRecipientsCount > 0)
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                    +{{ $hiddenEmailRecipientsCount }} more
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($deliveryStatus === 'delivered')
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Delivered</span>
                                    @elseif ($deliveryStatus === 'pending')
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Pending</span>
                                    @else
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">Not delivered</span>
                                    @endif

                                    @if ($openedCount > 0)
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Opened</span>
                                    @else
                                        <span class="inline-flex min-w-28 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Not opened</span>
                                    @endif

                                    <a class="inline-flex min-w-24 items-center justify-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'sent-email-detail', 'email' => $email->id]) }}">
                                        <i class="bi bi-eye"></i>
                                        View
                                    </a>
                                    <a class="inline-flex min-w-32 items-center justify-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10" href="{{ route('marketing', ['tab' => 'followups-create', 'email' => $email->id]) }}">
                                        <i class="bi bi-calendar-plus"></i>
                                        Followup
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No sent emails yet.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'sent-email-detail')
                    @php
                        $sentAt = $selectedEmail->sent_at ?: $selectedEmail->created_at;
                    @endphp
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $selectedEmail->subject }}</h1>
                            <p class="mt-2 text-slate-500 dark:text-slate-400">
                                {{ $selectedEmail->recipient_count }} recipient{{ $selectedEmail->recipient_count === 1 ? '' : 's' }}
                                &middot;
                                {{ optional($sentAt)->format('M d, Y h:i A') }}
                            </p>
                        </div>
                        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'sent-emails']) }}">
                            <i class="bi bi-arrow-left"></i>
                            Back
                        </a>
                    </div>

                    <div class="mt-7 grid gap-3">
                        @foreach ($selectedRecipientStatuses as $recipient)
                            <div class="{{ $card }} grid items-center gap-4 p-4 md:grid-cols-[minmax(0,1fr)_auto_auto]">
                                <div>
                                    <strong class="font-medium text-slate-900 dark:text-slate-100">{{ $recipient['email'] }}</strong>
                                    @if ($recipient['last_opened_at'])
                                        <div class="{{ $muted }}">
                                            Last opened {{ $recipient['last_opened_at']->format('M d, Y h:i A') }}
                                            &middot;
                                            {{ $recipient['open_count'] }} open{{ $recipient['open_count'] === 1 ? '' : 's' }}
                                        </div>
                                    @else
                                        <div class="{{ $muted }}">No open recorded yet</div>
                                    @endif
                                </div>

                                @if ($recipient['delivery_status'] === 'delivered')
                                    <span class="inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Delivered</span>
                                @elseif ($recipient['delivery_status'] === 'pending')
                                    <span class="inline-flex min-w-28 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Pending</span>
                                @else
                                    <span class="inline-flex min-w-28 justify-center rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">Not delivered</span>
                                @endif

                                @if ($recipient['opened_at'])
                                    <span class="inline-flex min-w-28 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Opened</span>
                                @else
                                    <span class="inline-flex min-w-28 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Not opened</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif ($activeTab === 'followups-create')
                    @php
                        $sentAt = $selectedFollowupEmail->sent_at ?: $selectedFollowupEmail->created_at;
                    @endphp
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Schedule follow-up</h1>
                            <p class="mt-2 text-slate-500 dark:text-slate-400">Set a template and time for a follow-up to this sent email.</p>
                        </div>
                        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'sent-emails']) }}">
                            <i class="bi bi-arrow-left"></i>
                            Back
                        </a>
                    </div>

                    <div class="{{ $card }} mt-7 p-5">
                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $selectedFollowupEmail->subject }}</div>
                        <div class="{{ $muted }} mt-1">
                            {{ $selectedFollowupEmail->recipient_count }} recipient{{ $selectedFollowupEmail->recipient_count === 1 ? '' : 's' }}
                            &middot;
                            {{ optional($sentAt)->format('M d, Y h:i A') }}
                        </div>
                    </div>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.followups.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="marketing_email_id" value="{{ $selectedFollowupEmail->id }}">

                        <div>
                            <label class="{{ $label }}" for="followup_template">Template</label>
                            <select id="followup_template" class="{{ $input }} @error('template_id') border-red-500 @enderror" name="template_id">
                                <option value="">Choose a saved template</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('template_id') == $template->id)>{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="followup_scheduled_at">Follow-up date and time</label>
                            <input id="followup_scheduled_at" class="{{ $input }} @error('scheduled_at') border-red-500 @enderror" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}">
                            @error('scheduled_at')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-calendar-plus"></i>
                                Schedule follow-up
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'followups-edit')
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Edit follow-up</h1>
                            <p class="mt-2 text-slate-500 dark:text-slate-400">Update the template or date for this scheduled follow-up.</p>
                        </div>
                        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'followups']) }}">
                            <i class="bi bi-arrow-left"></i>
                            Back
                        </a>
                    </div>

                    <div class="{{ $card }} mt-7 p-5">
                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $editingFollowupEmail->subject }}</div>
                        <div class="{{ $muted }} mt-1">
                            {{ $editingFollowupEmail->recipient_count }} recipient{{ $editingFollowupEmail->recipient_count === 1 ? '' : 's' }}
                            &middot;
                            Original: {{ optional($editingFollowupEmail->originalEmail)->subject ?: 'Deleted email' }}
                        </div>
                    </div>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.followups.update', $editingFollowupEmail) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="{{ $label }}" for="edit_followup_template">Template</label>
                            <select id="edit_followup_template" class="{{ $input }} @error('template_id') border-red-500 @enderror" name="template_id">
                                <option value="">Choose a saved template</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('template_id', $editingFollowupEmail->marketing_template_id) == $template->id)>{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="edit_followup_scheduled_at">Follow-up date and time</label>
                            <input id="edit_followup_scheduled_at" class="{{ $input }} @error('scheduled_at') border-red-500 @enderror" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', optional($editingFollowupEmail->scheduled_at)->format('Y-m-d\TH:i')) }}">
                            @error('scheduled_at')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <a class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'followups']) }}">Cancel</a>
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-save"></i>
                                Update follow-up
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'followups')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Followup email</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Review follow-up emails scheduled from sent campaigns.</p>

                    <div class="mt-7 grid gap-3">
                        @forelse ($followupEmails as $followup)
                            @php
                                $followupRecipients = collect($followup->recipients ?: [])->filter()->values();
                                $visibleFollowupRecipients = $followupRecipients->take(6);
                                $hiddenFollowupRecipientsCount = max($followupRecipients->count() - $visibleFollowupRecipients->count(), 0);
                                $openedFollowupRecipients = $followup->opens
                                    ->whereNotNull('opened_at')
                                    ->pluck('email')
                                    ->map(fn ($recipient) => strtolower(trim((string) $recipient)))
                                    ->flip();
                                $followupTimeDistance = $followup->scheduled_at
                                    ? $followup->scheduled_at->diffForHumans(now(), true, false, 2)
                                    : null;
                                $followupTimeLabel = match (true) {
                                    ! $followup->scheduled_at => null,
                                    $followup->status === 'pending' && $followup->scheduled_at->isFuture() => 'Send after '.$followupTimeDistance,
                                    $followup->status === 'pending' => 'Due now',
                                    $followup->status === 'sent' && $followup->sent_at => 'Sent '.$followup->sent_at->diffForHumans(),
                                    $followup->scheduled_at->isPast() => 'Was due '.$followupTimeDistance.' ago',
                                    default => 'Scheduled after '.$followupTimeDistance,
                                };
                            @endphp
                            <div class="{{ $card }} grid items-center gap-4 p-4 md:grid-cols-[minmax(0,1fr)_auto_auto_auto]">
                                <div class="min-w-0">
                                    <strong class="block truncate font-medium text-slate-900 dark:text-slate-100">{{ $followup->subject }}</strong>
                                    <div class="{{ $muted }} mt-1">
                                        {{ $followup->recipient_count }} recipient{{ $followup->recipient_count === 1 ? '' : 's' }}
                                        &middot;
                                        Scheduled {{ optional($followup->scheduled_at)->format('M d, Y h:i A') }}
                                        @if ($followupTimeLabel)
                                            &middot;
                                            {{ $followupTimeLabel }}
                                        @endif
                                    </div>
                                    <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                        Original: {{ optional($followup->originalEmail)->subject ?: 'Deleted email' }}
                                        @if ($followup->template)
                                            &middot; Template: {{ $followup->template->name }}
                                        @endif
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @forelse ($visibleFollowupRecipients as $recipient)
                                            @php
                                                $recipientOpened = $openedFollowupRecipients->has(strtolower(trim((string) $recipient)));
                                            @endphp
                                            <span class="inline-flex max-w-full items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $recipientOpened ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                                <i class="bi bi-envelope"></i>
                                                <span class="max-w-56 truncate">{{ $recipient }}</span>
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-500 dark:text-slate-400">No recipient emails saved.</span>
                                        @endforelse
                                        @if ($hiddenFollowupRecipientsCount > 0)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                +{{ $hiddenFollowupRecipientsCount }} more
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($followup->status === 'sent')
                                    <span class="inline-flex min-w-24 justify-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Sent</span>
                                @elseif ($followup->status === 'failed')
                                    <span class="inline-flex min-w-24 justify-center rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300">Failed</span>
                                @else
                                    <span class="inline-flex min-w-24 justify-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">Pending</span>
                                @endif

                                <span class="{{ $muted }}">{{ optional($followup->created_at)->format('M d, Y') }}</span>

                                <div class="flex flex-wrap justify-end gap-2">
                                    @if ($followup->status !== 'sent')
                                        <a class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10" href="{{ route('marketing', ['tab' => 'followups-edit', 'followup' => $followup->id]) }}">
                                            <i class="bi bi-pencil"></i>
                                            Edit
                                        </a>
                                    @endif
                                    <form action="{{ route('marketing.followups.delete', $followup) }}" method="POST" onsubmit="return confirm('Remove this follow-up email?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10" type="submit">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No follow-up emails scheduled yet.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'contacts')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">My contacts</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Contacts are collected from email addresses you have sent messages to.</p>

                    <div class="mt-7 grid gap-3">
                        @forelse ($contacts as $contact)
                            <div class="{{ $card }} flex items-center justify-between gap-4 p-4">
                                <span>{{ $contact }}</span>
                                <i class="bi bi-envelope text-slate-400"></i>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No contacts yet.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'templates-create')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Create template</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Create a reusable email template for the send page.</p>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label class="{{ $label }}" for="name">Template name</label>
                            <input id="name" class="{{ $input }} @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name') }}">
                            @error('name')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $createSubjects = old('subjects', []);
                            $createSubjects = is_array($createSubjects) ? $createSubjects : [];
                            $createSubjects = array_pad(array_slice($createSubjects, 0, 5), 5, '');
                        @endphp
                        <div>
                            <label class="{{ $label }}" for="template_subject_0">Email titles</label>
                            <div class="grid gap-3">
                                @foreach ($createSubjects as $index => $subject)
                                    <input id="template_subject_{{ $index }}" class="{{ $input }} @error('subjects.'.$index) border-red-500 @enderror" type="text" name="subjects[]" value="{{ $subject }}" placeholder="Subject {{ $index + 1 }}{{ $index === 0 ? ' (required)' : '' }}">
                                    @error('subjects.'.$index)
                                        <div class="-mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            </div>
                            @error('subjects')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="template_content">Email template</label>
                            <textarea id="template_content" class="{{ $input }} min-h-44 @error('content') border-red-500 @enderror" name="content">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="template_attachment">Template attachment</label>
                            <input id="template_attachment" class="{{ $input }} @error('attachment') border-red-500 @enderror" type="file" name="attachment">
                            @error('attachment')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-save"></i>
                                Save template
                            </button>
                        </div>
                    </form>
                @elseif ($activeTab === 'templates-edit')
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Edit template</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Update this saved template.</p>

                    <form class="mt-7 grid gap-5" action="{{ route('marketing.templates.update', $editingTemplate) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="{{ $label }}" for="edit_name">Template name</label>
                            <input id="edit_name" class="{{ $input }} @error('name') border-red-500 @enderror" type="text" name="name" value="{{ old('name', $editingTemplate->name) }}">
                            @error('name')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $editSubjects = old('subjects', $editingTemplate->subject_options ?: [$editingTemplate->subject]);
                            $editSubjects = is_array($editSubjects) ? $editSubjects : [];
                            $editSubjects = array_pad(array_slice($editSubjects, 0, 5), 5, '');
                        @endphp
                        <div>
                            <label class="{{ $label }}" for="edit_template_subject_0">Email titles</label>
                            <div class="grid gap-3">
                                @foreach ($editSubjects as $index => $subject)
                                    <input id="edit_template_subject_{{ $index }}" class="{{ $input }} @error('subjects.'.$index) border-red-500 @enderror" type="text" name="subjects[]" value="{{ $subject }}" placeholder="Subject {{ $index + 1 }}{{ $index === 0 ? ' (required)' : '' }}">
                                    @error('subjects.'.$index)
                                        <div class="-mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            </div>
                            @error('subjects')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="edit_template_content">Email template</label>
                            <textarea id="edit_template_content" class="{{ $input }} min-h-44 @error('content') border-red-500 @enderror" name="content">{{ old('content', $editingTemplate->content) }}</textarea>
                            @error('content')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}" for="edit_template_attachment">Template attachment</label>
                            @if ($editingTemplate->attachment_name)
                                <div class="mb-3 flex flex-col gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm dark:border-slate-800 dark:bg-slate-950 md:flex-row md:items-center md:justify-between">
                                    <span class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-200">
                                        <i class="bi bi-paperclip"></i>
                                        {{ $editingTemplate->attachment_name }}
                                    </span>
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                        <input class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950" type="checkbox" name="remove_attachment" value="1">
                                        Remove attachment
                                    </label>
                                </div>
                            @endif
                            <input id="edit_template_attachment" class="{{ $input }} @error('attachment') border-red-500 @enderror" type="file" name="attachment">
                            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">Choose a new file to replace the current attachment.</div>
                            @error('attachment')
                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <a class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('marketing', ['tab' => 'templates-list']) }}">Cancel</a>
                            <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700" type="submit">
                                <i class="bi bi-save"></i>
                                Update template
                            </button>
                        </div>
                    </form>
                @else
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Template listings</h1>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Manage saved templates for the send page.</p>

                    <div class="mt-7 grid gap-3">
                        @forelse ($templates as $template)
                            <div class="{{ $card }} grid items-center gap-4 p-4 md:grid-cols-[minmax(0,1fr)_auto]">
                                <div>
                                    <strong class="font-medium text-slate-900 dark:text-slate-100">{{ $template->name }}</strong>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        {{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before(str_replace(["\r\n", "\r"], "\n", $template->content), "\n"), 140) }}
                                    </p>
                                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                        {{ count($template->subject_options ?: [$template->subject]) }} subject{{ count($template->subject_options ?: [$template->subject]) === 1 ? '' : 's' }}
                                    </div>
                                    @if ($template->attachment_name)
                                        <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            <i class="bi bi-paperclip"></i>
                                            {{ $template->attachment_name }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <a class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10" href="{{ route('marketing', ['tab' => 'templates-edit', 'template' => $template->id]) }}">
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>
                                    <form action="{{ route('marketing.templates.delete', $template) }}" method="POST" onsubmit="return confirm('Delete this template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10" type="submit">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No templates yet.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </main>
</section>

<script>
    const templateSelect = document.getElementById('template');
    const templates = @json($templateOptions);
    const themeToggle = document.getElementById('themeToggle');

    if (templateSelect) {
        const templateAttachmentNote = document.getElementById('templateAttachmentNote');
        const templateSubjectChoiceWrap = document.getElementById('templateSubjectChoiceWrap');
        const templateSubjectChoice = document.getElementById('templateSubjectChoice');
        const subjectInput = document.getElementById('subject');

        function updateTemplateAttachmentNote(template) {
            if (!templateAttachmentNote) {
                return;
            }

            if (template && template.attachment_name) {
                templateAttachmentNote.textContent = 'Template attachment: ' + template.attachment_name;
                templateAttachmentNote.classList.remove('hidden');
                return;
            }

            templateAttachmentNote.textContent = '';
            templateAttachmentNote.classList.add('hidden');
        }

        function updateTemplateSubjectChoices(template, shouldSetSubject) {
            if (!templateSubjectChoice || !templateSubjectChoiceWrap || !subjectInput) {
                return;
            }

            templateSubjectChoice.innerHTML = '';

            if (!template || !Array.isArray(template.subjects) || template.subjects.length === 0) {
                templateSubjectChoiceWrap.classList.add('hidden');
                return;
            }

            template.subjects.forEach(function (subject, index) {
                const option = document.createElement('option');
                option.value = subject;
                option.textContent = subject || ('Subject ' + (index + 1));
                templateSubjectChoice.appendChild(option);
            });

            templateSubjectChoiceWrap.classList.remove('hidden');

            if (subjectInput.value && template.subjects.includes(subjectInput.value)) {
                templateSubjectChoice.value = subjectInput.value;
            }

            if (shouldSetSubject || !subjectInput.value) {
                subjectInput.value = template.subjects[0] || template.subject || '';
                templateSubjectChoice.value = subjectInput.value;
            }
        }

        updateTemplateAttachmentNote(templates[templateSelect.value]);
        updateTemplateSubjectChoices(templates[templateSelect.value], false);

        if (templateSubjectChoice) {
            templateSubjectChoice.addEventListener('change', function () {
                if (subjectInput) {
                    subjectInput.value = this.value;
                }
            });
        }

        templateSelect.addEventListener('change', function () {
            const content = document.getElementById('content');
            const template = templates[this.value];

            if (!template) {
                updateTemplateAttachmentNote(null);
                updateTemplateSubjectChoices(null, true);
                return;
            }

            content.value = template.content || '';
            updateTemplateAttachmentNote(template);
            updateTemplateSubjectChoices(template, true);
        });
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('marketing-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        });
    }
</script>
</body>

</html>
