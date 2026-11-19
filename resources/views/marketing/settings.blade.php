<div>
    @php
        $mailServiceLimit = $marketingPlanLimits['mail_services'] ?? null;
        $signatureLimit = $marketingPlanLimits['signatures'] ?? null;
        $mailServiceLimitReached = $mailServiceLimit !== null && $mailServices->count() >= $mailServiceLimit;
        $signatureLimitReached = $signatureLimit !== null && $signatures->count() >= $signatureLimit;
        $marketingPlanLabel = $marketingPlan['label'] ?? 'current';
    @endphp

    <style>
        .marketing-dashboard [data-smtp-edit]:hover,
        .marketing-dashboard [data-smtp-edit]:focus-visible {
            background-color: #8bdcba !important;
            border-color: #8bdcba !important;
            color: #ffffff !important;
        }

        .marketing-dashboard [data-smtp-edit]:hover i,
        .marketing-dashboard [data-smtp-edit]:focus-visible i {
            color: #ffffff !important;
        }

        .marketing-dashboard [data-signature-edit]:hover,
        .marketing-dashboard [data-signature-edit]:focus-visible {
            background-color: #8bdcba !important;
            border-color: #8bdcba !important;
            color: #ffffff !important;
        }

        .marketing-dashboard [data-signature-edit]:hover i,
        .marketing-dashboard [data-signature-edit]:focus-visible i {
            color: #ffffff !important;
        }
    </style>

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-gear"></i></span>
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Settings</h1>
                    <p class="mt-1 text-slate-500 dark:text-slate-400">Manage your analytics workspace and connected websites.</p>
                </div>
            </div>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"><i class="bi bi-stars"></i> Flexible for future plans</span>
    </div>

    <div class="mt-8 border-b border-slate-200 dark:border-slate-800">
        <nav class="flex gap-6 overflow-x-auto" aria-label="Settings sections">
            <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'websites']) }}" class="border-b-2 px-1 pb-3 text-sm font-medium {{ $settingsTab === 'websites' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">Connected websites</a>
            <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'services']) }}" class="border-b-2 px-1 pb-3 text-sm font-medium {{ $settingsTab === 'email' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">Email settings</a>
            @if (auth()->user()?->canManageTeam())
                <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'team']) }}" class="border-b-2 px-1 pb-3 text-sm font-medium {{ $settingsTab === 'team' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">Team</a>
            @endif
            <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'account']) }}" class="border-b-2 px-1 pb-3 text-sm font-medium {{ $settingsTab === 'account' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">Account Settings</a>
            <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'billing']) }}" class="border-b-2 px-1 pb-3 text-sm font-medium {{ $settingsTab === 'billing' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">Billing</a>
            <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'notifications']) }}" class="border-b-2 px-1 pb-3 text-sm font-medium {{ $settingsTab === 'notifications' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-400 dark:text-emerald-300' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">Notifications</a>
        </nav>
    </div>

    @if ($settingsTab === 'websites')
        <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
            <section class="{{ $card }} p-6">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-plus-lg"></i></span>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Connect a website</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add a website to keep its analytics separate from the rest of your workspace.</p>
                    </div>
                </div>

                <form class="mt-6 grid gap-4" action="{{ route('marketing.websites.store') }}" method="POST">
                    @csrf
                    <div>
                        <label class="{{ $label }}" for="websiteName">Website name</label>
                        <input id="websiteName" name="name" type="text" class="{{ $input }}" value="{{ old('name') }}" placeholder="Faisal Imtiaz" required>
                    </div>
                    <div>
                        <label class="{{ $label }}" for="websiteDomain">Domain</label>
                        <input id="websiteDomain" name="domain" type="text" class="{{ $input }}" value="{{ old('domain') }}" placeholder="faisalimtiaz.com" required>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Use the domain only. https:// and www. are optional.</p>
                    </div>
                    <button type="submit" class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-link-45deg"></i> Connect website</button>
                </form>
            </section>

            <section>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Your websites</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Switch between these sites from Website Analytics or Performance.</p>
                    </div>
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $connectedWebsites->count() }} connected</span>
                </div>

                <div class="mt-4 grid gap-4">
                    @forelse ($connectedWebsites as $website)
                        <article class="{{ $card }} p-5">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="flex min-w-0 items-start gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-globe2"></i></span>
                                    <div class="min-w-0">
                                        <h3 class="truncate font-semibold text-slate-950 dark:text-white">{{ $website->name }}</h3>
                                        <p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $website->domain }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Connected</span>
                            </div>
                            <div class="mt-5 rounded-lg bg-slate-950 p-4 text-xs text-slate-300">
                                <div class="mb-2 flex items-center justify-between gap-3 text-slate-400"><span>Tracking key</span><span class="font-mono">{{ Str::limit($website->tracking_key, 18, '…') }}</span></div>
                                <code class="block break-all text-emerald-300">&lt;script data-website-key="{{ $website->tracking_key }}" src="{{ url('assets/js/website-analytics.js') }}"&gt;&lt;/script&gt;</code>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                <a href="{{ route('marketing', ['tab' => 'analytics', 'website_id' => $website->id]) }}" class="text-sm font-medium text-emerald-700 hover:underline dark:text-emerald-300">View analytics <i class="bi bi-arrow-up-right"></i></a>
                                <form action="{{ route('marketing.websites.delete', $website) }}" method="POST" onsubmit="return confirm('Disconnect this website? Historical analytics will be kept but unassigned.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-slate-500 hover:text-red-600 dark:text-slate-400 dark:hover:text-red-400">Disconnect</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 px-6 py-10 text-center dark:border-slate-700">
                            <i class="bi bi-globe2 text-3xl text-slate-300 dark:text-slate-600"></i>
                            <h3 class="mt-3 font-medium text-slate-900 dark:text-slate-100">No websites connected yet</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Connect your first website to start organizing analytics by property.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    @elseif ($settingsTab === 'email')
        <div class="mt-7 rounded-lg border border-slate-200 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-950">
            <nav class="flex flex-wrap gap-1" aria-label="Email settings sections">
                <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'services']) }}" class="rounded-md px-3 py-2 text-sm font-medium {{ $emailSettingsTab === 'services' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">SMTP services</a>
                <a href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'signatures']) }}" class="rounded-md px-3 py-2 text-sm font-medium {{ $emailSettingsTab === 'signatures' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">Signatures</a>
            </nav>
        </div>

        @if ($emailSettingsTab === 'services')
            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                @if (! $mailServiceLimitReached)
                    <section class="{{ $card }} p-6">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-envelope-plus"></i></span>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Add email service</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Save a sender identity and SMTP connection for future campaigns.</p>
                        </div>
                    </div>
                    <form class="mt-6 grid gap-4" action="{{ route('marketing.mail-services.store') }}" method="POST">
                        @csrf
                        <div>
                            <label class="{{ $label }}" for="serviceName">Service name</label>
                            <input id="serviceName" name="name" type="text" class="{{ $input }}" value="{{ old('name') }}" placeholder="My business SMTP" required>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="fromName">From name</label>
                                <input id="fromName" name="from_name" type="text" class="{{ $input }}" value="{{ old('from_name') }}" placeholder="Faisal Imtiaz">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="fromEmail">From email</label>
                                <input id="fromEmail" name="from_email" type="email" class="{{ $input }}" value="{{ old('from_email') }}" placeholder="hello@example.com" required>
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_110px]">
                            <div>
                                <label class="{{ $label }}" for="smtpHost">SMTP host</label>
                                <input id="smtpHost" name="smtp_host" type="text" class="{{ $input }}" value="{{ old('smtp_host') }}" placeholder="smtp.example.com" required>
                            </div>
                            <div>
                                <label class="{{ $label }}" for="smtpPort">Port</label>
                                <input id="smtpPort" name="smtp_port" type="number" min="1" max="65535" class="{{ $input }}" value="{{ old('smtp_port', 587) }}" required>
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}" for="smtpUsername">SMTP username</label>
                                <input id="smtpUsername" name="smtp_username" type="text" class="{{ $input }}" value="{{ old('smtp_username') }}">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="smtpPassword">SMTP password</label>
                                <input id="smtpPassword" name="smtp_password" type="password" class="{{ $input }}" autocomplete="new-password" required>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}" for="smtpEncryption">Encryption</label>
                            <select id="smtpEncryption" name="smtp_encryption" class="{{ $input }}">
                                <option value="tls" @selected(old('smtp_encryption', 'tls') === 'tls')>TLS</option>
                                <option value="ssl" @selected(old('smtp_encryption') === 'ssl')>SSL</option>
                                <option value="none" @selected(old('smtp_encryption') === 'none')>None</option>
                            </select>
                        </div>
                        <button type="submit" class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-plus-lg"></i> Save email service</button>
                        <p class="text-xs text-slate-500 dark:text-slate-400">SMTP passwords are encrypted before they are stored.</p>
                    </form>
                    </section>
                @else
                    <section class="{{ $card }} flex min-h-64 flex-col items-center justify-center p-6 text-center">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-lock-fill text-lg"></i></span>
                        <h2 class="mt-4 text-lg font-semibold text-slate-950 dark:text-white">SMTP limit reached</h2>
                        <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500 dark:text-slate-400">Your {{ $marketingPlanLabel }} plan includes {{ $mailServiceLimit }} SMTP {{ $mailServiceLimit === 1 ? 'service' : 'services' }}. Upgrade to a premium membership to add more.</p>
                        <a href="{{ url('/marketing#pricing') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-stars"></i> View premium plans</a>
                    </section>
                @endif

                <section>
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Available services</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose one when composing a new email.</p>
                        </div>
                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ $mailServices->count() }} saved</span>
                    </div>
                    <div class="mt-4 grid gap-4">
                        @forelse ($mailServices as $service)
                            <article class="{{ $card }} p-5">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-server"></i></span>
                                        <div class="min-w-0">
                                            <h3 class="truncate font-semibold text-slate-950 dark:text-white">{{ $service->name }}</h3>
                                            <p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $service->from_name ? $service->from_name.' · ' : '' }}{{ $service->from_email }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Ready</span>
                                </div>
                                <div class="mt-5 grid gap-3 rounded-lg bg-slate-50 p-4 text-sm dark:bg-slate-950 sm:grid-cols-3">
                                    <div><span class="block text-xs text-slate-500 dark:text-slate-400">SMTP host</span><strong class="mt-1 block truncate text-slate-800 dark:text-slate-200">{{ $service->smtp_host }}</strong></div>
                                    <div><span class="block text-xs text-slate-500 dark:text-slate-400">Port</span><strong class="mt-1 block text-slate-800 dark:text-slate-200">{{ $service->smtp_port }}</strong></div>
                                    <div><span class="block text-xs text-slate-500 dark:text-slate-400">Encryption</span><strong class="mt-1 block uppercase text-slate-800 dark:text-slate-200">{{ $service->smtp_encryption ?: 'none' }}</strong></div>
                                </div>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" data-smtp-edit title="Edit service" aria-label="Edit {{ $service->name }}" data-service="{{ json_encode(['id' => $service->id, 'name' => $service->name, 'from_name' => $service->from_name, 'from_email' => $service->from_email, 'smtp_host' => $service->smtp_host, 'smtp_port' => $service->smtp_port, 'smtp_username' => $service->smtp_username, 'smtp_encryption' => $service->smtp_encryption], JSON_UNESCAPED_SLASHES) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-slate-700 dark:text-slate-400 dark:hover:border-emerald-500 dark:hover:bg-emerald-600 dark:hover:text-white"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('marketing.mail-services.delete', $service) }}" method="POST" onsubmit="return confirm('Remove this email service?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Remove service" aria-label="Remove {{ $service->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-red-300 hover:bg-red-50 hover:text-red-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-red-500/40 dark:hover:bg-red-500/10 dark:hover:text-red-400"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 px-6 py-10 text-center dark:border-slate-700"><i class="bi bi-server text-3xl text-slate-300 dark:text-slate-600"></i><h3 class="mt-3 font-medium text-slate-900 dark:text-slate-100">No SMTP services yet</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add one to make it available in Send new email.</p></div>
                        @endforelse
                    </div>
                </section>
            </div>
            <div id="smtpEditModal" data-smtp-update-base="{{ url('marketing/settings/email-services') }}" class="fixed inset-0 z-[80] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200 ease-out" role="dialog" aria-modal="true" aria-labelledby="smtpEditModalTitle">
                <div data-smtp-modal-backdrop class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm"></div>
                <div data-smtp-modal-panel class="relative z-10 max-h-[90vh] w-full max-w-2xl translate-y-3 scale-95 overflow-y-auto rounded-xl border border-slate-200 bg-white opacity-0 shadow-2xl transition duration-200 ease-out dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">SMTP service</p><h2 id="smtpEditModalTitle" class="mt-1 text-xl font-semibold text-slate-950 dark:text-white">Edit service</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update the saved connection details used when sending email.</p></div>
                        <button type="button" data-smtp-modal-close class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close edit service"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <form id="smtpEditModalForm" class="grid gap-4 p-6" action="#" method="POST">
                        @csrf
                        @method('PUT')
                        <div><label class="{{ $label }}" for="smtpModalName">Service name</label><input id="smtpModalName" name="name" type="text" class="{{ $input }}" required></div>
                        <div class="grid gap-4 sm:grid-cols-2"><div><label class="{{ $label }}" for="smtpModalFromName">From name</label><input id="smtpModalFromName" name="from_name" type="text" class="{{ $input }}"></div><div><label class="{{ $label }}" for="smtpModalFromEmail">From email</label><input id="smtpModalFromEmail" name="from_email" type="email" class="{{ $input }}" required></div></div>
                        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_110px]"><div><label class="{{ $label }}" for="smtpModalHost">SMTP host</label><input id="smtpModalHost" name="smtp_host" type="text" class="{{ $input }}" required></div><div><label class="{{ $label }}" for="smtpModalPort">Port</label><input id="smtpModalPort" name="smtp_port" type="number" min="1" max="65535" class="{{ $input }}" required></div></div>
                        <div class="grid gap-4 sm:grid-cols-2"><div><label class="{{ $label }}" for="smtpModalUsername">SMTP username</label><input id="smtpModalUsername" name="smtp_username" type="text" class="{{ $input }}"></div><div><label class="{{ $label }}" for="smtpModalPassword">New SMTP password</label><input id="smtpModalPassword" name="smtp_password" type="password" class="{{ $input }}" autocomplete="new-password" placeholder="Leave blank to keep current password"></div></div>
                        <div><label class="{{ $label }}" for="smtpModalEncryption">Encryption</label><select id="smtpModalEncryption" name="smtp_encryption" class="{{ $input }}"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="none">None</option></select></div>
                        <div class="flex justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700"><button type="button" data-smtp-modal-close class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button><button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-check2"></i> Save changes</button></div>
                    </form>
                </div>
            </div>
            <script>
            (() => {
                const modal = document.getElementById('smtpEditModal');
                const form = document.getElementById('smtpEditModalForm');
                if (!modal || !form) return;
                const panel = modal.querySelector('[data-smtp-modal-panel]');
                let closeTimer;

                const fields = {
                    name: document.getElementById('smtpModalName'),
                    from_name: document.getElementById('smtpModalFromName'),
                    from_email: document.getElementById('smtpModalFromEmail'),
                    smtp_host: document.getElementById('smtpModalHost'),
                    smtp_port: document.getElementById('smtpModalPort'),
                    smtp_username: document.getElementById('smtpModalUsername'),
                    smtp_password: document.getElementById('smtpModalPassword'),
                    smtp_encryption: document.getElementById('smtpModalEncryption'),
                };
                const close = () => {
                    window.clearTimeout(closeTimer);
                    modal.classList.add('opacity-0');
                    panel?.classList.add('translate-y-3', 'scale-95', 'opacity-0');
                    closeTimer = window.setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }, 200);
                    fields.smtp_password.value = '';
                };

                document.querySelectorAll('[data-smtp-edit]').forEach(button => button.addEventListener('click', () => {
                    let data;
                    try {
                        data = JSON.parse(button.dataset.service || '{}');
                    } catch (error) {
                        const decoder = document.createElement('textarea');
                        decoder.innerHTML = button.getAttribute('data-service') || '{}';
                        data = JSON.parse(decoder.value);
                    }
                    Object.entries(fields).forEach(([key, field]) => {
                        if (field && key !== 'smtp_password') field.value = data[key] ?? '';
                    });
                    fields.smtp_encryption.value = data.smtp_encryption || 'none';
                    form.action = `${modal.dataset.smtpUpdateBase}/${encodeURIComponent(data.id)}`;
                    window.clearTimeout(closeTimer);
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    requestAnimationFrame(() => {
                        modal.classList.remove('opacity-0');
                        panel?.classList.remove('translate-y-3', 'scale-95', 'opacity-0');
                    });
                    fields.name.focus();
                }));
                modal.querySelectorAll('[data-smtp-modal-close]').forEach(button => button.addEventListener('click', close));
                modal.querySelector('[data-smtp-modal-backdrop]')?.addEventListener('click', close);
                document.addEventListener('keydown', event => {
                    if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
                });
            })();
            </script>
        @else
            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)]">
                @if (! $signatureLimitReached)
                    <section class="{{ $card }} p-6">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-image"></i></span>
                        <div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Add signature</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Upload an image to include in email content and keep open tracking intact.</p></div>
                    </div>
                    <form class="mt-6 grid gap-4" action="{{ route('marketing.signatures.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div><label class="{{ $label }}" for="signatureName">Signature name</label><input id="signatureName" name="name" type="text" class="{{ $input }}" value="{{ old('name') }}" placeholder="Personal signature" required></div>
                        <div><label class="{{ $label }}" for="signatureImage">Signature image</label><input id="signatureImage" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $input }}" required><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">JPG, PNG, GIF, or WebP up to 5 MB.</p></div>
                        <button type="submit" class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-upload"></i> Save signature</button>
                    </form>
                    </section>
                @else
                    <section class="{{ $card }} flex min-h-64 flex-col items-center justify-center p-6 text-center">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><i class="bi bi-lock-fill text-lg"></i></span>
                        <h2 class="mt-4 text-lg font-semibold text-slate-950 dark:text-white">Signature limit reached</h2>
                        <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500 dark:text-slate-400">Your {{ $marketingPlanLabel }} plan includes {{ $signatureLimit }} {{ $signatureLimit === 1 ? 'signature' : 'signatures' }}. Upgrade to a premium membership to add more.</p>
                        <a href="{{ url('/marketing#pricing') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-stars"></i> View premium plans</a>
                    </section>
                @endif
                <section>
                    <div class="flex items-end justify-between gap-4"><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Your signatures</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose one while composing an email.</p></div><span class="text-sm text-slate-500 dark:text-slate-400">{{ $signatures->count() }} saved</span></div>
                    <div class="mt-4 grid gap-4">
                        @forelse ($signatures as $signature)
                            <article class="{{ $card }} flex flex-wrap items-center justify-between gap-4 p-5">
                                <div class="flex min-w-0 items-center gap-4"><div class="flex h-16 w-24 items-center justify-center overflow-hidden rounded-lg bg-slate-50 p-2 dark:bg-slate-950"><img src="{{ route('marketing.signatures.image', $signature) }}" alt="{{ $signature->name }}" class="max-h-full max-w-full object-contain"></div><div class="min-w-0"><h3 class="truncate font-semibold text-slate-950 dark:text-white">{{ $signature->name }}</h3><p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $signature->image_name }}</p></div></div>
                                <div class="flex items-center gap-2">
                                    <button type="button" data-signature-edit title="Edit signature" aria-label="Edit {{ $signature->name }}" data-signature="{{ json_encode(['id' => $signature->id, 'name' => $signature->name, 'image_url' => route('marketing.signatures.image', $signature)], JSON_UNESCAPED_SLASHES) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-slate-700 dark:text-slate-400 dark:hover:border-emerald-500 dark:hover:bg-emerald-600 dark:hover:text-white"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('marketing.signatures.delete', $signature) }}" method="POST" onsubmit="return confirm('Remove this signature?');">@csrf @method('DELETE')<button type="submit" title="Remove signature" aria-label="Remove {{ $signature->name }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-red-300 hover:bg-red-50 hover:text-red-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-red-500/40 dark:hover:bg-red-500/10 dark:hover:text-red-400"><i class="bi bi-trash3"></i></button></form>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-slate-300 px-6 py-10 text-center dark:border-slate-700"><i class="bi bi-image text-3xl text-slate-300 dark:text-slate-600"></i><h3 class="mt-3 font-medium text-slate-900 dark:text-slate-100">No signatures yet</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add a signature image to make it available in Send new email.</p></div>
                        @endforelse
                    </div>
                </section>
            </div>
            <div id="signatureEditModal" data-signature-update-base="{{ url('marketing/settings/signatures') }}" class="fixed inset-0 z-[80] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200 ease-out" role="dialog" aria-modal="true" aria-labelledby="signatureEditModalTitle">
                <div data-signature-modal-backdrop class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm"></div>
                <div data-signature-modal-panel class="relative z-10 w-full max-w-lg translate-y-3 scale-95 rounded-xl border border-slate-200 bg-white opacity-0 shadow-2xl transition duration-200 ease-out dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-700"><div><p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Email signature</p><h2 id="signatureEditModalTitle" class="mt-1 text-xl font-semibold text-slate-950 dark:text-white">Edit signature</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update the name or replace the signature image.</p></div><button type="button" data-signature-modal-close class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close edit signature"><i class="bi bi-x-lg"></i></button></div>
                    <form id="signatureEditModalForm" class="grid gap-4 p-6" action="#" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center gap-4 rounded-lg bg-slate-50 p-3 dark:bg-slate-950"><img id="signatureModalPreview" src="" alt="Current signature" class="h-14 w-28 rounded-lg object-contain"><span class="text-xs text-slate-500 dark:text-slate-400">Current image</span></div>
                        <div><label class="{{ $label }}" for="signatureModalName">Signature name</label><input id="signatureModalName" name="name" type="text" class="{{ $input }}" required></div>
                        <div><label class="{{ $label }}" for="signatureModalImage">Replace image</label><input id="signatureModalImage" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $input }}"><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Leave blank to keep the current image. JPG, PNG, GIF, or WebP up to 5 MB.</p></div>
                        <div class="flex justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700"><button type="button" data-signature-modal-close class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</button><button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-check2"></i> Save changes</button></div>
                    </form>
                </div>
            </div>
            <script>
            (() => {
                const modal = document.getElementById('signatureEditModal');
                const form = document.getElementById('signatureEditModalForm');
                if (!modal || !form) return;
                const panel = modal.querySelector('[data-signature-modal-panel]');
                const name = document.getElementById('signatureModalName');
                const image = document.getElementById('signatureModalImage');
                const preview = document.getElementById('signatureModalPreview');
                let closeTimer;
                const close = () => {
                    window.clearTimeout(closeTimer);
                    modal.classList.add('opacity-0');
                    panel?.classList.add('translate-y-3', 'scale-95', 'opacity-0');
                    closeTimer = window.setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }, 200);
                    image.value = '';
                };
                document.querySelectorAll('[data-signature-edit]').forEach(button => button.addEventListener('click', () => {
                    const data = JSON.parse(button.dataset.signature || '{}');
                    name.value = data.name || '';
                    preview.src = data.image_url || '';
                    form.action = `${modal.dataset.signatureUpdateBase}/${encodeURIComponent(data.id)}`;
                    window.clearTimeout(closeTimer);
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    requestAnimationFrame(() => {
                        modal.classList.remove('opacity-0');
                        panel?.classList.remove('translate-y-3', 'scale-95', 'opacity-0');
                    });
                    name.focus();
                }));
                modal.querySelectorAll('[data-signature-modal-close]').forEach(button => button.addEventListener('click', close));
                modal.querySelector('[data-signature-modal-backdrop]')?.addEventListener('click', close);
                document.addEventListener('keydown', event => {
                    if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
                });
            })();
            </script>
        @endif
    @elseif ($settingsTab === 'billing')
        @php
            $billingAccount = auth()->user()?->account;
            $billingStatus = $billingAccount?->paddle_subscription_status ?: 'free';
            $nextBilledAt = $billingAccount?->paddle_next_billed_at;
            $scheduledAction = $billingAccount?->paddle_scheduled_change_action;
        @endphp
        <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
            <section class="{{ $card }} p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi bi-credit-card"></i></span>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Your plan</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your MyLeadTrail subscription and workspace limits.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ $marketingPlan['label'] ?? 'Starter' }}{{ $billingStatus !== 'free' ? ' · '.str_replace('_', ' ', $billingStatus) : '' }}</span>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950"><span class="block text-xs text-slate-500 dark:text-slate-400">Current plan</span><strong class="mt-1 block text-slate-950 dark:text-white">{{ $marketingPlan['label'] ?? 'Starter' }}</strong></div>
                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950"><span class="block text-xs text-slate-500 dark:text-slate-400">Subscription status</span><strong class="mt-1 block capitalize text-slate-950 dark:text-white">{{ str_replace('_', ' ', $billingStatus) }}</strong></div>
                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950"><span class="block text-xs text-slate-500 dark:text-slate-400">Next billing</span><strong class="mt-1 block text-slate-950 dark:text-white">{{ $nextBilledAt?->format('M j, Y') ?: '—' }}</strong></div>
                </div>
                @if ($scheduledAction === 'cancel')
                    <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">Your subscription is scheduled to cancel{{ $billingAccount?->paddle_scheduled_change_effective_at ? ' on '.$billingAccount->paddle_scheduled_change_effective_at->format('M j, Y') : '' }}.</p>
                @endif
            </section>
            <section class="{{ $card }} p-6">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Upgrade your workspace</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">Checkout is handled securely by Paddle. Your plan changes after the payment webhook is confirmed.</p>
                <div class="mt-5 grid gap-3">
                    <button type="button" data-paddle-plan="growth" class="inline-flex items-center justify-between gap-3 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-emerald-700"><span><i class="bi bi-stars mr-2"></i>Growth · $12/month</span><i class="bi bi-arrow-right"></i></button>
                    <button type="button" data-paddle-plan="scale" class="inline-flex items-center justify-between gap-3 rounded-lg border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10"><span>Scale · $49/month</span><i class="bi bi-arrow-right"></i></button>
                </div>
                @if (! config('marketing.paddle.client_token'))
                    <p class="mt-4 text-xs text-amber-700 dark:text-amber-300">Paddle checkout is not configured yet. Add the client token to the environment before accepting payments.</p>
                @endif
            </section>
        </div>
        @php
            $billingSuccessUrl = route('marketing', ['tab' => 'settings', 'settings_tab' => 'billing', 'billing' => 'success']);
        @endphp
        <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
        <script>
            (() => {
                const token = @json(config('marketing.paddle.client_token'));
                const prices = @json(config('marketing.paddle.prices'));
                if (!token || !window.Paddle) return;
                if (@json(config('marketing.paddle.environment', 'sandbox')) === 'sandbox') {
                    Paddle.Environment.set('sandbox');
                }
                Paddle.Initialize({ token });
                document.querySelectorAll('[data-paddle-plan]').forEach((button) => button.addEventListener('click', () => {
                    const plan = button.dataset.paddlePlan;
                    const priceId = prices[plan];
                    if (!priceId) return;
                    Paddle.Checkout.open({
                        items: [{ priceId, quantity: 1 }],
                        customer: { email: @json(auth()->user()?->email) },
                        customData: { user_id: @json((string) auth()->id()), marketing_account_id: @json((string) (auth()->user()?->marketing_account_id ?? '')), plan },
                        settings: { displayMode: 'overlay', variant: 'one-page', successUrl: @json($billingSuccessUrl) },
                    });
                }));
            })();
        </script>
    @elseif ($settingsTab === 'account')
        @php
            $accountUser = auth()->user();
        @endphp
        <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <section class="{{ $card }} p-6 xl:col-span-2">
                <div class="flex flex-wrap items-center gap-5">
                    @if ($accountUser->profile_photo_path)
                        <img src="{{ route('marketing.account.profile-image', $accountUser) }}" alt="{{ $accountUser->name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-emerald-50 dark:ring-emerald-500/10">
                    @else
                        <span class="inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-emerald-100 text-2xl font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ strtoupper(substr($accountUser->name, 0, 1)) }}</span>
                    @endif
                    <div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Account Settings</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update your personal profile, password, and dashboard appearance.</p><span class="mt-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ str_replace('_', ' ', $accountUser->role) }}</span></div>
                </div>
            </section>

            <section class="{{ $card }} p-6">
                <div class="flex items-start gap-3"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-person-vcard"></i></span><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Profile</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your name, email, and profile picture.</p></div></div>
                <form class="mt-6 grid gap-4" action="{{ route('marketing.account.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div><label class="{{ $label }}" for="accountName">Name</label><input id="accountName" name="name" type="text" class="{{ $input }}" value="{{ old('name', $accountUser->name) }}" required></div>
                    <div><label class="{{ $label }}" for="accountEmail">Email address</label><input id="accountEmail" name="email" type="email" class="{{ $input }}" value="{{ old('email', $accountUser->email) }}" required></div>
                    <div><label class="{{ $label }}" for="profilePhoto">Profile picture</label><input id="profilePhoto" name="profile_photo" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="{{ $input }}"><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">JPG, PNG, GIF, or WebP up to 5 MB.</p></div>
                    <button type="submit" class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-check2"></i> Save profile</button>
                </form>
            </section>

            <section class="{{ $card }} p-6">
                <div class="flex items-start gap-3"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-lock"></i></span><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Update password</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Use at least 8 characters for your new password.</p></div></div>
                <form class="mt-6 grid gap-4" action="{{ route('marketing.account.password.update') }}" method="POST">
                    @csrf
                    <div><label class="{{ $label }}" for="currentPassword">Current password</label><input id="currentPassword" name="current_password" type="password" class="{{ $input }}" autocomplete="current-password" required></div>
                    <div><label class="{{ $label }}" for="newPassword">New password</label><input id="newPassword" name="password" type="password" class="{{ $input }}" autocomplete="new-password" required></div>
                    <div><label class="{{ $label }}" for="newPasswordConfirmation">Confirm new password</label><input id="newPasswordConfirmation" name="password_confirmation" type="password" class="{{ $input }}" autocomplete="new-password" required></div>
                    <button type="submit" class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"><i class="bi bi-key"></i> Update password</button>
                </form>
            </section>

            <section class="{{ $card }} p-6 xl:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-4"><div class="flex items-start gap-3"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300"><i class="bi bi-palette"></i></span><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Theme</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose the appearance for your entire MyLeadTrail workspace.</p></div></div><form action="{{ route('marketing.account.theme.update') }}" method="POST" class="flex items-center gap-3">@csrf<label class="text-sm font-medium text-slate-700 dark:text-slate-200" for="accountTheme">Appearance</label><select id="accountTheme" name="theme" class="{{ $input }} min-w-32" onchange="this.form.submit()"><option value="light" @selected(($accountUser->theme ?: 'light') === 'light')>Light</option><option value="dark" @selected($accountUser->theme === 'dark')>Dark</option></select></form></div>
            </section>
        </div>
    @elseif ($settingsTab === 'team')
        @if (auth()->user()?->canManageTeam())
            @php
                $moderatorLimit = $marketingPlanLimits['moderators'] ?? null;
                $moderatorCount = $teamMembers->where('role', 'moderator')->count();
            @endphp
            <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
                <section class="{{ $card }} p-6">
                    <div class="flex items-start gap-3"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi bi-person-plus"></i></span><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Add moderator</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Moderators can work inside this account but cannot manage its team.</p></div></div>
                    <form class="mt-6 grid gap-4" action="{{ route('marketing.moderators.store') }}" method="POST">
                        @csrf
                        <div><label class="{{ $label }}" for="moderatorName">Name</label><input id="moderatorName" name="name" type="text" class="{{ $input }}" value="{{ old('name') }}" required></div>
                        <div><label class="{{ $label }}" for="moderatorEmail">Email</label><input id="moderatorEmail" name="email" type="email" class="{{ $input }}" value="{{ old('email') }}" required></div>
                        <div><label class="{{ $label }}" for="moderatorPassword">Temporary password</label><input id="moderatorPassword" name="password" type="password" class="{{ $input }}" autocomplete="new-password" required></div>
                        <div><label class="{{ $label }}" for="moderatorPasswordConfirmation">Confirm password</label><input id="moderatorPasswordConfirmation" name="password_confirmation" type="password" class="{{ $input }}" autocomplete="new-password" required></div>
                        <button type="submit" class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"><i class="bi bi-person-plus"></i> Add moderator</button>
                    </form>
                </section>
                <section>
                    <div class="flex items-end justify-between gap-4"><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Account members</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Everyone listed here belongs to this workspace only.</p></div><span class="text-sm text-slate-500 dark:text-slate-400">{{ $teamMembers->count() }} member{{ $teamMembers->count() === 1 ? '' : 's' }}</span></div>
                    <div class="mt-4 grid gap-3">
                        @foreach ($teamMembers as $member)
                            <article class="{{ $card }} flex flex-wrap items-center justify-between gap-4 p-4"><div class="flex min-w-0 items-center gap-3"><span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="bi {{ $member->role === 'moderator' ? 'bi-person' : 'bi-shield-check' }}"></i></span><div class="min-w-0"><h3 class="truncate font-medium text-slate-950 dark:text-white">{{ $member->name }}</h3><p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $member->email }}</p></div></div><div class="flex items-center gap-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ str_replace('_', ' ', $member->role) }}</span>@if ($member->role === 'moderator')<form action="{{ route('marketing.moderators.delete', $member) }}" method="POST" onsubmit="return confirm('Remove this moderator?');">@csrf @method('DELETE')<button type="submit" class="text-sm text-slate-500 hover:text-red-600 dark:text-slate-400 dark:hover:text-red-400">Remove</button></form>@endif</div></article>
                        @endforeach
                    </div>
                </section>
            </div>
        @else
            <div class="mt-7 rounded-lg border border-dashed border-slate-300 p-8 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">Only admins can manage account members.</div>
        @endif
    @else
        <div class="mt-7 max-w-2xl rounded-lg border border-dashed border-slate-300 p-8 dark:border-slate-700">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><i class="bi {{ $settingsTab === 'profile' ? 'bi-person' : 'bi-bell' }}"></i></span>
            <h2 class="mt-4 text-lg font-semibold text-slate-950 dark:text-white">{{ $settingsTab === 'profile' ? 'Profile settings' : 'Notification settings' }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">This section is ready for the next workspace settings as the SaaS grows. Connected websites and analytics selection are available now.</p>
            <a class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-emerald-700 hover:underline dark:text-emerald-300" href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'websites']) }}">Back to connected websites <i class="bi bi-arrow-right"></i></a>
        </div>
    @endif
</div>
