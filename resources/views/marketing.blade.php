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
            <a class="{{ $navBase }} {{ $activeTab === 'send' ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'send']) }}">
                <i class="bi bi-send"></i>
                <span>Send new email</span>
            </a>
            <a class="{{ $navBase }} {{ in_array($activeTab, ['sent-emails', 'sent-email-detail'], true) ? $navActive : '' }}" href="{{ route('marketing', ['tab' => 'sent-emails']) }}">
                <i class="bi bi-envelope-check"></i>
                <span>Sent emails</span>
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
                            @endphp
                            <a class="{{ $card }} block p-4 transition hover:border-slate-300 hover:shadow-md dark:hover:border-slate-700" href="{{ route('marketing', ['tab' => 'sent-email-detail', 'email' => $email->id]) }}">
                                <div class="grid items-center gap-4 md:grid-cols-[minmax(0,1fr)_auto_auto]">
                                    <div>
                                        <strong class="font-medium text-slate-900 dark:text-slate-100">{{ $email->subject }}</strong>
                                        <div class="{{ $muted }}">
                                            {{ $email->recipient_count }} recipient{{ $email->recipient_count === 1 ? '' : 's' }}
                                            &middot;
                                            {{ optional($sentAt)->format('M d, Y h:i A') }}
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
                                </div>
                            </a>
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
