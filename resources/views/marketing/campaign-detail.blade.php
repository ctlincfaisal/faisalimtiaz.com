@php
    $campaignStats = $campaignStats ?? ['opened' => 0, 'sent' => 0, 'remaining' => 0, 'failed' => 0, 'open_rate' => 0, 'records' => 0, 'first_failure' => null];
    $campaignEmailRecords = $campaignEmailRecords ?? collect();
@endphp

@if (! $selectedCampaign)
    <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700"><i class="bi bi-exclamation-circle text-3xl text-slate-400"></i><h1 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">Campaign not found</h1><p class="mt-2 text-sm text-slate-500 dark:text-slate-400">This campaign may have been removed or is no longer available.</p><a href="{{ route('marketing', ['tab' => 'campaigns-list']) }}" class="mt-5 inline-flex rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white">Back to all campaigns</a></div>
@else
    @php
        $campaignEmailModalData = $campaignEmailRecords->mapWithKeys(function ($email) use ($selectedCampaign) {
            $openedRecords = $email->opens->whereNotNull('opened_at')->sortBy('opened_at')->values();
            $sentAt = $email->sent_at ?: $email->created_at;
            $deliveryStatus = $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'pending');
            $emailIsHtml = $email->content_format === 'html' || strip_tags((string) $email->body) !== (string) $email->body;
            $templateName = $email->template_name ?: ($email->campaignStep?->template?->name ?: 'Custom email');

            return [$email->id => [
                'subject' => (string) $email->subject,
                'recipient_count' => (int) ($email->recipient_count ?: count($email->recipients ?: [])),
                'delivery_status' => $deliveryStatus,
                'sent_at' => $sentAt?->format('M d, Y h:i A'),
                'opened_count' => $openedRecords->count(),
                'first_opened_at' => $openedRecords->first()?->opened_at?->format('M d, Y h:i A'),
                'campaign_name' => $selectedCampaign->name,
                'step' => $email->campaignStep?->step_order,
                'template_name' => $templateName,
                'body_html' => \App\Support\MarketingHtml::render((string) $email->body, $emailIsHtml),
                'format' => $emailIsHtml ? 'HTML email' : 'Plain text',
                'from_email' => $email->mailService?->from_email ?: 'Not recorded',
                'mail_service' => $email->mailService?->name ?: 'Default mail settings',
                'signature' => $email->signature?->name ?: 'No signature recorded',
                'attachment' => $email->attachment_name ?: 'None',
                'delivery_error' => $email->delivery_error ?: 'No failure reason was recorded.',
                'retry_url' => route('marketing.campaigns.email.retry', $email->id),
                'recipients' => collect($email->recipients ?: [])->map(function ($recipient) use ($email) {
                    $open = $email->opens->first(fn ($item) => strtolower(trim((string) $item->email)) === strtolower(trim((string) $recipient)));

                    return [
                        'email' => $recipient,
                        'status' => $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'pending'),
                        'opened_at' => $open?->opened_at?->format('M d, Y h:i A'),
                        'open_count' => $open?->open_count ?: 0,
                    ];
                })->values()->all(),
            ]];
        })->all();
        $statusClass = match ($selectedCampaign->status) {
            'running' => 'bg-lime-100 text-lime-700 dark:bg-lime-500/15 dark:text-lime-300',
            'queued' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
            'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
            'failed' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
            default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        };
        $failedCount = (int) ($campaignStats['failed'] ?? 0);
        $progressTotal = max(1, (int) $campaignStats['sent'] + (int) $campaignStats['failed'] + (int) $campaignStats['remaining']);
        $progressPercent = min(100, round((((int) $campaignStats['sent'] + $failedCount) / $progressTotal) * 100));
    @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between"><div><div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><span>Campaigns</span><span>/</span><span class="font-medium text-violet-500">Campaign details</span></div><div class="flex flex-wrap items-center gap-3"><h1 class="text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $selectedCampaign->name }}</h1><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($selectedCampaign->status) }}</span></div><p class="mt-2 text-slate-500 dark:text-slate-400">{{ $selectedCampaign->description ?: 'Campaign delivery and open activity.' }}</p></div><div class="flex gap-3"><a href="{{ route('marketing', ['tab' => 'campaigns-list']) }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 px-4 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200"><i class="bi bi-arrow-left"></i> All campaigns</a></div></div>

        <div id="campaignFailureAlert" class="{{ $failedCount > 0 ? '' : 'hidden ' }}flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
            <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
            <div class="min-w-0 flex-1"><strong id="campaignFailureMessage" class="font-semibold">{{ number_format($failedCount) }} email{{ $failedCount === 1 ? '' : 's' }} failed to send.</strong> Use the Retry button in a failed delivery record below, or click the record to view its reason and retry it.<span id="campaignLatestFailure" class="mt-1 block text-xs {{ empty($campaignStats['first_failure']) ? 'hidden' : '' }}">Latest error: {{ $campaignStats['first_failure'] }}</span></div>
            <form method="POST" action="{{ route('marketing.campaigns.retry-failed', $selectedCampaign->id) }}" id="campaignRetryAllForm" class="shrink-0 {{ $failedCount > 0 ? '' : 'hidden' }}">@csrf<button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40"><i class="bi bi-arrow-repeat"></i> Retry all failed</button></form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"><div class="{{ $card }} p-5"><span class="{{ $muted }}">Opened</span><strong data-progress-stat="opened" class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($campaignStats['opened']) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Unique recipients</span></div><div class="{{ $card }} p-5"><span class="{{ $muted }}">Sent</span><strong data-progress-stat="sent" class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($campaignStats['sent']) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Successfully delivered</span></div><div class="{{ $card }} p-5"><span class="{{ $muted }}">Failed</span><strong data-progress-stat="failed" class="mt-2 block text-3xl font-semibold text-red-600 dark:text-red-300">{{ number_format($failedCount) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Click a record to retry</span></div><div class="{{ $card }} p-5"><span class="{{ $muted }}">Remaining to send</span><strong data-progress-stat="remaining" class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($campaignStats['remaining']) }}</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Queued campaign jobs</span></div><div class="{{ $card }} p-5"><span class="{{ $muted }}">Opening percentage</span><strong data-progress-stat="open_rate" class="mt-2 block text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($campaignStats['open_rate'], 1) }}%</strong><span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Opened / sent</span></div></div>

        <section id="campaignProgress" data-progress-url="{{ route('marketing.campaigns.progress', $selectedCampaign->id) }}" data-campaign-status="{{ $selectedCampaign->status }}" class="{{ $card }} p-6"><div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Campaign progress</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><span data-progress-records>{{ number_format($campaignStats['records']) }}</span> delivery record{{ $campaignStats['records'] === 1 ? '' : 's' }} recorded across {{ $selectedCampaign->sequence_count }} step{{ $selectedCampaign->sequence_count === 1 ? '' : 's' }}.</p></div><span data-progress-percent class="text-sm font-semibold text-violet-600 dark:text-violet-300">{{ $progressPercent }}%</span></div><div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"><div data-progress-bar class="h-full rounded-full bg-violet-600 transition-all duration-500" style="width: {{ $progressPercent }}%"></div></div><div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500 dark:text-slate-400"><span>Audience: <span data-progress-audience>{{ number_format($selectedCampaign->recipient_count) }}</span> recipients</span><span>Current step: <span data-progress-step>{{ $selectedCampaign->current_step ? 'Step '.$selectedCampaign->current_step : 'Not started' }}</span></span><span>Opened: <span data-progress-opened>{{ number_format($campaignStats['opened']) }}</span></span><span>Failed: <span data-progress-failed>{{ number_format($failedCount) }}</span></span><span>Pending: <span data-progress-remaining>{{ number_format($campaignStats['remaining']) }}</span></span><span>Started: {{ optional($selectedCampaign->started_at)->format('M d, Y h:i A') ?: 'Not started' }}</span></div></section>

        <section id="campaignDeliveryRecords" class="{{ $card }} overflow-hidden"><div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"><h2 class="text-lg font-semibold text-slate-950 dark:text-white">All delivery records</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Click any recipient to view the full email, current step, open time, delivery status, and failure details.</p></div><div class="overflow-x-auto"><table class="w-full min-w-[1020px] text-left text-sm"><thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"><tr><th class="px-6 py-3">Recipient</th><th class="px-3 py-3">Step</th><th class="px-3 py-3">Subject</th><th class="px-3 py-3">Delivery</th><th class="px-3 py-3">Opened</th><th class="px-3 py-3">Sent at</th><th class="px-6 py-3 text-right">Action</th></tr></thead><tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($campaignEmailRecords as $record)
                        @php
                            $recipient = collect($record->recipients ?: [])->first() ?: 'Unknown recipient';
                            $opened = $record->opens->whereNotNull('opened_at')->sortBy('opened_at');
                            $firstOpened = $opened->first();
                            $openedAt = $firstOpened?->opened_at?->format('M d, Y h:i A');
                            $deliveryStatus = $record->delivery_status ?: 'pending';
                            $deliveryClass = $deliveryStatus === 'delivered' ? 'bg-lime-100 text-lime-700 dark:bg-lime-500/15 dark:text-lime-300' : ($deliveryStatus === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' : 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300');
                            $recordPayload = $campaignEmailModalData[$record->id] ?? [];
                        @endphp
                        <tr data-campaign-email-row data-campaign-email-id="{{ $record->id }}" tabindex="0" role="button" aria-label="View email details for {{ $recipient }}" class="cursor-pointer transition hover:bg-violet-50/50 focus:bg-violet-50/70 focus:outline-none dark:hover:bg-violet-500/5 dark:focus:bg-violet-500/10"><td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">{{ $recipient }}</td><td class="px-3 py-4 text-slate-600 dark:text-slate-300">{{ $recordPayload['step'] ? 'Step '.$recordPayload['step'] : 'Email' }}</td><td class="max-w-xs truncate px-3 py-4 text-slate-600 dark:text-slate-300">{{ $record->subject }}</td><td class="px-3 py-4"><span data-campaign-email-delivery class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $deliveryClass }}">{{ ucfirst($deliveryStatus) }}</span></td><td class="px-3 py-4"><span data-campaign-email-opened class="{{ $opened->isNotEmpty() ? 'font-medium text-lime-700 dark:text-lime-300' : 'text-slate-500 dark:text-slate-400' }}">{{ $opened->isNotEmpty() ? 'Yes' : 'Not opened' }}</span><span data-campaign-email-open-count class="mt-1 block text-xs text-slate-500 dark:text-slate-400 {{ $opened->isNotEmpty() ? '' : 'hidden' }}">{{ $opened->count() }} open{{ $opened->count() === 1 ? '' : 's' }}</span></td><td data-campaign-email-sent-at class="whitespace-nowrap px-3 py-4 text-slate-500 dark:text-slate-400">{{ optional($record->sent_at)->format('M d, Y h:i A') ?: 'Pending' }}</td><td data-campaign-email-action class="px-6 py-4 text-right">@if ($deliveryStatus === 'failed')<form method="POST" action="{{ route('marketing.campaigns.email.retry', $record->id) }}" data-campaign-email-retry-form class="inline-flex">@csrf<button type="submit" data-campaign-email-retry class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40"><i class="bi bi-arrow-repeat"></i> Retry</button></form>@else<span class="text-xs text-slate-400 dark:text-slate-500">—</span>@endif</td></tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-14 text-center text-slate-500 dark:text-slate-400">No delivery records have been created yet. The campaign jobs will appear here as the queue processes them.</td></tr>
                    @endforelse
                </tbody></table></div></section>
    </div>

    <style>
        #campaignEmailModal { opacity: 0; transition: opacity .18s ease; }
        #campaignEmailModal.is-open { opacity: 1; }
        #campaignEmailModal [data-campaign-email-modal-panel] { opacity: 0; transform: translateY(12px) scale(.985); transition: opacity .18s ease, transform .18s ease; }
        #campaignEmailModal.is-open [data-campaign-email-modal-panel] { opacity: 1; transform: translateY(0) scale(1); }
    </style>
    <div id="campaignEmailModal" class="fixed inset-0 z-[80] hidden" aria-hidden="true">
        <button type="button" data-campaign-email-backdrop class="absolute inset-0 bg-slate-950/45 backdrop-blur-[2px]" aria-label="Close email details"></button>
        <div class="relative flex h-full items-center justify-center overflow-y-auto p-4 md:p-8">
            <div data-campaign-email-modal-panel role="dialog" aria-modal="true" aria-labelledby="campaignEmailModalTitle" class="relative my-auto w-full max-w-6xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 md:px-7 dark:border-slate-700">
                    <div class="min-w-0"><span class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Campaign email details</span><h2 id="campaignEmailModalTitle" class="mt-1 truncate text-xl font-semibold text-slate-900 dark:text-white">Email details</h2><p id="campaignEmailModalRecipient" class="mt-1 text-sm text-slate-500 dark:text-slate-400"></p></div>
                    <button type="button" data-campaign-email-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300" aria-label="Close email details"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="max-h-[calc(100vh-10rem)] overflow-y-auto p-5 md:p-7">
                    <div class="flex flex-wrap items-center justify-between gap-3"><span id="campaignEmailStatus" class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold"></span><form id="campaignEmailRetryForm" method="POST" class="hidden">@csrf<button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"><i class="bi bi-arrow-repeat"></i> Retry email</button></form></div>
                    <div id="campaignEmailFailure" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"><div class="flex items-start gap-3"><i class="bi bi-exclamation-triangle mt-0.5"></i><div><strong>Delivery failed</strong><p id="campaignEmailFailureReason" class="mt-1 whitespace-pre-wrap break-words"></p></div></div></div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4"><div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><span class="text-xs text-slate-500 dark:text-slate-400">Sent at</span><strong id="campaignEmailSentAt" class="mt-2 block text-sm font-semibold text-slate-900 dark:text-white"></strong></div><div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><span class="text-xs text-slate-500 dark:text-slate-400">Open activity</span><strong id="campaignEmailOpened" class="mt-2 block text-sm font-semibold text-slate-900 dark:text-white"></strong></div><div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><span class="text-xs text-slate-500 dark:text-slate-400">Campaign / step</span><strong id="campaignEmailCampaign" class="mt-2 block truncate text-sm font-semibold text-slate-900 dark:text-white"></strong></div><div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700"><span class="text-xs text-slate-500 dark:text-slate-400">Template</span><strong id="campaignEmailTemplate" class="mt-2 block truncate text-sm font-semibold text-slate-900 dark:text-white"></strong></div></div>
                    <div class="mt-5 grid items-start gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(280px,.65fr)]"><section class="rounded-lg border border-slate-200 p-5 dark:border-slate-700"><div class="flex items-center justify-between gap-3"><h3 class="text-lg font-semibold text-slate-900 dark:text-white">Email content</h3><span id="campaignEmailFormat" class="text-xs font-semibold text-slate-500 dark:text-slate-400"></span></div><div class="mt-4"><span class="text-xs text-slate-500 dark:text-slate-400">Subject</span><p id="campaignEmailSubject" class="mt-1 font-semibold text-slate-900 dark:text-white"></p></div><div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-700"><span class="text-xs text-slate-500 dark:text-slate-400">Message</span><div id="campaignEmailBody" class="marketing-email-detail-body mt-3 max-h-96 overflow-auto rounded-lg border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></div></div></section><aside class="rounded-lg border border-slate-200 p-5 dark:border-slate-700"><h3 class="text-lg font-semibold text-slate-900 dark:text-white">Sending details</h3><dl class="mt-4 divide-y divide-slate-200 text-sm dark:divide-slate-700"><div class="flex items-start justify-between gap-3 py-3 first:pt-0"><dt class="text-slate-500 dark:text-slate-400">From email</dt><dd id="campaignEmailFrom" class="text-right font-medium text-slate-900 dark:text-white"></dd></div><div class="flex items-start justify-between gap-3 py-3"><dt class="text-slate-500 dark:text-slate-400">SMTP service</dt><dd id="campaignEmailService" class="text-right font-medium text-slate-900 dark:text-white"></dd></div><div class="flex items-start justify-between gap-3 py-3"><dt class="text-slate-500 dark:text-slate-400">Signature</dt><dd id="campaignEmailSignature" class="text-right font-medium text-slate-900 dark:text-white"></dd></div><div class="flex items-start justify-between gap-3 py-3 last:pb-0"><dt class="text-slate-500 dark:text-slate-400">Attachment</dt><dd id="campaignEmailAttachment" class="text-right font-medium text-slate-900 dark:text-white"></dd></div></dl></aside></div>
                    <section class="mt-5 rounded-lg border border-slate-200 p-5 dark:border-slate-700"><div class="flex items-center justify-between gap-3"><div><h3 class="text-lg font-semibold text-slate-900 dark:text-white">Recipients</h3><p id="campaignEmailRecipientCount" class="mt-1 text-sm text-slate-500 dark:text-slate-400"></p></div></div><div id="campaignEmailRecipients" class="mt-4 grid gap-2"></div></section>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
(() => {
    const modal = document.getElementById('campaignEmailModal');
    if (!modal) return;

    const data = @json($campaignEmailModalData);
    window.__campaignEmailModalData = data;
    const panel = modal.querySelector('[data-campaign-email-modal-panel]');
    const retryForm = modal.querySelector('#campaignEmailRetryForm');
    const elements = {
        title: modal.querySelector('#campaignEmailModalTitle'),
        subtitle: modal.querySelector('#campaignEmailModalRecipient'),
        status: modal.querySelector('#campaignEmailStatus'),
        failure: modal.querySelector('#campaignEmailFailure'),
        failureReason: modal.querySelector('#campaignEmailFailureReason'),
        sentAt: modal.querySelector('#campaignEmailSentAt'),
        opened: modal.querySelector('#campaignEmailOpened'),
        campaign: modal.querySelector('#campaignEmailCampaign'),
        template: modal.querySelector('#campaignEmailTemplate'),
        subject: modal.querySelector('#campaignEmailSubject'),
        format: modal.querySelector('#campaignEmailFormat'),
        body: modal.querySelector('#campaignEmailBody'),
        from: modal.querySelector('#campaignEmailFrom'),
        service: modal.querySelector('#campaignEmailService'),
        signature: modal.querySelector('#campaignEmailSignature'),
        attachment: modal.querySelector('#campaignEmailAttachment'),
        recipientCount: modal.querySelector('#campaignEmailRecipientCount'),
        recipients: modal.querySelector('#campaignEmailRecipients'),
    };
    const statusClasses = {
        delivered: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        pending: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        failed: 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
    };
    let activeId = null;
    let modalPoll = null;
    let previousOverflow = '';
    const setText = (element, value) => { if (element) element.textContent = value || '—'; };
    const closeModal = () => {
        if (modalPoll) { window.clearInterval(modalPoll); modalPoll = null; }
        modal.classList.remove('is-open');
        document.body.style.overflow = previousOverflow;
        window.setTimeout(() => { modal.classList.add('hidden'); modal.setAttribute('aria-hidden', 'true'); }, 180);
    };
    const renderRecipients = email => {
        setText(elements.recipientCount, `${email.recipients.length} recipient${email.recipients.length === 1 ? '' : 's'}`);
        elements.recipients.innerHTML = '';
        email.recipients.forEach(recipient => {
            const row = document.createElement('div');
            row.className = 'flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm dark:border-slate-700';
            row.dataset.campaignModalRecipient = String(recipient.email || '').trim().toLowerCase();
            const address = document.createElement('strong');
            address.className = 'font-medium text-slate-900 dark:text-white';
            address.textContent = recipient.email;
            const meta = document.createElement('span');
            meta.className = 'text-xs text-slate-500 dark:text-slate-400';
            meta.dataset.campaignModalRecipientMeta = 'true';
            meta.textContent = `${recipient.status === 'delivered' ? 'Delivered' : recipient.status === 'pending' ? 'Pending' : 'Not delivered'} · ${recipient.opened_at ? `Opened ${recipient.opened_at} · ${recipient.open_count} open${recipient.open_count === 1 ? '' : 's'}` : 'Not opened'}`;
            const status = document.createElement('span');
            status.className = recipient.opened_at ? 'text-xs font-semibold text-emerald-700 dark:text-emerald-300' : 'text-xs font-semibold text-slate-500 dark:text-slate-400';
            status.dataset.campaignModalRecipientStatus = 'true';
            status.textContent = recipient.opened_at ? 'Opened' : 'Not opened';
            row.append(address, meta, status);
            elements.recipients.appendChild(row);
        });
    };
    const refreshOpenActivity = id => fetch('{{ route('marketing.progress', ['email' => '__ID__']) }}'.replace('__ID__', id), { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
        .then(response => response.ok ? response.json() : null)
        .then(progress => {
            if (!progress || !data[id]) return;
            const email = data[id];
            email.opened_count = progress.opened_count || 0;
            email.first_opened_at = progress.first_opened_at || null;
            setText(elements.opened, `${email.opened_count} opened${email.first_opened_at ? ` · First: ${email.first_opened_at}` : ' · Not opened'}`);
            (progress.recipients || []).forEach(recipient => {
                const key = String(recipient.email || '').trim().toLowerCase();
                const row = Array.from(elements.recipients.querySelectorAll('[data-campaign-modal-recipient]')).find(item => item.dataset.campaignModalRecipient === key);
                if (!row) return;
                const meta = row.querySelector('[data-campaign-modal-recipient-meta]');
                const status = row.querySelector('[data-campaign-modal-recipient-status]');
                if (recipient.opened_at) {
                    if (meta) meta.textContent = `Delivered · Opened ${recipient.opened_at} · ${recipient.open_count} open${recipient.open_count === 1 ? '' : 's'}`;
                    if (status) { status.textContent = 'Opened'; status.className = 'text-xs font-semibold text-emerald-700 dark:text-emerald-300'; }
                }
            });
        })
        .catch(() => {});
    const openModal = id => {
        const email = data[id];
        if (!email) return;
        activeId = id;
        setText(elements.title, email.subject || 'Email details');
        setText(elements.subtitle, `${email.recipient_count} recipient${email.recipient_count === 1 ? '' : 's'} · ${email.campaign_name}${email.step ? ` · Step ${email.step}` : ''}`);
        const deliveryStatus = email.delivery_status || 'pending';
        elements.status.className = `inline-flex rounded-full px-3 py-1.5 text-xs font-semibold ${statusClasses[deliveryStatus] || statusClasses.pending}`;
        setText(elements.status, deliveryStatus.charAt(0).toUpperCase() + deliveryStatus.slice(1));
        elements.failure.classList.toggle('hidden', deliveryStatus !== 'failed');
        setText(elements.failureReason, email.delivery_error);
        retryForm.classList.toggle('hidden', deliveryStatus !== 'failed');
        retryForm.action = email.retry_url || '#';
        setText(elements.sentAt, email.sent_at || 'Not sent');
        setText(elements.opened, `${email.opened_count || 0} opened${email.first_opened_at ? ` · First: ${email.first_opened_at}` : ' · Not opened'}`);
        setText(elements.campaign, `${email.campaign_name}${email.step ? ` · Step ${email.step}` : ''}`);
        setText(elements.template, email.template_name);
        setText(elements.subject, email.subject);
        setText(elements.format, email.format);
        elements.body.innerHTML = email.body_html || '<p>No email body recorded.</p>';
        setText(elements.from, email.from_email);
        setText(elements.service, email.mail_service);
        setText(elements.signature, email.signature);
        setText(elements.attachment, email.attachment);
        renderRecipients(email);
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => modal.classList.add('is-open'));
        modal.querySelector('[data-campaign-email-close]')?.focus();
        refreshOpenActivity(id);
        if (modalPoll) window.clearInterval(modalPoll);
        modalPoll = window.setInterval(() => refreshOpenActivity(activeId), 5000);
    };

    document.querySelectorAll('[data-campaign-email-row]').forEach(row => {
        row.addEventListener('click', event => { if (!event.target.closest('form,button,a')) openModal(row.dataset.campaignEmailId); });
        row.addEventListener('keydown', event => {
            if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('form,button,a')) { event.preventDefault(); openModal(row.dataset.campaignEmailId); }
        });
    });
    modal.querySelectorAll('[data-campaign-email-close]').forEach(button => button.addEventListener('click', closeModal));
    modal.querySelector('[data-campaign-email-backdrop]')?.addEventListener('click', closeModal);
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
})();
</script>

<script>
(() => {
    const progress = document.getElementById('campaignProgress');
    if (!progress || !['queued', 'running'].includes(progress.dataset.campaignStatus)) return;

    const formatNumber = value => Number(value || 0).toLocaleString();
    const setText = (selector, value) => {
        const element = progress.querySelector(selector);
        if (element) element.textContent = value;
    };
    const statusClasses = ['bg-lime-100', 'text-lime-700', 'dark:bg-lime-500/15', 'dark:text-lime-300', 'bg-red-100', 'text-red-700', 'dark:bg-red-500/15', 'dark:text-red-300', 'bg-violet-100', 'text-violet-700', 'dark:bg-violet-500/15', 'dark:text-violet-300'];
    const statusStyles = {
        delivered: { label: 'Delivered', classes: ['bg-lime-100', 'text-lime-700', 'dark:bg-lime-500/15', 'dark:text-lime-300'] },
        failed: { label: 'Failed', classes: ['bg-red-100', 'text-red-700', 'dark:bg-red-500/15', 'dark:text-red-300'] },
        pending: { label: 'Pending', classes: ['bg-violet-100', 'text-violet-700', 'dark:bg-violet-500/15', 'dark:text-violet-300'] },
    };

    const updateDeliveryRecord = record => {
        const row = document.querySelector(`[data-campaign-email-id="${record.id}"]`);
        if (!row) return;

        const status = record.delivery_status || 'pending';
        const style = statusStyles[status] || statusStyles.pending;
        const badge = row.querySelector('[data-campaign-email-delivery]');
        const opened = row.querySelector('[data-campaign-email-opened]');
        const openCount = row.querySelector('[data-campaign-email-open-count]');
        const sentAt = row.querySelector('[data-campaign-email-sent-at]');
        const action = row.querySelector('[data-campaign-email-action]');

        if (badge) {
            badge.classList.remove(...statusClasses);
            badge.classList.add(...style.classes);
            badge.textContent = style.label;
        }
        if (sentAt) sentAt.textContent = record.sent_at || 'Pending';
        if (opened) {
            const hasOpened = Number(record.open_count || 0) > 0;
            opened.textContent = hasOpened ? 'Yes' : 'Not opened';
            opened.classList.toggle('font-medium', hasOpened);
            opened.classList.toggle('text-lime-700', hasOpened);
            opened.classList.toggle('dark:text-lime-300', hasOpened);
            opened.classList.toggle('text-slate-500', !hasOpened);
            opened.classList.toggle('dark:text-slate-400', !hasOpened);
        }
        if (openCount) {
            const count = Number(record.open_count || 0);
            openCount.textContent = `${count} open${count === 1 ? '' : 's'}`;
            openCount.classList.toggle('hidden', count === 0);
        }
        if (action) {
            const form = action.querySelector('form');
            if (status === 'failed' && form) {
                form.action = record.retry_url || form.action;
            } else if (status === 'failed' && !form) {
                const token = document.querySelector('input[name="_token"]')?.value;
                if (token && record.retry_url) {
                    action.innerHTML = `<form method="POST" action="${record.retry_url}" data-campaign-email-retry-form class="inline-flex"><input type="hidden" name="_token" value="${token}"><button type="submit" data-campaign-email-retry class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40"><i class="bi bi-arrow-repeat"></i> Retry</button></form>`;
                    action.querySelector('form')?.addEventListener('click', event => event.stopPropagation());
                }
            } else if (status !== 'failed' && form) {
                action.innerHTML = '<span class="text-xs text-slate-400 dark:text-slate-500">—</span>';
            }
        }

        const details = window.__campaignEmailModalData?.[String(record.id)];
        if (details) {
            details.delivery_status = status;
            details.sent_at = record.sent_at || null;
            details.opened_count = Number(record.open_count || 0);
            details.first_opened_at = record.opened_at && record.opened_at !== 'Not opened' ? record.opened_at : null;
            if (record.delivery_error) details.delivery_error = record.delivery_error;
        }
    };

    const refreshProgress = async () => {
        try {
            const response = await fetch(progress.dataset.progressUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!response.ok) throw new Error('Unable to refresh campaign progress.');

            const data = await response.json();
            progress.dataset.campaignStatus = data.status || progress.dataset.campaignStatus;
            setText('[data-progress-percent]', `${data.progress || 0}%`);
            setText('[data-progress-records]', formatNumber(data.records));
            setText('[data-progress-step]', data.current_step ? `Step ${data.current_step}` : 'Not started');
            setText('[data-progress-opened]', formatNumber(data.opened));
            setText('[data-progress-failed]', formatNumber(data.failed));
            setText('[data-progress-remaining]', formatNumber(data.remaining));
            progress.querySelector('[data-progress-bar]')?.style.setProperty('width', `${data.progress || 0}%`);

            (data.records_detail || []).forEach(updateDeliveryRecord);

            document.querySelectorAll('[data-progress-stat]').forEach(stat => {
                const key = stat.dataset.progressStat;
                stat.textContent = key === 'open_rate' ? `${Number(data.open_rate || 0).toFixed(1)}%` : formatNumber(data[key]);
            });

            const failureAlert = document.getElementById('campaignFailureAlert');
            const failureMessage = document.getElementById('campaignFailureMessage');
            const latestFailure = document.getElementById('campaignLatestFailure');
            const retryAllForm = document.getElementById('campaignRetryAllForm');
            if (failureAlert && failureMessage && latestFailure) {
                const failed = Number(data.failed || 0);
                failureAlert.classList.toggle('hidden', failed === 0);
                retryAllForm?.classList.toggle('hidden', failed === 0);
                failureMessage.textContent = `${formatNumber(failed)} email${failed === 1 ? '' : 's'} failed to send.`;
                latestFailure.textContent = data.first_failure ? `Latest error: ${data.first_failure}` : '';
                latestFailure.classList.toggle('hidden', !data.first_failure);
            }

            if (['queued', 'running'].includes(data.status)) {
                window.setTimeout(refreshProgress, 2000);
            }
        } catch (error) {
            window.setTimeout(refreshProgress, 5000);
        }
    };

    refreshProgress();
})();
</script>
