<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\MarketingEmail;
use App\Models\MarketingEmailOpen;
use App\Models\MarketingFollowupEmail;
use App\Models\MarketingTemplate;
use App\Models\MarketingUnsubscribe;
use App\Models\WebsiteClick;
use App\Models\WebsiteVisit;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;

class MainController extends Controller
{
    public function contactus(ContactRequest $request){

        $contact = new Contact;
        $contact->firstname = $request->firstname;
        $contact->lastname = $request->lastname;
        $contact->email = $request->email;
        $contact->budget = $request->budget;
        $contact->details = $request->details;

        if( $contact->save() ){
            return response()->json(['msg'=>'success']);
        }else{
            return response()->json(['msg'=>'error']);
        }


    }

    public function aboutme(){
        return view('aboutme');
    }
    
    
    public function getcontacts(){
        $contacts = Contact::all();
        return json_encode(['msg'=>'success', 'data'=>$contacts]);
    }

    public function showMarketingLogin(Request $request)
    {
        if ($request->session()->get('marketing_authenticated') === true) {
            return redirect()->route('marketing');
        }

        return view('marketing-login');
    }

    public function loginMarketing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $validator->validate();

        $configuredUsername = (string) config('marketing.auth.username');
        $configuredPassword = (string) config('marketing.auth.password');

        if ($configuredUsername === '' || $configuredPassword === '') {
            return back()
                ->withInput($request->only('username'))
                ->with('marketing_login_error', 'Marketing login credentials are not configured.');
        }

        $validCredentials = hash_equals($configuredUsername, (string) $request->input('username'))
            && hash_equals($configuredPassword, (string) $request->input('password'));

        if (! $validCredentials) {
            return back()
                ->withInput($request->only('username'))
                ->with('marketing_login_error', 'Invalid username or password.');
        }

        $request->session()->regenerate();
        $request->session()->put('marketing_authenticated', true);

        return redirect()->intended(route('marketing'));
    }

    public function logoutMarketing(Request $request)
    {
        $request->session()->forget('marketing_authenticated');
        $request->session()->regenerateToken();

        return redirect()->route('marketing.login');
    }

    public function marketing(Request $request)
    {
        $activeTab = $request->query('tab', 'dashboard');

        if ($activeTab === 'templates') {
            $activeTab = 'templates-list';
        }

        if (! in_array($activeTab, ['dashboard', 'send', 'sent-emails', 'sent-email-detail', 'followups', 'followups-create', 'followups-edit', 'contacts', 'templates-create', 'templates-list', 'templates-edit', 'analytics'], true)) {
            $activeTab = 'dashboard';
        }

        $databaseReady = true;
        $editingTemplate = null;
        $editingFollowupEmail = null;
        $selectedEmail = null;
        $selectedFollowupEmail = null;
        $selectedRecipientStatuses = collect();
        $followupEmails = collect();
        $websiteVisits = collect();
        $websiteClicks = collect();
        $topPages = collect();
        $topClicks = collect();
        $topLocations = collect();
        $activeVisitGroups = collect();
        $activeVisitorsCount = 0;
        $websiteVisitsTotal = 0;
        $websiteClicksTotal = 0;

        try {
            MarketingUnsubscribe::query()->limit(1)->exists();
            MarketingEmailOpen::query()->limit(1)->exists();
            MarketingTemplate::query()->limit(1)->exists();
            MarketingFollowupEmail::query()->limit(1)->exists();
            Schema::hasColumn('marketing_emails', 'delivery_status') || throw new \RuntimeException('Marketing email delivery status column is missing.');
            Schema::hasColumn('marketing_templates', 'attachment_path') || throw new \RuntimeException('Marketing template attachment column is missing.');
            Schema::hasColumn('marketing_templates', 'subject_options') || throw new \RuntimeException('Marketing template subject options column is missing.');
            WebsiteVisit::query()->limit(1)->exists();
            WebsiteClick::query()->limit(1)->exists();
            Schema::hasColumn('website_visits', 'country') || throw new \RuntimeException('Website visit location columns are missing.');
            Schema::hasColumn('website_visits', 'last_seen_at') || throw new \RuntimeException('Website visit online status column is missing.');
            $marketingEmails = MarketingEmail::with('opens')->latest('sent_at')->get();
            $templates = MarketingTemplate::latest()->get();
            $followupEmails = MarketingFollowupEmail::with(['originalEmail.opens', 'template'])->latest('scheduled_at')->get();
            $websiteVisitsTotal = WebsiteVisit::count();
            $websiteClicksTotal = WebsiteClick::count();
            $activeVisits = WebsiteVisit::query()
                ->where('last_seen_at', '>=', now()->subMinutes(5))
                ->latest('last_seen_at')
                ->get()
                ->unique('session_id')
                ->values();
            $activeVisitorsCount = $activeVisits->count();
            $activeVisitGroups = $this->websiteAnalyticsActiveGroups($activeVisits);
            $websiteVisits = WebsiteVisit::withCount('clicks')->latest('visited_at')->take(50)->get();
            $websiteClicks = WebsiteClick::latest('clicked_at')->take(50)->get();
            $topPages = WebsiteVisit::query()
                ->selectRaw('path, count(*) as visits_count')
                ->groupBy('path')
                ->orderByDesc('visits_count')
                ->take(8)
                ->get();
            $topClicks = WebsiteClick::query()
                ->selectRaw('path, element_text, element, count(*) as clicks_count')
                ->groupBy('path', 'element_text', 'element')
                ->orderByDesc('clicks_count')
                ->take(8)
                ->get();
            $topLocations = WebsiteVisit::query()
                ->selectRaw('country, region, city, count(*) as visits_count')
                ->whereNotNull('country')
                ->groupBy('country', 'region', 'city')
                ->orderByDesc('visits_count')
                ->take(8)
                ->get();
            $selectedEmail = $activeTab === 'sent-email-detail'
                ? MarketingEmail::with('opens')->find($request->query('email'))
                : null;
            $selectedFollowupEmail = $activeTab === 'followups-create'
                ? MarketingEmail::find($request->query('email'))
                : null;
            $editingFollowupEmail = $activeTab === 'followups-edit'
                ? MarketingFollowupEmail::with(['originalEmail.opens', 'template'])->find($request->query('followup'))
                : null;
            $editingTemplate = $activeTab === 'templates-edit'
                ? MarketingTemplate::find($request->query('template'))
                : null;

            if ($activeTab === 'sent-email-detail' && ! $selectedEmail) {
                $activeTab = 'sent-emails';
            }

            if ($activeTab === 'followups-create' && ! $selectedFollowupEmail) {
                $activeTab = 'sent-emails';
            }

            if ($activeTab === 'followups-edit' && (! $editingFollowupEmail || $editingFollowupEmail->status === 'sent')) {
                $activeTab = 'followups';
            }

            if ($activeTab === 'templates-edit' && ! $editingTemplate) {
                $activeTab = 'templates-list';
            }

            if ($selectedEmail) {
                $opensByEmail = $selectedEmail->opens->keyBy('email');

                $selectedRecipientStatuses = collect($selectedEmail->recipients)
                    ->map(function ($recipient) use ($selectedEmail, $opensByEmail) {
                        $open = $opensByEmail->get($recipient);

                        return [
                            'email' => $recipient,
                            'delivery_status' => $selectedEmail->delivery_status ?: ($selectedEmail->sent_at ? 'delivered' : 'failed'),
                            'opened_at' => optional($open)->opened_at,
                            'last_opened_at' => optional($open)->last_opened_at,
                            'open_count' => optional($open)->open_count ?: 0,
                        ];
                    });
            }

            $contacts = $marketingEmails
                ->pluck('recipients')
                ->flatten()
                ->filter()
                ->unique()
                ->sort()
                ->values();
        } catch (\Throwable $exception) {
            $databaseReady = false;
            $activeTab = in_array($activeTab, ['templates-edit', 'sent-email-detail', 'followups-create', 'followups-edit'], true)
                ? ($activeTab === 'templates-edit' ? 'templates-list' : ($activeTab === 'followups-edit' ? 'followups' : 'sent-emails'))
                : $activeTab;
            $marketingEmails = collect();
            $templates = collect();
            $contacts = collect();
            $followupEmails = collect();
            $websiteVisits = collect();
            $websiteClicks = collect();
            $topPages = collect();
            $topClicks = collect();
            $topLocations = collect();
            $activeVisitGroups = collect();
            $activeVisitorsCount = 0;
            $websiteVisitsTotal = 0;
            $websiteClicksTotal = 0;
        }

        return view('marketing', [
            'activeTab' => $activeTab,
            'databaseReady' => $databaseReady,
            'emailsSent' => $marketingEmails->sum('recipient_count'),
            'emailsOpened' => $marketingEmails->sum(fn ($email) => $email->opens->whereNotNull('opened_at')->count()),
            'contactsCount' => $contacts->count(),
            'contacts' => $contacts,
            'recentEmails' => $marketingEmails->take(5),
            'sentEmails' => $marketingEmails,
            'selectedEmail' => $selectedEmail,
            'selectedFollowupEmail' => $selectedFollowupEmail,
            'selectedRecipientStatuses' => $selectedRecipientStatuses,
            'templates' => $templates,
            'followupEmails' => $followupEmails,
            'editingTemplate' => $editingTemplate,
            'editingFollowupEmail' => $editingFollowupEmail,
            'websiteVisits' => $websiteVisits,
            'websiteClicks' => $websiteClicks,
            'topPages' => $topPages,
            'topClicks' => $topClicks,
            'topLocations' => $topLocations,
            'activeVisitGroups' => $activeVisitGroups,
            'activeVisitorsCount' => $activeVisitorsCount,
            'websiteVisitsCount' => $websiteVisitsTotal,
            'websiteClicksCount' => $websiteClicksTotal,
            'templateOptions' => $templates->mapWithKeys(fn ($template) => [
                $template->id => [
                    'subject' => $template->subject,
                    'subjects' => $template->subject_options ?: [$template->subject],
                    'content' => $template->content,
                    'attachment_name' => $template->attachment_name,
                ],
            ]),
        ]);
    }

    public function trackWebsiteVisit(Request $request)
    {
        try {
            $ipAddress = $this->websiteAnalyticsIpAddress($request);
            $userAgent = (string) $request->userAgent();
            $location = $this->websiteAnalyticsLocation($ipAddress);
            $machine = $this->websiteAnalyticsMachine($userAgent);

            $visit = WebsiteVisit::create([
                'session_id' => $this->websiteAnalyticsSessionId($request),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'url' => (string) $request->input('url'),
                'path' => (string) $request->input('path', '/'),
                'referrer' => (string) $request->input('referrer'),
                'country' => $location['country'],
                'region' => $location['region'],
                'city' => $location['city'],
                'postal' => $location['postal'],
                'timezone' => $location['timezone'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'organization' => $location['organization'],
                'browser' => $machine['browser'],
                'browser_version' => $machine['browser_version'],
                'operating_system' => $machine['operating_system'],
                'device_type' => $machine['device_type'],
                'screen_width' => $request->integer('screen_width') ?: null,
                'screen_height' => $request->integer('screen_height') ?: null,
                'viewport_width' => $request->integer('viewport_width') ?: null,
                'viewport_height' => $request->integer('viewport_height') ?: null,
                'visited_at' => now(),
                'last_seen_at' => now(),
            ]);

            return response()->json(['id' => $visit->id]);
        } catch (\Throwable $exception) {
            return response()->json(['id' => null], 204);
        }
    }

    public function trackWebsiteHeartbeat(Request $request)
    {
        try {
            $sessionId = $this->websiteAnalyticsSessionId($request);
            $visit = WebsiteVisit::query()
                ->where('session_id', $sessionId)
                ->when($request->integer('visit_id'), fn ($query, $visitId) => $query->where('id', $visitId))
                ->latest('visited_at')
                ->first();

            if ($visit) {
                $visit->update([
                    'url' => (string) $request->input('url', $visit->url),
                    'path' => (string) $request->input('path', $visit->path),
                    'last_seen_at' => now(),
                ]);
            }
        } catch (\Throwable $exception) {
        }

        return response()->noContent();
    }

    public function trackWebsiteClick(Request $request)
    {
        try {
            WebsiteClick::create([
                'website_visit_id' => $request->integer('visit_id') ?: null,
                'session_id' => $this->websiteAnalyticsSessionId($request),
                'ip_address' => $this->websiteAnalyticsIpAddress($request),
                'url' => (string) $request->input('url'),
                'path' => (string) $request->input('path', '/'),
                'element' => Str::limit((string) $request->input('element'), 255, ''),
                'element_text' => Str::limit((string) $request->input('element_text'), 500, ''),
                'x' => $request->integer('x') ?: null,
                'y' => $request->integer('y') ?: null,
                'clicked_at' => now(),
            ]);
        } catch (\Throwable $exception) {
        }

        return response()->noContent();
    }

    public function sendMarketingEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipients' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'template_id' => ['nullable', 'integer'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $recipients = collect(explode(',', (string) $request->input('recipients')))
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter()
            ->unique()
            ->values();

        $invalidRecipients = $recipients->reject(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

        if ($recipients->isEmpty()) {
            $validator->after(function ($validator) {
                $validator->errors()->add('recipients', 'Add at least one valid email address.');
            });
        }

        if ($invalidRecipients->isNotEmpty()) {
            $validator->after(function ($validator) use ($invalidRecipients) {
                $validator->errors()->add('recipients', 'Invalid email address: '.$invalidRecipients->first());
            });
        }

        $validator->validate();

        try {
            MarketingEmail::query()->limit(1)->exists();
            MarketingUnsubscribe::query()->limit(1)->exists();
            MarketingEmailOpen::query()->limit(1)->exists();
            Schema::hasColumn('marketing_emails', 'delivery_status') || throw new \RuntimeException('Marketing email delivery status column is missing.');
            Schema::hasColumn('marketing_templates', 'attachment_path') || throw new \RuntimeException('Marketing template attachment column is missing.');
            Schema::hasColumn('marketing_templates', 'subject_options') || throw new \RuntimeException('Marketing template subject options column is missing.');
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('marketing_error', 'Marketing storage is not ready yet. Please run the migration after your database credentials are configured.');
        }

        $selectedTemplate = $request->filled('template_id')
            ? MarketingTemplate::find($request->integer('template_id'))
            : null;

        $unsubscribed = MarketingUnsubscribe::query()
            ->whereIn('email', $recipients)
            ->pluck('email');

        $recipients = $recipients->diff($unsubscribed)->values();

        if ($recipients->isEmpty()) {
            return back()
                ->withInput()
                ->with('marketing_error', 'All selected recipients have unsubscribed from marketing emails.');
        }

        $attachmentPath = null;
        $attachmentName = null;
        $deleteAttachmentOnFailure = false;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('marketing-attachments');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
            $deleteAttachmentOnFailure = true;
        } elseif ($selectedTemplate && $selectedTemplate->attachment_path) {
            $attachmentPath = $selectedTemplate->attachment_path;
            $attachmentName = $selectedTemplate->attachment_name;
        }

        $marketingEmail = MarketingEmail::create([
            'recipients' => $recipients->all(),
            'recipient_count' => $recipients->count(),
            'subject' => $request->input('subject'),
            'body' => $request->input('content'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'delivery_status' => 'pending',
        ]);

        $openTrackers = $recipients->mapWithKeys(function ($recipient) use ($marketingEmail) {
            $tracker = MarketingEmailOpen::create([
                'marketing_email_id' => $marketingEmail->id,
                'email' => $recipient,
                'tracking_id' => (string) Str::uuid(),
            ]);

            return [$recipient => $tracker];
        });

        try {
            foreach ($recipients as $recipient) {
                $unsubscribeUrl = route('marketing.unsubscribe', ['email' => $recipient]);
                $plainBody = trim($request->input('content'));
                $trackingUrl = $this->marketingTrackingUrl($openTrackers[$recipient]->tracking_id);
                $htmlBody = '<div style="white-space:pre-wrap;font-family:Arial,sans-serif;font-size:14px;line-height:1.5;color:#111827;">'
                    .e($plainBody)
                    .'</div>'
                    .'<div style="margin-top:18px;">'
                    .'<img src="'.e($this->marketingDebugImageUrl()).'" width="320" alt="Faisal Imtiaz" style="display:block;width:320px;max-width:100%;height:auto;border:0;">'
                    .'</div>'
                    .'<img src="'.e($trackingUrl).'" width="1" height="1" alt="" style="width:1px;height:1px;border:0;opacity:0;">';

                Mail::send([], [], function ($message) use ($recipient, $request, $attachmentPath, $attachmentName, $unsubscribeUrl, $plainBody, $htmlBody) {
                    $message->to($recipient)
                        ->subject($request->input('subject'))
                        ->text($plainBody)
                        ->html($htmlBody);

                    $headers = $message->getSymfonyMessage()->getHeaders();
                    $headers->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                    $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

                    if ($attachmentPath) {
                        $message->attach(Storage::path($attachmentPath), ['as' => $attachmentName]);
                    }
                });
            }
        } catch (\Throwable $exception) {
            if ($attachmentPath && $deleteAttachmentOnFailure) {
                Storage::delete($attachmentPath);
            }

            $marketingEmail->update([
                'attachment_path' => null,
                'attachment_name' => null,
                'delivery_status' => 'failed',
                'delivery_error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('marketing_error', 'Email could not be sent. Please check your mail settings and try again.');
        }

        $marketingEmail->update([
            'delivery_status' => 'delivered',
            'delivery_error' => null,
            'sent_at' => now(),
        ]);

        return redirect()
            ->route('marketing', ['tab' => 'dashboard'])
            ->with('marketing_success', 'Email sent to '.$recipients->count().' contact'.($recipients->count() === 1 ? '.' : 's.'));
    }

    public function storeMarketingFollowup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'marketing_email_id' => ['required', 'integer', 'exists:marketing_emails,id'],
            'template_id' => ['required', 'integer', 'exists:marketing_templates,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $validator->validate();

        try {
            MarketingFollowupEmail::query()->limit(1)->exists();
            Schema::hasColumn('marketing_followup_emails', 'scheduled_at') || throw new \RuntimeException('Marketing follow-up storage is missing.');
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('marketing_error', 'Follow-up storage is not ready yet. Please run the migration after your database credentials are configured.');
        }

        $originalEmail = MarketingEmail::findOrFail($request->integer('marketing_email_id'));
        $template = MarketingTemplate::findOrFail($request->integer('template_id'));
        $subjects = collect($template->subject_options ?: [$template->subject])
            ->map(fn ($subject) => trim((string) $subject))
            ->filter()
            ->values();

        MarketingFollowupEmail::create([
            'marketing_email_id' => $originalEmail->id,
            'marketing_template_id' => $template->id,
            'recipients' => $originalEmail->recipients ?: [],
            'recipient_count' => $originalEmail->recipient_count,
            'subject' => $subjects->first() ?: $template->subject,
            'body' => $template->content,
            'attachment_path' => $template->attachment_path,
            'attachment_name' => $template->attachment_name,
            'status' => 'pending',
            'scheduled_at' => $request->date('scheduled_at'),
        ]);

        return redirect()
            ->route('marketing', ['tab' => 'followups'])
            ->with('marketing_success', 'Follow-up email scheduled.');
    }

    public function updateMarketingFollowup(Request $request, MarketingFollowupEmail $followup)
    {
        if ($followup->status === 'sent') {
            return redirect()
                ->route('marketing', ['tab' => 'followups'])
                ->with('marketing_error', 'Sent follow-up emails cannot be edited.');
        }

        $validator = Validator::make($request->all(), [
            'template_id' => ['required', 'integer', 'exists:marketing_templates,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $validator->validate();

        $template = MarketingTemplate::findOrFail($request->integer('template_id'));
        $subjects = collect($template->subject_options ?: [$template->subject])
            ->map(fn ($subject) => trim((string) $subject))
            ->filter()
            ->values();

        $followup->update([
            'marketing_template_id' => $template->id,
            'subject' => $subjects->first() ?: $template->subject,
            'body' => $template->content,
            'attachment_path' => $template->attachment_path,
            'attachment_name' => $template->attachment_name,
            'status' => 'pending',
            'scheduled_at' => $request->date('scheduled_at'),
            'sent_at' => null,
            'delivery_error' => null,
        ]);

        return redirect()
            ->route('marketing', ['tab' => 'followups'])
            ->with('marketing_success', 'Follow-up email updated.');
    }

    public function deleteMarketingFollowup(MarketingFollowupEmail $followup)
    {
        $followup->delete();

        return redirect()
            ->route('marketing', ['tab' => 'followups'])
            ->with('marketing_success', 'Follow-up email removed.');
    }

    public function storeMarketingTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'subjects' => ['required', 'array', 'max:5'],
            'subjects.*' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $subjects = $this->normalizeMarketingTemplateSubjects($request->input('subjects', []));

        if ($subjects->isEmpty()) {
            $validator->after(function ($validator) {
                $validator->errors()->add('subjects.0', 'Add at least one email title.');
            });
        }

        $validator->validate();

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('marketing-template-attachments');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        }

        try {
            MarketingTemplate::create([
                'name' => $request->input('name'),
                'subject' => $subjects->first(),
                'subject_options' => $subjects->all(),
                'content' => $request->input('content'),
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
            ]);
        } catch (\Throwable $exception) {
            if ($attachmentPath) {
                Storage::delete($attachmentPath);
            }

            return back()
                ->withInput()
                ->with('marketing_error', 'Template could not be saved. Please run the migration after your database credentials are configured.');
        }

        return redirect()
            ->route('marketing', ['tab' => 'templates-list'])
            ->with('marketing_success', 'Template saved.');
    }

    public function updateMarketingTemplate(Request $request, MarketingTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'subjects' => ['required', 'array', 'max:5'],
            'subjects.*' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'remove_attachment' => ['nullable', 'boolean'],
        ]);

        $subjects = $this->normalizeMarketingTemplateSubjects($request->input('subjects', []));

        if ($subjects->isEmpty()) {
            $validator->after(function ($validator) {
                $validator->errors()->add('subjects.0', 'Add at least one email title.');
            });
        }

        $validator->validate();

        $attachmentPath = $template->attachment_path;
        $attachmentName = $template->attachment_name;
        $originalAttachmentPath = $attachmentPath;
        $oldAttachmentPath = null;

        if ($request->boolean('remove_attachment')) {
            $oldAttachmentPath = $attachmentPath;
            $attachmentPath = null;
            $attachmentName = null;
        }

        if ($request->hasFile('attachment')) {
            $oldAttachmentPath = $originalAttachmentPath;
            $attachmentPath = $request->file('attachment')->store('marketing-template-attachments');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        }

        $template->update([
            'name' => $request->input('name'),
            'subject' => $subjects->first(),
            'subject_options' => $subjects->all(),
            'content' => $request->input('content'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        if ($oldAttachmentPath) {
            Storage::delete($oldAttachmentPath);
        }

        return redirect()
            ->route('marketing', ['tab' => 'templates-list'])
            ->with('marketing_success', 'Template updated.');
    }

    public function deleteMarketingTemplate(MarketingTemplate $template)
    {
        if ($template->attachment_path) {
            Storage::delete($template->attachment_path);
        }

        $template->delete();

        return redirect()
            ->route('marketing', ['tab' => 'templates-list'])
            ->with('marketing_success', 'Template deleted.');
    }

    public function trackMarketingOpen(Request $request, string $trackingId)
    {
        try {
            $tracker = MarketingEmailOpen::where('tracking_id', $trackingId)->first();

            if ($tracker) {
                $tracker->forceFill([
                    'opened_at' => $tracker->opened_at ?: now(),
                    'last_opened_at' => now(),
                    'open_count' => $tracker->open_count + 1,
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ])->save();
            }
        } catch (\Throwable $exception) {
        }

        $pixel = base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==');

        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    private function normalizeMarketingTemplateSubjects($subjects)
    {
        if (! is_array($subjects)) {
            $subjects = [];
        }

        return collect($subjects)
            ->map(fn ($subject) => trim((string) $subject))
            ->filter()
            ->unique()
            ->take(5)
            ->values();
    }

    private function websiteAnalyticsSessionId(Request $request): string
    {
        return Str::limit((string) $request->input('session_id', 'unknown'), 80, '');
    }

    private function websiteAnalyticsIpAddress(Request $request): ?string
    {
        $candidates = collect([
            $request->header('CF-Connecting-IP'),
            $request->header('X-Real-IP'),
            ...explode(',', (string) $request->header('X-Forwarded-For')),
            $request->ip(),
        ]);

        return $candidates
            ->map(fn ($ip) => trim((string) $ip))
            ->first(fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP)) ?: null;
    }

    private function websiteAnalyticsActiveGroups($activeVisits)
    {
        return $activeVisits
            ->groupBy(fn ($visit) => implode('|', [
                $visit->country ?: 'Unknown country',
                $visit->city ?: '',
                $visit->path ?: '/',
            ]))
            ->map(function ($visits) {
                $first = $visits->first();
                $location = collect([$first->city, $first->region, $first->country])
                    ->filter()
                    ->join(', ');

                return [
                    'count' => $visits->count(),
                    'location' => $location ?: 'Unknown location',
                    'page' => $this->websiteAnalyticsPageLabel($first->path ?: '/'),
                    'last_seen_at' => $visits->max('last_seen_at'),
                ];
            })
            ->sortByDesc('last_seen_at')
            ->values();
    }

    private function websiteAnalyticsPageLabel(string $path): string
    {
        return match ($path) {
            '/', '' => 'homepage',
            '/aboutme', 'aboutme' => 'about page',
            default => trim($path, '/') ?: 'homepage',
        };
    }

    private function websiteAnalyticsLocation(?string $ipAddress): array
    {
        $empty = [
            'country' => null,
            'region' => null,
            'city' => null,
            'postal' => null,
            'timezone' => null,
            'latitude' => null,
            'longitude' => null,
            'organization' => null,
        ];

        if ($ipAddress && ! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return $empty;
        }

        try {
            $position = filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                ? Location::get($ipAddress)
                : Location::get();

            if (! $position) {
                return $empty;
            }

            return [
                'country' => Str::limit((string) ($position->countryName ?? $position->countryCode ?? ''), 255, '') ?: null,
                'region' => Str::limit((string) ($position->regionName ?? ''), 255, '') ?: null,
                'city' => Str::limit((string) ($position->cityName ?? ''), 255, '') ?: null,
                'postal' => Str::limit((string) ($position->zipCode ?? ''), 255, '') ?: null,
                'timezone' => Str::limit((string) ($position->timezone ?? ''), 255, '') ?: null,
                'latitude' => is_numeric($position->latitude ?? null) ? $position->latitude : null,
                'longitude' => is_numeric($position->longitude ?? null) ? $position->longitude : null,
                'organization' => null,
            ];
        } catch (\Throwable $exception) {
            return $empty;
        }
    }

    private function websiteAnalyticsMachine(string $userAgent): array
    {
        $browser = $this->websiteAnalyticsBrowser($userAgent);

        return [
            'browser' => $browser['name'],
            'browser_version' => $browser['version'],
            'operating_system' => $this->websiteAnalyticsOperatingSystem($userAgent),
            'device_type' => $this->websiteAnalyticsDeviceType($userAgent),
        ];
    }

    private function websiteAnalyticsBrowser(string $userAgent): array
    {
        $browsers = [
            'Edg' => 'Microsoft Edge',
            'OPR' => 'Opera',
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'Version' => 'Safari',
            'Safari' => 'Safari',
        ];

        foreach ($browsers as $token => $name) {
            if (preg_match('/'.$token.'\/([0-9.]+)/', $userAgent, $matches)) {
                if ($token === 'Safari' && str_contains($userAgent, 'Chrome')) {
                    continue;
                }

                return ['name' => $name, 'version' => $matches[1] ?? null];
            }
        }

        return ['name' => 'Unknown browser', 'version' => null];
    }

    private function websiteAnalyticsOperatingSystem(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Windows NT 10') => 'Windows 10/11',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS/iPadOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };
    }

    private function websiteAnalyticsDeviceType(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet') => 'Tablet',
            str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'Android') => 'Mobile',
            default => 'Desktop',
        };
    }

    private function marketingTrackingUrl(string $trackingId): string
    {
        return route('marketing.open', ['trackingId' => $trackingId], true);
    }

    private function marketingDebugImageUrl(): string
    {
        $path = $this->marketingDebugImagePath();

        return route('marketing.debug-image', ['v' => file_exists($path) ? filemtime($path) : time()], true);
    }

    public function marketingDebugImage()
    {
        $path = $this->marketingDebugImagePath();

        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function marketingDebugImagePath(): string
    {
        return public_path('assets/faisalimtiaz/email-logo.png');
    }

    public function unsubscribeMarketingEmail(Request $request)
    {
        $email = strtolower(trim((string) $request->query('email')));

        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL), 404);

        MarketingUnsubscribe::updateOrCreate(
            ['email' => $email],
            ['unsubscribed_at' => now()]
        );

        if ($request->isMethod('post')) {
            return response('', 204);
        }

        return view('marketing-unsubscribed', ['email' => $email]);
    }
}
