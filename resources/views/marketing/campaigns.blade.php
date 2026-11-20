@php
    $campaigns = $campaigns ?? collect();
    $campaignLeadSegments = $campaignLeadSegments ?? collect();
    $selectedLeadEmails = $selectedLeadEmails ?? collect();
    $selectedCampaign = $selectedCampaign ?? null;
    $campaignStep = $campaignStep ?? 1;
    $campaignsReady = $campaignsReady ?? true;
    $campaignLeadTotal = $campaignLeadTotal ?? 0;
    $campaignSteps = $selectedCampaign?->steps?->values() ?? collect();
    $firstCampaignStep = $campaignSteps->first();
    $campaignName = old('name', $selectedCampaign?->name ?: '');
    $campaignDescription = old('description', $selectedCampaign?->description ?: '');
    $defaultManualRecipients = $selectedCampaign?->manual_recipients ?: $selectedLeadEmails->all();
    $audienceType = old('audience_type', $selectedCampaign?->audience_type ?: ($selectedLeadEmails->isNotEmpty() ? 'manual' : 'all_leads'));
    $audienceSegment = old('audience_segment', $selectedCampaign?->audience_segment ?: '');
    $audienceStatus = old('audience_status', $selectedCampaign?->audience_status ?: '');
    $subject = old('subject', $firstCampaignStep?->subject ?: '');
    $content = old('content', $firstCampaignStep?->body ?: '');
    $selectedTemplateId = old('template_id', $firstCampaignStep?->marketing_template_id ?: '');
    $selectedMailServiceId = old('marketing_mail_service_id', $firstCampaignStep?->marketing_mail_service_id ?: $mailServices->first()?->id);
    $selectedSignatureId = old('marketing_signature_id', $firstCampaignStep?->marketing_signature_id ?: $signatures->first()?->id);
    $sequenceStepLimit = $marketingPlanLimits['sequence_steps'] ?? 8;
    $statusClass = match ($selectedCampaign?->status) {
        'running' => 'bg-lime-100 text-lime-700 dark:bg-lime-500/15 dark:text-lime-300',
        'queued' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
        'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    };
@endphp

<div class="marketing-campaign-page flex flex-col gap-6">
    @if (! $campaignsReady)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">Campaign storage is not ready yet. Run <code>php artisan migrate</code> on the server to enable it.</div>
    @endif

    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
        <div><div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"><span>Workspace</span><span>/</span><span class="font-medium text-violet-500">Campaigns</span></div><div class="flex flex-wrap items-center gap-3"><h1 class="text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $campaignName ?: 'Create campaign' }}</h1>@if ($selectedCampaign)<span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($selectedCampaign->status) }}</span>@endif</div><p class="mt-2 text-slate-500 dark:text-slate-400">Build a sequence, select your audience, and keep delivery running in the background.</p></div>
        <div class="flex flex-wrap items-center gap-3">@if ($selectedCampaign)<span class="inline-flex h-10 items-center rounded-xl border border-slate-300 px-4 text-sm font-medium text-slate-600 dark:border-slate-700 dark:text-slate-300">{{ number_format($selectedCampaign->recipient_count) }} recipients</span><form method="POST" action="{{ route('marketing.campaigns.delete', $selectedCampaign->id) }}" onsubmit="return confirm('Delete this campaign and all of its delivery records? This action cannot be undone.');">@csrf @method('DELETE')<button type="submit" class="campaign-delete-button inline-flex h-10 items-center gap-2 rounded-xl border border-red-200 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10"><i class="bi bi-trash3"></i> Delete Campaign</button></form>@endif</div>
    </div>

    <div class="grid grid-cols-4 gap-1.5 rounded-xl border border-slate-200 bg-slate-50 p-1.5 dark:border-slate-800 dark:bg-slate-950">
        @foreach ([1 => ['Audience', 'people'], 2 => ['Content', 'file-earmark-text'], 3 => ['Sequence', 'diagram-3'], 4 => ['Schedule', 'calendar3']] as $number => [$labelText, $icon])
            @php $stepIsActive = $campaignStep === $number; $stepIsDone = $selectedCampaign && $campaignStep > $number; @endphp
            <a href="{{ $selectedCampaign ? route('marketing', ['tab' => 'campaigns', 'campaign_id' => $selectedCampaign->id, 'campaign_step' => $number]) : ($number === 1 ? route('marketing', ['tab' => 'campaigns', 'campaign_step' => 1]) : '#') }}" class="flex min-w-0 items-center gap-2 rounded-lg px-2 py-2 text-xs font-semibold transition {{ $stepIsActive ? 'bg-violet-600 text-white shadow-md shadow-violet-600/20' : ($stepIsDone ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400') }}"><span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $stepIsActive ? 'bg-white text-emerald-700' : ($stepIsDone ? 'bg-emerald-400 text-white' : 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300') }} text-[11px]">@if ($stepIsDone)<i class="bi bi-check2"></i>@else{{ $number }}@endif</span><span class="truncate">{{ $labelText }}</span></a>
        @endforeach
    </div>

    @if ($campaignStep === 1 || ! $selectedCampaign)
        <section class="{{ $card }} p-6">
            <div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Choose your audience</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Select the leads who should enter this campaign.</p></div>
            <form action="{{ route('marketing.campaigns.audience') }}" method="POST" class="mt-6 grid gap-5">
                @csrf
                @if ($selectedCampaign)<input type="hidden" name="campaign_id" value="{{ $selectedCampaign->id }}">@endif
                <div class="grid gap-5 md:grid-cols-2"><div><label class="{{ $label }}" for="campaignName">Campaign name</label><input id="campaignName" name="name" type="text" class="{{ $input }}" value="{{ $campaignName }}" placeholder="Wedding photographers outreach" required></div><div><label class="{{ $label }}" for="campaignDescription">Description</label><input id="campaignDescription" name="description" type="text" class="{{ $input }}" value="{{ $campaignDescription }}" placeholder="Introduce your platform and start conversations"></div></div>
                <div><span class="{{ $label }}">Audience source</span><div class="grid gap-3 md:grid-cols-3"><label class="cursor-pointer rounded-2xl border p-4 transition has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 dark:has-[:checked]:border-violet-400 dark:has-[:checked]:bg-violet-500/10"><input class="campaign-audience-type sr-only" type="radio" name="audience_type" value="all_leads" @checked($audienceType === 'all_leads')><span class="flex items-center gap-3"><i class="bi bi-people text-xl text-violet-500"></i><span><strong class="block text-sm text-slate-900 dark:text-white">All leads</strong><small class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($campaignLeadTotal) }} available</small></span></span></label><label class="cursor-pointer rounded-2xl border p-4 transition has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 dark:has-[:checked]:border-violet-400 dark:has-[:checked]:bg-violet-500/10"><input class="campaign-audience-type sr-only" type="radio" name="audience_type" value="segment" @checked($audienceType === 'segment')><span class="flex items-center gap-3"><i class="bi bi-collection text-xl text-violet-500"></i><span><strong class="block text-sm text-slate-900 dark:text-white">Lead category</strong><small class="text-xs text-slate-500 dark:text-slate-400">Use an imported category</small></span></span></label><label class="cursor-pointer rounded-2xl border p-4 transition has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 dark:has-[:checked]:border-violet-400 dark:has-[:checked]:bg-violet-500/10"><input class="campaign-audience-type sr-only" type="radio" name="audience_type" value="manual" @checked($audienceType === 'manual')><span class="flex items-center gap-3"><i class="bi bi-envelope text-xl text-violet-500"></i><span><strong class="block text-sm text-slate-900 dark:text-white">Email list</strong><small class="text-xs text-slate-500 dark:text-slate-400">Paste recipients directly</small></span></span></label></div></div>
                <div id="campaignSegmentFields" class="grid gap-5 md:grid-cols-2 {{ $audienceType === 'segment' ? '' : 'hidden' }}"><div><label class="{{ $label }}" for="campaignSegment">Lead category</label><select id="campaignSegment" name="audience_segment" class="{{ $input }}"><option value="">Choose a category</option>@foreach ($campaignLeadSegments as $segment)<option value="{{ $segment->name }}" @selected($audienceSegment === $segment->name)>{{ $segment->name }}</option>@endforeach</select>@error('audience_segment')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror</div><div><label class="{{ $label }}" for="campaignAudienceStatus">Lead status</label><select id="campaignAudienceStatus" name="audience_status" class="{{ $input }}"><option value="">Any status</option>@foreach (\App\Models\MarketingLead::STATUSES as $value => $labelText)<option value="{{ $value }}" @selected($audienceStatus === $value)>{{ $labelText }}</option>@endforeach</select></div></div>
                <div id="campaignManualFields" class="{{ $audienceType === 'manual' ? '' : 'hidden' }}"><label class="{{ $label }}" for="campaignManualRecipients">Email addresses</label><textarea id="campaignManualRecipients" name="manual_recipients" class="{{ $input }} min-h-28" placeholder="one@example.com, two@example.com">{{ old('manual_recipients', implode(', ', $defaultManualRecipients)) }}</textarea>@error('manual_recipients')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror</div>
                <label class="inline-flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200"><input type="checkbox" name="stop_on_reply" value="1" class="h-4 w-4 accent-violet-600" @checked(old('stop_on_reply', $selectedCampaign?->stop_on_reply ?? true))><span><strong class="font-semibold">Stop on reply</strong><span class="ml-2 text-slate-500 dark:text-slate-400">Pause future steps when a lead opens an email.</span></span></label>
                <div class="flex justify-end border-t border-slate-200 pt-5 dark:border-slate-800"><button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 hover:bg-violet-500">Continue to content <i class="bi bi-arrow-right"></i></button></div>
            </form>
        </section>
    @elseif ($campaignStep === 2)
        <form action="{{ route('marketing.campaigns.content') }}" method="POST" enctype="multipart/form-data" id="campaignContentForm" class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.25fr)_minmax(300px,.75fr)]">
            @csrf<input type="hidden" name="campaign_id" value="{{ $selectedCampaign->id }}">
            <section class="{{ $card }} p-6">
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Email content</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Craft the first email in your outreach sequence.</p>
                <div class="mt-6 grid gap-5">
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="{{ $label }} mb-0" for="campaignTemplate">Saved template</label>
                            <a href="{{ route('marketing', ['tab' => 'templates-create']) }}" class="text-xs font-semibold text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200">Create new template <i class="bi bi-arrow-up-right"></i></a>
                        </div>
                        <select id="campaignTemplate" name="template_id" class="{{ $input }}">
                            <option value="">Start from scratch</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}" @selected($selectedTemplateId == $template->id)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                        @if ($templates->isEmpty())
                            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400">No saved templates yet. Create one to reuse it in this campaign.</p>
                        @endif
                    </div>
                    <div>
                        <label class="{{ $label }}" for="campaignSubject">Subject <span class="text-red-500">*</span></label>
                        <input id="campaignSubject" name="subject" type="text" class="{{ $input }}" value="{{ $subject }}" placeholder="Collect every wedding memory" required>
                    </div>
                    <div>
                        <label class="{{ $label }}" for="campaignContent">Email body <span class="text-red-500">*</span></label>
                        @include('marketing.rich-editor', ['editorId' => 'campaignContentEditor', 'inputId' => 'campaignContent', 'initialContent' => $content, 'hasError' => $errors->has('content')])
                    </div>
                    <div>
                        <label class="{{ $label }}" for="campaignAttachment">Attach file</label>
                        <input id="campaignAttachment" name="attachment" type="file" class="{{ $input }}">
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Optional. A saved template attachment will be reused when no new file is selected.</p>
                    </div>
                </div>
            </section>
            <aside class="{{ $card }} p-6">
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Delivery settings</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Keep your saved SMTP service and signature for every step.</p>
                <div class="mt-6 grid gap-5">
                    <div>
                        <label class="{{ $label }}" for="campaignMailService">Send using</label>
                        <select id="campaignMailService" name="marketing_mail_service_id" class="{{ $input }}" required>
                            <option value="">Select an SMTP service</option>
                            @foreach ($mailServices as $service)
                                <option value="{{ $service->id }}" @selected($selectedMailServiceId == $service->id)>{{ $service->name }} · {{ $service->from_email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}" for="campaignSignature">Email signature</label>
                        <select id="campaignSignature" name="marketing_signature_id" class="{{ $input }}" required>
                            <option value="">Select an email signature</option>
                            @foreach ($signatures as $signature)
                                <option value="{{ $signature->id }}" @selected($selectedSignatureId == $signature->id)>{{ $signature->name }} · {{ $signature->image_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p id="campaignDeliverySettingsError" class="hidden text-sm font-medium text-red-600 dark:text-red-400">Select an SMTP service and an email signature before continuing.</p>
                    <div class="marketing-audience-notice rounded-lg border px-3 py-2.5 text-sm font-medium"><i class="bi bi-people mr-2"></i>{{ number_format($selectedCampaign->recipient_count) }} recipients will enter this campaign.</div>
                </div>
                <div class="mt-6 flex justify-between border-t border-slate-200 pt-5 dark:border-slate-800">
                    <a href="{{ route('marketing', ['tab' => 'campaigns', 'campaign_id' => $selectedCampaign->id, 'campaign_step' => 1]) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">Back</a>
                    <div class="flex gap-2">
                        <button type="submit" name="save_draft" value="1" formnovalidate class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"><i class="bi bi-save"></i> Save draft</button>
                        <button type="submit" id="campaignContentContinue" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-50" @disabled($mailServices->isEmpty() || $signatures->isEmpty())>Continue <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>
            </aside>
        </form>
    @elseif ($campaignStep === 3)
        <form action="{{ route('marketing.campaigns.sequence') }}" method="POST" id="campaignSequenceForm">
            @csrf<input type="hidden" name="campaign_id" value="{{ $selectedCampaign->id }}">
            <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,.85fr)]"><section class="{{ $card }} p-6"><div class="flex items-start justify-between gap-4"><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Sequence</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The emails and timing for this campaign.</p></div><span id="campaignStepCount" class="text-sm text-slate-500 dark:text-slate-400">{{ $campaignSteps->count() }} step{{ $campaignSteps->count() === 1 ? '' : 's' }}</span></div><div id="campaignSequenceSteps" class="mt-6 space-y-4">
                @foreach ($campaignSteps as $index => $sequenceStep)
                    <div class="relative rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950" data-sequence-card>
                        <div class="flex items-center gap-3"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-violet-600 text-sm font-semibold text-white">{{ $index + 1 }}</span><div><strong class="text-sm text-slate-900 dark:text-white">{{ $index === 0 ? 'Email 1' : 'Follow-up '.$index }}</strong><p class="text-xs text-slate-500 dark:text-slate-400">{{ $index === 0 ? 'First outreach email' : 'Sent after the wait period' }}</p></div></div>
                        <div class="mt-4 grid gap-3 {{ $index === 0 ? 'md:grid-cols-1' : 'md:grid-cols-[150px_minmax(0,1fr)]' }}">@if ($index > 0)<div><label class="{{ $label }} text-xs" for="sequenceWait{{ $index }}">Wait days</label><input id="sequenceWait{{ $index }}" name="sequence[{{ $index }}][wait_days]" type="number" min="0" max="365" class="{{ $input }}" value="{{ $sequenceStep->wait_days }}"></div>@else<input type="hidden" name="sequence[0][wait_days]" value="0">@endif<div><label class="{{ $label }} text-xs" for="sequenceSubject{{ $index }}">Subject</label><input id="sequenceSubject{{ $index }}" data-sequence-subject name="sequence[{{ $index }}][subject]" type="text" class="{{ $input }}" value="{{ $sequenceStep->subject }}" required></div></div>
                        <div class="mt-4">
                            <div><span class="{{ $label }} text-xs">Email body</span>@include('marketing.rich-editor', ['editorId' => 'sequenceEditor'.$index, 'inputId' => 'sequenceContent'.$index, 'inputName' => 'sequence['.$index.'][content]', 'initialContent' => $sequenceStep->body, 'hasError' => $errors->has('sequence.'.$index.'.content'), 'showHint' => false])</div>
                        </div>
                        <input type="hidden" name="sequence[{{ $index }}][template_id]" value="{{ $sequenceStep->marketing_template_id }}"><input type="hidden" name="sequence[{{ $index }}][marketing_mail_service_id]" value="{{ $sequenceStep->marketing_mail_service_id }}"><input type="hidden" name="sequence[{{ $index }}][marketing_signature_id]" value="{{ $sequenceStep->marketing_signature_id }}">@if ($index > 0)<button type="button" data-remove-sequence class="mt-3 text-xs font-semibold text-slate-500 hover:text-red-600 dark:text-slate-400 dark:hover:text-red-400">Remove this step</button>@endif
                    </div>
                @endforeach
            </div><template id="campaignSequenceStepTemplate"><div class="relative rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950" data-sequence-card><div class="flex items-center gap-3"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-violet-600 text-sm font-semibold text-white">__INDEX_NUMBER__</span><div><strong class="text-sm text-slate-900 dark:text-white">Follow-up __INDEX__</strong><p class="text-xs text-slate-500 dark:text-slate-400">Sent after the wait period</p></div></div><div class="mt-4 grid gap-3 md:grid-cols-[150px_minmax(0,1fr)]"><div><label class="{{ $label }} text-xs" for="sequenceWait__INDEX__">Wait days</label><input id="sequenceWait__INDEX__" name="sequence[__INDEX__][wait_days]" type="number" min="0" max="365" class="{{ $input }}" value="3"></div><div><label class="{{ $label }} text-xs" for="sequenceSubject__INDEX__">Subject</label><input id="sequenceSubject__INDEX__" data-sequence-subject name="sequence[__INDEX__][subject]" type="text" class="{{ $input }}" value="A quick follow-up" required></div></div><div class="mt-4"><div><span class="{{ $label }} text-xs">Email body</span>@include('marketing.rich-editor', ['editorId' => 'sequenceEditor__INDEX__', 'inputId' => 'sequenceContent__INDEX__', 'inputName' => 'sequence[__INDEX__][content]', 'initialContent' => '', 'showHint' => false])</div></div><input type="hidden" name="sequence[__INDEX__][template_id]" value=""><input type="hidden" name="sequence[__INDEX__][marketing_mail_service_id]" value="{{ $selectedMailServiceId }}"><input type="hidden" name="sequence[__INDEX__][marketing_signature_id]" value="{{ $selectedSignatureId }}"><button type="button" data-remove-sequence class="mt-3 text-xs font-semibold text-slate-500 hover:text-red-600 dark:text-slate-400 dark:hover:text-red-400">Remove this step</button></div></template><button type="button" id="addCampaignStep" class="mt-5 inline-flex items-center gap-2 rounded-xl border border-dashed border-violet-300 px-4 py-2.5 text-sm font-semibold text-violet-700 hover:bg-violet-50 dark:border-violet-500/40 dark:text-violet-300 dark:hover:bg-violet-500/10"><i class="bi bi-plus"></i> Add another step</button><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Your {{ $marketingPlan['label'] ?? 'current' }} plan allows {{ $sequenceStepLimit === null ? 'unlimited' : $sequenceStepLimit }} sequence step{{ $sequenceStepLimit === 1 ? '' : 's' }} per campaign.</p></section>
            <aside class="{{ $card }} p-6"><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Sequence settings</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Every follow-up uses the SMTP service and signature selected for the campaign.</p><div class="mt-6 space-y-4"><div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800"><span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Audience</span><strong class="mt-1 block text-slate-900 dark:text-white">{{ number_format($selectedCampaign->recipient_count) }} recipients</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $selectedCampaign->audience_segment ?: 'All leads' }}</span></div><div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800"><span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Stop on reply</span><strong class="mt-1 block text-slate-900 dark:text-white">{{ $selectedCampaign->stop_on_reply ? 'Enabled' : 'Disabled' }}</strong></div></div><div class="mt-6 flex justify-between border-t border-slate-200 pt-5 dark:border-slate-800"><a href="{{ route('marketing', ['tab' => 'campaigns', 'campaign_id' => $selectedCampaign->id, 'campaign_step' => 2]) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">Back</a><button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 hover:bg-violet-500">Save sequence <i class="bi bi-arrow-right"></i></button></div></aside></div>
        </form>
    @else
        @if ($signatures->isEmpty())
            <div class="marketing-signature-required mb-5 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm" role="alert">
                <i class="bi bi-lock mt-0.5"></i>
                <div><strong class="font-semibold">Email signature required.</strong> Add at least one signature in Email settings before starting this campaign. <a class="font-semibold underline" href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'email']) }}">Open Email settings</a>.</div>
            </div>
        @endif
        @if ($mailServices->isEmpty())
            <div class="marketing-smtp-required mb-5 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm" role="alert">
                <i class="bi bi-lock mt-0.5"></i>
                <div><strong class="font-semibold">SMTP settings required.</strong> Add an SMTP service in Email settings before starting this campaign. <a class="font-semibold underline" href="{{ route('marketing', ['tab' => 'settings', 'settings_tab' => 'email']) }}">Open Email settings</a>.</div>
            </div>
        @endif
        <section class="{{ $card }} p-6"><div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"><div><h2 class="text-xl font-semibold text-slate-950 dark:text-white">Ready to launch</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review the audience and schedule the campaign. Delivery will continue through the queue until every step is processed.</p></div><span class="inline-flex items-center gap-2 rounded-full bg-lime-100 px-3 py-1 text-sm font-semibold text-lime-700 dark:bg-lime-500/15 dark:text-lime-300"><span class="h-2 w-2 rounded-full bg-lime-500"></span>Queue ready</span></div><div class="mt-7 grid gap-4 md:grid-cols-3"><div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-800"><span class="{{ $muted }}">Audience</span><strong class="mt-2 block text-lg text-slate-900 dark:text-white">{{ number_format($selectedCampaign->recipient_count) }} leads</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $selectedCampaign->audience_segment ?: 'All leads' }}</span></div><div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-800"><span class="{{ $muted }}">Sequence</span><strong class="mt-2 block text-lg text-slate-900 dark:text-white">{{ $campaignSteps->count() }} email{{ $campaignSteps->count() === 1 ? '' : 's' }}</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $selectedCampaign->stop_on_reply ? 'Stops on reply' : 'Runs all steps' }}</span></div><div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-800"><span class="{{ $muted }}">Delivery</span><strong class="mt-2 block text-lg text-slate-900 dark:text-white">SMTP + signature</strong><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Settings saved per step</span></div></div><form action="{{ route('marketing.campaigns.start') }}" method="POST" class="mt-7 grid gap-5 border-t border-slate-200 pt-6 dark:border-slate-800 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">@csrf<input type="hidden" name="campaign_id" value="{{ $selectedCampaign->id }}"><div><label class="{{ $label }}" for="campaignSchedule">Schedule start</label><input id="campaignSchedule" name="scheduled_at" type="datetime-local" class="{{ $input }} md:w-72" value="{{ old('scheduled_at', now()->format('Y-m-d\TH:i')) }}"><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Use the current time to start immediately.</p></div><div><label class="{{ $label }}" for="campaignSendRate">Emails per minute</label><select id="campaignSendRate" name="send_rate_per_minute" class="{{ $input }}" data-campaign-send-rate data-recipient-count="{{ $selectedCampaign->recipient_count }}" data-step-count="{{ $campaignSteps->count() }}"><option value="1" @selected((int) old('send_rate_per_minute', $selectedCampaign->send_rate_per_minute ?: 5) === 1)>1 email/min</option><option value="2" @selected((int) old('send_rate_per_minute', $selectedCampaign->send_rate_per_minute ?: 5) === 2)>2 emails/min</option><option value="5" @selected((int) old('send_rate_per_minute', $selectedCampaign->send_rate_per_minute ?: 5) === 5)>5 emails/min</option><option value="10" @selected((int) old('send_rate_per_minute', $selectedCampaign->send_rate_per_minute ?: 5) === 10)>10 emails/min</option><option value="20" @selected((int) old('send_rate_per_minute', $selectedCampaign->send_rate_per_minute ?: 5) === 20)>20 emails/min</option></select><p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Start low to avoid SMTP rate-limit errors. Estimated time: <span data-campaign-send-estimate>—</span>.</p></div><div class="flex gap-3"><a href="{{ route('marketing', ['tab' => 'campaigns', 'campaign_id' => $selectedCampaign->id, 'campaign_step' => 3]) }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">Back</a><button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-50" @disabled($mailServices->isEmpty())><i class="bi bi-calendar-check"></i> Start campaign <i class="bi bi-arrow-right"></i></button></div></form></section>
    @endif

</div>

<script>
(() => {
    const audienceTypes = document.querySelectorAll('.campaign-audience-type');
    const segmentFields = document.getElementById('campaignSegmentFields');
    const manualFields = document.getElementById('campaignManualFields');
    const updateAudienceFields = () => {
        const type = document.querySelector('.campaign-audience-type:checked')?.value;
        segmentFields?.classList.toggle('hidden', type !== 'segment');
        manualFields?.classList.toggle('hidden', type !== 'manual');
    };
    audienceTypes.forEach(input => input.addEventListener('change', updateAudienceFields));
    updateAudienceFields();

    const templateSelect = document.getElementById('campaignTemplate');
    const subjectInput = document.getElementById('campaignSubject');
    const contentInput = document.getElementById('campaignContent');
    const contentEditor = document.querySelector('[data-editor-id="campaignContentEditor"]');
    const templateOptions = @json($templateOptions);
    templateSelect?.addEventListener('change', () => {
        const template = templateOptions[templateSelect.value];
        if (template) {
            subjectInput.value = template.subject || (template.subjects?.[0] || '');
            contentInput.value = template.content || '';
        } else {
            contentInput.value = '';
        }

        contentEditor?.dispatchEvent(new CustomEvent('marketing-editor:set-content', {
            detail: { content: contentInput.value },
        }));
    });

    const settingsUrl = @json(route('marketing', ['tab' => 'settings', 'settings_tab' => 'email']));
    [
        ['campaignMailService', 'Add SMTP settings'],
        ['campaignSignature', 'Add email signature'],
    ].forEach(([selectId, linkText]) => {
        const select = document.getElementById(selectId);
        const label = select?.previousElementSibling;
        if (!select || !label || select.options.length > 1) return;

        label.classList.add('flex', 'items-center', 'justify-between', 'gap-3');
        const link = document.createElement('a');
        link.href = settingsUrl;
        link.className = 'shrink-0 text-xs font-semibold text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200';
        link.textContent = linkText;
        label.appendChild(link);
    });

    const campaignContentForm = document.getElementById('campaignContentForm');
    const campaignMailService = document.getElementById('campaignMailService');
    const campaignSignature = document.getElementById('campaignSignature');
    const campaignContentContinue = document.getElementById('campaignContentContinue');
    const campaignDeliverySettingsError = document.getElementById('campaignDeliverySettingsError');
    const updateCampaignDeliveryState = () => {
        const ready = Boolean(campaignMailService?.value && campaignSignature?.value);
        if (campaignContentContinue) campaignContentContinue.disabled = !ready;
        if (ready) campaignDeliverySettingsError?.classList.add('hidden');
    };
    [campaignMailService, campaignSignature].forEach(select => select?.addEventListener('change', updateCampaignDeliveryState));
    campaignContentForm?.addEventListener('submit', event => {
        if (event.submitter?.name === 'save_draft') return;
        if (campaignMailService?.value && campaignSignature?.value) return;

        event.preventDefault();
        campaignDeliverySettingsError?.classList.remove('hidden');
        campaignMailService?.reportValidity();
        campaignSignature?.reportValidity();
    });
    updateCampaignDeliveryState();

    const sequenceContainer = document.getElementById('campaignSequenceSteps');
    const addStepButton = document.getElementById('addCampaignStep');
    const stepCount = document.getElementById('campaignStepCount');
    const sequenceStepLimit = @json($sequenceStepLimit);
    const sequenceTemplate = document.getElementById('campaignSequenceStepTemplate');
    let nextIndex = sequenceContainer ? sequenceContainer.querySelectorAll('[data-sequence-card]').length : 0;

    const setupSequenceEditor = (card) => {
        const editor = card.querySelector('[data-rich-text-editor]');
        if (!editor) return;
        window.setupMarketingEditor?.(editor);
    };

    const refreshSequence = () => {
        if (stepCount) stepCount.textContent = nextIndex + ' step' + (nextIndex === 1 ? '' : 's');
        if (addStepButton && sequenceStepLimit !== null) {
            addStepButton.disabled = nextIndex >= sequenceStepLimit;
            addStepButton.title = addStepButton.disabled ? 'Your current plan has reached its sequence limit.' : '';
        }
        sequenceContainer?.querySelectorAll('[data-sequence-card]').forEach(setupSequenceEditor);
        sequenceContainer?.querySelectorAll('[data-remove-sequence]').forEach(button => button.onclick = () => { button.closest('[data-sequence-card]').remove(); nextIndex = sequenceContainer.querySelectorAll('[data-sequence-card]').length; refreshSequence(); });
    };
    addStepButton?.addEventListener('click', () => {
        if (! sequenceContainer || ! sequenceTemplate || (sequenceStepLimit !== null && nextIndex >= sequenceStepLimit)) return;
        const index = nextIndex;
        const fragment = sequenceTemplate.content.cloneNode(true);
        const wrapper = document.createElement('div');
        wrapper.appendChild(fragment);
        wrapper.innerHTML = wrapper.innerHTML.replaceAll('__INDEX_NUMBER__', String(index + 1)).replaceAll('__INDEX__', String(index));
        const card = wrapper.firstElementChild;
        sequenceContainer.appendChild(card);
        nextIndex++;
        refreshSequence();
    });
    refreshSequence();
})();
</script>

<script>
(() => {
    const rateSelect = document.querySelector('[data-campaign-send-rate]');
    const estimate = document.querySelector('[data-campaign-send-estimate]');
    if (!rateSelect || !estimate) return;

    const updateEstimate = () => {
        const rate = Math.max(1, Number(rateSelect.value || 5));
        const recipients = Math.max(0, Number(rateSelect.dataset.recipientCount || 0));
        const steps = Math.max(1, Number(rateSelect.dataset.stepCount || 1));
        const minutes = Math.ceil((recipients * steps) / rate);
        if (minutes < 60) {
            estimate.textContent = `${minutes} minute${minutes === 1 ? '' : 's'}`;
            return;
        }
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        estimate.textContent = `${hours}h${remainingMinutes ? ` ${remainingMinutes}m` : ''}`;
    };

    rateSelect.addEventListener('change', updateEstimate);
    updateEstimate();
})();
</script>
